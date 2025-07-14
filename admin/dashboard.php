<?php
session_start();
require_once '../includes/config.php';

$page_title = 'Dashboard';

// İstatistikler
try {
    // Hizmet sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM services WHERE status = 'active'");
    $active_services = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM services");
    $total_services = $stmt->fetch()['total'];
    
    // Portfolio sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM portfolio WHERE status = 'published'");
    $active_portfolio = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM portfolio");
    $total_portfolio = $stmt->fetch()['total'];
    
    // Galeri sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery_photos WHERE status = 'active'");
    $active_photos = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery_videos WHERE status = 'active'");
    $active_videos = $stmt->fetch()['total'];
    
    // Mesaj sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages");
    $total_messages = $stmt->fetch()['total'];
    
    // Son eklenen hizmetler
    $stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC LIMIT 5");
    $recent_services = $stmt->fetchAll();
    
    // Son portfolio projeleri
    $stmt = $pdo->query("SELECT * FROM portfolio ORDER BY created_at DESC LIMIT 5");
    $recent_portfolio = $stmt->fetchAll();
    
    // Son mesajlar
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recent_messages = $stmt->fetchAll();
    
} catch (PDOException $e) {
    write_log("Dashboard error: " . $e->getMessage(), 'error');
    $active_services = $total_services = 0;
    $active_portfolio = $total_portfolio = 0;
    $active_photos = $active_videos = 0;
    $unread_messages = $total_messages = 0;
    $recent_services = $recent_portfolio = $recent_messages = [];
}

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-light mb-1">Hoş Geldiniz, <?php echo escape_output($_SESSION['admin_username']); ?>!</h2>
                <p class="text-muted">İşte sitenizin genel durumu...</p>
            </div>
            <div class="text-end">
                <small class="text-muted">Son Güncelleme: <?php echo date('d.m.Y H:i'); ?></small>
            </div>
        </div>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 text-warning"><?php echo $active_services; ?></h3>
                        <p class="mb-0 text-muted">Aktif Hizmet</p>
                        <small class="text-muted">Toplam: <?php echo $total_services; ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 text-warning"><?php echo $active_portfolio; ?></h3>
                        <p class="mb-0 text-muted">Portfolio Projesi</p>
                        <small class="text-muted">Toplam: <?php echo $total_portfolio; ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 text-warning"><?php echo $active_photos + $active_videos; ?></h3>
                        <p class="mb-0 text-muted">Galeri İçerikleri</p>
                        <small class="text-muted"><?php echo $active_photos; ?> Fotoğraf, <?php echo $active_videos; ?> Video</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon me-3">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1 text-warning"><?php echo $unread_messages; ?></h3>
                        <p class="mb-0 text-muted">Okunmamış Mesaj</p>
                        <small class="text-muted">Toplam: <?php echo $total_messages; ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hızlı Eylemler -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent border-bottom-0">
                <h5 class="card-title text-warning mb-0">
                    <i class="fas fa-rocket me-2"></i>
                    Hızlı Eylemler
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a href="content/add.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span>Yeni Hizmet Ekle</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="portfolio/add.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-folder-plus fa-2x mb-2"></i>
                            <span>Portfolio Projesi</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="gallery/add.php" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-image fa-2x mb-2"></i>
                            <span>Galeri İçeriği</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="../index.php" target="_blank" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4">
                            <i class="fas fa-external-link-alt fa-2x mb-2"></i>
                            <span>Siteyi Görüntüle</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- İçerik Tabloları -->
