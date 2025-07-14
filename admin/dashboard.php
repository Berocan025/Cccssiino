<?php
/**
 * Admin Dashboard
 * SQLite Database Integration
 */

require_once '../includes/config.php';

// Admin yetkilendirme kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('index.php');
    exit();
}

$stats = array();

// Dashboard Statistics
try {
    // Total services count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
    $result = $stmt->fetch();
    $stats['services'] = $result ? (int)$result['count'] : 0;
    
    // Total portfolio count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM portfolio WHERE status = 'active'");
    $result = $stmt->fetch();
    $stats['portfolio'] = $result ? (int)$result['count'] : 0;
    
    // Total gallery photos count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_photos WHERE status = 'active'");
    $result = $stmt->fetch();
    $gallery_photos = $result ? (int)$result['count'] : 0;
    
    // Total gallery videos count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_videos WHERE status = 'active'");
    $result = $stmt->fetch();
    $gallery_videos = $result ? (int)$result['count'] : 0;
    
    $stats['gallery'] = $gallery_photos + $gallery_videos;
    
    // Total messages count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
    $result = $stmt->fetch();
    $stats['total_messages'] = $result ? (int)$result['count'] : 0;
    
    // Unread messages count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
    $result = $stmt->fetch();
    $stats['messages'] = $result ? (int)$result['count'] : 0;

    // Recent messages (last 5)
    $stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_messages = $stmt->fetchAll();

    // Recent services (last 5)
    $stmt = $pdo->prepare("SELECT * FROM services ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_services = $stmt->fetchAll();

    // Recent portfolio (last 5)
    $stmt = $pdo->prepare("SELECT * FROM portfolio ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_portfolio = $stmt->fetchAll();

} catch (PDOException $e) {
    $stats = array(
        'services' => 0,
        'portfolio' => 0,
        'gallery' => 0,
        'messages' => 0,
        'total_messages' => 0
    );
    $recent_messages = array();
    $recent_services = array();
    $recent_portfolio = array();
    write_log("Dashboard stats error: " . $e->getMessage(), 'error');
}

// Admin bilgileri
$admin_info = array(
    'username' => $_SESSION['admin_username'] ?? 'Admin',
    'last_login' => $_SESSION['admin_last_login'] ?? date('Y-m-d H:i:s')
);

include 'includes/admin_header.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 page-title">Dashboard</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-2"><?php echo $stats['services']; ?></h4>
                                <h6 class="text-muted mb-0">Toplam Hizmetler</h6>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <i class="fas fa-cogs text-white"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-2"><?php echo $stats['portfolio']; ?></h4>
                                <h6 class="text-muted mb-0">Toplam Projeler</h6>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-success">
                                        <i class="fas fa-briefcase text-white"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-2"><?php echo $stats['gallery']; ?></h4>
                                <h6 class="text-muted mb-0">Galeri Öğeleri</h6>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-info">
                                        <i class="fas fa-images text-white"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-2"><?php echo $stats['messages']; ?></h4>
                                <h6 class="text-muted mb-0">Okunmamış Mesajlar</h6>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-warning">
                                        <i class="fas fa-envelope text-white"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Hızlı İşlemler</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="content/add.php" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus me-2"></i>Yeni Hizmet Ekle
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="portfolio/add.php" class="btn btn-success btn-block">
                                    <i class="fas fa-plus me-2"></i>Yeni Proje Ekle
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="gallery/add.php" class="btn btn-info btn-block">
                                    <i class="fas fa-plus me-2"></i>Galeri Ekle
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="messages/" class="btn btn-warning btn-block">
                                    <i class="fas fa-envelope me-2"></i>Mesajları Görüntüle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <?php if (!empty($recent_messages)): ?>
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Son Mesajlar</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>Gönderen</th>
                                        <th>Konu</th>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_messages as $message): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <h5 class="font-size-14 mb-1"><?php echo escape_output($message['name']); ?></h5>
                                                <p class="text-muted mb-0"><?php echo escape_output($message['email']); ?></p>
                                            </div>
                                        </td>
                                        <td><?php echo escape_output(substr($message['subject'], 0, 30)) . (strlen($message['subject']) > 30 ? '...' : ''); ?></td>
                                        <td><?php echo format_datetime($message['created_at']); ?></td>
                                        <td>
                                            <?php if ($message['status'] == 'unread'): ?>
                                                <span class="badge bg-warning">Okunmadı</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Okundu</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Services -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Son Hizmetler</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>Başlık</th>
                                        <th>Durum</th>
                                        <th>Tarih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_services as $service): ?>
                                    <tr>
                                        <td>
                                            <h5 class="font-size-14 mb-0">
                                                <a href="content/edit.php?id=<?php echo $service['id']; ?>">
                                                    <?php echo escape_output($service['title']); ?>
                                                </a>
                                            </h5>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $service['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo $service['status'] == 'active' ? 'Aktif' : 'Pasif'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_datetime($service['created_at']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>