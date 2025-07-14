<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$page_title = 'Site Ayarları';
$page_subtitle = 'Genel site ayarlarını düzenle';
$page_header = true;

$breadcrumbs = [
    ['title' => 'Ayarlar']
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
    
    if ($action === 'update_settings') {
        $updates = 0;
        $errors = 0;
        
        foreach ($_POST as $key => $value) {
            if ($key !== 'action' && $key !== 'csrf_token') {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                if ($stmt->execute([$value, $key])) {
                    $updates++;
                } else {
                    $errors++;
                }
            }
        }
        
        if ($updates > 0) {
            $_SESSION['admin_success'] = "$updates ayar güncellendi.";
        }
        if ($errors > 0) {
            $_SESSION['admin_error'] = "$errors ayar güncellenirken hata oluştu.";
        }
    }
    
    if ($action === 'add_setting') {
        $setting_key = sanitize_input($_POST['setting_key']);
        $setting_value = $_POST['setting_value'];
        $category = sanitize_input($_POST['category']);
        $description = sanitize_input($_POST['description']);
        
        // Check if key already exists
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $stmt->execute([$setting_key]);
        
        if ($stmt->fetch()) {
            $_SESSION['admin_error'] = 'Bu ayar anahtarı zaten mevcut.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, category, description) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$setting_key, $setting_value, $category, $description])) {
                $_SESSION['admin_success'] = 'Yeni ayar eklendi.';
            } else {
                $_SESSION['admin_error'] = 'Ayar eklenirken hata oluştu.';
            }
        }
    }
    
    if ($action === 'delete_setting') {
        $setting_id = (int)$_POST['setting_id'];
        
        $stmt = $pdo->prepare("DELETE FROM settings WHERE id = ?");
        if ($stmt->execute([$setting_id])) {
            $_SESSION['admin_success'] = 'Ayar silindi.';
        } else {
            $_SESSION['admin_error'] = 'Ayar silinirken hata oluştu.';
        }
    }
    
    header("Location: index.php");
    exit();
}

// Get settings
$stmt = $pdo->query("SELECT * FROM settings ORDER BY category, setting_key");
$settings = $stmt->fetchAll();

// Group settings by category
$grouped_settings = [];
foreach ($settings as $setting) {
    $grouped_settings[$setting['category']][] = $setting;
}

// Get categories
$categories = array_keys($grouped_settings);

include '../includes/admin_header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                <i class="fas fa-plus"></i> Yeni Ayar Ekle
            </button>
            <button type="button" class="btn btn-success" onclick="saveAllSettings()">
                <i class="fas fa-save"></i> Tüm Ayarları Kaydet
            </button>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary" onclick="resetToDefaults()">
                <i class="fas fa-undo"></i> Varsayılan Değerler
            </button>
            <button type="button" class="btn btn-outline-info" onclick="exportSettings()">
                <i class="fas fa-download"></i> Ayarları Dışa Aktar
            </button>
        </div>
    </div>
</div>

