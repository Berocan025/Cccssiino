<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$page_title = 'İçerik Yönetimi';
$page_subtitle = 'Site içeriklerini düzenle ve yönet';
$page_header = true;

$breadcrumbs = [
    ['title' => 'İçerik Yönetimi']
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
    
    if ($action === 'toggle_status' && isset($_POST['service_id'])) {
        $service_id = (int)$_POST['service_id'];
        
        $stmt = $pdo->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?");
        if ($stmt->execute([$service_id])) {
            $_SESSION['admin_success'] = 'Hizmet durumu güncellendi.';
        } else {
            $_SESSION['admin_error'] = 'Hizmet güncellenirken hata oluştu.';
        }
    }
    
    if ($action === 'delete_service' && isset($_POST['service_id'])) {
        $service_id = (int)$_POST['service_id'];
        
        // Try to get service info for file deletion
        try {
            $stmt = $pdo->prepare("SELECT image_path FROM services WHERE id = ?");
            $stmt->execute([$service_id]);
            $service = $stmt->fetch();
            
            if ($service && !empty($service['image_path']) && file_exists($service['image_path'])) {
                unlink($service['image_path']);
            }
        } catch (PDOException $e) {
            // image_path column doesn't exist, skip file deletion
        }
        
        // Delete from database
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            if ($stmt->execute([$service_id])) {
                $_SESSION['admin_success'] = 'Hizmet silindi.';
            } else {
                $_SESSION['admin_error'] = 'Hizmet silinirken hata oluştu.';
            }
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = 'Hizmet silinirken hata oluştu: ' . $e->getMessage();
        }
    }
    
    header("Location: index.php");
    exit();
}

// Get filter
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if ($status_filter !== '') {
    $where_conditions[] = 'is_active = ?';
    $params[] = (int)$status_filter;
}

if (!empty($search)) {
    $where_conditions[] = 'title LIKE ?';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get services
$query = "SELECT * FROM services $where_clause ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
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
                    <h5 class="card-title mb-0">İçerik Yönetimi</h5>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni İçerik Ekle
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row mb-3">
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">Tüm Durumlar</option>
                                <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <input type="text" name="search" class="form-control" placeholder="Başlık veya açıklama ara..." value="<?php echo escape_output($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search"></i> Filtrele
                            </button>
                        </div>
                    </form>
                    
                    <!-- Services Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Görsel</th>
                                    <th>Başlık</th>
                                    <th>Açıklama</th>
                                    <th>Durum</th>
                                    <th>Oluşturulma</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($services)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Henüz hizmet bulunmamaktadır.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($services as $service): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($service['image_path'])): ?>
                                                    <img src="<?php echo escape_output($service['image_path']); ?>" 
                                                         alt="<?php echo escape_output($service['title']); ?>" 
                                                         class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo escape_output($service['title']); ?></strong>
                                            </td>
                                            <td>
                                                <?php if (!empty($service['description'])): ?>
                                                    <small class="text-muted"><?php echo escape_output(substr($service['description'], 0, 150)) . '...'; ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted"><em>Açıklama eklenmemiş</em></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                    <button type="submit" class="btn btn-sm <?php echo $service['is_active'] ? 'btn-success' : 'btn-secondary'; ?>">
                                                        <?php echo $service['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <small><?php echo date('d.m.Y H:i', strtotime($service['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu hizmeti silmek istediğinizden emin misiniz?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="delete_service">
                                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
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