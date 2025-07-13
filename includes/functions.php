<?php
/**
 * BonusBoss Portfolio Website Functions
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Doğrudan erişimi engelle
if (!defined('DB_HOST')) {
    die('Direct access not allowed');
}

/**
 * Site ayarlarını al
 */
function get_setting($key, $default = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        write_log("Setting error: " . $e->getMessage(), 'error');
        return $default;
    }
}

/**
 * Site ayarını güncelle
 */
function update_setting($key, $value) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
        return $stmt->execute([$value, $key]);
    } catch (PDOException $e) {
        write_log("Setting update error: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Kullanıcı girdilerini temizle
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Güvenli çıktı
 */
function escape_output($output) {
    return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
}

/**
 * Yönlendirme fonksiyonu
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Site metinlerini al
 */
function get_site_text($key, $default = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT text_value FROM site_texts WHERE text_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['text_value'] : $default;
    } catch (PDOException $e) {
        write_log("Site text error: " . $e->getMessage(), 'error');
        return $default;
    }
}

/**
 * Site metnini güncelle
 */
function update_site_text($key, $value) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE site_texts SET text_value = ?, updated_at = NOW() WHERE text_key = ?");
        return $stmt->execute([$value, $key]);
    } catch (PDOException $e) {
        write_log("Site text update error: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Sayfa SEO bilgilerini al
 */
function get_page_seo($page_name) {
    $seo = array();
    
    switch ($page_name) {
        case 'home':
            $seo['title'] = get_site_text('hero_title', 'BonusBoss') . ' - ' . get_site_text('hero_subtitle', 'Profesyonel Casino Yayıncısı');
            $seo['description'] = get_site_text('hero_description', 'Kazançlı ortaklıklar için doğru adres');
            $seo['keywords'] = get_setting('site_keywords', '');
            break;
        case 'about':
            $seo['title'] = get_site_text('about_page_title', 'Hakkımda') . ' - ' . get_setting('site_title', 'BonusBoss');
            $seo['description'] = get_site_text('about_story_content', '');
            $seo['keywords'] = 'hakkımda, ' . get_setting('site_keywords', '');
            break;
        case 'services':
            $seo['title'] = get_site_text('services_page_title', 'Hizmetler') . ' - ' . get_setting('site_title', 'BonusBoss');
            $seo['description'] = get_site_text('services_page_subtitle', '');
            $seo['keywords'] = 'hizmetler, ' . get_setting('site_keywords', '');
            break;
        case 'portfolio':
            $seo['title'] = get_site_text('portfolio_page_title', 'Portföy') . ' - ' . get_setting('site_title', 'BonusBoss');
            $seo['description'] = get_site_text('portfolio_page_subtitle', '');
            $seo['keywords'] = 'portföy, projeler, ' . get_setting('site_keywords', '');
            break;
        case 'gallery':
            $seo['title'] = get_site_text('gallery_page_title', 'Galeri') . ' - ' . get_setting('site_title', 'BonusBoss');
            $seo['description'] = get_site_text('gallery_page_subtitle', '');
            $seo['keywords'] = 'galeri, fotoğraf, video, ' . get_setting('site_keywords', '');
            break;
        case 'contact':
            $seo['title'] = get_site_text('contact_page_title', 'İletişim') . ' - ' . get_setting('site_title', 'BonusBoss');
            $seo['description'] = get_site_text('contact_page_subtitle', '');
            $seo['keywords'] = 'iletişim, ' . get_setting('site_keywords', '');
            break;
        default:
            $seo['title'] = get_setting('site_title', 'BonusBoss');
            $seo['description'] = get_setting('site_description', '');
            $seo['keywords'] = get_setting('site_keywords', '');
    }
    
    return $seo;
}

/**
 * Hizmetleri al
 */
function get_services($limit = 0, $category_id = 0) {
    global $pdo;
    
    $sql = "SELECT s.*, c.name as category_name FROM services s 
            LEFT JOIN categories c ON s.category_id = c.id 
            WHERE s.status = 'active'";
    
    if ($category_id > 0) {
        $sql .= " AND s.category_id = ?";
    }
    
    $sql .= " ORDER BY s.sort_order ASC, s.created_at DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        
        if ($category_id > 0 && $limit > 0) {
            $stmt->execute([$category_id, $limit]);
        } elseif ($category_id > 0) {
            $stmt->execute([$category_id]);
        } elseif ($limit > 0) {
            $stmt->execute([$limit]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        write_log("Services error: " . $e->getMessage(), 'error');
        return array();
    }
}

/**
 * Tek hizmet al
 */
function get_service($id_or_slug) {
    global $pdo;
    
    try {
        if (is_numeric($id_or_slug)) {
            $stmt = $pdo->prepare("SELECT s.*, c.name as category_name FROM services s 
                                   LEFT JOIN categories c ON s.category_id = c.id 
                                   WHERE s.id = ? AND s.status = 'active'");
        } else {
            $stmt = $pdo->prepare("SELECT s.*, c.name as category_name FROM services s 
                                   LEFT JOIN categories c ON s.category_id = c.id 
                                   WHERE s.slug = ? AND s.status = 'active'");
        }
        
        $stmt->execute([$id_or_slug]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        write_log("Service error: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Portföy projelerini al
 */
function get_portfolio($limit = 0, $category_id = 0, $featured = false) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name FROM portfolio p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    
    if ($category_id > 0) {
        $sql .= " AND p.category_id = ?";
    }
    
    if ($featured) {
        $sql .= " AND p.featured = 'yes'";
    }
    
    $sql .= " ORDER BY p.sort_order ASC, p.created_at DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        
        if ($category_id > 0 && $limit > 0) {
            $stmt->execute([$category_id, $limit]);
        } elseif ($category_id > 0) {
            $stmt->execute([$category_id]);
        } elseif ($limit > 0) {
            $stmt->execute([$limit]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        write_log("Portfolio error: " . $e->getMessage(), 'error');
        return array();
    }
}

/**
 * Tek portföy projesi al
 */
function get_portfolio_item($id_or_slug) {
    global $pdo;
    
    try {
        if (is_numeric($id_or_slug)) {
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM portfolio p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   WHERE p.id = ? AND p.status = 'active'");
        } else {
            $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM portfolio p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   WHERE p.slug = ? AND p.status = 'active'");
        }
        
        $stmt->execute([$id_or_slug]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        write_log("Portfolio item error: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Galeri fotoğraflarını al
 */
function get_gallery_photos($limit = 0, $category_id = 0) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name FROM gallery_photos p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    
    if ($category_id > 0) {
        $sql .= " AND p.category_id = ?";
    }
    
    $sql .= " ORDER BY p.sort_order ASC, p.created_at DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        
        if ($category_id > 0 && $limit > 0) {
            $stmt->execute([$category_id, $limit]);
        } elseif ($category_id > 0) {
            $stmt->execute([$category_id]);
        } elseif ($limit > 0) {
            $stmt->execute([$limit]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        write_log("Gallery photos error: " . $e->getMessage(), 'error');
        return array();
    }
}

/**
 * Galeri videolarını al
 */
function get_gallery_videos($limit = 0, $category_id = 0) {
    global $pdo;
    
    $sql = "SELECT v.*, c.name as category_name FROM gallery_videos v 
            LEFT JOIN categories c ON v.category_id = c.id 
            WHERE v.status = 'active'";
    
    if ($category_id > 0) {
        $sql .= " AND v.category_id = ?";
    }
    
    $sql .= " ORDER BY v.sort_order ASC, v.created_at DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        
        if ($category_id > 0 && $limit > 0) {
            $stmt->execute([$category_id, $limit]);
        } elseif ($category_id > 0) {
            $stmt->execute([$category_id]);
        } elseif ($limit > 0) {
            $stmt->execute([$limit]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        write_log("Gallery videos error: " . $e->getMessage(), 'error');
        return array();
    }
}

/**
 * Kategorileri al
 */
function get_categories($type = '') {
    global $pdo;
    
    $sql = "SELECT * FROM categories WHERE status = 'active'";
    
    if ($type) {
        $sql .= " AND type = ?";
    }
    
    $sql .= " ORDER BY sort_order ASC, name ASC";
    
    try {
        $stmt = $pdo->prepare($sql);
        
        if ($type) {
            $stmt->execute([$type]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        write_log("Categories error: " . $e->getMessage(), 'error');
        return array();
    }
}

/**
 * Tek kategori al
 */
function get_category($id_or_slug) {
    global $pdo;
    
    try {
        if (is_numeric($id_or_slug)) {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND status = 'active'");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND status = 'active'");
        }
        
        $stmt->execute([$id_or_slug]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        write_log("Category error: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Testimonialleri al
 */
function get_testimonials($limit = 0) {
    global $pdo;
    
    $sql = "SELECT * FROM testimonials WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        
        if ($limit > 0) {
            $stmt->execute([$limit]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        write_log("Testimonials error: " . $e->getMessage(), 'error');
        return array();
    }
}

/**
 * İletişim mesajı kaydet
 */
function save_contact_message($name, $email, $phone, $subject, $message) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, user_agent, created_at) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        
        return $stmt->execute([
            $name,
            $email,
            $phone,
            $subject,
            $message,
            get_client_ip(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (PDOException $e) {
        write_log("Contact message error: " . $e->getMessage(), 'error');
        return false;
    }
}

/**
 * Breadcrumb oluştur
 */
function generate_breadcrumb($page_title = '', $additional_links = array()) {
    $breadcrumb = array();
    
    // Ana sayfa
    $breadcrumb[] = array(
        'title' => get_site_text('nav_home', 'Ana Sayfa'),
        'url' => 'index.php'
    );
    
    // Ek linkler
    if (!empty($additional_links)) {
        foreach ($additional_links as $link) {
            $breadcrumb[] = $link;
        }
    }
    
    // Mevcut sayfa
    if ($page_title) {
        $breadcrumb[] = array(
            'title' => $page_title,
            'url' => '',
            'active' => true
        );
    }
    
    return $breadcrumb;
}

/**
 * Dosya yükle
 */
function upload_file($file, $upload_dir = UPLOAD_PATH, $allowed_types = null) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'message' => 'Dosya yüklenirken hata oluştu');
    }
    
    if (!$allowed_types) {
        $allowed_types = ALLOWED_IMAGE_TYPES;
    }
    
    $file_info = pathinfo($file['name']);
    $file_ext = strtolower($file_info['extension']);
    
    if (!in_array($file_ext, $allowed_types)) {
        return array('success' => false, 'message' => 'İzin verilmeyen dosya türü');
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return array('success' => false, 'message' => 'Dosya boyutu çok büyük');
    }
    
    // Dosya adını oluştur
    $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;
    
    // Dizin yoksa oluştur
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return array(
            'success' => true, 
            'filename' => $new_filename,
            'path' => $upload_path,
            'url' => SITE_URL . '/' . $upload_path
        );
    } else {
        return array('success' => false, 'message' => 'Dosya yüklenirken hata oluştu');
    }
}

/**
 * Dosya sil
 */
function delete_file($file_path) {
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return true;
}

/**
 * Resim boyutlandır
 */
function resize_image($source_path, $destination_path, $max_width, $max_height, $quality = 85) {
    if (!file_exists($source_path)) {
        return false;
    }
    
    $image_info = getimagesize($source_path);
    if (!$image_info) {
        return false;
    }
    
    $source_width = $image_info[0];
    $source_height = $image_info[1];
    $source_type = $image_info[2];
    
    // Oranı koru
    $ratio = min($max_width / $source_width, $max_height / $source_height);
    $new_width = round($source_width * $ratio);
    $new_height = round($source_height * $ratio);
    
    // Kaynak resmi yükle
    switch ($source_type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }
    
    // Yeni resim oluştur
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // PNG ve GIF için şeffaflık korunması
    if ($source_type == IMAGETYPE_PNG || $source_type == IMAGETYPE_GIF) {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }
    
    // Resmi boyutlandır
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);
    
    // Resmi kaydet
    switch ($source_type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($new_image, $destination_path, $quality);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($new_image, $destination_path);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($new_image, $destination_path);
            break;
        default:
            $result = false;
    }
    
    // Bellek temizle
    imagedestroy($source_image);
    imagedestroy($new_image);
    
    return $result;
}

/**
 * Email gönder
 */
function send_email($to, $subject, $message, $headers = '') {
    $default_headers = "From: " . NOREPLY_EMAIL . "\r\n";
    $default_headers .= "Reply-To: " . CONTACT_EMAIL . "\r\n";
    $default_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $default_headers .= "X-Mailer: PHP/" . phpversion();
    
    if ($headers) {
        $headers = $default_headers . "\r\n" . $headers;
    } else {
        $headers = $default_headers;
    }
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Pagination oluştur
 */
function create_pagination($total_items, $items_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $pagination = '<nav aria-label="Sayfalama">';
    $pagination .= '<ul class="pagination justify-content-center">';
    
    // Önceki sayfa
    if ($current_page > 1) {
        $pagination .= '<li class="page-item">';
        $pagination .= '<a class="page-link" href="' . $base_url . '?page=' . ($current_page - 1) . '">';
        $pagination .= '<i class="fas fa-chevron-left"></i>';
        $pagination .= '</a>';
        $pagination .= '</li>';
    }
    
    // Sayfa numaraları
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $pagination .= '<li class="page-item ' . $active . '">';
        $pagination .= '<a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a>';
        $pagination .= '</li>';
    }
    
    // Sonraki sayfa
    if ($current_page < $total_pages) {
        $pagination .= '<li class="page-item">';
        $pagination .= '<a class="page-link" href="' . $base_url . '?page=' . ($current_page + 1) . '">';
        $pagination .= '<i class="fas fa-chevron-right"></i>';
        $pagination .= '</a>';
        $pagination .= '</li>';
    }
    
    $pagination .= '</ul>';
    $pagination .= '</nav>';
    
    return $pagination;
}

/**
 * Cache kontrol
 */
function get_cache($key) {
    if (!CACHE_ENABLED) return false;
    
    $cache_file = 'cache/' . md5($key) . '.cache';
    
    if (file_exists($cache_file)) {
        $cache_data = file_get_contents($cache_file);
        $cache_info = unserialize($cache_data);
        
        if ($cache_info['expires'] > time()) {
            return $cache_info['data'];
        } else {
            unlink($cache_file);
        }
    }
    
    return false;
}

/**
 * Cache kaydet
 */
function set_cache($key, $data, $expire_time = CACHE_TIME) {
    if (!CACHE_ENABLED) return false;
    
    $cache_dir = 'cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    $cache_file = $cache_dir . md5($key) . '.cache';
    $cache_info = array(
        'data' => $data,
        'expires' => time() + $expire_time
    );
    
    return file_put_contents($cache_file, serialize($cache_info), LOCK_EX);
}

/**
 * Cache temizle
 */
function clear_cache($pattern = '*') {
    $cache_dir = 'cache/';
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . $pattern . '.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

/**
 * JSON response
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Debug fonksiyonu
 */
function debug($data, $die = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Performans ölçümü
 */
function get_execution_time() {
    return round((microtime(true) - START_TIME) * 1000, 2);
}

/**
 * Bellek kullanımı
 */
function get_memory_usage() {
    return file_size_format(memory_get_usage(true));
}

/**
 * Site durum kontrolü
 */
function check_site_health() {
    $health = array(
        'database' => false,
        'uploads' => false,
        'cache' => false,
        'logs' => false
    );
    
    // Database kontrolü
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT 1");
        $health['database'] = true;
    } catch (PDOException $e) {
        $health['database'] = false;
    }
    
    // Upload dizini kontrolü
    $health['uploads'] = is_writable(UPLOAD_PATH);
    
    // Cache dizini kontrolü
    $health['cache'] = is_writable('cache/') || mkdir('cache/', 0755, true);
    
    // Logs dizini kontrolü
    $health['logs'] = is_writable('logs/') || mkdir('logs/', 0755, true);
    
    return $health;
}

?>