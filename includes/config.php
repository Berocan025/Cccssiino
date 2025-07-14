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

// Veritabanı bağlantı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'bonusboss');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site ayarları
define('SITE_URL', 'http://localhost/bonusboss'); // Production'da gerçek URL
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

// Veritabanı bağlantısı
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        )
    );
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Mysqli bağlantısı (eski kodlar için)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Veritabanı bağlantı hatası: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

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
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function get_current_url() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
           "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function get_client_ip() {
    $ipkeys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
    foreach ($ipkeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'];
}

// Güvenlik kontrolü
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
    return preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $phone);
}

function generate_slug($string) {
    $turkish = array('ş','Ş','ı','I','İ','ğ','Ğ','ü','Ü','ö','Ö','ç','Ç');
    $english = array('s','s','i','i','i','g','g','u','u','o','o','c','c');
    $string = str_replace($turkish, $english, $string);
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

function format_date($date, $format = 'd.m.Y') {
    return date($format, strtotime($date));
}

function format_datetime($datetime, $format = 'd.m.Y H:i') {
    return date($format, strtotime($datetime));
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'şimdi';
    if ($time < 3600) return floor($time/60) . ' dakika önce';
    if ($time < 86400) return floor($time/3600) . ' saat önce';
    if ($time < 2592000) return floor($time/86400) . ' gün önce';
    if ($time < 31536000) return floor($time/2592000) . ' ay önce';
    
    return floor($time/31536000) . ' yıl önce';
}

function file_size_format($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Maintenance mode kontrolü
function check_maintenance_mode() {
    $maintenance = get_setting('maintenance_mode');
    if ($maintenance && !is_admin_logged_in()) {
        include 'maintenance.php';
        exit();
    }
}

// Admin giriş kontrolü
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        redirect('login.php');
    }
}

// İstatistikler
function get_site_stats() {
    global $pdo;
    
    $stats = array();
    
    // Toplam mesaj sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
    $stats['total_messages'] = $stmt->fetch()['count'];
    
    // Okunmamış mesaj sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
    $stats['unread_messages'] = $stmt->fetch()['count'];
    
    // Toplam portföy sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM portfolio WHERE status = 'active'");
    $stats['total_portfolio'] = $stmt->fetch()['count'];
    
    // Toplam hizmet sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
    $stats['total_services'] = $stmt->fetch()['count'];
    
    // Toplam galeri fotoğrafı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_photos WHERE status = 'active'");
    $stats['total_photos'] = $stmt->fetch()['count'];
    
    // Toplam galeri videosu
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_videos WHERE status = 'active'");
    $stats['total_videos'] = $stmt->fetch()['count'];
    
    return $stats;
}

// Log fonksiyonu
function write_log($message, $type = 'info') {
    $log_file = 'logs/site_' . date('Y-m-d') . '.log';
    $log_message = date('Y-m-d H:i:s') . ' [' . strtoupper($type) . '] ' . $message . PHP_EOL;
    
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

// Hata yakalama
function handle_error($errno, $errstr, $errfile, $errline) {
    $error_message = "Error: [$errno] $errstr - $errfile:$errline";
    write_log($error_message, 'error');
    
    if (ini_get('display_errors')) {
        echo "<div style='color: red; padding: 10px; background: #ffebee; border: 1px solid #f44336; margin: 10px;'>";
        echo "<strong>Error:</strong> $error_message";
        echo "</div>";
    }
    
    return true;
}

set_error_handler('handle_error');

// Başlık güvenliği
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Dosya dahil etme
require_once 'functions.php';

// Sayfa başlama zamanı (performans ölçümü için)
define('START_TIME', microtime(true));

// Bakım modu kontrolü
check_maintenance_mode();

// Oturum timeout kontrolü
if (is_admin_logged_in()) {
    if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > ADMIN_SESSION_TIMEOUT)) {
        session_destroy();
        redirect('login.php?timeout=1');
    }
    $_SESSION['admin_last_activity'] = time();
}

?>