<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$page_title = 'Metin Yönetimi';
$page_subtitle = 'Site metinlerini düzenle ve yönet';
$page_header = true;

$breadcrumbs = [
    ['title' => 'Metin Yönetimi']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_text' && isset($_POST['text_id']) && isset($_POST['text_value'])) {
        $text_id = (int)$_POST['text_id'];
        $text_value = $_POST['text_value'];
        
        $stmt = $pdo->prepare("UPDATE site_texts SET text_value = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$text_value, $text_id])) {
            $_SESSION['admin_success'] = 'Metin güncellendi.';
        } else {
            $_SESSION['admin_error'] = 'Metin güncellenirken hata oluştu.';
        }
    }
    
    if ($action === 'add_text' && isset($_POST['text_key']) && isset($_POST['text_value'])) {
        $text_key = sanitize_input($_POST['text_key']);
        $text_value = $_POST['text_value'];
        $category = sanitize_input($_POST['category']);
        $description = sanitize_input($_POST['description']);
        
        // Check if key already exists
        $stmt = $pdo->prepare("SELECT id FROM site_texts WHERE text_key = ?");
        $stmt->execute([$text_key]);
        
        if ($stmt->fetch()) {
            $_SESSION['admin_error'] = 'Bu anahtar zaten mevcut.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO site_texts (text_key, text_value, category, description, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt->execute([$text_key, $text_value, $category, $description])) {
                $_SESSION['admin_success'] = 'Yeni metin eklendi.';
            } else {
                $_SESSION['admin_error'] = 'Metin eklenirken hata oluştu.';
            }
        }
    }
    
    if ($action === 'delete_text' && isset($_POST['text_id'])) {
        $text_id = (int)$_POST['text_id'];
        
        $stmt = $pdo->prepare("DELETE FROM site_texts WHERE id = ?");
        if ($stmt->execute([$text_id])) {
            $_SESSION['admin_success'] = 'Metin silindi.';
        } else {
            $_SESSION['admin_error'] = 'Metin silinirken hata oluştu.';
        }
    }
    
    header("Location: index.php");
    exit();
}

// Get filter
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Get all categories
$stmt = $pdo->query("SELECT DISTINCT category FROM site_texts ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($category_filter)) {
    $where_conditions[] = 'category = ?';
    $params[] = $category_filter;
}

if (!empty($search)) {
    $where_conditions[] = '(text_key LIKE ? OR text_value LIKE ? OR description LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get texts
$sql = "SELECT * FROM site_texts $where_clause ORDER BY category, text_key";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$texts = $stmt->fetchAll();

// Group texts by category
$grouped_texts = [];
foreach ($texts as $text) {
    $grouped_texts[$text['category']][] = $text;
}

include '../includes/admin_header.php';
?>

<div class="row mb-4">
    <div class="col-md-4">
        <form method="GET" class="d-flex">
            <select name="category" class="form-select me-2" onchange="this.form.submit()">
                <option value="">Tüm Kategoriler</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category_filter === $category) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>
    <div class="col-md-4">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control" placeholder="Metinlerde ara..." value="<?php echo htmlspecialchars($search); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
            <button type="submit" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTextModal">
            <i class="fas fa-plus"></i> Yeni Metin Ekle
        </button>
    </div>
</div>