<form method="POST" id="settingsForm">
    <input type="hidden" name="action" value="update_settings">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <?php foreach ($grouped_settings as $category => $category_settings): ?>
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    <?php echo htmlspecialchars($category); ?>
                    <small class="text-muted">(<?php echo count($category_settings); ?> ayar)</small>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($category_settings as $setting): ?>
                        <div class="col-md-6 mb-4">
                            <div class="setting-item">
                                <label class="form-label">
                                    <strong><?php echo htmlspecialchars($setting['setting_key']); ?></strong>
                                    <?php if (!empty($setting['description'])): ?>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($setting['description']); ?></small>
                                    <?php endif; ?>
                                </label>
                                
                                <?php
                                $input_type = 'text';
                                $is_textarea = false;
                                
                                // Determine input type based on setting key or value
                                if (strpos($setting['setting_key'], 'email') !== false) {
                                    $input_type = 'email';
                                } elseif (strpos($setting['setting_key'], 'url') !== false || strpos($setting['setting_key'], 'link') !== false) {
                                    $input_type = 'url';
                                } elseif (strpos($setting['setting_key'], 'phone') !== false) {
                                    $input_type = 'tel';
                                } elseif (strpos($setting['setting_key'], 'color') !== false) {
                                    $input_type = 'color';
                                } elseif (in_array($setting['setting_value'], ['true', 'false', '1', '0'])) {
                                    $input_type = 'checkbox';
                                } elseif (strlen($setting['setting_value']) > 100) {
                                    $is_textarea = true;
                                }
                                ?>
                                
                                <div class="input-group">
                                    <?php if ($input_type === 'checkbox'): ?>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" name="<?php echo htmlspecialchars($setting['setting_key']); ?>" 
                                                   id="setting_<?php echo $setting['id']; ?>" 
                                                   value="1" <?php echo (in_array($setting['setting_value'], ['true', '1']) ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="setting_<?php echo $setting['id']; ?>">
                                                <?php echo (in_array($setting['setting_value'], ['true', '1']) ? 'Aktif' : 'Pasif'); ?>
                                            </label>
                                        </div>
                                    <?php elseif ($is_textarea): ?>
                                        <textarea name="<?php echo htmlspecialchars($setting['setting_key']); ?>" 
                                                  class="form-control" rows="4" 
                                                  id="setting_<?php echo $setting['id']; ?>"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                    <?php else: ?>
                                        <input type="<?php echo $input_type; ?>" 
                                               name="<?php echo htmlspecialchars($setting['setting_key']); ?>" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                               id="setting_<?php echo $setting['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteSetting(<?php echo $setting['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <small class="text-muted">
                                    Son güncelleme: <?php echo date('d.m.Y H:i', strtotime($setting['updated_at'] ?? $setting['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save"></i> Ayarları Kaydet
        </button>
    </div>
</form>

<!-- Add Setting Modal -->
<div class="modal fade" id="addSettingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Ayar Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="setting_key" class="form-label">Ayar Anahtarı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="setting_key" name="setting_key" required>
                        <small class="form-text text-muted">Örn: site_title, contact_email</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" required>
                            <option value="">Kategori Seçin</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <input type="text" class="form-control" name="description" 
                               placeholder="Bu ayarın ne için kullanıldığını açıklayın">
                    </div>
                    
                    <div class="mb-3">
                        <label for="setting_value" class="form-label">Değer <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="setting_value" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="add_setting">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Save all settings
function saveAllSettings() {
    document.getElementById('settingsForm').submit();
}

// Delete setting
function deleteSetting(settingId) {
    if (confirm('Bu ayarı silmek istediğinize emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_setting">
            <input type="hidden" name="setting_id" value="${settingId}">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Reset to defaults
function resetToDefaults() {
    if (confirm('Tüm ayarları varsayılan değerlerine sıfırlamak istediğinize emin misiniz?')) {
        // This would require a separate endpoint to handle defaults
        alert('Bu özellik henüz uygulanmamıştır.');
    }
}

// Export settings
function exportSettings() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export.php';
    form.innerHTML = `
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Auto-save functionality
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('#settingsForm input, #settingsForm textarea');
    inputs.forEach(input => {
        let originalValue = input.value;
        let saveTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                if (this.value !== originalValue) {
                    showNotification('Değişiklik algılandı, kaydetmeyi unutmayın!', 'warning');
                }
            }, 1000);
        });
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
    }, 5000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveAllSettings();
    }
});

// Handle checkbox changes
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const label = this.parentElement.querySelector('label');
        if (label) {
            label.textContent = this.checked ? 'Aktif' : 'Pasif';
        }
        
        // Update hidden input for form submission
        let hiddenInput = this.parentElement.querySelector('input[type="hidden"]');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = this.name;
            this.parentElement.appendChild(hiddenInput);
        }
        hiddenInput.value = this.checked ? '1' : '0';
    });
});
</script>

<style>
.setting-item {
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.setting-item:hover {
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
}

.form-check-input:checked {
    background-color: var(--primary-gold);
    border-color: var(--primary-gold);
}

.form-check-input:focus {
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

.gap-2 {
    gap: 0.5rem;
}

.me-2 {
    margin-right: 0.5rem;
}

.d-flex {
    display: flex;
}

.text-end {
    text-align: right;
}

.input-group {
    position: relative;
}

.input-group .btn {
    border-radius: 0 0.375rem 0.375rem 0;
}

.form-switch .form-check-input {
    width: 2em;
    height: 1em;
    margin-left: 0;
}
</style>

<?php include '../includes/admin_footer.php'; ?>