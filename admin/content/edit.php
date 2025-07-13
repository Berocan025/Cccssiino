<?php
/**
 * Admin Service Edit Page - Services Database Integration
 */

require_once '../includes/admin_config.php';
require_admin_login();

$success = '';
$error = '';
$service = null;

// Get service ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit();
}

// Get service data
try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    
    if (!$service) {
        $_SESSION['admin_error'] = 'Hizmet bulunamadı!';
        header('Location: index.php');
        exit();
    }
    
    // Decode features JSON
    if ($service['features']) {
        $service['features'] = json_decode($service['features'], true) ?: [];
    } else {
        $service['features'] = [];
    }
} catch (PDOException $e) {
    $_SESSION['admin_error'] = 'Veri alınamadı: ' . $e->getMessage();
    header('Location: index.php');
    exit();
}

// Get categories for dropdown
try {
    $categories_stmt = $pdo->query("SELECT * FROM categories WHERE type = 'service' ORDER BY name");
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
        $short_description = trim($_POST['short_description'] ?? '');
        $full_description = trim($_POST['full_description'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $price_text = trim($_POST['price_text'] ?? '');
        $features = $_POST['features'] ?? [];
        $category_id = (int)($_POST['category_id'] ?? 0);
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $seo_title = trim($_POST['seo_title'] ?? '');
        $seo_description = trim($_POST['seo_description'] ?? '');
        $seo_keywords = trim($_POST['seo_keywords'] ?? '');
        
        // Validation
        if (empty($title)) {
            $error = 'Hizmet başlığı gereklidir!';
        } elseif (empty($short_description)) {
            $error = 'Kısa açıklama gereklidir!';
        } else {
            // Generate slug
            $slug = generate_slug($title);
            
            // Check if slug exists (exclude current service)
            $stmt = $pdo->prepare("SELECT id FROM services WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
            if ($stmt->fetch()) {
                $slug .= '-' . time();
            }
            
            // Create upload directory if it doesn't exist
            $upload_dir = '../../uploads/services/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Handle file upload
            $image_filename = $service['image']; // Keep existing image by default
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    if ($file['size'] <= 5 * 1024 * 1024) { // 5MB
                        $new_image_filename = time() . '_' . uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_image_filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                            // Delete old image
                            if ($service['image'] && file_exists($upload_dir . $service['image'])) {
                                unlink($upload_dir . $service['image']);
                            }
                            $image_filename = $new_image_filename;
                        } else {
                            $error = 'Dosya yükleme hatası!';
                        }
                    } else {
                        $error = 'Dosya boyutu çok büyük! (Max 5MB)';
                    }
                } else {
                    $error = 'Geçersiz dosya türü! Sadece JPG, PNG, GIF ve WebP dosyaları kabul edilir.';
                }
            }
            
            // Handle image deletion
            if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
                if ($service['image'] && file_exists($upload_dir . $service['image'])) {
                    unlink($upload_dir . $service['image']);
                }
                $image_filename = '';
            }
            
            // Convert features array to JSON
            $features_json = !empty($features) ? json_encode(array_filter($features), JSON_UNESCAPED_UNICODE) : null;
            
            if (empty($error)) {
                try {
                    $sql = "UPDATE services SET 
                        title = ?, slug = ?, short_description = ?, full_description = ?, 
                        icon = ?, image = ?, price_text = ?, features = ?, 
                        category_id = ?, sort_order = ?, status = ?,
                        seo_title = ?, seo_description = ?, seo_keywords = ?, updated_at = NOW()
                        WHERE id = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $result = $stmt->execute([
                        $title, $slug, $short_description, $full_description, 
                        $icon, $image_filename, $price_text, $features_json,
                        $category_id > 0 ? $category_id : null, $sort_order, $status,
                        $seo_title, $seo_description, $seo_keywords, $id
                    ]);
                    
                    if ($result) {
                        $_SESSION['admin_success'] = 'Hizmet başarıyla güncellendi!';
                        header('Location: index.php');
                        exit();
                    } else {
                        $error = 'Hizmet güncellenirken hata oluştu!';
                    }
                } catch (PDOException $e) {
                    $error = 'Veritabanı hatası: ' . $e->getMessage();
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
                    <h5 class="card-title mb-0">Hizmet Düzenle: <?php echo escape_output($service['title']); ?></h5>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Geri Dön
                    </a>
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
                        
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Temel Bilgiler</h6>
                        </div>
                        
                        <div class="col-md-8">
                            <label for="title" class="form-label">Hizmet Başlığı *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo escape_output($service['title']); ?>" 
                                   required maxlength="255">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Kategori Seçin</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $service['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape_output($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label for="short_description" class="form-label">Kısa Açıklama *</label>
                            <textarea class="form-control" id="short_description" name="short_description" 
                                      rows="3" required maxlength="500"><?php echo escape_output($service['short_description']); ?></textarea>
                            <div class="form-text">Hizmet kartında görünecek kısa açıklama</div>
                        </div>
                        
                        <div class="col-12">
                            <label for="full_description" class="form-label">Detaylı Açıklama</label>
                            <textarea class="form-control" id="full_description" name="full_description" 
                                      rows="5"><?php echo escape_output($service['full_description']); ?></textarea>
                            <div class="form-text">Hizmet detay sayfasında görünecek açıklama</div>
                        </div>
                        
                        <!-- Visual Elements -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4">Görsel Öğeler</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="image" class="form-label">Hizmet Görseli</label>
                            <?php if ($service['image']): ?>
                                <div class="current-image mb-2">
                                    <img src="../../uploads/services/<?php echo escape_output($service['image']); ?>" 
                                         alt="Mevcut Görsel" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="delete_image" name="delete_image">
                                        <label class="form-check-label" for="delete_image">
                                            Mevcut görseli sil
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">JPG, PNG, GIF veya WebP formatında, maksimum 5MB</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="icon" class="form-label">İkon (FontAwesome)</label>
                            <input type="text" class="form-control" id="icon" name="icon" 
                                   value="<?php echo escape_output($service['icon']); ?>" 
                                   placeholder="fas fa-cogs" maxlength="100">
                            <div class="form-text">Örnek: fas fa-cogs, fab fa-facebook</div>
                        </div>
                        
                        <!-- Pricing & Features -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4">Fiyatlandırma ve Özellikler</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="price_text" class="form-label">Fiyat Metni</label>
                            <input type="text" class="form-control" id="price_text" name="price_text" 
                                   value="<?php echo escape_output($service['price_text']); ?>" 
                                   placeholder="Aylık abonelik, Paket başı..." maxlength="100">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="sort_order" class="form-label">Sıralama</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                   value="<?php echo (int)$service['sort_order']; ?>" 
                                   min="0">
                            <div class="form-text">Küçük sayılar daha önce görünür</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Hizmet Özellikleri</label>
                            <div id="features-container">
                                <?php if (!empty($service['features'])): ?>
                                    <?php foreach ($service['features'] as $feature): ?>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="features[]" 
                                                   value="<?php echo escape_output($feature); ?>" 
                                                   placeholder="Hizmet özelliği girin...">
                                            <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="features[]" 
                                               placeholder="Hizmet özelliği girin...">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFeature()">
                                <i class="fas fa-plus"></i> Özellik Ekle
                            </button>
                        </div>
                        
                        <!-- SEO Settings -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4">SEO Ayarları</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="seo_title" class="form-label">SEO Başlığı</label>
                            <input type="text" class="form-control" id="seo_title" name="seo_title" 
                                   value="<?php echo escape_output($service['seo_title']); ?>" 
                                   maxlength="255">
                            <div class="form-text">Boş bırakılırsa hizmet başlığı kullanılır</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="seo_keywords" class="form-label">SEO Anahtar Kelimeler</label>
                            <input type="text" class="form-control" id="seo_keywords" name="seo_keywords" 
                                   value="<?php echo escape_output($service['seo_keywords']); ?>" 
                                   placeholder="hizmet, casino, yayın..." maxlength="255">
                        </div>
                        
                        <div class="col-12">
                            <label for="seo_description" class="form-label">SEO Açıklaması</label>
                            <textarea class="form-control" id="seo_description" name="seo_description" 
                                      rows="3" maxlength="255"><?php echo escape_output($service['seo_description']); ?></textarea>
                            <div class="form-text">Arama motorlarında görünecek açıklama</div>
                        </div>
                        
                        <!-- Status -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4">Durum</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label">Yayın Durumu</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo $service['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo $service['status'] === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="col-12">
                            <hr class="my-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
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
function addFeature() {
    const container = document.getElementById('features-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="features[]" placeholder="Hizmet özelliği girin...">
        <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeFeature(button) {
    button.parentElement.remove();
}
</script>

<?php include '../includes/admin_footer.php'; ?>