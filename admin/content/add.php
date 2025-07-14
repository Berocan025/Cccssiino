<?php
/**
 * Admin Service Add Page - SQLite Database Integration
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

// Kategorileri getir
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE type = 'service' ORDER BY sort_order ASC");
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
        $icon = sanitize_input($_POST['icon'] ?? '');
        $price_text = sanitize_input($_POST['price_text'] ?? '');
        $features = $_POST['features'] ?? '';
        $category_id = (int)($_POST['category_id'] ?? 0);
        $sort_order = (int)($_POST['sort_order'] ?? 0);
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
            $stmt = $pdo->prepare("SELECT id FROM services WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug .= '-' . time();
            }
            
            // Resim yükleme
            $image_filename = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_types)) {
                    $image_filename = time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = '../../assets/uploads/' . $image_filename;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $error = 'Resim yüklenirken hata oluştu.';
                    }
                } else {
                    $error = 'Geçersiz resim formatı. İzin verilen formatlar: ' . implode(', ', $allowed_types);
                }
            }
            
            if (empty($error)) {
                try {
                    // Veritabanına ekle
                    $stmt = $pdo->prepare("
                        INSERT INTO services (
                            title, slug, short_description, full_description, icon, image,
                            price_text, features, category_id, sort_order, status,
                            seo_title, seo_description, seo_keywords, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                    ");
                    
                    $features_json = is_array($features) ? json_encode($features, JSON_UNESCAPED_UNICODE) : $features;
                    
                    $stmt->execute([
                        $title, $slug, $short_description, $full_description, $icon, $image_filename,
                        $price_text, $features_json, $category_id > 0 ? $category_id : null, $sort_order, $status,
                        $seo_title, $seo_description, $seo_keywords
                    ]);
                    
                    $success = 'Hizmet başarıyla eklendi!';
                    
                    // Log yaz
                    write_log("Service added: $title", 'info');
                    
                    // Formu temizle
                    $_POST = [];
                    
                } catch (PDOException $e) {
                    $error = 'Veritabanı hatası: ' . $e->getMessage();
                    write_log("Service add error: " . $e->getMessage(), 'error');
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
                    <h4 class="mb-sm-0 page-title">Yeni Hizmet Ekle</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Hizmetler</a></li>
                            <li class="breadcrumb-item active">Yeni Hizmet</li>
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
                        <h4 class="card-title">Hizmet Bilgileri</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo escape_output($_POST['title'] ?? ''); ?>" required>
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
                                <label for="short_description" class="form-label">Kısa Açıklama <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="2" required><?php echo escape_output($_POST['short_description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="full_description" class="form-label">Detaylı Açıklama</label>
                                <textarea class="form-control" id="full_description" name="full_description" rows="5"><?php echo escape_output($_POST['full_description'] ?? ''); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="icon" class="form-label">İkon (Font Awesome)</label>
                                        <input type="text" class="form-control" id="icon" name="icon" 
                                               value="<?php echo escape_output($_POST['icon'] ?? ''); ?>" 
                                               placeholder="fas fa-cogs">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="price_text" class="form-label">Fiyat Metni</label>
                                        <input type="text" class="form-control" id="price_text" name="price_text" 
                                               value="<?php echo escape_output($_POST['price_text'] ?? ''); ?>" 
                                               placeholder="Paket başı fiyatlandırma">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label">Sıralama</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                               value="<?php echo escape_output($_POST['sort_order'] ?? 0); ?>" min="0">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="features" class="form-label">Özellikler (Her satırda bir özellik)</label>
                                <textarea class="form-control" id="features" name="features" rows="4"><?php echo escape_output($_POST['features'] ?? ''); ?></textarea>
                                <div class="form-text">Her satırda bir özellik yazın.</div>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Hizmet Resmi</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">İzin verilen formatlar: JPG, JPEG, PNG, GIF, WebP</div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Durum</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (($_POST['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo (($_POST['status'] ?? '') == 'inactive') ? 'selected' : ''; ?>>Pasif</option>
                                </select>
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
                                       placeholder="casino, bonus, yayın">
                                <div class="form-text">Virgülle ayırın</div>
                            </div>

                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i>Geri Dön
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Hizmeti Kaydet
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