<?php
/**
 * Admin Service Add Page - Services Database Integration
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

// URL slug oluşturma
function generate_slug($string) {
    $string = trim($string);
    $string = mb_strtolower($string, 'UTF-8');
    
    // Türkçe karakterleri değiştir
    $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü'];
    $english = ['c', 'g', 'i', 'o', 's', 'u'];
    $string = str_replace($turkish, $english, $string);
    
    // Özel karakterleri kaldır
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    
    return $string;
}

$success = '';
$error = '';

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
            
            // Check if slug exists
            $stmt = $pdo->prepare("SELECT id FROM services WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug .= '-' . time();
            }
            
            // Create upload directory if it doesn't exist
            $upload_dir = '../../uploads/services/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Handle file upload
            $image_filename = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    if ($file['size'] <= 5 * 1024 * 1024) { // 5MB
                        $image_filename = time() . '_' . uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $image_filename;
                        
                        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                            $error = 'Dosya yükleme hatası!';
                        }
                    } else {
                        $error = 'Dosya boyutu çok büyük! (Max 5MB)';
                    }
                } else {
                    $error = 'Geçersiz dosya türü! Sadece JPG, PNG, GIF ve WebP dosyaları kabul edilir.';
                }
            }
            
            // Convert features array to JSON
            $features_json = !empty($features) ? json_encode(array_filter($features), JSON_UNESCAPED_UNICODE) : null;
            
            if (empty($error)) {
                try {
                    $sql = "INSERT INTO services (
                        title, slug, short_description, full_description, icon, image, 
                        price_text, features, category_id, sort_order, status,
                        seo_title, seo_description, seo_keywords, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                    )";
                    
                    $stmt = $pdo->prepare($sql);
                    $result = $stmt->execute([
                        $title, $slug, $short_description, $full_description, $icon, $image_filename,
                        $price_text, $features_json, $category_id > 0 ? $category_id : null, $sort_order, $status,
                        $seo_title, $seo_description, $seo_keywords
                    ]);
                    
                    if ($result) {
                        $_SESSION['admin_success'] = 'Hizmet başarıyla eklendi!';
                        header('Location: index.php');
                        exit();
                    } else {
                        $error = 'Hizmet eklenirken hata oluştu!';
                        
                        // Delete uploaded file on error
                        if ($image_filename && file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                    }
                } catch (PDOException $e) {
                    $error = 'Veritabanı hatası: ' . $e->getMessage();
                    
                    // Delete uploaded file on error
                    if ($image_filename && file_exists($upload_dir . $image_filename)) {
                        unlink($upload_dir . $image_filename);
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
                    <h5 class="card-title mb-0">Yeni Hizmet Ekle</h5>
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
                            <label for="short_description" class="form-label">Kısa Açıklama *</label>
                            <textarea class="form-control" id="short_description" name="short_description" 
                                      rows="3" required maxlength="500"><?php echo isset($_POST['short_description']) ? escape_output($_POST['short_description']) : ''; ?></textarea>
                            <div class="form-text">Hizmet kartında görünecek kısa açıklama</div>
                        </div>
                        
                        <div class="col-12">
                            <label for="full_description" class="form-label">Detaylı Açıklama</label>
                            <textarea class="form-control" id="full_description" name="full_description" 
                                      rows="5"><?php echo isset($_POST['full_description']) ? escape_output($_POST['full_description']) : ''; ?></textarea>
                            <div class="form-text">Hizmet detay sayfasında görünecek açıklama</div>
                        </div>
                        
                        <!-- Visual Elements -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4">Görsel Öğeler</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="image" class="form-label">Hizmet Görseli</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">JPG, PNG, GIF veya WebP formatında, maksimum 5MB</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="icon" class="form-label">İkon (FontAwesome)</label>
                            <input type="text" class="form-control" id="icon" name="icon" 
                                   value="<?php echo isset($_POST['icon']) ? escape_output($_POST['icon']) : ''; ?>" 
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
                                   value="<?php echo isset($_POST['price_text']) ? escape_output($_POST['price_text']) : ''; ?>" 
                                   placeholder="Aylık abonelik, Paket başı..." maxlength="100">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="sort_order" class="form-label">Sıralama</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                   value="<?php echo isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0; ?>" 
                                   min="0">
                            <div class="form-text">Küçük sayılar daha önce görünür</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Hizmet Özellikleri</label>
                            <div id="features-container">
                                <?php 
                                $existing_features = isset($_POST['features']) ? $_POST['features'] : [''];
                                foreach ($existing_features as $index => $feature): 
                                ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="features[]" 
                                               value="<?php echo escape_output($feature); ?>" 
                                               placeholder="Hizmet özelliği girin...">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
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
                                   value="<?php echo isset($_POST['seo_title']) ? escape_output($_POST['seo_title']) : ''; ?>" 
                                   maxlength="255">
                            <div class="form-text">Boş bırakılırsa hizmet başlığı kullanılır</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="seo_keywords" class="form-label">SEO Anahtar Kelimeler</label>
                            <input type="text" class="form-control" id="seo_keywords" name="seo_keywords" 
                                   value="<?php echo isset($_POST['seo_keywords']) ? escape_output($_POST['seo_keywords']) : ''; ?>" 
                                   placeholder="hizmet, casino, yayın..." maxlength="255">
                        </div>
                        
                        <div class="col-12">
                            <label for="seo_description" class="form-label">SEO Açıklaması</label>
                            <textarea class="form-control" id="seo_description" name="seo_description" 
                                      rows="3" maxlength="255"><?php echo isset($_POST['seo_description']) ? escape_output($_POST['seo_description']) : ''; ?></textarea>
                            <div class="form-text">Arama motorlarında görünecek açıklama</div>
                        </div>
                        
                        <!-- Status -->
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3 mt-4">Durum</h6>
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
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Hizmeti Kaydet
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

// Icon preview
document.getElementById('icon').addEventListener('input', function() {
    const iconClass = this.value;
    const preview = document.getElementById('icon-preview');
    if (preview) {
        preview.className = iconClass || 'fas fa-question';
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>