<?php
/**
 * Admin Gallery Add Page - SQLite Database Integration
 */

require_once '../../includes/config.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

// CSRF token validasyonu
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

$success = '';
$error = '';

// Kategorileri getir
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE type = 'gallery' ORDER BY sort_order ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $error = 'Kategoriler yüklenirken hata oluştu.';
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validate_csrf_token($csrf_token)) {
        $error = 'Güvenlik hatası!';
    } else {
        $media_type = $_POST['media_type'] ?? 'photo';
        $title = sanitize_input($_POST['title'] ?? '');
        $description = $_POST['description'] ?? '';
        $category_id = (int)($_POST['category_id'] ?? 0);
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        // Validasyon
        if (empty($title)) {
            $error = 'Başlık gereklidir.';
        } else {
            if ($media_type === 'photo') {
                // Fotoğraf ekleme
                if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Fotoğraf dosyası gereklidir.';
                } else {
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($file_extension, $allowed_types)) {
                        $error = 'Geçersiz fotoğraf formatı. İzin verilen formatlar: ' . implode(', ', $allowed_types);
                    } else {
                        $image_filename = time() . '_' . uniqid() . '.' . $file_extension;
                        $upload_path = '../../assets/uploads/gallery/' . $image_filename;
                        
                        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                            $error = 'Fotoğraf yüklenirken hata oluştu.';
                        } else {
                            // Thumbnail oluştur (basit kopya)
                            $thumbnail_filename = 'thumb_' . $image_filename;
                            $thumbnail_path = '../../assets/uploads/gallery/' . $thumbnail_filename;
                            copy($upload_path, $thumbnail_path);
                            
                            // Veritabanına ekle
                            try {
                                $stmt = $pdo->prepare("
                                    INSERT INTO gallery_photos (
                                        title, description, image, thumbnail, alt_text, category_id,
                                        sort_order, status, created_at, updated_at
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                                ");
                                
                                $stmt->execute([
                                    $title, $description, $image_filename, $thumbnail_filename, $title,
                                    $category_id > 0 ? $category_id : null, $sort_order, $status
                                ]);
                                
                                $success = 'Fotoğraf başarıyla eklendi!';
                                write_log("Photo added: $title", 'info');
                                $_POST = [];
                                
                            } catch (PDOException $e) {
                                $error = 'Veritabanı hatası: ' . $e->getMessage();
                                write_log("Photo add error: " . $e->getMessage(), 'error');
                            }
                        }
                    }
                }
            } else {
                // Video ekleme
                $video_url = sanitize_input($_POST['video_url'] ?? '');
                $video_type = $_POST['video_type'] ?? 'youtube';
                $duration = sanitize_input($_POST['duration'] ?? '');
                
                if (empty($video_url)) {
                    $error = 'Video URL\'si gereklidir.';
                } else {
                    // Thumbnail yükleme (video için)
                    $thumbnail_filename = '';
                    if (isset($_FILES['video_thumbnail']) && $_FILES['video_thumbnail']['error'] === UPLOAD_ERR_OK) {
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $file_extension = strtolower(pathinfo($_FILES['video_thumbnail']['name'], PATHINFO_EXTENSION));
                        
                        if (in_array($file_extension, $allowed_types)) {
                            $thumbnail_filename = 'video_thumb_' . time() . '_' . uniqid() . '.' . $file_extension;
                            $thumbnail_path = '../../assets/uploads/gallery/' . $thumbnail_filename;
                            move_uploaded_file($_FILES['video_thumbnail']['tmp_name'], $thumbnail_path);
                        }
                    }
                    
                    // Veritabanına ekle
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO gallery_videos (
                                title, description, video_url, video_type, thumbnail, duration,
                                category_id, sort_order, status, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                        ");
                        
                        $stmt->execute([
                            $title, $description, $video_url, $video_type, $thumbnail_filename, $duration,
                            $category_id > 0 ? $category_id : null, $sort_order, $status
                        ]);
                        
                        $success = 'Video başarıyla eklendi!';
                        write_log("Video added: $title", 'info');
                        $_POST = [];
                        
                    } catch (PDOException $e) {
                        $error = 'Veritabanı hatası: ' . $e->getMessage();
                        write_log("Video add error: " . $e->getMessage(), 'error');
                    }
                }
            }
        }
    }
}

