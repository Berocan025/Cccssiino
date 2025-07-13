<?php
/**
 * Admin Gallery Add Page - Gallery Photos & Videos Database Integration
 */

require_once '../includes/admin_config.php';
require_admin_login();

$success = '';
$error = '';

// Get type from URL parameter (photo or video)
$type = isset($_GET['type']) ? $_GET['type'] : 'photo';
if (!in_array($type, ['photo', 'video'])) {
    $type = 'photo';
}

// Get categories for dropdown
try {
    $categories_stmt = $pdo->query("SELECT * FROM categories WHERE type = 'gallery' ORDER BY name");
    $categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validate_csrf_token($csrf_token)) {
        $error = 'Güvenlik hatası!';
    } else {
        // Get form data
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $alt_text = trim($_POST['alt_text'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $submit_type = $_POST['type'] ?? $type;
        
        // Validation
        if (empty($title)) {
            $error = 'Başlık gereklidir!';
        } else {
            // Create upload directories if they don't exist
            $upload_dir = '../../uploads/gallery/';
            $thumb_dir = '../../uploads/gallery/thumbs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            if (!file_exists($thumb_dir)) {
                mkdir($thumb_dir, 0755, true);
            }
            
            if ($submit_type === 'photo') {
                // Handle photo upload
                if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Fotoğraf yükleme gereklidir!';
                } else {
                    $file = $_FILES['image'];
                    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        if ($file['size'] <= 10 * 1024 * 1024) { // 10MB
                            $image_filename = time() . '_' . uniqid() . '.' . $file_extension;
                            $upload_path = $upload_dir . $image_filename;
                            
                            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                // Create thumbnail
                                $thumb_filename = 'thumb_' . $image_filename;
                                $thumb_path = $thumb_dir . $thumb_filename;
                                
                                // Simple thumbnail creation (you can improve this with image manipulation library)
                                if (copy($upload_path, $thumb_path)) {
                                    // Insert to database
                                    try {
                                        $sql = "INSERT INTO gallery_photos (
                                            title, description, image, thumbnail, alt_text, 
                                            category_id, sort_order, status, created_at, updated_at
                                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                                        
                                        $stmt = $pdo->prepare($sql);
                                        $result = $stmt->execute([
                                            $title, $description, $image_filename, $thumb_filename, $alt_text,
                                            $category_id > 0 ? $category_id : null, $sort_order, $status
                                        ]);
                                        
                                        if ($result) {
                                            $_SESSION['admin_success'] = 'Fotoğraf başarıyla eklendi!';
                                            header('Location: index.php');
                                            exit();
                                        } else {
                                            $error = 'Fotoğraf kaydedilirken hata oluştu!';
                                            
                                            // Delete uploaded files on error
                                            if (file_exists($upload_path)) unlink($upload_path);
                                            if (file_exists($thumb_path)) unlink($thumb_path);
                                        }
                                    } catch (PDOException $e) {
                                        $error = 'Veritabanı hatası: ' . $e->getMessage();
                                        
                                        // Delete uploaded files on error
                                        if (file_exists($upload_path)) unlink($upload_path);
                                        if (file_exists($thumb_path)) unlink($thumb_path);
                                    }
                                } else {
                                    $error = 'Thumbnail oluşturulamadı!';
                                    if (file_exists($upload_path)) unlink($upload_path);
                                }
                            } else {
                                $error = 'Dosya yükleme hatası!';
                            }
                        } else {
                            $error = 'Dosya boyutu çok büyük! (Max 10MB)';
                        }
                    } else {
                        $error = 'Geçersiz dosya türü! Sadece JPG, PNG, GIF ve WebP dosyaları kabul edilir.';
                    }
                }
            } elseif ($submit_type === 'video') {
                // Handle video data
                $video_url = trim($_POST['video_url'] ?? '');
                $video_type = $_POST['video_type'] ?? 'youtube';
                $duration = trim($_POST['duration'] ?? '');
                
                if (empty($video_url)) {
                    $error = 'Video URL gereklidir!';
                } else {
                    // Handle thumbnail upload for video
                    $thumb_filename = '';
                    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['thumbnail'];
                        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        
                        if (in_array($file_extension, $allowed_extensions)) {
                            if ($file['size'] <= 5 * 1024 * 1024) { // 5MB
                                $thumb_filename = 'video_thumb_' . time() . '_' . uniqid() . '.' . $file_extension;
                                $thumb_path = $thumb_dir . $thumb_filename;
                                
                                if (!move_uploaded_file($file['tmp_name'], $thumb_path)) {
                                    $error = 'Thumbnail yükleme hatası!';
                                }
                            } else {
                                $error = 'Thumbnail boyutu çok büyük! (Max 5MB)';
                            }
                        } else {
                            $error = 'Geçersiz thumbnail türü!';
                        }
                    }
                    
                    if (empty($error)) {
                        // Insert video to database
                        try {
                            $sql = "INSERT INTO gallery_videos (
                                title, description, video_url, video_type, thumbnail, 
                                duration, category_id, sort_order, status, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                            
                            $stmt = $pdo->prepare($sql);
                            $result = $stmt->execute([
                                $title, $description, $video_url, $video_type, $thumb_filename,
                                $duration, $category_id > 0 ? $category_id : null, $sort_order, $status
                            ]);
                            
                            if ($result) {
                                $_SESSION['admin_success'] = 'Video başarıyla eklendi!';
                                header('Location: index.php');
                                exit();
                            } else {
                                $error = 'Video kaydedilirken hata oluştu!';
                                
                                // Delete uploaded thumbnail on error
                                if ($thumb_filename && file_exists($thumb_dir . $thumb_filename)) {
                                    unlink($thumb_dir . $thumb_filename);
                                }
                            }
                        } catch (PDOException $e) {
                            $error = 'Veritabanı hatası: ' . $e->getMessage();
                            
                            // Delete uploaded thumbnail on error
                            if ($thumb_filename && file_exists($thumb_dir . $thumb_filename)) {
                                unlink($thumb_dir . $thumb_filename);
                            }
                        }
                    }
                }
            }
        }
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<?php include '../includes/admin_header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <?php echo $type === 'photo' ? 'Yeni Fotoğraf Ekle' : 'Yeni Video Ekle'; ?>
                    </h5>
                    <div>
                        <a href="add.php?type=photo" class="btn btn-sm <?php echo $type === 'photo' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-image"></i> Fotoğraf
                        </a>
                        <a href="add.php?type=video" class="btn btn-sm <?php echo $type === 'video' ? 'btn-success' : 'btn-outline-success'; ?>">
                            <i class="fas fa-video"></i> Video
                        </a>
                        <a href="index.php" class="btn btn-secondary btn-sm ms-2">
                            <i class="fas fa-arrow-left"></i> Geri Dön
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo escape_output($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Temel Bilgiler</h6>
                        </div>
                        
                        <div class="col-md-8">
                            <label for="title" class="form-label">
                                <?php echo $type === 'photo' ? 'Fotoğraf' : 'Video'; ?> Başlığı *
                            </label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo isset($_POST['title']) ? escape_output($_POST['title']) : ''; ?>" 
                                   required maxlength="255">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Kategori Seçin</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo escape_output($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4"><?php echo isset($_POST['description']) ? escape_output($_POST['description']) : ''; ?></textarea>
                            <div class="form-text">Galeri öğesi hakkında açıklama</div>
                        </div>
                        
                        <?php if ($type === 'photo'): ?>
                            <!-- Photo Upload -->
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Fotoğraf Yükleme</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="image" class="form-label">Fotoğraf Dosyası *</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                <div class="form-text">JPG, PNG, GIF veya WebP formatında, maksimum 10MB</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="alt_text" class="form-label">Alt Text</label>
                                <input type="text" class="form-control" id="alt_text" name="alt_text" 
                                       value="<?php echo isset($_POST['alt_text']) ? escape_output($_POST['alt_text']) : ''; ?>" 
                                       maxlength="255">
                                <div class="form-text">SEO ve erişilebilirlik için alternatif metin</div>
                            </div>
                            
                        <?php else: ?>
                            <!-- Video Information -->
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Video Bilgileri</h6>
                            </div>
                            
                            <div class="col-md-8">
                                <label for="video_url" class="form-label">Video URL *</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" 
                                       value="<?php echo isset($_POST['video_url']) ? escape_output($_POST['video_url']) : ''; ?>" 
                                       required maxlength="255">
                                <div class="form-text">YouTube, Vimeo veya direkt video URL'si</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="video_type" class="form-label">Video Türü</label>
                                <select class="form-select" id="video_type" name="video_type">
                                    <option value="youtube" <?php echo (isset($_POST['video_type']) && $_POST['video_type'] === 'youtube') ? 'selected' : ''; ?>>YouTube</option>
                                    <option value="vimeo" <?php echo (isset($_POST['video_type']) && $_POST['video_type'] === 'vimeo') ? 'selected' : ''; ?>>Vimeo</option>
                                    <option value="upload" <?php echo (isset($_POST['video_type']) && $_POST['video_type'] === 'upload') ? 'selected' : ''; ?>>Yüklenen Dosya</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="duration" class="form-label">Süre</label>
                                <input type="text" class="form-control" id="duration" name="duration" 
                                       value="<?php echo isset($_POST['duration']) ? escape_output($_POST['duration']) : ''; ?>" 
                                       placeholder="00:05:30" maxlength="10">
                                <div class="form-text">Video süresi (örn: 05:30)</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="thumbnail" class="form-label">Video Thumbnail</label>
                                <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                                <div class="form-text">Video önizleme resmi (JPG, PNG, GIF, WebP - Max 5MB)</div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Additional Settings -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4">Ek Ayarlar</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="sort_order" class="form-label">Sıralama</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                   value="<?php echo isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0; ?>" 
                                   min="0">
                            <div class="form-text">Küçük sayılar daha önce görünür</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label">Yayın Durumu</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="col-12">
                            <hr class="my-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-<?php echo $type === 'photo' ? 'primary' : 'success'; ?>">
                                    <i class="fas fa-<?php echo $type === 'photo' ? 'image' : 'video'; ?>"></i> 
                                    <?php echo $type === 'photo' ? 'Fotoğrafı' : 'Videoyu'; ?> Kaydet
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> İptal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview functionality for image uploads
document.getElementById('image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create preview if it doesn't exist
            let preview = document.getElementById('image-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'image-preview';
                preview.className = 'mt-3';
                e.target.closest('.col-md-6').appendChild(preview);
            }
            
            preview.innerHTML = `
                <div class="card" style="max-width: 300px;">
                    <img src="${e.target.result}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Önizleme">
                    <div class="card-body">
                        <small class="text-muted">Önizleme: ${file.name}</small>
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
});

// YouTube video ID extraction and thumbnail preview
document.getElementById('video_url')?.addEventListener('input', function(e) {
    const url = e.target.value;
    let preview = document.getElementById('video-preview');
    
    if (!preview) {
        preview = document.createElement('div');
        preview.id = 'video-preview';
        preview.className = 'mt-3';
        e.target.closest('.col-md-8').appendChild(preview);
    }
    
    // Extract YouTube video ID
    const youtubeRegex = /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/;
    const match = url.match(youtubeRegex);
    
    if (match) {
        const videoId = match[1];
        const thumbnailUrl = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
        
        preview.innerHTML = `
            <div class="card" style="max-width: 300px;">
                <img src="${thumbnailUrl}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Video Önizleme">
                <div class="card-body">
                    <small class="text-muted">YouTube Video Önizleme</small>
                </div>
            </div>
        `;
        
        // Auto-set video type to YouTube
        document.getElementById('video_type').value = 'youtube';
    } else if (url.includes('vimeo.com')) {
        // Auto-set video type to Vimeo
        document.getElementById('video_type').value = 'vimeo';
        preview.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Vimeo video algılandı
            </div>
        `;
    } else if (url) {
        // Other video URL
        preview.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Video türünü manuel olarak belirleyin
            </div>
        `;
    } else {
        preview.innerHTML = '';
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>