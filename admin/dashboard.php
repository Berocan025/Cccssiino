<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$page_title = 'Dashboard';
$page_subtitle = 'Hoş geldiniz, ' . $_SESSION['admin_username'];
$page_header = true;

// Get statistics
$stats = [];

// Portfolio items count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM portfolio WHERE is_active = 1");
$stats['portfolio'] = $stmt->fetch()['count'];

// Total portfolio items
$stmt = $pdo->query("SELECT COUNT(*) as count FROM portfolio");
$stats['portfolio_total'] = $stmt->fetch()['count'];

// Gallery photos count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_photos WHERE is_active = 1");
$stats['gallery_photos'] = $stmt->fetch()['count'];

// Gallery videos count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery_videos WHERE is_active = 1");
$stats['gallery_videos'] = $stmt->fetch()['count'];

// Contact messages count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
$stats['unread_messages'] = $stmt->fetch()['count'];

// Total messages
$stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
$stats['total_messages'] = $stmt->fetch()['count'];

// Services count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM services WHERE is_active = 1");
$stats['services'] = $stmt->fetch()['count'];

// Recent messages (last 5)
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
$recent_messages = $stmt->fetchAll();

// Recent portfolio items (last 5)
$stmt = $pdo->query("SELECT * FROM portfolio ORDER BY created_at DESC LIMIT 5");
$recent_portfolio = $stmt->fetchAll();

// Monthly statistics for chart
$stmt = $pdo->query("
    SELECT 
        MONTH(created_at) as month,
        COUNT(*) as count
    FROM contact_messages 
    WHERE YEAR(created_at) = YEAR(NOW())
    GROUP BY MONTH(created_at)
    ORDER BY month
");
$monthly_messages = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Aktif Portfolio
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['portfolio']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Toplam: <?php echo $stats['portfolio_total']; ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Galeri Medya
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['gallery_photos'] + $stats['gallery_videos']; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-images fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        Foto: <?php echo $stats['gallery_photos']; ?> | 
                        Video: <?php echo $stats['gallery_videos']; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Okunmamış Mesaj
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['unread_messages']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-envelope fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Toplam: <?php echo $stats['total_messages']; ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Aktif Hizmetler
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['services']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cogs fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Sunulan hizmet sayısı</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Messages -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Son Mesajlar</h6>
                <a href="messages/index.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> Tümünü Gör
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_messages)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-envelope fa-3x text-gray-300 mb-3"></i>
                        <p class="text-muted">Henüz mesaj bulunmuyor.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                            <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($message['email']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($message['subject'], 0, 30)) . '...'; ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($message['is_read']): ?>
                                                <span class="badge badge-success">Okundu</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Yeni</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Portfolio -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Son Portfolio</h6>
                <a href="portfolio/index.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> Tümünü Gör
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_portfolio)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-briefcase fa-3x text-gray-300 mb-3"></i>
                        <p class="text-muted">Henüz portfolio öğesi bulunmuyor.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Kategori</th>
                                    <th>Tarih</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_portfolio as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                                            $stmt->execute([$item['category_id']]);
                                            $category = $stmt->fetch();
                                            echo htmlspecialchars($category['name'] ?? 'Kategori Yok');
                                            ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($item['is_active']): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monthly Messages Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Aylık Mesaj İstatistikleri</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="messageChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Hızlı İşlemler</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="portfolio/add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Yeni Portfolio Ekle
                    </a>
                    <a href="gallery/add.php" class="btn btn-success">
                        <i class="fas fa-image"></i> Galeri Ekle
                    </a>
                    <a href="messages/index.php" class="btn btn-info">
                        <i class="fas fa-envelope"></i> Mesajları Görüntüle
                    </a>
                    <a href="texts/index.php" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Metinleri Düzenle
                    </a>
                    <a href="settings/index.php" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> Site Ayarları
                    </a>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Sistem Bilgileri</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">PHP Sürümü:</small>
                        <br>
                        <strong><?php echo PHP_VERSION; ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">MySQL Sürümü:</small>
                        <br>
                        <strong><?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></strong>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Sunucu Zamanı:</small>
                        <br>
                        <strong><?php echo date('d.m.Y H:i'); ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Disk Kullanımı:</small>
                        <br>
                        <strong>
                            <?php 
                            $bytes = disk_free_space(".");
                            $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
                            $base = 1024;
                            $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
                            echo sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
                            ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly messages chart
    const ctx = document.getElementById('messageChart').getContext('2d');
    const monthlyData = <?php echo json_encode($monthly_messages); ?>;
    
    const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 
                   'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    
    const chartData = {
        labels: monthlyData.map(item => months[item.month - 1]),
        datasets: [{
            label: 'Mesaj Sayısı',
            data: monthlyData.map(item => item.count),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    };
    
    const config = {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Aylık Mesaj İstatistikleri'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };
    
    new Chart(ctx, config);
});
</script>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df!important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a!important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc!important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e!important;
}

.text-primary {
    color: var(--primary-blue)!important;
}

.text-success {
    color: #1cc88a!important;
}

.text-info {
    color: #36b9cc!important;
}

.text-warning {
    color: #f6c23e!important;
}

.badge-success {
    background-color: #1cc88a;
}

.badge-warning {
    background-color: #f6c23e;
}

.badge-secondary {
    background-color: #6c757d;
}

.chart-area {
    position: relative;
    height: 300px;
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}
</style>

<?php include 'includes/admin_footer.php'; ?>