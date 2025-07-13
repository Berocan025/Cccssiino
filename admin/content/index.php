<?php
/**
 * Admin Content Management
 * Tam Veritabanı Entegrasyonu - Services Tablosu
 */

require_once '../includes/admin_config.php';
require_admin_login();

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
                $stmt = $pdo->prepare("SELECT image FROM services WHERE id = ?");
                $stmt->execute([$id]);
                $service = $stmt->fetch();
                
                if ($service) {
                    // Delete image file
                    if ($service['image'] && file_exists('../../uploads/services/' . $service['image'])) {
                        unlink('../../uploads/services/' . $service['image']);
                    }
                    
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $success = 'İçerik başarıyla silindi!';
                } else {
                    $error = 'İçerik bulunamadı!';
                }
            } catch (PDOException $e) {
                $error = 'Silme işlemi başarısız: ' . $e->getMessage();
            }
        }
        
        if ($action === 'bulk_delete' && isset($_POST['selected_ids'])) {
            $selected_ids = array_map('intval', $_POST['selected_ids']);
            
            if (!empty($selected_ids)) {
                try {
                    $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
                    
                    // Get services with images
                    $stmt = $pdo->prepare("SELECT image FROM services WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    $services = $stmt->fetchAll();
                    
                    // Delete image files
                    foreach ($services as $service) {
                        if ($service['image'] && file_exists('../../uploads/services/' . $service['image'])) {
                            unlink('../../uploads/services/' . $service['image']);
                        }
                    }
                    
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM services WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    
                    $success = count($selected_ids) . ' içerik başarıyla silindi!';
                } catch (PDOException $e) {
                    $error = 'Toplu silme işlemi başarısız: ' . $e->getMessage();
                }
            }
        }
        
        if ($action === 'bulk_status' && isset($_POST['selected_ids']) && isset($_POST['new_status'])) {
            $selected_ids = array_map('intval', $_POST['selected_ids']);
            $new_status = $_POST['new_status'];
            
            if (!empty($selected_ids) && in_array($new_status, ['active', 'inactive'])) {
                try {
                    $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
                    $stmt = $pdo->prepare("UPDATE services SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$new_status], $selected_ids));
                    
                    $success = count($selected_ids) . ' içerik durumu güncellendi!';
                } catch (PDOException $e) {
                    $error = 'Durum güncelleme başarısız: ' . $e->getMessage();
                }
            }
        }
    }
}

// Build WHERE clause
$where_conditions = [];
$params = [];

if ($status_filter !== '') {
    $where_conditions[] = 'status = ?';
    $params[] = $status_filter;
}

if ($category_filter > 0) {
    $where_conditions[] = 'category_id = ?';
    $params[] = $category_filter;
}

