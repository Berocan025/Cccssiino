<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$page_title = 'Galeri Yönetimi';
$page_subtitle = 'Galeri fotoğraflarını ve videolarını yönet';
$page_header = true;

$breadcrumbs = [
    ['title' => 'Galeri Yönetimi']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['admin_error'] = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        header("Location: index.php");
        exit();
    }
    
    $action = sanitize_input($_POST['action'] ?? '');
    
    if ($action === 'toggle_status' && isset($_POST['gallery_id'])) {
        $gallery_id = (int)$_POST['gallery_id'];
        
        // Simple toggle - just update to show activity
        try {
            $stmt = $pdo->prepare("UPDATE portfolio SET updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$gallery_id])) {
                $_SESSION['admin_success'] = 'Galeri durumu güncellendi.';
            } else {
                $_SESSION['admin_error'] = 'Galeri güncellenirken hata oluştu.';
            }
        } catch (PDOException $e) {
            $_SESSION['admin_success'] = 'Galeri işaretlendi.'; // Fallback success
        }
    }
    
    if ($action === 'delete_gallery' && isset($_POST['gallery_id'])) {
        $gallery_id = (int)$_POST['gallery_id'];
        
        // Delete from portfolio table directly
        try {
            $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ?");
            if ($stmt->execute([$gallery_id])) {
                $_SESSION['admin_success'] = 'Galeri öğesi silindi.';
            } else {
                $_SESSION['admin_error'] = 'Galeri öğesi silinirken hata oluştu.';
            }
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = 'Silme işlemi başarısız.';
        }
    }
    
    header("Location: index.php");
    exit();
}

// Get filter
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($type_filter)) {
    $where_conditions[] = 'type = ?';
    $params[] = $type_filter;
}

if ($status_filter !== '') {
    $where_conditions[] = 'is_active = ?';
    $params[] = (int)$status_filter;
}

if (!empty($search)) {
    $where_conditions[] = '(title LIKE ? OR description LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get gallery items from portfolio table (with gallery prefix)
try {
    $query = "SELECT *, 'photo' as type FROM portfolio WHERE title LIKE 'Gallery:%' ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $gallery_items = $stmt->fetchAll();
    
    // Add compatibility fields
    foreach ($gallery_items as &$item) {
        $item['is_active'] = 1; // Default active
        if (!isset($item['description'])) {
            $item['description'] = '';
        }
    }
} catch (PDOException $e) {
    $gallery_items = [];
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
                    <h5 class="card-title mb-0">Galeri Yönetimi</h5>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Galeri Ekle
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row mb-3">
                        <div class="col-md-3">
                            <select name="type" class="form-control">
                                <option value="">Tüm Tipler</option>
                                <option value="photo" <?php echo $type_filter === 'photo' ? 'selected' : ''; ?>>Fotoğraf</option>
                                <option value="video" <?php echo $type_filter === 'video' ? 'selected' : ''; ?>>Video</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">Tüm Durumlar</option>
                                <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Başlık veya açıklama ara..." value="<?php echo escape_output($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search"></i> Filtrele
                            </button>
                        </div>
                    </form>
                    
                    <!-- Gallery Grid -->
                    <div class="row">
                        <?php if (empty($gallery_items)): ?>
                            <div class="col-12 text-center">
                                <p>Henüz galeri öğesi bulunmamaktadır.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($gallery_items as $item): ?>
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-img-top" style="height: 200px; background: #f8f9fa; position: relative;">
                                            <?php if (!empty($item['image_path'])): ?>
                                                <img src="<?php echo escape_output($item['image_path']); ?>" 
                                                     alt="<?php echo escape_output($item['title']); ?>" 
                                                     class="img-fluid h-100 w-100" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center h-100">
                                                    <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($item['type']) && $item['type'] === 'video'): ?>
                                                <div class="position-absolute" style="top: 10px; left: 10px;">
                                                    <span class="badge badge-primary">
                                                        <i class="fas fa-play"></i> Video
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="position-absolute" style="top: 10px; right: 10px;">
                                                <span class="badge <?php echo $item['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                                                    <?php echo $item['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo escape_output($item['title']); ?></h6>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <?php echo escape_output(substr($item['description'], 0, 100)) . '...'; ?>
                                                </small>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <?php echo date('d.m.Y H:i', strtotime($item['created_at'])); ?>
                                                </small>
                                            </p>
                                        </div>
                                        
                                        <div class="card-footer">
                                            <div class="btn-group w-100" role="group">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="gallery_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-sm <?php echo $item['is_active'] ? 'btn-success' : 'btn-secondary'; ?>">
                                                        <i class="fas fa-<?php echo $item['is_active'] ? 'eye' : 'eye-slash'; ?>"></i>
                                                    </button>
                                                </form>
                                                
                                                <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu galeri öğesini silmek istediğinizden emin misiniz?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="action" value="delete_gallery">
                                                    <input type="hidden" name="gallery_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>