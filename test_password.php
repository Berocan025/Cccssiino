<?php
// Şifre test dosyası - kullandıktan sonra silin!

// Test edilecek şifreler
$test_passwords = ['admin123', 'password', 'secret', 'admin', '123456'];

// Veritabanından hash'i al
require_once 'includes/config.php';

try {
    $stmt = $pdo->prepare("SELECT username, password FROM users WHERE username = 'admin' AND role = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<h2>🔍 Şifre Test Sonuçları</h2>";
        echo "<strong>Kullanıcı:</strong> " . $user['username'] . "<br>";
        echo "<strong>Hash:</strong> " . $user['password'] . "<br><br>";
        
        echo "<h3>Test Edilen Şifreler:</h3>";
        foreach ($test_passwords as $password) {
            $result = password_verify($password, $user['password']);
            echo "🔑 <strong>$password</strong> → " . ($result ? "✅ DOĞRU" : "❌ YANLIŞ") . "<br>";
        }
        
        echo "<br><h3>🔧 Yeni Şifre Oluştur:</h3>";
        $new_password = 'admin123';
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        echo "Yeni şifre: <strong>$new_password</strong><br>";
        echo "Yeni hash: <strong>$new_hash</strong><br>";
        
        // Şifreyi güncelle
        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin' AND role = 'admin'");
        if ($update_stmt->execute([$new_hash])) {
            echo "<br>✅ <strong>Şifre güncellendi! Artık 'admin123' ile giriş yapabilirsiniz.</strong>";
        } else {
            echo "<br>❌ Şifre güncellenemedi!";
        }
        
    } else {
        echo "❌ Admin kullanıcısı bulunamadı!";
    }
} catch (PDOException $e) {
    echo "❌ Veritabanı hatası: " . $e->getMessage();
}

echo "<br><br>🚨 <strong>GÜVENLİK UYARISI: Bu dosyayı hemen silin!</strong>";
?>