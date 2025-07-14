<?php
require_once '../includes/config.php';

// CSRF token kontrolü
if (isset($_POST['logout']) && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('CSRF token mismatch');
    }
}

// Kullanıcı bilgilerini logla
if (isset($_SESSION['admin_username'])) {
    write_log("Admin logout: " . $_SESSION['admin_username'], 'info');
}

// Session'ı temizle
session_unset();
session_destroy();

// Remember me cookie'sini sil
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/admin/');
}

// Yeni session başlat ve success mesajı ekle
session_start();
$_SESSION['logout_success'] = 'Başarıyla çıkış yaptınız.';

// Giriş sayfasına yönlendir
redirect('index.php');
exit();
?>