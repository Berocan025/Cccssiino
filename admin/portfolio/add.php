<?php
/**
 * Admin Portfolio Add Page - SQLite Database Integration
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
    $stmt = $pdo->query("SELECT * FROM categories WHERE type = 'portfolio' ORDER BY sort_order ASC");
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
        $title = sanitize_input($_POST['title'] ?? '');
        $short_description = sanitize_input($_POST['short_description'] ?? '');
        $full_description = $_POST['full_description'] ?? '';
        $client_name = sanitize_input($_POST['client_name'] ?? '');
        $project_date = $_POST['project_date'] ?? '';
        $project_url = sanitize_input($_POST['project_url'] ?? '');
        $technologies = sanitize_input($_POST['technologies'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $featured = $_POST['featured'] ?? 'no';
        $status = $_POST['status'] ?? 'active';
        $seo_title = sanitize_input($_POST['seo_title'] ?? '');
        $seo_description = sanitize_input($_POST['seo_description'] ?? '');
        $seo_keywords = sanitize_input($_POST['seo_keywords'] ?? '');

        // Validasyon
        if (empty($title)) {
            $error = 'Başlık gereklidir.';
        } elseif (empty($short_description)) {
            $error = 'Kısa açıklama gereklidir.';
        } else {
            // Slug oluştur
            $slug = generate_slug($title);
            
            // Slug benzersizliği kontrolü
            $stmt = $pdo->prepare("SELECT id FROM portfolio WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug .= '-' . time();
            }
            
            // Ana resim yükleme
            $image_filename = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_types)) {
                    $image_filename = time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = '../../assets/uploads/portfolio/' . $image_filename;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $error = 'Ana resim yüklenirken hata oluştu.';
                    }
                } else {
                    $error = 'Geçersiz resim formatı. İzin verilen formatlar: ' . implode(', ', $allowed_types);
                }
            }
            
            // Galeri resimleri yükleme
            $gallery_images = [];
            if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                for ($i = 0; $i < count($_FILES['gallery_images']['name']); $i++) {
                    if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $file_extension = strtolower(pathinfo($_FILES['gallery_images']['name'][$i], PATHINFO_EXTENSION));
                        
                        if (in_array($file_extension, $allowed_types)) {
                            $gallery_filename = time() . '_' . $i . '_' . uniqid() . '.' . $file_extension;
                            $gallery_upload_path = '../../assets/uploads/portfolio/' . $gallery_filename;
                            
                            if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $gallery_upload_path)) {
                                $gallery_images[] = $gallery_filename;
                            }
                        }
                    }
                }
            }
            
            if (empty($error)) {
                try {
                    // Veritabanına ekle
                    $stmt = $pdo->prepare("
                        INSERT INTO portfolio (
                            title, slug, short_description, full_description, client_name, project_date,
                            project_url, image, gallery_images, technologies, category_id, sort_order,
                            featured, status, seo_title, seo_description, seo_keywords, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                    ");
                    
                    $gallery_images_json = !empty($gallery_images) ? json_encode($gallery_images, JSON_UNESCAPED_UNICODE) : null;
                    
                    $stmt->execute([
                        $title, $slug, $short_description, $full_description, $client_name, $project_date ?: null,
                        $project_url, $image_filename, $gallery_images_json, $technologies, 
                        $category_id > 0 ? $category_id : null, $sort_order, $featured, $status,
                        $seo_title, $seo_description, $seo_keywords
                    ]);
                    
                    $success = 'Proje başarıyla eklendi!';
                    
                    // Log yaz
                    write_log("Portfolio added: $title", 'info');
                    
                    // Formu temizle
                    $_POST = [];
                    
                } catch (PDOException $e) {
                    $error = 'Veritabanı hatası: ' . $e->getMessage();
                    write_log("Portfolio add error: " . $e->getMessage(), 'error');
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
                    <h4 class="mb-sm-0 page-title">Yeni Proje Ekle</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Portfolio</a></li>
                            <li class="breadcrumb-item active">Yeni Proje</li>
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
                        <h4 class="card-title">Proje Bilgileri</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Proje Başlığı <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo escape_output($_POST['title'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                                <label for="short_description" class="form-label">Kısa Açıklama <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="2" required><?php echo escape_output($_POST['short_description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="full_description" class="form-label">Detaylı Açıklama</label>
                                <textarea class="form-control" id="full_description" name="full_description" rows="5"><?php echo escape_output($_POST['full_description'] ?? ''); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client_name" class="form-label">Müşteri Adı</label>
                                        <input type="text" class="form-control" id="client_name" name="client_name" 
                                               value="<?php echo escape_output($_POST['client_name'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="project_date" class="form-label">Proje Tarihi</label>
                                        <input type="date" class="form-control" id="project_date" name="project_date" 
                                               value="<?php echo escape_output($_POST['project_date'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="project_url" class="form-label">Proje URL'si</label>
                                        <input type="url" class="form-control" id="project_url" name="project_url" 
                                               value="<?php echo escape_output($_POST['project_url'] ?? ''); ?>" 
                                               placeholder="https://example.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="technologies" class="form-label">Kullanılan Teknolojiler</label>
                                        <input type="text" class="form-control" id="technologies" name="technologies" 
                                               value="<?php echo escape_output($_POST['technologies'] ?? ''); ?>" 
                                               placeholder="PHP, JavaScript, MySQL">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label">Sıralama</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                               value="<?php echo escape_output($_POST['sort_order'] ?? 0); ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="featured" class="form-label">Öne Çıkan</label>
                                        <select class="form-select" id="featured" name="featured">
                                            <option value="no" <?php echo (($_POST['featured'] ?? 'no') == 'no') ? 'selected' : ''; ?>>Hayır</option>
                                            <option value="yes" <?php echo (($_POST['featured'] ?? '') == 'yes') ? 'selected' : ''; ?>>Evet</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Durum</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo (($_POST['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="inactive" <?php echo (($_POST['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Pasif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Ana Proje Resmi</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">İzin verilen formatlar: JPG, JPEG, PNG, GIF, WebP</div>
                            </div>

                            <div class="mb-3">
                                <label for="gallery_images" class="form-label">Galeri Resimleri</label>
                                <input type="file" class="form-control" id="gallery_images" name="gallery_images[]" accept="image/*" multiple>
                                <div class="form-text">Birden fazla resim seçebilirsiniz. İzin verilen formatlar: JPG, JPEG, PNG, GIF, WebP</div>
                            </div>

                            <hr>
                            <h5>SEO Ayarları</h5>

                            <div class="mb-3">
                                <label for="seo_title" class="form-label">SEO Başlık</label>
                                <input type="text" class="form-control" id="seo_title" name="seo_title" 
                                       value="<?php echo escape_output($_POST['seo_title'] ?? ''); ?>" 
                                       maxlength="60">
                                <div class="form-text">Maksimum 60 karakter</div>
                            </div>

                            <div class="mb-3">
                                <label for="seo_description" class="form-label">SEO Açıklama</label>
                                <textarea class="form-control" id="seo_description" name="seo_description" rows="2" 
                                          maxlength="160"><?php echo escape_output($_POST['seo_description'] ?? ''); ?></textarea>
                                <div class="form-text">Maksimum 160 karakter</div>
                            </div>

                            <div class="mb-3">
                                <label for="seo_keywords" class="form-label">SEO Anahtar Kelimeler</label>
                                <input type="text" class="form-control" id="seo_keywords" name="seo_keywords" 
                                       value="<?php echo escape_output($_POST['seo_keywords'] ?? ''); ?>" 
                                       placeholder="proje, web, tasarım">
                                <div class="form-text">Virgülle ayırın</div>
                            </div>

                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i>Geri Dön
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Projeyi Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>