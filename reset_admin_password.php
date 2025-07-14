<?php
// Admin şifresini sıfırlamak için geçici dosya
// Kullanım: Bu dosyayı çalıştırın, sonra silin!

require_once 'includes/config.php';

// Yeni şifre belirleme
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // Admin kullanıcısının şifresini güncelle
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin' AND role = 'admin'");
    $result = $stmt->execute([$hashed_password]);
    
    if ($result) {
        echo "✅ Admin şifresi başarıyla güncellendi!<br>";
        echo "👤 Kullanıcı adı: admin<br>";
        echo "🔑 Yeni şifre: " . $new_password . "<br>";
        echo "🔐 Hash: " . $hashed_password . "<br>";
        echo "<br>🚨 GÜVENLİK: Bu dosyayı hemen silin!";
    } else {
        echo "❌ Şifre güncellenemedi!";
    }
} catch (PDOException $e) {
    echo "❌ Veritabanı hatası: " . $e->getMessage();
}
?>