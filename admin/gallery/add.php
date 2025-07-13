<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('../index.php');
    exit();
}

$page_title = 'Yeni Galeri Ekle';
$page_subtitle = 'Yeni fotoğraf veya video ekle';
$page_header = true;

$breadcrumbs = [
    ['title' => 'Galeri Yönetimi', 'url' => 'index.php'],
    ['title' => 'Yeni Galeri Ekle']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['admin_error'] = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        header("Location: add.php");
        exit();
    }
    
    $title = sanitize_input($_POST['title'] ?? '');
    $description = $_POST['description'] ?? '';
    $type = sanitize_input($_POST['type'] ?? 'photo');
    $video_url = sanitize_input($_POST['video_url'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Başlık gereklidir.';
    }
    
    if ($type === 'video' && empty($video_url)) {
        $errors[] = 'Video URL\'si gereklidir.';
    }
    
    // File upload for photos
    $image_path = '';
    if ($type === 'photo' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/gallery/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'uploads/gallery/' . $filename;
            } else {
                $errors[] = 'Dosya yüklenirken hata oluştu.';
            }
        } else {
            $errors[] = 'Geçersiz dosya formatı. JPG, PNG, GIF veya WEBP dosyası yükleyin.';
        }
    } elseif ($type === 'photo') {
        $errors[] = 'Fotoğraf dosyası seçilmelidir.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, description, type, image_path, video_url, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$title, $description, $type, $image_path, $video_url, $is_active])) {
                $_SESSION['admin_success'] = 'Galeri öğesi başarıyla eklendi.';
                header("Location: index.php");
                exit();
            } else {
                $errors[] = 'Galeri öğesi eklenirken hata oluştu.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<?php include '../includes/admin_header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Yeni Galeri Ekle</h5>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo escape_output($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="title">Başlık *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo escape_output($_POST['title'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Açıklama</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"><?php echo escape_output($_POST['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="type">Medya Tipi *</label>
                                    <select class="form-control" id="type" name="type" required onchange="toggleMediaFields()">
                                        <option value="photo" <?php echo ($_POST['type'] ?? 'photo') === 'photo' ? 'selected' : ''; ?>>Fotoğraf</option>
                                        <option value="video" <?php echo ($_POST['type'] ?? '') === 'video' ? 'selected' : ''; ?>>Video</option>
                                    </select>
                                </div>
                                
                                <div id="photo_field" class="form-group">
                                    <label for="image">Fotoğraf Dosyası *</label>
                                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                                    <small class="form-text text-muted">JPG, PNG, GIF veya WEBP formatında olmalıdır.</small>
                                </div>
                                
                                <div id="video_field" class="form-group" style="display: none;">
                                    <label for="video_url">Video URL'si *</label>
                                    <input type="url" class="form-control" id="video_url" name="video_url" 
                                           value="<?php echo escape_output($_POST['video_url'] ?? ''); ?>" 
                                           placeholder="https://www.youtube.com/watch?v=VIDEO_ID">
                                    <small class="form-text text-muted">YouTube, Vimeo veya diğer video platformu URL'si</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                               <?php echo !isset($_POST) || isset($_POST['is_active']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Aktif
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <small class="font-weight-bold">Önizleme</small>
                                    </div>
                                    <div class="card-body">
                                        <div id="preview_area" class="text-center">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                            <p class="text-muted mt-2">Dosya seçildikten sonra önizleme burada görünecek</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Galeri Ekle
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMediaFields() {
    const type = document.getElementById('type').value;
    const photoField = document.getElementById('photo_field');
    const videoField = document.getElementById('video_field');
    const imageInput = document.getElementById('image');
    const videoInput = document.getElementById('video_url');
    
    if (type === 'photo') {
        photoField.style.display = 'block';
        videoField.style.display = 'none';
        imageInput.required = true;
        videoInput.required = false;
    } else {
        photoField.style.display = 'none';
        videoField.style.display = 'block';
        imageInput.required = false;
        videoInput.required = true;
    }
}

// File preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewArea = document.getElementById('preview_area');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewArea.innerHTML = `
                <img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">
                <p class="text-muted mt-2 small">${file.name}</p>
            `;
        };
        reader.readAsDataURL(file);
    }
});

// Initialize field visibility
document.addEventListener('DOMContentLoaded', function() {
    toggleMediaFields();
});
</script>

<?php include '../includes/admin_footer.php'; ?>