<?php if (empty($grouped_texts)): ?>
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-edit fa-3x text-gray-300 mb-3"></i>
            <h4>Metin bulunmuyor</h4>
            <p class="text-muted">Arama kriterlerinize uygun metin bulunamadı.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($grouped_texts as $category => $category_texts): ?>
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-folder-open me-2"></i>
                    <?php echo htmlspecialchars($category); ?>
                    <small class="text-muted">(<?php echo count($category_texts); ?> metin)</small>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($category_texts as $text): ?>
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-3 h-100">
                                <form method="POST" class="h-100 d-flex flex-column">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <strong><?php echo htmlspecialchars($text['text_key']); ?></strong>
                                            <?php if (!empty($text['description'])): ?>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($text['description']); ?></small>
                                            <?php endif; ?>
                                        </label>
                                        <?php if (strlen($text['text_value']) > 100): ?>
                                            <textarea name="text_value" class="form-control" rows="5" required><?php echo htmlspecialchars($text['text_value']); ?></textarea>
                                        <?php else: ?>
                                            <input type="text" name="text_value" class="form-control" value="<?php echo htmlspecialchars($text['text_value']); ?>" required>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <input type="hidden" name="action" value="update_text">
                                                <input type="hidden" name="text_id" value="<?php echo $text['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-save"></i> Kaydet
                                                </button>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteText(<?php echo $text['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            Son güncelleme: <?php echo date('d.m.Y H:i', strtotime($text['updated_at'] ?? $text['created_at'])); ?>
                                        </small>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Add Text Modal -->
<div class="modal fade" id="addTextModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Metin Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="text_key" class="form-label">Anahtar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="text_key" name="text_key" required 
                               placeholder="Örn: home_hero_title">
                        <small class="form-text text-muted">Yalnızca küçük harf, rakam ve alt çizgi kullanın.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Kategori Seçin</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" class="form-control mt-2" id="new_category" placeholder="Yeni kategori adı">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <input type="text" class="form-control" id="description" name="description" 
                               placeholder="Bu metnin ne için kullanıldığını açıklayın">
                    </div>
                    
                    <div class="mb-3">
                        <label for="text_value" class="form-label">Metin Değeri <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="text_value" name="text_value" rows="4" required 
                                  placeholder="Metin içeriğini buraya yazın..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="add_text">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Delete text function
function deleteText(textId) {
    if (confirm('Bu metni silmek istediğinize emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_text">
            <input type="hidden" name="text_id" value="${textId}">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Handle new category input
document.getElementById('new_category').addEventListener('input', function() {
    const newCategory = this.value.trim();
    if (newCategory) {
        document.getElementById('category').value = '';
        document.getElementById('category').name = '';
        this.name = 'category';
    } else {
        document.getElementById('category').name = 'category';
        this.name = '';
    }
});

// Validate text key format
document.getElementById('text_key').addEventListener('input', function() {
    this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
});

// Auto-save functionality
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[method="POST"]');
    forms.forEach(form => {
        if (form.querySelector('input[name="action"][value="update_text"]')) {
            const textValue = form.querySelector('input[name="text_value"], textarea[name="text_value"]');
            if (textValue) {
                let originalValue = textValue.value;
                let saveTimeout;
                
                textValue.addEventListener('input', function() {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        if (this.value !== originalValue && this.value.trim() !== '') {
                            // Auto-save
                            const formData = new FormData(form);
                            fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (response.ok) {
                                    originalValue = this.value;
                                    showNotification('Otomatik kaydedildi', 'success');
                                }
                            })
                            .catch(error => {
                                console.error('Auto-save error:', error);
                            });
                        }
                    }, 2000);
                });
            }
        }
    });
});

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        <strong>${message}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.classList.remove('show');
        setTimeout(function() {
            notification.remove();
        }, 150);
    }, 3000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        // Save all forms
        const forms = document.querySelectorAll('form[method="POST"]');
        forms.forEach(form => {
            if (form.querySelector('input[name="action"][value="update_text"]')) {
                form.submit();
            }
        });
    }
});
</script>

<style>
.border {
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.border:hover {
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

.form-control:focus {
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

.form-select:focus {
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-gold) 0%, var(--hover-gold) 100%);
    border: none;
    color: var(--dark-blue);
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--hover-gold) 0%, var(--primary-gold) 100%);
    transform: translateY(-1px);
}

.card-header {
    background: linear-gradient(135deg, var(--primary-gold) 0%, var(--hover-gold) 100%);
    color: var(--dark-blue);
    border-bottom: none;
}

.me-2 {
    margin-right: 0.5rem;
}

.ms-2 {
    margin-left: 0.5rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.justify-content-between {
    justify-content: space-between;
}

.text-end {
    text-align: right;
}

.h-100 {
    height: 100%;
}

.flex-column {
    flex-direction: column;
}

.mt-auto {
    margin-top: auto;
}
</style>

<?php include '../includes/admin_footer.php'; ?>