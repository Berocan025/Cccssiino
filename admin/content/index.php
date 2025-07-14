<?php
/**
 * Admin Content Management - SQLite Database Integration
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

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validate_csrf_token($csrf_token)) {
        $error = 'Güvenlik hatası!';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            try {
                // Get service details
                $stmt = $pdo->prepare("SELECT title, image FROM services WHERE id = ?");
                $stmt->execute([$id]);
                $service = $stmt->fetch();
                
                if ($service) {
                    // Delete image file
                    if ($service['image'] && file_exists('../../assets/uploads/' . $service['image'])) {
                        unlink('../../assets/uploads/' . $service['image']);
                    }
                    
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $success = 'Hizmet başarıyla silindi!';
                    write_log("Service deleted: " . $service['title'], 'info');
                } else {
                    $error = 'Hizmet bulunamadı!';
                }
            } catch (PDOException $e) {
                $error = 'Silme işlemi başarısız: ' . $e->getMessage();
                write_log("Service delete error: " . $e->getMessage(), 'error');
            }
        } elseif ($action === 'bulk_delete' && isset($_POST['selected_items'])) {
            $selected_items = array_map('intval', $_POST['selected_items']);
            $deleted_count = 0;
            
            try {
                foreach ($selected_items as $id) {
                    $stmt = $pdo->prepare("SELECT title, image FROM services WHERE id = ?");
                    $stmt->execute([$id]);
                    $service = $stmt->fetch();
                    
                    if ($service) {
                        // Delete image file
                        if ($service['image'] && file_exists('../../assets/uploads/' . $service['image'])) {
                            unlink('../../assets/uploads/' . $service['image']);
                        }
                        
                        // Delete from database
                        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
                        $stmt->execute([$id]);
                        $deleted_count++;
                    }
                }
                
                $success = "$deleted_count hizmet başarıyla silindi!";
                write_log("Bulk delete: $deleted_count services deleted", 'info');
            } catch (PDOException $e) {
                $error = 'Toplu silme işlemi başarısız: ' . $e->getMessage();
                write_log("Bulk delete error: " . $e->getMessage(), 'error');
            }
        } elseif ($action === 'status_change' && isset($_POST['id']) && isset($_POST['new_status'])) {
            $id = (int)$_POST['id'];
            $new_status = $_POST['new_status'];
            
            try {
                $stmt = $pdo->prepare("UPDATE services SET status = ?, updated_at = datetime('now') WHERE id = ?");
                $stmt->execute([$new_status, $id]);
                
                $success = 'Durum başarıyla güncellendi!';
                write_log("Service status changed: ID $id to $new_status", 'info');
            } catch (PDOException $e) {
                $error = 'Durum güncelleme başarısız: ' . $e->getMessage();
                write_log("Status update error: " . $e->getMessage(), 'error');
            }
        }
    }
}

// Build query with filters
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR short_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($category_filter > 0) {
    $where_conditions[] = "category_id = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
try {
    $count_sql = "SELECT COUNT(*) as total FROM services $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_items = $stmt->fetch()['total'];
    $total_pages = ceil($total_items / $items_per_page);
} catch (PDOException $e) {
    $total_items = 0;
    $total_pages = 0;
    $error = 'Sayım hatası: ' . $e->getMessage();
}

// Get services
try {
    $sql = "SELECT s.*, c.name as category_name 
            FROM services s 
            LEFT JOIN categories c ON s.category_id = c.id 
            $where_clause 
            ORDER BY s.sort_order ASC, s.created_at DESC 
            LIMIT $items_per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
    $error = 'Hizmetler yüklenirken hata: ' . $e->getMessage();
}

// Get categories for filter
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE type = 'service' ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

include '../includes/admin_header.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 page-title">Hizmet Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Hizmetler</li>
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

        <!-- Filters and Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h4 class="card-title mb-0">Hizmetler (<?php echo $total_items; ?>)</h4>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="add.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Yeni Hizmet Ekle
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filter Form -->
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo escape_output($search); ?>" 
                                       placeholder="Başlık veya açıklama ara...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">Tüm Kategoriler</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape_output($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search me-1"></i>Filtrele
                                </button>
                            </div>
                        </form>

                        <?php if (!empty($services)): ?>
                        <!-- Bulk Actions -->
                        <form id="bulk-form" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" id="bulk-action">
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="select-all">
                                            </th>
                                            <th>Hizmet</th>
                                            <th>Kategori</th>
                                            <th>Durum</th>
                                            <th>Sıra</th>
                                            <th>Tarih</th>
                                            <th width="150">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($services as $service): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_items[]" value="<?php echo $service['id']; ?>" class="item-checkbox">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($service['image']): ?>
                                                    <img src="../../assets/uploads/<?php echo $service['image']; ?>" 
                                                         class="rounded me-3" width="50" height="50" style="object-fit: cover;">
                                                    <?php else: ?>
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="<?php echo $service['icon'] ?: 'fas fa-image'; ?> text-muted"></i>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo escape_output($service['title']); ?></h6>
                                                        <small class="text-muted"><?php echo escape_output(substr($service['short_description'], 0, 60)) . '...'; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($service['category_name']): ?>
                                                    <span class="badge bg-secondary"><?php echo escape_output($service['category_name']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="action" value="status_change">
                                                    <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                                    <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="active" <?php echo $service['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                                        <option value="inactive" <?php echo $service['status'] === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?php echo $service['sort_order']; ?></td>
                                            <td>
                                                <small><?php echo format_date($service['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit.php?id=<?php echo $service['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Düzenle">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo escape_output($service['title']); ?>')" 
                                                            title="Sil">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Bulk Actions Bar -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div id="bulk-actions" style="display: none;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                                        <i class="fas fa-trash me-1"></i>Seçilenleri Sil
                                    </button>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo $category_filter; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Henüz hizmet bulunmuyor</h5>
                            <p class="text-muted">İlk hizmetinizi eklemek için aşağıdaki butona tıklayın.</p>
                            <a href="add.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>İlk Hizmetimi Ekle
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hizmet Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bu hizmeti silmek istediğinizden emin misiniz?</p>
                <p><strong id="service-name"></strong></p>
                <p class="text-muted">Bu işlem geri alınamaz!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
function deleteService(id, name) {
    document.getElementById('delete-id').value = id;
    document.getElementById('service-name').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function bulkDelete() {
    const selected = document.querySelectorAll('.item-checkbox:checked');
    if (selected.length === 0) {
        alert('Lütfen silinecek hizmetleri seçin.');
        return;
    }
    
    if (confirm(`Seçilen ${selected.length} hizmeti silmek istediğinizden emin misiniz?`)) {
        document.getElementById('bulk-action').value = 'bulk_delete';
        document.getElementById('bulk-form').submit();
    }
}

// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    toggleBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', toggleBulkActions);
});

function toggleBulkActions() {
    const selected = document.querySelectorAll('.item-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    bulkActions.style.display = selected.length > 0 ? 'block' : 'none';
}
</script>

<?php include '../includes/admin_footer.php'; ?>