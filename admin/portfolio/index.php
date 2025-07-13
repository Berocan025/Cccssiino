<?php
/**
 * Admin Portfolio Management
 * Tam Veritabanı Entegrasyonu - Portfolio Tablosu
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

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$featured_filter = isset($_GET['featured']) ? $_GET['featured'] : '';
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
                // Get portfolio details
                $stmt = $pdo->prepare("SELECT image, gallery_images FROM portfolio WHERE id = ?");
                $stmt->execute([$id]);
                $portfolio = $stmt->fetch();
                
                if ($portfolio) {
                    // Delete main image file
                    if ($portfolio['image'] && file_exists('../../uploads/portfolio/' . $portfolio['image'])) {
                        unlink('../../uploads/portfolio/' . $portfolio['image']);
                    }
                    
                    // Delete gallery images
                    if ($portfolio['gallery_images']) {
                        $gallery_images = json_decode($portfolio['gallery_images'], true);
                        if (is_array($gallery_images)) {
                            foreach ($gallery_images as $image) {
                                if (file_exists('../../uploads/portfolio/gallery/' . $image)) {
                                    unlink('../../uploads/portfolio/gallery/' . $image);
                                }
                            }
                        }
                    }
                    
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $success = 'Portföy projesi başarıyla silindi!';
                } else {
                    $error = 'Portföy projesi bulunamadı!';
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
                    
                    // Get portfolios with images
                    $stmt = $pdo->prepare("SELECT image, gallery_images FROM portfolio WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    $portfolios = $stmt->fetchAll();
                    
                    // Delete image files
                    foreach ($portfolios as $portfolio) {
                        if ($portfolio['image'] && file_exists('../../uploads/portfolio/' . $portfolio['image'])) {
                            unlink('../../uploads/portfolio/' . $portfolio['image']);
                        }
                        
                        if ($portfolio['gallery_images']) {
                            $gallery_images = json_decode($portfolio['gallery_images'], true);
                            if (is_array($gallery_images)) {
                                foreach ($gallery_images as $image) {
                                    if (file_exists('../../uploads/portfolio/gallery/' . $image)) {
                                        unlink('../../uploads/portfolio/gallery/' . $image);
                                    }
                                }
                            }
                        }
                    }
                    
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM portfolio WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    
                    $success = count($selected_ids) . ' portföy projesi başarıyla silindi!';
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
                    $stmt = $pdo->prepare("UPDATE portfolio SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$new_status], $selected_ids));
                    
                    $success = count($selected_ids) . ' proje durumu güncellendi!';
                } catch (PDOException $e) {
                    $error = 'Durum güncelleme başarısız: ' . $e->getMessage();
                }
            }
        }
        
        if ($action === 'bulk_featured' && isset($_POST['selected_ids']) && isset($_POST['new_featured'])) {
            $selected_ids = array_map('intval', $_POST['selected_ids']);
            $new_featured = $_POST['new_featured'];
            
            if (!empty($selected_ids) && in_array($new_featured, ['yes', 'no'])) {
                try {
                    $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
                    $stmt = $pdo->prepare("UPDATE portfolio SET featured = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$new_featured], $selected_ids));
                    
                    $success = count($selected_ids) . ' proje öne çıkarma durumu güncellendi!';
                } catch (PDOException $e) {
                    $error = 'Öne çıkarma güncelleme başarısız: ' . $e->getMessage();
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

if ($featured_filter !== '') {
    $where_conditions[] = 'featured = ?';
    $params[] = $featured_filter;
}

if (!empty($search)) {
    $where_conditions[] = '(title LIKE ? OR short_description LIKE ? OR client_name LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
try {
    $count_sql = "SELECT COUNT(*) as total FROM portfolio $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetch()['total'];
} catch (PDOException $e) {
    $total_items = 0;
}

// Get portfolios
try {
    $sql = "SELECT p.*, c.name as category_name 
            FROM portfolio p 
            LEFT JOIN categories c ON p.category_id = c.id 
            $where_clause 
            ORDER BY p.featured DESC, p.sort_order ASC, p.created_at DESC 
            LIMIT $items_per_page OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $portfolios = $stmt->fetchAll();
} catch (PDOException $e) {
    $portfolios = [];
    $error = 'Veri alınamadı: ' . $e->getMessage();
}

// Get categories for filter
try {
    $categories_stmt = $pdo->query("SELECT * FROM categories WHERE type = 'portfolio' ORDER BY name");
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
                    <h5 class="card-title mb-0">Portföy Yönetimi</h5>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Proje Ekle
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
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">Tüm Durumlar</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select">
                                <option value="">Tüm Kategoriler</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo escape_output($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="featured" class="form-select">
                                <option value="">Tüm Projeler</option>
                                <option value="yes" <?php echo $featured_filter === 'yes' ? 'selected' : ''; ?>>Öne Çıkan</option>
                                <option value="no" <?php echo $featured_filter === 'no' ? 'selected' : ''; ?>>Normal</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Proje adı, açıklama veya müşteri ara..." value="<?php echo escape_output($search); ?>">
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
                        <input type="hidden" name="new_featured" value="">
                        
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <button type="button" id="select-all" class="btn btn-sm btn-outline-primary">Tümünü Seç</button>
                            <button type="button" onclick="bulkAction('bulk_status', 'active')" class="btn btn-sm btn-success" disabled id="bulk-activate">Aktif Yap</button>
                            <button type="button" onclick="bulkAction('bulk_status', 'inactive')" class="btn btn-sm btn-warning" disabled id="bulk-deactivate">Pasif Yap</button>
                            <button type="button" onclick="bulkFeatured('yes')" class="btn btn-sm btn-info" disabled id="bulk-feature">Öne Çıkar</button>
                            <button type="button" onclick="bulkFeatured('no')" class="btn btn-sm btn-secondary" disabled id="bulk-unfeature">Normal Yap</button>
                            <button type="button" onclick="bulkAction('bulk_delete')" class="btn btn-sm btn-danger" disabled id="bulk-delete">Sil</button>
                            <span id="selected-count" class="text-muted ms-2">0 öğe seçili</span>
                        </div>
                        
                        <!-- Portfolio Table -->
                        <div class="table-responsive mt-3">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                                        </th>
                                        <th width="80">Görsel</th>
                                        <th>Proje Adı</th>
                                        <th>Müşteri</th>
                                        <th>Kategori</th>
                                        <th>Tarih</th>
                                        <th width="100">Özellik</th>
                                        <th width="100">Durum</th>
                                        <th width="150">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($portfolios)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">Henüz portföy projesi eklenmemiş.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($portfolios as $portfolio): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_ids[]" value="<?php echo $portfolio['id']; ?>" class="form-check-input row-checkbox">
                                                </td>
                                                <td>
                                                    <?php if ($portfolio['image']): ?>
                                                        <img src="../../uploads/portfolio/<?php echo escape_output($portfolio['image']); ?>" 
                                                             alt="<?php echo escape_output($portfolio['title']); ?>" 
                                                             class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($portfolio['featured'] === 'yes'): ?>
                                                            <i class="fas fa-star text-warning me-2" title="Öne Çıkan Proje"></i>
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?php echo escape_output($portfolio['title']); ?></strong>
                                                            <?php if ($portfolio['short_description']): ?>
                                                                <br><small class="text-muted"><?php echo escape_output(substr($portfolio['short_description'], 0, 80)); ?>...</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo $portfolio['client_name'] ? escape_output($portfolio['client_name']) : '<span class="text-muted">Belirtilmemiş</span>'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $portfolio['category_name'] ? escape_output($portfolio['category_name']) : '<span class="text-muted">Kategori Yok</span>'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $portfolio['project_date'] ? date('d.m.Y', strtotime($portfolio['project_date'])) : '<span class="text-muted">-</span>'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($portfolio['featured'] === 'yes'): ?>
                                                        <span class="badge bg-warning text-dark">Öne Çıkan</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">Normal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $portfolio['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $portfolio['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit.php?id=<?php echo $portfolio['id']; ?>" class="btn btn-outline-primary" title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($portfolio['project_url']): ?>
                                                            <a href="<?php echo escape_output($portfolio['project_url']); ?>" target="_blank" class="btn btn-outline-info" title="Proje Linki">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-danger" onclick="deletePortfolio(<?php echo $portfolio['id']; ?>)" title="Sil">
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
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo $category_filter; ?>&featured=<?php echo urlencode($featured_filter); ?>&search=<?php echo urlencode($search); ?>">Önceki</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo $category_filter; ?>&featured=<?php echo urlencode($featured_filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo $category_filter; ?>&featured=<?php echo urlencode($featured_filter); ?>&search=<?php echo urlencode($search); ?>">Sonraki</a>
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
                <h5 class="modal-title" id="deleteModalLabel">Portföy Projesini Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bu portföy projesini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz ve proje ile ilgili tüm dosyalar da silinecektir.
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
function deletePortfolio(id) {
    document.getElementById('delete-id').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Bulk selection functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectedCount = document.getElementById('selected-count');
    const bulkButtons = document.querySelectorAll('#bulk-activate, #bulk-deactivate, #bulk-feature, #bulk-unfeature, #bulk-delete');
    
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
        message = checkedBoxes.length + ' projeyi silmek istediğinizden emin misiniz?';
    } else if (action === 'bulk_status') {
        message = checkedBoxes.length + ' projenin durumunu değiştirmek istediğinizden emin misiniz?';
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

function bulkFeatured(featured) {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Lütfen en az bir öğe seçin.');
        return;
    }
    
    const message = checkedBoxes.length + ' projenin öne çıkarma durumunu değiştirmek istediğinizden emin misiniz?';
    
    if (confirm(message)) {
        const form = document.getElementById('bulk-form');
        form.querySelector('input[name="action"]').value = 'bulk_featured';
        form.querySelector('input[name="new_featured"]').value = featured;
        form.submit();
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>