include '../includes/admin_header.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 page-title">Galeri Ekle</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Galeri</a></li>
                            <li class="breadcrumb-item active">Yeni Medya</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($success)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Medya Bilgileri</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="galleryForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="media_type" class="form-label">Medya Türü <span class="text-danger">*</span></label>
                                        <select class="form-select" id="media_type" name="media_type" required onchange="toggleMediaType()">
                                            <option value="photo" <?php echo (($_POST['media_type'] ?? 'photo') == 'photo') ? 'selected' : ''; ?>>Fotoğraf</option>
                                            <option value="video" <?php echo (($_POST['media_type'] ?? '') == 'video') ? 'selected' : ''; ?>>Video</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Kategori</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Kategori Seçin</option>
                                            <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo escape_output($category['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo escape_output($_POST['title'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo escape_output($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <!-- Fotoğraf Alanları -->
                            <div id="photo_fields" style="display: <?php echo (($_POST['media_type'] ?? 'photo') == 'photo') ? 'block' : 'none'; ?>;">
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Fotoğraf Dosyası <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                    <div class="form-text">İzin verilen formatlar: JPG, JPEG, PNG, GIF, WebP. Maksimum boyut: 5MB</div>
                                </div>
                            </div>

                            <!-- Video Alanları -->
                            <div id="video_fields" style="display: <?php echo (($_POST['media_type'] ?? '') == 'video') ? 'block' : 'none'; ?>;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="video_url" class="form-label">Video URL'si <span class="text-danger">*</span></label>
                                            <input type="url" class="form-control" id="video_url" name="video_url" 
                                                   value="<?php echo escape_output($_POST['video_url'] ?? ''); ?>" 
                                                   placeholder="https://www.youtube.com/watch?v=...">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="video_type" class="form-label">Video Türü</label>
                                            <select class="form-select" id="video_type" name="video_type">
                                                <option value="youtube" <?php echo (($_POST['video_type'] ?? 'youtube') == 'youtube') ? 'selected' : ''; ?>>YouTube</option>
                                                <option value="vimeo" <?php echo (($_POST['video_type'] ?? '') == 'vimeo') ? 'selected' : ''; ?>>Vimeo</option>
                                                <option value="upload" <?php echo (($_POST['video_type'] ?? '') == 'upload') ? 'selected' : ''; ?>>Yüklenmiş</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Video Süresi</label>
                                            <input type="text" class="form-control" id="duration" name="duration" 
                                                   value="<?php echo escape_output($_POST['duration'] ?? ''); ?>" 
                                                   placeholder="3:45">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="video_thumbnail" class="form-label">Video Kapak Resmi</label>
                                            <input type="file" class="form-control" id="video_thumbnail" name="video_thumbnail" accept="image/*">
                                            <div class="form-text">Opsiyonel. Otomatik thumbnail kullanılacak.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label">Sıralama</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                               value="<?php echo escape_output($_POST['sort_order'] ?? 0); ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Durum</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo (($_POST['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="inactive" <?php echo (($_POST['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Pasif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i>Geri Dön
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Medyayı Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMediaType() {
    const mediaType = document.getElementById('media_type').value;
    const photoFields = document.getElementById('photo_fields');
    const videoFields = document.getElementById('video_fields');
    
    if (mediaType === 'photo') {
        photoFields.style.display = 'block';
        videoFields.style.display = 'none';
        document.getElementById('photo').required = true;
        document.getElementById('video_url').required = false;
    } else {
        photoFields.style.display = 'none';
        videoFields.style.display = 'block';
        document.getElementById('photo').required = false;
        document.getElementById('video_url').required = true;
    }
}

// Sayfa yüklendiğinde doğru alanları göster
document.addEventListener('DOMContentLoaded', function() {
    toggleMediaType();
});
</script>

<?php include '../includes/admin_footer.php'; ?>