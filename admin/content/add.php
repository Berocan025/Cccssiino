<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$page_title = 'Yeni Hizmet Ekle';
$page_subtitle = 'Yeni hizmet/içerik oluştur';
$page_header = true;

$breadcrumbs = [
    ['title' => 'İçerik Yönetimi', 'url' => 'index.php'],
    ['title' => 'Yeni Hizmet Ekle']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['admin_error'] = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        header("Location: add.php");
        exit();
    }
    
    $title = sanitize_input($_POST['title'] ?? '');
    $description = $_POST['description'] ?? '';
    $short_description = sanitize_input($_POST['short_description'] ?? '');
    $price = sanitize_input($_POST['price'] ?? '');
    $features = $_POST['features'] ?? '';
    $delivery_time = sanitize_input($_POST['delivery_time'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Başlık gereklidir.';
    }
    
    if (empty($description)) {
        $errors[] = 'Açıklama gereklidir.';
    }
    
    // File upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/services/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'uploads/services/' . $filename;
            } else {
                $errors[] = 'Dosya yüklenirken hata oluştu.';
            }
        } else {
            $errors[] = 'Geçersiz dosya formatı. JPG, PNG, GIF veya WEBP dosyası yükleyin.';
        }
    }
    
    if (empty($errors)) {
        try {
            // Try with all fields first
            $stmt = $pdo->prepare("INSERT INTO services (title, description, short_description, image_path, price, features, delivery_time, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$title, $description, $short_description, $image_path, $price, $features, $delivery_time, $is_active])) {
                $_SESSION['admin_success'] = 'Hizmet başarıyla eklendi.';
                header("Location: index.php");
                exit();
            } else {
                $errors[] = 'Hizmet eklenirken hata oluştu.';
            }
        } catch (PDOException $e) {
            // If some columns don't exist, try with basic fields
            try {
                $stmt = $pdo->prepare("INSERT INTO services (title, description, image_path, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
                
                if ($stmt->execute([$title, $description, $image_path, $is_active])) {
                    $_SESSION['admin_success'] = 'Hizmet başarıyla eklendi.';
                    header("Location: index.php");
                    exit();
                } else {
                    $errors[] = 'Hizmet eklenirken hata oluştu.';
                }
            } catch (PDOException $e2) {
                $errors[] = 'Veritabanı hatası: ' . $e2->getMessage();
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
                <div class="card-header">
                    <h5 class="card-title mb-0">Yeni Hizmet Ekle</h5>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo escape_output($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Hizmet Başlığı *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo escape_output($_POST['title'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="short_description">Kısa Açıklama</label>
                                    <input type="text" class="form-control" id="short_description" name="short_description" 
                                           value="<?php echo escape_output($_POST['short_description'] ?? ''); ?>" 
                                           maxlength="255" placeholder="Liste görünümünde gösterilecek kısa açıklama">
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Detaylı Açıklama *</label>
                                    <textarea class="form-control" id="description" name="description" rows="10" required><?php echo escape_output($_POST['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="features">Özellikler</label>
                                    <textarea class="form-control" id="features" name="features" rows="5" placeholder="Her satıra bir özellik yazın"><?php echo escape_output($_POST['features'] ?? ''); ?></textarea>
                                    <small class="form-text text-muted">Her satıra bir özellik yazın</small>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Fiyat</label>
                                            <input type="text" class="form-control" id="price" name="price" 
                                                   value="<?php echo escape_output($_POST['price'] ?? ''); ?>" 
                                                   placeholder="₺500, Ücretsiz, Teklif Al">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="delivery_time">Teslimat Süresi</label>
                                            <input type="text" class="form-control" id="delivery_time" name="delivery_time" 
                                                   value="<?php echo escape_output($_POST['delivery_time'] ?? ''); ?>" 
                                                   placeholder="1-3 gün, 1 hafta, 2 hafta">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="image">Hizmet Görseli</label>
                                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                                    <small class="form-text text-muted">JPG, PNG, GIF veya WEBP formatında olmalıdır.</small>
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                               <?php echo !isset($_POST) || isset($_POST['is_active']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Aktif
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <small class="font-weight-bold">Önizleme</small>
                                    </div>
                                    <div class="card-body">
                                        <div id="preview_area" class="text-center">
                                            <i class="fas fa-cogs fa-3x text-muted"></i>
                                            <p class="text-muted mt-2">Görsel seçildikten sonra önizleme burada görünecek</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Hizmet Ekle
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// File preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewArea = document.getElementById('preview_area');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewArea.innerHTML = `
                <img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">
                <p class="text-muted mt-2 small">${file.name}</p>
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include '../includes/admin_footer.php'; ?>