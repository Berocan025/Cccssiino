<?php
session_start();
require_once '../../includes/config.php';

$page_title = 'Hizmet Yönetimi';

// Sayfalama
$page = (int) ($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtreleme
$search = sanitize_input($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Toplu işlemler
if ($_POST['bulk_action'] ?? false) {
    $action = $_POST['bulk_action'];
    $selected_ids = $_POST['selected_items'] ?? [];
    
    if (!empty($selected_ids) && in_array($action, ['activate', 'deactivate', 'delete'])) {
        $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
        
        try {
            switch ($action) {
                case 'activate':
                    $stmt = $pdo->prepare("UPDATE services SET status = 'active' WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    $success = count($selected_ids) . ' hizmet aktif edildi.';
                    break;
                    
                case 'deactivate':
                    $stmt = $pdo->prepare("UPDATE services SET status = 'inactive' WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    $success = count($selected_ids) . ' hizmet pasif edildi.';
                    break;
                    
                case 'delete':
                    // Önce resimleri sil
                    $stmt = $pdo->prepare("SELECT image FROM services WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    foreach ($images as $image) {
                        if ($image && file_exists('../../' . $image)) {
                            unlink('../../' . $image);
                        }
                    }
                    
                    $stmt = $pdo->prepare("DELETE FROM services WHERE id IN ($placeholders)");
                    $stmt->execute($selected_ids);
                    $success = count($selected_ids) . ' hizmet silindi.';
                    break;
            }
            
            write_log("Bulk action: $action on services: " . implode(',', $selected_ids), 'info');
            
        } catch (PDOException $e) {
            $error = 'Toplu işlem sırasında hata oluştu.';
            write_log("Bulk action error: " . $e->getMessage(), 'error');
        }
    }
}

// Arama ve filtreleme sorgusu
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($category_filter)) {
    $where_conditions[] = "category_id = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Toplam kayıt sayısı
$count_sql = "SELECT COUNT(*) FROM services $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Hizmetleri getir
$sql = "SELECT s.*, c.name as category_name 
        FROM services s 
        LEFT JOIN categories c ON s.category_id = c.id 
        $where_clause 
        ORDER BY s.created_at DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Kategorileri getir
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="text-warning mb-1">
            <i class="fas fa-cogs me-2"></i>
            Hizmet Yönetimi
        </h3>
        <p class="text-muted mb-0">Toplam <?php echo $total_records; ?> hizmet</p>
    </div>
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>
        Yeni Hizmet Ekle
    </a>
</div>

<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filtreleme ve Arama -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Ara</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Başlık veya açıklama..." value="<?php echo escape_output($search); ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Durum</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tümü</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Kategori</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Tümü</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" 
                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo escape_output($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="?" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Hizmetler Tablosu -->
<div class="card">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Hizmetler</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-warning" onclick="selectAll()">
                <i class="fas fa-check-square me-1"></i>
                Tümünü Seç
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                <i class="fas fa-square me-1"></i>
                Seçimi Temizle
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($services)): ?>
        <form method="POST" id="bulkForm">
            <!-- Toplu İşlemler -->
            <div class="p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <select name="bulk_action" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Toplu İşlemler</option>
                                <option value="activate">Aktif Et</option>
                                <option value="deactivate">Pasif Et</option>
                                <option value="delete">Sil</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirmBulkAction()">
                                Uygula
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span id="selectedCount" class="text-muted small">0 öğe seçili</span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th width="80">Resim</th>
                            <th>Başlık</th>
                            <th>Kategori</th>
                            <th width="100">Durum</th>
                            <th width="120">Tarih</th>
                            <th width="150">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input item-checkbox" 
                                       name="selected_items[]" value="<?php echo $service['id']; ?>">
                            </td>
                            <td>
                                <?php if (!empty($service['image'])): ?>
                                <img src="../../<?php echo escape_output($service['image']); ?>" 
                                     alt="<?php echo escape_output($service['title']); ?>"
                                     class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <h6 class="mb-1">
                                        <?php if (!empty($service['icon'])): ?>
                                        <i class="<?php echo escape_output($service['icon']); ?> me-2 text-warning"></i>
                                        <?php endif; ?>
                                        <?php echo escape_output($service['title']); ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo escape_output(substr($service['description'], 0, 80)); ?>
                                        <?php if (strlen($service['description']) > 80): ?>...<?php endif; ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-gold">
                                    <?php echo escape_output($service['category_name'] ?? 'Kategorisiz'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $service['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $service['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('d.m.Y', strtotime($service['created_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="edit.php?id=<?php echo $service['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            onclick="toggleStatus(<?php echo $service['id']; ?>, '<?php echo $service['status']; ?>')"
                                            data-bs-toggle="tooltip" title="Durumu Değiştir">
                                        <i class="fas fa-<?php echo $service['status'] === 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteService(<?php echo $service['id']; ?>)"
                                            data-bs-toggle="tooltip" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        
        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-transparent">
            <nav aria-label="Sayfa navigasyonu">
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Hizmet bulunamadı</h5>
            <p class="text-muted">Henüz hiç hizmet eklenmemiş veya arama kriterlerinize uygun hizmet yok.</p>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                İlk Hizmeti Ekle
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Checkbox işlemleri
function selectAll() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    selectAllCheckbox.checked = true;
    updateSelectedCount();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectAllCheckbox.checked = false;
    updateSelectedCount();
}

// Select all toggle
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

// Individual checkbox change
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('.item-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        const selectAllCheckbox = document.getElementById('selectAll');
        
        selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
        updateSelectedCount();
    });
});

function updateSelectedCount() {
    const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    document.getElementById('selectedCount').textContent = checkedCheckboxes.length + ' öğe seçili';
}

// Toplu işlem onayı
function confirmBulkAction() {
    const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    const action = document.querySelector('select[name="bulk_action"]').value;
    
    if (checkedCheckboxes.length === 0) {
        alert('Lütfen en az bir öğe seçin.');
        return false;
    }
    
    if (!action) {
        alert('Lütfen bir işlem seçin.');
        return false;
    }
    
    const actionText = {
        'activate': 'aktif etmek',
        'deactivate': 'pasif etmek',
        'delete': 'silmek'
    };
    
    return confirm(`${checkedCheckboxes.length} hizmeti ${actionText[action]} istediğinizden emin misiniz?`);
}

// Durum değiştirme
function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const actionText = newStatus === 'active' ? 'aktif etmek' : 'pasif etmek';
    
    if (confirm(`Bu hizmeti ${actionText} istediğinizden emin misiniz?`)) {
        fetch('toggle_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu.');
        });
    }
}

// Hizmet silme
function deleteService(id) {
    if (confirm('Bu hizmeti silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu.');
        });
    }
}

// Tooltip'leri başlat
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    updateSelectedCount();
});
</script>

<?php include '../includes/footer.php'; ?>