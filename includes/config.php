<?php
/**
 * BonusBoss Portfolio Website Configuration
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Hata raporlama (production'da kapatılacak)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Oturum başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// SQLite veritabanı ayarları
define('DB_PATH', __DIR__ . '/../database/bonusboss.db');
define('DB_CHARSET', 'utf8mb4');

// Site ayarları
define('SITE_URL', 'http://localhost:8000'); // Production'da gerçek URL
define('SITE_NAME', 'BonusBoss');
define('SITE_TITLE', 'BonusBoss - Profesyonel Casino Yayıncısı');
define('SITE_DESCRIPTION', 'Kazançlı ortaklıklar için doğru adres');
define('SITE_KEYWORDS', 'casino, bonus, boss, yayıncı, ortaklık, canlı yayın');
define('SITE_AUTHOR', 'BERAT K');
define('SITE_VERSION', '1.0');

// Dosya yolu ayarları
define('UPLOAD_PATH', 'assets/uploads/');
define('PORTFOLIO_UPLOAD_PATH', 'assets/uploads/portfolio/');
define('GALLERY_UPLOAD_PATH', 'assets/uploads/gallery/');
define('LOGO_UPLOAD_PATH', 'assets/uploads/logos/');

// Dosya boyutu sınırları (MB)
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB

// İzin verilen dosya türleri
define('ALLOWED_IMAGE_TYPES', array('jpg', 'jpeg', 'png', 'gif', 'webp'));
define('ALLOWED_FILE_TYPES', array('pdf', 'doc', 'docx', 'txt'));

// Güvenlik ayarları
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 saat
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCK_TIME', 300); // 5 dakika

// Email ayarları
define('CONTACT_EMAIL', 'info@bonusboss.com');
define('ADMIN_EMAIL', 'admin@bonusboss.com');
define('NOREPLY_EMAIL', 'noreply@bonusboss.com');

// Sosyal medya ayarları
define('FACEBOOK_URL', 'https://facebook.com/bonusboss');
define('TWITTER_URL', 'https://twitter.com/bonusboss');
define('INSTAGRAM_URL', 'https://instagram.com/bonusboss');
define('TELEGRAM_URL', 'https://t.me/bonusboss');
define('YOUTUBE_URL', 'https://youtube.com/bonusboss');

// Cache ayarları
define('CACHE_ENABLED', true);
define('CACHE_TIME', 3600); // 1 saat

// Sayfa ayarları
define('POSTS_PER_PAGE', 12);
define('PORTFOLIO_PER_PAGE', 9);
define('GALLERY_PER_PAGE', 20);

// SQLite veritabanı bağlantısı
try {
    // Veritabanı dosyası yoksa oluştur
    if (!file_exists(DB_PATH)) {
        $db_dir = dirname(DB_PATH);
        if (!is_dir($db_dir)) {
            mkdir($db_dir, 0755, true);
        }
        touch(DB_PATH);
        chmod(DB_PATH, 0664);
    }

    $pdo = new PDO(
        "sqlite:" . DB_PATH,
        null,
        null,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
    
    // SQLite pragmaları
    $pdo->exec("PRAGMA foreign_keys = ON");
    $pdo->exec("PRAGMA journal_mode = WAL");
    $pdo->exec("PRAGMA synchronous = NORMAL");
    $pdo->exec("PRAGMA cache_size = 1000");
    $pdo->exec("PRAGMA temp_store = MEMORY");
    
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Timezone ayarı
date_default_timezone_set('Europe/Istanbul');

// Çıkış buffer'ı başlat
ob_start();

// CSRF token oluşturma
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Yardımcı fonksiyonlar

function escape_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function get_current_url() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function get_client_ip() {
    $ip_keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    return preg_match('/^[\+]?[0-9\s\-\(\)]{7,15}$/', $phone);
}

function generate_slug($string) {
    $string = trim($string);
    $string = mb_strtolower($string, 'UTF-8');
    
    // Türkçe karakterleri değiştir
    $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü'];
    $english = ['c', 'g', 'i', 'o', 's', 'u'];
    $string = str_replace($turkish, $english, $string);
    
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

function format_date($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

function format_datetime($datetime, $format = 'd.m.Y H:i') {
    return date($format, strtotime($datetime));
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'Az önce';
    if ($time < 3600) return floor($time/60) . ' dakika önce';
    if ($time < 86400) return floor($time/3600) . ' saat önce';
    if ($time < 2592000) return floor($time/86400) . ' gün önce';
    if ($time < 31104000) return floor($time/2592000) . ' ay önce';
    return floor($time/31104000) . ' yıl önce';
}

function file_size_format($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function check_maintenance_mode() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result && $result['setting_value'] == '1';
    } catch (PDOException $e) {
        return false;
    }
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        redirect('/admin/index.php');
    }
}

function get_site_stats() {
    global $pdo;
    
    $stats = array(
        'total_services' => 0,
        'total_portfolio' => 0,
        'total_gallery' => 0,
        'total_messages' => 0,
        'unread_messages' => 0
    );
    
    try {
        // Services count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
        $result = $stmt->fetch();
        $stats['total_services'] = $result ? (int)$result['count'] : 0;
        
        // Portfolio count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM portfolio WHERE status = 'active'");
        $result = $stmt->fetch();
        $stats['total_portfolio'] = $result ? (int)$result['count'] : 0;
        
        // Gallery photos count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_photos WHERE status = 'active'");
        $result = $stmt->fetch();
        $gallery_photos = $result ? (int)$result['count'] : 0;
        
        // Gallery videos count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_videos WHERE status = 'active'");
        $result = $stmt->fetch();
        $gallery_videos = $result ? (int)$result['count'] : 0;
        
        $stats['total_gallery'] = $gallery_photos + $gallery_videos;
        
        // Total messages count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
        $result = $stmt->fetch();
        $stats['total_messages'] = $result ? (int)$result['count'] : 0;
        
        // Unread messages count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
        $result = $stmt->fetch();
        $stats['unread_messages'] = $result ? (int)$result['count'] : 0;
        
    } catch (PDOException $e) {
        write_log("Stats error: " . $e->getMessage(), 'error');
    }
    
    return $stats;
}

function write_log($message, $type = 'info') {
    $log_file = __DIR__ . '/../logs/app.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$type] $message" . PHP_EOL;
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

function handle_error($errno, $errstr, $errfile, $errline) {
    $error_message = "Error: [$errno] $errstr in $errfile on line $errline";
    write_log($error_message, 'error');
    
    if (ini_get('display_errors')) {
        echo $error_message;
    }
    
    return false;
}

// Error handler ayarla
set_error_handler('handle_error');

// Veritabanını başlat
function init_database() {
    global $pdo;
    
    $sql_file = __DIR__ . '/../database/bonusboss_sqlite.sql';
    
    if (!file_exists($sql_file)) {
        die("SQL dosyası bulunamadı: $sql_file");
    }
    
    try {
        // Tablolar var mı kontrol et
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sqlite_master WHERE type='table' AND name='users'");
        $result = $stmt->fetch();
        
        if (!$result || $result['count'] == 0) {
            // Veritabanını oluştur
            $sql = file_get_contents($sql_file);
            $pdo->exec($sql);
            write_log("Database initialized successfully", 'info');
        }
    } catch (PDOException $e) {
        die("Veritabanı başlatma hatası: " . $e->getMessage());
    }
}

// Veritabanını başlat
init_database();

// Functions dosyasını dahil et
if (file_exists(__DIR__ . '/functions.php')) {
    require_once __DIR__ . '/functions.php';
}

?>