<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$gallery_id = (int)($_GET['id'] ?? 0);

if ($gallery_id <= 0) {
    $_SESSION['admin_error'] = 'Geçersiz galeri ID.';
    header("Location: index.php");
    exit();
}

// Get gallery data from portfolio table
try {
    $stmt = $pdo->prepare("SELECT * FROM portfolio WHERE id = ? AND title LIKE 'Gallery:%'");
    $stmt->execute([$gallery_id]);
    $gallery = $stmt->fetch();
    
    if (!$gallery) {
        $_SESSION['admin_error'] = 'Galeri öğesi bulunamadı.';
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['admin_error'] = 'Veritabanı hatası.';
    header("Location: index.php");
    exit();
}

$page_title = 'Galeri Düzenle';
$page_subtitle = 'Galeri öğesini güncelle';
$page_header = true;

$breadcrumbs = [
    ['title' => 'Galeri Yönetimi', 'url' => 'index.php'],
    ['title' => 'Galeri Düzenle']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['admin_error'] = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        header("Location: edit.php?id=" . $gallery_id);
        exit();
    }
    
    $title = sanitize_input($_POST['title'] ?? '');
    $description = $_POST['description'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Başlık gereklidir.';
    }
    
    if (empty($errors)) {
        try {
            $gallery_title = 'Gallery: ' . $title;
            
            // Try with description
            try {
                $stmt = $pdo->prepare("UPDATE portfolio SET title = ?, description = ? WHERE id = ?");
                if ($stmt->execute([$gallery_title, $description, $gallery_id])) {
                    $_SESSION['admin_success'] = 'Galeri öğesi başarıyla güncellendi.';
                    header("Location: index.php");
                    exit();
                }
            } catch (PDOException $e) {
                // Try without description
                $stmt = $pdo->prepare("UPDATE portfolio SET title = ? WHERE id = ?");
                if ($stmt->execute([$gallery_title, $gallery_id])) {
                    $_SESSION['admin_success'] = 'Galeri öğesi başarıyla güncellendi.';
                    header("Location: index.php");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Galeri öğesi güncellenirken hata oluştu.';
        }
    }
    
    // Update gallery data with form data
    $gallery['title'] = 'Gallery: ' . $title;
    $gallery['description'] = $description;
}

// Clean title for display (remove Gallery: prefix)
$display_title = str_replace('Gallery: ', '', $gallery['title']);

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<?php include '../includes/admin_header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Galeri Düzenle</h5>
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
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label for="title">Başlık *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo escape_output($display_title); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?php echo escape_output($gallery['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Galeri Güncelle
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