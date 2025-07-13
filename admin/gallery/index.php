<?php
/**
 * Admin Gallery Management
 * Tam Veritabanı Entegrasyonu - Gallery Photos & Videos
 */

require_once '../includes/admin_config.php';
require_admin_login();

// Pagination settings
$items_per_page = 20;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Filters
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validate_csrf_token($csrf_token)) {
        $error = 'Güvenlik hatası!';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'delete' && isset($_POST['id']) && isset($_POST['type'])) {
            $id = (int)$_POST['id'];
            $type = $_POST['type'];
            
            try {
                if ($type === 'photo') {
                    // Get photo details
                    $stmt = $pdo->prepare("SELECT image, thumbnail FROM gallery_photos WHERE id = ?");
                    $stmt->execute([$id]);
                    $item = $stmt->fetch();
                    
                    if ($item) {
                        // Delete image files
                        if ($item['image'] && file_exists('../../uploads/gallery/' . $item['image'])) {
                            unlink('../../uploads/gallery/' . $item['image']);
                        }
                        if ($item['thumbnail'] && file_exists('../../uploads/gallery/thumbs/' . $item['thumbnail'])) {
                            unlink('../../uploads/gallery/thumbs/' . $item['thumbnail']);
                        }
                        
                        // Delete from database
                        $stmt = $pdo->prepare("DELETE FROM gallery_photos WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        $success = 'Fotoğraf başarıyla silindi!';
                    }
                } elseif ($type === 'video') {
                    // Get video details
                    $stmt = $pdo->prepare("SELECT thumbnail FROM gallery_videos WHERE id = ?");
                    $stmt->execute([$id]);
                    $item = $stmt->fetch();
                    
                    if ($item) {
                        // Delete thumbnail file
                        if ($item['thumbnail'] && file_exists('../../uploads/gallery/thumbs/' . $item['thumbnail'])) {
                            unlink('../../uploads/gallery/thumbs/' . $item['thumbnail']);
                        }
                        
                        // Delete from database
                        $stmt = $pdo->prepare("DELETE FROM gallery_videos WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        $success = 'Video başarıyla silindi!';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Silme işlemi başarısız: ' . $e->getMessage();
            }
        }
        
        if ($action === 'bulk_delete' && isset($_POST['selected_items'])) {
            $selected_items = $_POST['selected_items']; // Array of type:id
            
            if (!empty($selected_items)) {
                try {
                    foreach ($selected_items as $item) {
                        list($type, $id) = explode(':', $item);
                        $id = (int)$id;
                        
                        if ($type === 'photo') {
                            // Get and delete photo
                            $stmt = $pdo->prepare("SELECT image, thumbnail FROM gallery_photos WHERE id = ?");
                            $stmt->execute([$id]);
                            $photo = $stmt->fetch();
                            
                            if ($photo) {
                                if ($photo['image'] && file_exists('../../uploads/gallery/' . $photo['image'])) {
                                    unlink('../../uploads/gallery/' . $photo['image']);
                                }
                                if ($photo['thumbnail'] && file_exists('../../uploads/gallery/thumbs/' . $photo['thumbnail'])) {
                                    unlink('../../uploads/gallery/thumbs/' . $photo['thumbnail']);
                                }
                                
                                $stmt = $pdo->prepare("DELETE FROM gallery_photos WHERE id = ?");
                                $stmt->execute([$id]);
                            }
                        } elseif ($type === 'video') {
                            // Get and delete video
                            $stmt = $pdo->prepare("SELECT thumbnail FROM gallery_videos WHERE id = ?");
                            $stmt->execute([$id]);
                            $video = $stmt->fetch();
                            
                            if ($video) {
                                if ($video['thumbnail'] && file_exists('../../uploads/gallery/thumbs/' . $video['thumbnail'])) {
                                    unlink('../../uploads/gallery/thumbs/' . $video['thumbnail']);
                                }
                                
                                $stmt = $pdo->prepare("DELETE FROM gallery_videos WHERE id = ?");
                                $stmt->execute([$id]);
                            }
                        }
                    }
                    
                    $success = count($selected_items) . ' öğe başarıyla silindi!';
                } catch (PDOException $e) {
                    $error = 'Toplu silme işlemi başarısız: ' . $e->getMessage();
                }
            }
        }
        
        if ($action === 'bulk_status' && isset($_POST['selected_items']) && isset($_POST['new_status'])) {
            $selected_items = $_POST['selected_items'];
            $new_status = $_POST['new_status'];
            
            if (!empty($selected_items) && in_array($new_status, ['active', 'inactive'])) {
                try {
                    foreach ($selected_items as $item) {
                        list($type, $id) = explode(':', $item);
                        $id = (int)$id;
                        
                        if ($type === 'photo') {
                            $stmt = $pdo->prepare("UPDATE gallery_photos SET status = ? WHERE id = ?");
                            $stmt->execute([$new_status, $id]);
                        } elseif ($type === 'video') {
                            $stmt = $pdo->prepare("UPDATE gallery_videos SET status = ? WHERE id = ?");
                            $stmt->execute([$new_status, $id]);
                        }
                    }
                    
                    $success = count($selected_items) . ' öğe durumu güncellendi!';
                } catch (PDOException $e) {
                    $error = 'Durum güncelleme başarısız: ' . $e->getMessage();
                }
            }
        }
    }
}

// Build WHERE clauses
$photo_where = ['1=1'];
$video_where = ['1=1'];
$photo_params = [];
$video_params = [];

if ($category_filter > 0) {
    $photo_where[] = 'category_id = ?';
    $video_where[] = 'category_id = ?';
    $photo_params[] = $category_filter;
    $video_params[] = $category_filter;
}

if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $photo_where[] = '(title LIKE ? OR description LIKE ?)';
    $video_where[] = '(title LIKE ? OR description LIKE ?)';
    $photo_params[] = $search_term;
    $photo_params[] = $search_term;
    $video_params[] = $search_term;
    $video_params[] = $search_term;
}

$photo_where_clause = 'WHERE ' . implode(' AND ', $photo_where);
$video_where_clause = 'WHERE ' . implode(' AND ', $video_where);

// Get gallery items
$gallery_items = [];

try {
    // Get photos
    if ($type_filter === '' || $type_filter === 'photo') {
        $count_sql = "SELECT COUNT(*) as total FROM gallery_photos $photo_where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($photo_params);
        $photo_count = $count_stmt->fetch()['total'];
        
        $sql = "SELECT gp.*, c.name as category_name, 'photo' as type 
                FROM gallery_photos gp 
                LEFT JOIN categories c ON gp.category_id = c.id 
                $photo_where_clause 
                ORDER BY gp.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($photo_params);
        $photos = $stmt->fetchAll();
        
        $gallery_items = array_merge($gallery_items, $photos);
    }
    
    // Get videos
    if ($type_filter === '' || $type_filter === 'video') {
        $count_sql = "SELECT COUNT(*) as total FROM gallery_videos $video_where_clause";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($video_params);
        $video_count = $count_stmt->fetch()['total'];
        
        $sql = "SELECT gv.*, c.name as category_name, 'video' as type 
                FROM gallery_videos gv 
                LEFT JOIN categories c ON gv.category_id = c.id 
                $video_where_clause 
                ORDER BY gv.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($video_params);
        $videos = $stmt->fetchAll();
        
        $gallery_items = array_merge($gallery_items, $videos);
    }
    
    // Sort combined results by created_at DESC
    usort($gallery_items, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Apply pagination
    $total_items = count($gallery_items);
    $gallery_items = array_slice($gallery_items, $offset, $items_per_page);
    
} catch (PDOException $e) {
    $gallery_items = [];
    $total_items = 0;
    $error = 'Veri alınamadı: ' . $e->getMessage();
}

// Get categories for filter
try {
    $categories_stmt = $pdo->query("SELECT * FROM categories WHERE type = 'gallery' ORDER BY name");
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
                    <h5 class="card-title mb-0">Galeri Yönetimi</h5>
                    <div>
                        <a href="add.php?type=photo" class="btn btn-primary me-2">
                            <i class="fas fa-image"></i> Fotoğraf Ekle
                        </a>
                        <a href="add.php?type=video" class="btn btn-success">
                            <i class="fas fa-video"></i> Video Ekle
                        </a>
                    </div>
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
                            <select name="type" class="form-select">
                                <option value="">Tüm Türler</option>
                                <option value="photo" <?php echo $type_filter === 'photo' ? 'selected' : ''; ?>>Fotoğraflar</option>
                                <option value="video" <?php echo $type_filter === 'video' ? 'selected' : ''; ?>>Videolar</option>
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
                        
                        <!-- Gallery Grid -->
                        <div class="row mt-3">
                            <?php if (empty($gallery_items)): ?>
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Henüz galeri öğesi eklenmemiş</h5>
                                        <p class="text-muted">Fotoğraf veya video ekleyerek galerini oluşturmaya başla</p>
                                        <a href="add.php?type=photo" class="btn btn-primary me-2">
                                            <i class="fas fa-image"></i> İlk Fotoğrafı Ekle
                                        </a>
                                        <a href="add.php?type=video" class="btn btn-success">
                                            <i class="fas fa-video"></i> İlk Videoyu Ekle
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($gallery_items as $item): ?>
                                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                                        <div class="card h-100">
                                            <div class="position-relative">
                                                <!-- Selection Checkbox -->
                                                <div class="position-absolute top-0 start-0 p-2">
                                                    <input type="checkbox" name="selected_items[]" 
                                                           value="<?php echo $item['type'] . ':' . $item['id']; ?>" 
                                                           class="form-check-input row-checkbox">
                                                </div>
                                                
                                                <!-- Type Badge -->
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <span class="badge bg-<?php echo $item['type'] === 'photo' ? 'primary' : 'success'; ?>">
                                                        <i class="fas fa-<?php echo $item['type'] === 'photo' ? 'image' : 'video'; ?>"></i>
                                                        <?php echo $item['type'] === 'photo' ? 'Fotoğraf' : 'Video'; ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Media Display -->
                                                <?php if ($item['type'] === 'photo'): ?>
                                                    <?php if ($item['image']): ?>
                                                        <img src="../../uploads/gallery/<?php echo escape_output($item['image']); ?>" 
                                                             class="card-img-top" style="height: 200px; object-fit: cover;" 
                                                             alt="<?php echo escape_output($item['title']); ?>">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                            <i class="fas fa-image fa-3x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if ($item['thumbnail']): ?>
                                                        <img src="../../uploads/gallery/thumbs/<?php echo escape_output($item['thumbnail']); ?>" 
                                                             class="card-img-top" style="height: 200px; object-fit: cover;" 
                                                             alt="<?php echo escape_output($item['title']); ?>">
                                                    <?php else: ?>
                                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                            <i class="fas fa-video fa-3x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Play Button Overlay -->
                                                    <div class="position-absolute top-50 start-50 translate-middle">
                                                        <div class="bg-dark bg-opacity-75 rounded-circle p-3">
                                                            <i class="fas fa-play text-white"></i>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="card-body">
                                                <h6 class="card-title mb-2"><?php echo escape_output($item['title']); ?></h6>
                                                <?php if ($item['description']): ?>
                                                    <p class="card-text small text-muted mb-2">
                                                        <?php echo escape_output(substr($item['description'], 0, 80)); ?>...
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <?php echo $item['category_name'] ? escape_output($item['category_name']) : 'Kategori Yok'; ?>
                                                    </small>
                                                    <span class="badge bg-<?php echo $item['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $item['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="card-footer bg-transparent">
                                                <div class="btn-group w-100">
                                                    <a href="edit.php?id=<?php echo $item['id']; ?>&type=<?php echo $item['type']; ?>" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-edit"></i> Düzenle
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo $item['type']; ?>')">
                                                        <i class="fas fa-trash"></i> Sil
                                                    </button>
                                                </div>
                                                <small class="text-muted d-block mt-2 text-center">
                                                    <?php echo date('d.m.Y H:i', strtotime($item['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Sayfa navigasyonu">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&type=<?php echo urlencode($type_filter); ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>">Önceki</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo urlencode($type_filter); ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&type=<?php echo urlencode($type_filter); ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>">Sonraki</a>
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
                <h5 class="modal-title" id="deleteModalLabel">Galeri Öğesini Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bu galeri öğesini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <input type="hidden" name="type" id="delete-type">
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteItem(id, type) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-type').value = type;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Bulk selection functionality
document.addEventListener('DOMContentLoaded', function() {
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
    
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButtons);
    });
    
    document.getElementById('select-all').addEventListener('click', function() {
        const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
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