<?php
/**
 * BonusBoss Admin Panel Configuration
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Main config dosyasını dahil et
require_once '../../includes/config.php';

// Admin session kontrolü
function require_admin_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../index.php');
        exit();
    }
}

// CSRF token validasyonu
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Admin log fonksiyonu
function admin_log($action, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $action,
            $details,
            get_client_ip(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (PDOException $e) {
        // Silent fail for logging
    }
}

// Upload dizinlerini oluştur
function ensure_upload_directories() {
    $directories = [
        '../../uploads',
        '../../uploads/services',
        '../../uploads/portfolio',
        '../../uploads/portfolio/gallery',
        '../../uploads/gallery',
        '../../uploads/gallery/thumbs'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Dosya yükleme fonksiyonu
function upload_admin_file($file, $upload_dir, $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'], $max_size = 10485760) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Dosya yükleme hatası!'];
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Geçersiz dosya türü!'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Dosya boyutu çok büyük!'];
    }
    
    // Dizini oluştur
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = time() . '_' . uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Dosya yükleme başarısız!'];
    }
}

// Dosya silme fonksiyonu
function delete_admin_file($file_path) {
    if ($file_path && file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

// URL slug oluşturma
function generate_slug($string) {
    $string = trim($string);
    $string = mb_strtolower($string, 'UTF-8');
    
    // Türkçe karakterleri değiştir
    $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü'];
    $english = ['c', 'g', 'i', 'o', 's', 'u'];
    $string = str_replace($turkish, $english, $string);
    
    // Özel karakterleri kaldır
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    
    return $string;
}

// Admin mesaj sistemi
function set_admin_message($message, $type = 'success') {
    $_SESSION['admin_message'] = [
        'text' => $message,
        'type' => $type
    ];
}

function get_admin_message() {
    if (isset($_SESSION['admin_message'])) {
        $message = $_SESSION['admin_message'];
        unset($_SESSION['admin_message']);
        return $message;
    }
    return null;
}

// Form validasyonu
function validate_required_fields($data, $required_fields) {
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[] = ucfirst($field) . ' alanı gereklidir!';
        }
    }
    
    return $errors;
}

// Admin istatistikleri
function get_admin_stats() {
    global $pdo;
    
    $stats = [
        'services' => 0,
        'portfolio' => 0,
        'gallery_photos' => 0,
        'gallery_videos' => 0,
        'messages' => 0,
        'categories' => 0
    ];
    
    try {
        // Services count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
        $stats['services'] = $stmt->fetch()['count'];
        
        // Portfolio count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM portfolio WHERE status = 'active'");
        $stats['portfolio'] = $stmt->fetch()['count'];
        
        // Gallery photos count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_photos WHERE status = 'active'");
        $stats['gallery_photos'] = $stmt->fetch()['count'];
        
        // Gallery videos count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_videos WHERE status = 'active'");
        $stats['gallery_videos'] = $stmt->fetch()['count'];
        
        // Messages count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
        $stats['messages'] = $stmt->fetch()['count'];
        
        // Categories count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $stats['categories'] = $stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        // Silent fail
    }
    
    return $stats;
}

// Upload dizinlerini oluştur
ensure_upload_directories();
?>