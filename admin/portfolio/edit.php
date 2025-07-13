<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$portfolio_id = (int)($_GET['id'] ?? 0);

if ($portfolio_id <= 0) {
    $_SESSION['admin_error'] = 'Geçersiz portfolio ID.';
    header("Location: index.php");
    exit();
}

// Get portfolio data
try {
    $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ?");
    $stmt->execute([$portfolio_id]);
    $portfolio = $stmt->fetch();
    
    if (!$portfolio) {
        $_SESSION['admin_error'] = 'Portfolio bulunamadı.';
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['admin_error'] = 'Veritabanı hatası.';
    header("Location: index.php");
    exit();
}

$page_title = 'Portfolio Düzenle: ' . $portfolio['title'];
$page_subtitle = 'Portfolio projesini güncelle';
$page_header = true;

$breadcrumbs = [
    ['title' => 'Portfolio Yönetimi', 'url' => 'index.php'],
    ['title' => 'Portfolio Düzenle']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['admin_error'] = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        header("Location: edit.php?id=" . $portfolio_id);
        exit();
    }
    
    $title = sanitize_input($_POST['title'] ?? '');
    $description = $_POST['description'] ?? '';
    $short_description = sanitize_input($_POST['short_description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $client_name = sanitize_input($_POST['client_name'] ?? '');
    $project_url = sanitize_input($_POST['project_url'] ?? '');
    $technologies = sanitize_input($_POST['technologies'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
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
    $image_path = $portfolio['image_path']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/portfolio/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image
                if (!empty($portfolio['image_path']) && file_exists('../../' . $portfolio['image_path'])) {
                    unlink('../../' . $portfolio['image_path']);
                }
                $image_path = 'uploads/portfolio/' . $filename;
            } else {
                $errors[] = 'Dosya yüklenirken hata oluştu.';
            }
        } else {
            $errors[] = 'Geçersiz dosya formatı. JPG, PNG, GIF veya WEBP dosyası yükleyin.';
        }
    }
    
    if (empty($errors)) {
        try {
            // Try with basic fields
            $stmt = $pdo->prepare("UPDATE portfolio SET title = ?, image_path = ?, updated_at = NOW() WHERE id = ?");
            
            if ($stmt->execute([$title, $image_path, $portfolio_id])) {
                $_SESSION['admin_success'] = 'Portfolio başarıyla güncellendi.';
                header("Location: index.php");
                exit();
            } else {
                $errors[] = 'Portfolio güncellenirken hata oluştu.';
            }
        } catch (PDOException $e) {
            // Fallback with just title
            try {
                $stmt = $pdo->prepare("UPDATE portfolio SET title = ? WHERE id = ?");
                if ($stmt->execute([$title, $portfolio_id])) {
                    $_SESSION['admin_success'] = 'Portfolio başarıyla güncellendi.';
                    header("Location: index.php");
                    exit();
                }
            } catch (PDOException $e2) {
                $errors[] = 'Veritabanı hatası: ' . $e2->getMessage();
            }
        }
    }
    
    // Update portfolio data with form data
    $portfolio['title'] = $title;
    $portfolio['description'] = $description;
    $portfolio['short_description'] = $short_description;
    $portfolio['category_id'] = $category_id;
    $portfolio['client_name'] = $client_name;
    $portfolio['project_url'] = $project_url;
    $portfolio['technologies'] = $technologies;
    $portfolio['is_featured'] = $is_featured;
    $portfolio['is_active'] = $is_active;
}

// Get categories
try {
    $stmt = $pdo->query("SELECT * FROM portfolio_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
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
                    <h5 class="card-title mb-0">Portfolio Düzenle</h5>
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
                                    <label for="title">Başlık *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo escape_output($portfolio['title']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="short_description">Kısa Açıklama</label>
                                    <input type="text" class="form-control" id="short_description" name="short_description" 
                                           value="<?php echo escape_output($portfolio['short_description'] ?? ''); ?>" 
                                           maxlength="255" placeholder="Liste görünümünde gösterilecek kısa açıklama">
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Detaylı Açıklama *</label>
                                    <textarea class="form-control" id="description" name="description" rows="10" required><?php echo escape_output($portfolio['description']); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_name">Müşteri Adı</label>
                                            <input type="text" class="form-control" id="client_name" name="client_name" 
                                                   value="<?php echo escape_output($portfolio['client_name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="project_url">Proje URL'si</label>
                                            <input type="url" class="form-control" id="project_url" name="project_url" 
                                                   value="<?php echo escape_output($portfolio['project_url'] ?? ''); ?>" 
                                                   placeholder="https://example.com">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="technologies">Teknolojiler</label>
                                    <input type="text" class="form-control" id="technologies" name="technologies" 
                                           value="<?php echo escape_output($portfolio['technologies'] ?? ''); ?>" 
                                           placeholder="PHP, JavaScript, MySQL, Bootstrap">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="image">Portfolio Görseli</label>
                                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                                    <small class="form-text text-muted">Yeni dosya seçmezseniz mevcut görsel korunur.</small>
                                    
                                    <?php if (!empty($portfolio['image_path'])): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo escape_output('../../' . $portfolio['image_path']); ?>" 
                                                 alt="Mevcut görsel" class="img-thumbnail" style="max-width: 200px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="category_id">Kategori</label>
                                    <select class="form-control" id="category_id" name="category_id">
                                        <option value="0">Kategori Seçin</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo ($portfolio['category_id'] ?? 0) == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo escape_output($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" 
                                               <?php echo $portfolio['is_featured'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Öne Çıkan Proje
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                               <?php echo $portfolio['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Portfolio Güncelle
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

<?php include '../includes/admin_footer.php'; ?>