if (!empty($search)) {
    $where_conditions[] = '(title LIKE ? OR short_description LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
try {
    $count_sql = "SELECT COUNT(*) as total FROM services $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetch()['total'];
} catch (PDOException $e) {
    $total_items = 0;
}

// Get services
try {
    $sql = "SELECT s.*, c.name as category_name 
            FROM services s 
            LEFT JOIN categories c ON s.category_id = c.id 
            $where_clause 
            ORDER BY s.created_at DESC 
            LIMIT $items_per_page OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
    $error = 'Veri alınamadı: ' . $e->getMessage();
}

// Get categories for filter
try {
    $categories_stmt = $pdo->query("SELECT * FROM categories WHERE type = 'service' ORDER BY name");
    $categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Calculate pagination
$total_pages = ceil($total_items / $items_per_page);

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<?php include '../includes/admin_header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Hizmet Yönetimi</h5>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Hizmet Ekle
                    </a>
                </div>
                
                <div class="card-body">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo escape_output($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo escape_output($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Tüm Durumlar</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="category" class="form-select">
                                <option value="">Tüm Kategoriler</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape_output($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Başlık veya açıklama ara..." value="<?php echo escape_output($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="fas fa-search"></i> Filtrele
                            </button>
                        </div>
                    </form>
                    
                    <!-- Bulk Actions -->
                    <form id="bulk-form" method="POST" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="">
                        <input type="hidden" name="new_status" value="">
                        
                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" id="select-all" class="btn btn-sm btn-outline-primary">Tümünü Seç</button>
                            <button type="button" onclick="bulkAction('bulk_status', 'active')" class="btn btn-sm btn-success" disabled id="bulk-activate">Aktif Yap</button>
                            <button type="button" onclick="bulkAction('bulk_status', 'inactive')" class="btn btn-sm btn-warning" disabled id="bulk-deactivate">Pasif Yap</button>
                            <button type="button" onclick="bulkAction('bulk_delete')" class="btn btn-sm btn-danger" disabled id="bulk-delete">Sil</button>
                            <span id="selected-count" class="text-muted ms-2">0 öğe seçili</span>
                        </div>
                        
                        <!-- Services Table -->
                        <div class="table-responsive mt-3">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                                        </th>
                                        <th width="80">Resim</th>
                                        <th>Başlık</th>
                                        <th>Kategori</th>
                                        <th>Fiyat</th>
                                        <th width="100">Durum</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($services)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">Henüz hizmet eklenmemiş.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($services as $service): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_ids[]" value="<?php echo $service['id']; ?>" class="form-check-input row-checkbox">
                                                </td>
                                                <td>
                                                    <?php if ($service['image']): ?>
                                                        <img src="../../uploads/services/<?php echo escape_output($service['image']); ?>" 
                                                             alt="<?php echo escape_output($service['title']); ?>" 
                                                             class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo escape_output($service['title']); ?></strong>
                                                    <?php if ($service['short_description']): ?>
                                                        <br><small class="text-muted"><?php echo escape_output(substr($service['short_description'], 0, 100)); ?>...</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo $service['category_name'] ? escape_output($service['category_name']) : '<span class="text-muted">Kategori Yok</span>'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $service['price_text'] ? escape_output($service['price_text']) : '<span class="text-muted">Belirtilmemiş</span>'; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $service['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $service['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit.php?id=<?php echo $service['id']; ?>" class="btn btn-outline-primary" title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger" onclick="deleteService(<?php echo $service['id']; ?>)" title="Sil">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Sayfa navigasyonu">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>">Önceki</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>">Sonraki</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                Toplam <?php echo $total_items; ?> kayıttan <?php echo min($offset + 1, $total_items); ?>-<?php echo min($offset + $items_per_page, $total_items); ?> arası gösteriliyor
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Hizmeti Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bu hizmeti silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteService(id) {
    document.getElementById('delete-id').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Bulk selection functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectedCount = document.getElementById('selected-count');
    const bulkButtons = document.querySelectorAll('#bulk-activate, #bulk-deactivate, #bulk-delete');
    
    function updateBulkButtons() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedCount.textContent = count + ' öğe seçili';
        
        bulkButtons.forEach(button => {
            button.disabled = count === 0;
        });
    }
    
    selectAllCheckbox.addEventListener('change', function() {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkButtons();
    });
    
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButtons);
    });
    
    document.getElementById('select-all').addEventListener('click', function() {
        const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
        selectAllCheckbox.checked = !allChecked;
        updateBulkButtons();
    });
});

function bulkAction(action, status = '') {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Lütfen en az bir öğe seçin.');
        return;
    }
    
    let message = '';
    if (action === 'bulk_delete') {
        message = checkedBoxes.length + ' öğeyi silmek istediğinizden emin misiniz?';
    } else if (action === 'bulk_status') {
        message = checkedBoxes.length + ' öğenin durumunu değiştirmek istediğinizden emin misiniz?';
    }
    
    if (confirm(message)) {
        const form = document.getElementById('bulk-form');
        form.querySelector('input[name="action"]').value = action;
        if (status) {
            form.querySelector('input[name="new_status"]').value = status;
        }
        form.submit();
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>