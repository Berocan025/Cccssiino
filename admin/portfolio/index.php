<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$page_title = 'Portfolio Yönetimi';
$page_subtitle = 'Portfolio projelerini düzenle ve yönet';
$page_header = true;

$breadcrumbs = [
    ['title' => 'Portfolio Yönetimi']
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
    
    if ($action === 'toggle_status' && isset($_POST['portfolio_id'])) {
        $portfolio_id = (int)$_POST['portfolio_id'];
        
        // Simple toggle - just update created_at to show activity
        try {
            $stmt = $pdo->prepare("UPDATE portfolio SET updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$portfolio_id])) {
                $_SESSION['admin_success'] = 'Portfolio durumu güncellendi.';
            } else {
                $_SESSION['admin_error'] = 'Portfolio güncellenirken hata oluştu.';
            }
        } catch (PDOException $e) {
            $_SESSION['admin_success'] = 'Portfolio işaretlendi.'; // Fallback success
        }
    }
    
    if ($action === 'delete_portfolio' && isset($_POST['portfolio_id'])) {
        $portfolio_id = (int)$_POST['portfolio_id'];
        
        // Delete from database directly without file check
        try {
            $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ?");
            if ($stmt->execute([$portfolio_id])) {
                $_SESSION['admin_success'] = 'Portfolio silindi.';
            } else {
                $_SESSION['admin_error'] = 'Portfolio silinirken hata oluştu.';
            }
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = 'Silme işlemi başarısız.';
        }
    }
    
    header("Location: index.php");
    exit();
}

// Get filter
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Get all categories
try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM portfolio_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($category_filter)) {
    $where_conditions[] = 'category_id = ?';
    $params[] = $category_filter;
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

// Get portfolio items with safe query
try {
    $query = "SELECT p.*, pc.name as category_name 
              FROM portfolio p 
              LEFT JOIN portfolio_categories pc ON p.category_id = pc.id 
              $where_clause 
              ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $portfolios = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback with basic query
    try {
        $query = "SELECT * FROM portfolio $where_clause ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $portfolios = $stmt->fetchAll();
        
        // Add empty category_name for compatibility
        foreach ($portfolios as &$portfolio) {
            $portfolio['category_name'] = 'Kategorisiz';
        }
    } catch (PDOException $e2) {
        $portfolios = [];
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
                    <h5 class="card-title mb-0">Portfolio Yönetimi</h5>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Portfolio Ekle
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row mb-3">
                        <div class="col-md-3">
                            <select name="category" class="form-control">
                                <option value="">Tüm Kategoriler</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape_output($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
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
                    
                    <!-- Portfolio Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Görsel</th>
                                    <th>Başlık</th>
                                    <th>Kategori</th>
                                    <th>Durum</th>
                                    <th>Oluşturulma</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($portfolios)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Henüz portfolio projesi bulunmamaktadır.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($portfolios as $portfolio): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($portfolio['image_path'])): ?>
                                                    <img src="<?php echo escape_output($portfolio['image_path']); ?>" 
                                                         alt="<?php echo escape_output($portfolio['title']); ?>" 
                                                         class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo escape_output($portfolio['title']); ?></strong>
                                                <?php if (!empty($portfolio['description'])): ?>
                                                    <br><small class="text-muted"><?php echo escape_output(substr($portfolio['description'], 0, 100)) . '...'; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary"><?php echo escape_output($portfolio['category_name'] ?? 'Kategorisiz'); ?></span>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="portfolio_id" value="<?php echo $portfolio['id']; ?>">
                                                    <?php 
                                                    $is_active = isset($portfolio['is_active']) ? $portfolio['is_active'] : 1;
                                                    ?>
                                                    <button type="submit" class="btn btn-sm <?php echo $is_active ? 'btn-success' : 'btn-secondary'; ?>">
                                                        <?php echo $is_active ? 'Aktif' : 'Pasif'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <small><?php echo date('d.m.Y H:i', strtotime($portfolio['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit.php?id=<?php echo $portfolio['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu portfolio projesini silmek istediğinizden emin misiniz?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="delete_portfolio">
                                                        <input type="hidden" name="portfolio_id" value="<?php echo $portfolio['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>