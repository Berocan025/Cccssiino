<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$page_title = 'Mesaj Yönetimi';
$page_subtitle = 'Gelen mesajları görüntüle ve yönet';
$page_header = true;

$breadcrumbs = [
    ['title' => 'Mesajlar']
];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['admin_error'] = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        header("Location: index.php");
        exit();
    }
    
    $action = sanitize_input($_POST['action'] ?? '');
    
    if ($action === 'mark_read' && isset($_POST['message_id'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        if ($stmt->execute([$message_id])) {
            $_SESSION['admin_success'] = 'Mesaj okundu olarak işaretlendi.';
        }
    }
    
    if ($action === 'mark_unread' && isset($_POST['message_id'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?");
        if ($stmt->execute([$message_id])) {
            $_SESSION['admin_success'] = 'Mesaj okunmadı olarak işaretlendi.';
        }
    }
    
    if ($action === 'delete' && isset($_POST['message_id'])) {
        $message_id = (int)$_POST['message_id'];
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        if ($stmt->execute([$message_id])) {
            $_SESSION['admin_success'] = 'Mesaj silindi.';
        }
    }
    
    if ($action === 'bulk_action' && isset($_POST['bulk_action']) && isset($_POST['message_ids'])) {
        $bulk_action = $_POST['bulk_action'];
        $message_ids = $_POST['message_ids'];
        
        if (!empty($message_ids)) {
            $placeholders = str_repeat('?,', count($message_ids) - 1) . '?';
            
            switch ($bulk_action) {
                case 'mark_read':
                    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id IN ($placeholders)");
                    if ($stmt->execute($message_ids)) {
                        $_SESSION['admin_success'] = count($message_ids) . ' mesaj okundu olarak işaretlendi.';
                    }
                    break;
                    
                case 'mark_unread':
                    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 0 WHERE id IN ($placeholders)");
                    if ($stmt->execute($message_ids)) {
                        $_SESSION['admin_success'] = count($message_ids) . ' mesaj okunmadı olarak işaretlendi.';
                    }
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
                    if ($stmt->execute($message_ids)) {
                        $_SESSION['admin_success'] = count($message_ids) . ' mesaj silindi.';
                    }
                    break;
            }
        }
    }
    
    header("Location: index.php");
    exit();
}

// Get messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($filter === 'unread') {
    $where_conditions[] = 'is_read = 0';
} elseif ($filter === 'read') {
    $where_conditions[] = 'is_read = 1';
}

if (!empty($search)) {
    $where_conditions[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM contact_messages $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_messages = $count_stmt->fetch()['total'];

// Get messages
$sql = "SELECT * FROM contact_messages $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$total_pages = ceil($total_messages / $per_page);

include '../includes/admin_header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="d-flex gap-2">
            <a href="?filter=" class="btn btn-outline-primary <?php echo ($filter === '') ? 'active' : ''; ?>">
                Tümü (<?php echo $total_messages; ?>)
            </a>
            <a href="?filter=unread" class="btn btn-outline-warning <?php echo ($filter === 'unread') ? 'active' : ''; ?>">
                Okunmamış
            </a>
            <a href="?filter=read" class="btn btn-outline-success <?php echo ($filter === 'read') ? 'active' : ''; ?>">
                Okunmuş
            </a>
        </div>
    </div>
    <div class="col-md-4">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control" placeholder="Mesajlarda ara..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<div class="card shadow">
    <div class="card-header">
        <h3 class="mb-0">Mesajlar</h3>
    </div>
    <div class="card-body">
        <?php if (empty($messages)): ?>
            <div class="text-center py-5">
                <i class="fas fa-envelope fa-3x text-gray-300 mb-3"></i>
                <h4>Mesaj bulunmuyor</h4>
                <p class="text-muted">Henüz hiç mesaj gelmemiş.</p>
            </div>
        <?php else: ?>
            <form method="POST" id="bulk-form">
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="select-all">
                                <label class="form-check-label" for="select-all">
                                    Tümünü Seç
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <select name="bulk_action" class="form-select" style="width: auto;">
                                    <option value="">Toplu İşlem</option>
                                    <option value="mark_read">Okundu İşaretle</option>
                                    <option value="mark_unread">Okunmadı İşaretle</option>
                                    <option value="delete">Sil</option>
                                </select>
                                <button type="submit" class="btn btn-primary" onclick="return confirmBulkAction()">
                                    Uygula
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="action" value="bulk_action">
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="select-all-header">
                                </th>
                                <th>Gönderen</th>
                                <th>Konu</th>
                                <th>Mesaj</th>
                                <th>Tarih</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                                <tr class="<?php echo !$message['is_read'] ? 'table-warning' : ''; ?>">
                                    <td>
                                        <input type="checkbox" name="message_ids[]" value="<?php echo $message['id']; ?>" class="message-checkbox">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($message['email']); ?></small>
                                        <?php if (!empty($message['phone'])): ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($message['phone']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($message['subject']); ?></strong>
                                    </td>
                                    <td>
                                        <div class="message-preview">
                                            <?php echo htmlspecialchars(substr($message['message'], 0, 100)) . '...'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($message['is_read']): ?>
                                            <span class="badge bg-success">Okundu</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Yeni</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewMessage(<?php echo $message['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (!$message['is_read']): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="markAsRead(<?php echo $message['id']; ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="markAsUnread(<?php echo $message['id']; ?>)">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteMessage(<?php echo $message['id']; ?>)">
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
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="d-flex justify-content-center mt-4">
        <nav>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>">
                            Önceki
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>">
                            Sonraki
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php endif; ?>

<!-- Message View Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mesaj Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageContent">
                <!-- Message content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.message-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

document.getElementById('select-all-header').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.message-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// View message
function viewMessage(messageId) {
    fetch(`view_message.php?id=${messageId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('messageContent').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('messageModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Mesaj yüklenirken hata oluştu.');
        });
}

// Mark as read
function markAsRead(messageId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="mark_read">
        <input type="hidden" name="message_id" value="${messageId}">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Mark as unread
function markAsUnread(messageId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="mark_unread">
        <input type="hidden" name="message_id" value="${messageId}">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Delete message
function deleteMessage(messageId) {
    if (confirm('Bu mesajı silmek istediğinize emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="message_id" value="${messageId}">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Confirm bulk action
function confirmBulkAction() {
    const selectedCheckboxes = document.querySelectorAll('.message-checkbox:checked');
    const bulkAction = document.querySelector('select[name="bulk_action"]').value;
    
    if (selectedCheckboxes.length === 0) {
        alert('Lütfen en az bir mesaj seçin.');
        return false;
    }
    
    if (!bulkAction) {
        alert('Lütfen bir işlem seçin.');
        return false;
    }
    
    const actionText = {
        'mark_read': 'okundu olarak işaretlemek',
        'mark_unread': 'okunmadı olarak işaretlemek',
        'delete': 'silmek'
    };
    
    return confirm(`${selectedCheckboxes.length} mesajı ${actionText[bulkAction]} istediğinize emin misiniz?`);
}

// Auto-refresh every 30 seconds
setInterval(function() {
    // Only refresh if no modal is open
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
</script>

<style>
.message-preview {
    max-width: 300px;
    word-wrap: break-word;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.gap-2 {
    gap: 0.5rem;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>

<?php include '../includes/admin_footer.php'; ?>