<div class="row g-4">
    <!-- Son Hizmetler -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title text-warning mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Son Hizmetler
                </h5>
                <a href="content/" class="btn btn-sm btn-outline-warning">Tümünü Gör</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recent_services)): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
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
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($service['icon'])): ?>
                                        <i class="<?php echo escape_output($service['icon']); ?> me-2 text-warning"></i>
                                        <?php endif; ?>
                                        <span><?php echo escape_output(substr($service['title'], 0, 30)); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $service['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $service['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y', strtotime($service['created_at'])); ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Henüz hizmet eklenmemiş.</p>
                    <a href="content/add.php" class="btn btn-primary">İlk Hizmeti Ekle</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Son Portfolio -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title text-warning mb-0">
                    <i class="fas fa-briefcase me-2"></i>
                    Son Portfolio
                </h5>
                <a href="portfolio/" class="btn btn-sm btn-outline-warning">Tümünü Gör</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recent_portfolio)): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Proje</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_portfolio as $project): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($project['featured_image'])): ?>
                                        <img src="../<?php echo escape_output($project['featured_image']); ?>" 
                                             alt="" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                        <?php endif; ?>
                                        <span><?php echo escape_output(substr($project['title'], 0, 25)); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $project['status'] === 'published' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $project['status'] === 'published' ? 'Yayında' : 'Taslak'; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y', strtotime($project['created_at'])); ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Henüz portfolio projesi eklenmemiş.</p>
                    <a href="portfolio/add.php" class="btn btn-success">İlk Projeyi Ekle</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Son Mesajlar -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title text-warning mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    Son Mesajlar
                </h5>
                <a href="#" class="btn btn-sm btn-outline-warning">Tümünü Gör</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recent_messages)): ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Gönderen</th>
                                <th>Konu</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_messages as $message): ?>
                            <tr class="<?php echo $message['status'] === 'unread' ? 'table-warning' : ''; ?>">
                                <td>
                                    <div>
                                        <strong><?php echo escape_output($message['name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo escape_output($message['email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php echo escape_output(substr($message['subject'], 0, 40)); ?>
                                    <?php if (strlen($message['subject']) > 40): ?>...<?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $message['status'] === 'unread' ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                        <?php echo $message['status'] === 'unread' ? 'Okunmadı' : 'Okundu'; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewMessage(<?php echo $message['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Henüz mesaj bulunmuyor.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Alanı -->
<div class="row mt-5">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-transparent">
                <h5 class="card-title text-warning mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    İçerik İstatistikleri
                </h5>
            </div>
            <div class="card-body">
                <canvas id="contentChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-transparent">
                <h5 class="card-title text-warning mb-0">
                    <i class="fas fa-pie-chart me-2"></i>
                    İçerik Dağılımı
                </h5>
            </div>
            <div class="card-body">
                <canvas id="distributionChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Grafik verileri
document.addEventListener('DOMContentLoaded', function() {
    // Bar Chart
    const ctx1 = document.getElementById('contentChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ['Hizmetler', 'Portfolio', 'Fotoğraflar', 'Videolar', 'Mesajlar'],
            datasets: [{
                label: 'İçerik Sayısı',
                data: [<?php echo $total_services; ?>, <?php echo $total_portfolio; ?>, <?php echo $active_photos; ?>, <?php echo $active_videos; ?>, <?php echo $total_messages; ?>],
                backgroundColor: [
                    'rgba(255, 215, 0, 0.8)',
                    'rgba(0, 153, 255, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 215, 0, 1)',
                    'rgba(0, 153, 255, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(108, 117, 125, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#adb5bd'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#adb5bd'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        }
    });

    // Pie Chart
    const ctx2 = document.getElementById('distributionChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Aktif Hizmetler', 'Portfolio Projeleri', 'Galeri İçerikleri'],
            datasets: [{
                data: [<?php echo $active_services; ?>, <?php echo $active_portfolio; ?>, <?php echo $active_photos + $active_videos; ?>],
                backgroundColor: [
                    'rgba(255, 215, 0, 0.8)',
                    'rgba(0, 153, 255, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 215, 0, 1)',
                    'rgba(0, 153, 255, 1)',
                    'rgba(40, 167, 69, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#adb5bd',
                        padding: 20
                    }
                }
            }
        }
    });
});

// Mesaj görüntüleme
function viewMessage(id) {
    // Modal ile mesaj detayını göster
    alert('Mesaj görüntüleme özelliği yakında eklenecek. ID: ' + id);
}
</script>

<?php include 'includes/footer.php'; ?>