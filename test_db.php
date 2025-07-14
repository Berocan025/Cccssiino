<?php
/**
 * SQLite Database Test and Initialization
 */

require_once 'includes/config.php';

echo "<!DOCTYPE html><html><head><title>Database Test</title></head><body>";
echo "<h1>BonusBoss SQLite Database Test</h1>";

try {
    // Veritabanı bağlantısını test et
    echo "<h2>Database Connection Test</h2>";
    echo "Database Path: " . DB_PATH . "<br>";
    echo "Database exists: " . (file_exists(DB_PATH) ? "Yes" : "No") . "<br>";
    
    // Tablolar kontrolü
    echo "<h2>Tables Check</h2>";
    $tables = ['users', 'settings', 'categories', 'services', 'portfolio', 'gallery_photos', 'gallery_videos', 'contact_messages', 'testimonials'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "$table: " . $result['count'] . " records<br>";
        } catch (PDOException $e) {
            echo "$table: ERROR - " . $e->getMessage() . "<br>";
        }
    }
    
    // Admin kullanıcısı kontrolü
    echo "<h2>Admin User Check</h2>";
    $stmt = $pdo->query("SELECT username, email, role FROM users WHERE role = 'admin'");
    $admin = $stmt->fetch();
    if ($admin) {
        echo "Admin User: " . $admin['username'] . " (" . $admin['email'] . ")<br>";
    } else {
        echo "No admin user found!<br>";
    }
    
    // Settings kontrolü
    echo "<h2>Settings Check</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch();
    echo "Settings count: " . $result['count'] . "<br>";
    
    echo "<h2>Success!</h2>";
    echo "<p>Database is working properly!</p>";
    echo "<a href='admin/index.php'>Go to Admin Panel</a> | ";
    echo "<a href='index.php'>Go to Website</a>";
    
} catch (PDOException $e) {
    echo "<h2>Database Error</h2>";
    echo "Error: " . $e->getMessage();
    echo "<br><br>Will try to create database now...<br>";
    
    try {
        // Veritabanı dosyasını oluştur
        $db_dir = dirname(DB_PATH);
        if (!is_dir($db_dir)) {
            mkdir($db_dir, 0755, true);
        }
        
        // SQL dosyasını oku ve çalıştır
        $sql_file = __DIR__ . '/database/bonusboss_sqlite.sql';
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            $pdo->exec($sql);
            echo "Database created successfully!<br>";
            echo "<a href='test_db.php'>Test Again</a>";
        } else {
            echo "SQL file not found: $sql_file<br>";
        }
    } catch (PDOException $e2) {
        echo "Failed to create database: " . $e2->getMessage();
    }
}

echo "</body></html>";
?>