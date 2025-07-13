<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('Unauthorized');
}

$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$message_id) {
    http_response_code(400);
    exit('Invalid message ID');
}

// Get message details
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$message_id]);
$message = $stmt->fetch();

if (!$message) {
    http_response_code(404);
    exit('Message not found');
}

// Mark as read when viewed
if (!$message['is_read']) {
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$message_id]);
}
?>

<div class="message-detail">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6 class="text-muted">Gönderen:</h6>
            <p class="mb-0">
                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                <br>
                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="text-primary">
                    <?php echo htmlspecialchars($message['email']); ?>
                </a>
                <?php if (!empty($message['phone'])): ?>
                    <br>
                    <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>" class="text-primary">
                        <?php echo htmlspecialchars($message['phone']); ?>
                    </a>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted">Tarih:</h6>
            <p class="mb-0"><?php echo date('d.m.Y H:i:s', strtotime($message['created_at'])); ?></p>
            
            <h6 class="text-muted mt-3">Durum:</h6>
            <p class="mb-0">
                <?php if ($message['is_read']): ?>
                    <span class="badge bg-success">Okundu</span>
                <?php else: ?>
                    <span class="badge bg-warning">Yeni</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-12">
            <h6 class="text-muted">Konu:</h6>
            <p class="mb-0"><strong><?php echo htmlspecialchars($message['subject']); ?></strong></p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <h6 class="text-muted">Mesaj:</h6>
            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" onclick="replyToMessage()">
                    <i class="fas fa-reply"></i> Yanıtla
                </button>
                <button type="button" class="btn btn-success" onclick="copyEmail()">
                    <i class="fas fa-copy"></i> E-posta Kopyala
                </button>
                <?php if (!empty($message['phone'])): ?>
                    <button type="button" class="btn btn-info" onclick="copyPhone()">
                        <i class="fas fa-phone"></i> Telefon Kopyala
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-danger" onclick="deleteFromModal(<?php echo $message['id']; ?>)">
                    <i class="fas fa-trash"></i> Sil
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Reply to message
function replyToMessage() {
    const email = '<?php echo htmlspecialchars($message['email']); ?>';
    const subject = 'Re: <?php echo htmlspecialchars($message['subject']); ?>';
    const name = '<?php echo htmlspecialchars($message['name']); ?>';
    
    const body = `Merhaba ${name},\n\nMesajınız için teşekkürler.\n\n\n\nSaygılarımla,\nBonusBoss`;
    
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.open(mailtoLink);
}

// Copy email to clipboard
function copyEmail() {
    const email = '<?php echo htmlspecialchars($message['email']); ?>';
    navigator.clipboard.writeText(email).then(function() {
        showNotification('E-posta adresi kopyalandı', 'success');
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = email;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('E-posta adresi kopyalandı', 'success');
    });
}

// Copy phone to clipboard
function copyPhone() {
    const phone = '<?php echo htmlspecialchars($message['phone']); ?>';
    navigator.clipboard.writeText(phone).then(function() {
        showNotification('Telefon numarası kopyalandı', 'success');
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = phone;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Telefon numarası kopyalandı', 'success');
    });
}

// Delete message from modal
function deleteFromModal(messageId) {
    if (confirm('Bu mesajı silmek istediğinize emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="message_id" value="${messageId}">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Show notification
function showNotification(message, type) {
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
</script>

<style>
.message-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid var(--primary-gold);
    max-height: 400px;
    overflow-y: auto;
    word-wrap: break-word;
    line-height: 1.6;
}

.message-detail .row {
    margin-bottom: 1rem;
}

.message-detail h6 {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--dark-blue);
}

.gap-2 {
    gap: 0.5rem;
}

.btn i {
    margin-right: 0.25rem;
}
</style>