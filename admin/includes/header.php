<?php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('index.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - BonusBoss</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bs-primary: #ffd700;
            --bs-secondary: #0099ff;
            --bs-dark: #1a1a1a;
            --bs-border-color: #404040;
            --sidebar-width: 280px;
        }

        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e1e1e 0%, #0d1117 100%);
            border-right: 1px solid var(--bs-border-color);
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--bs-border-color);
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #000;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: #000;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand:hover {
            color: #333;
        }

        .sidebar-menu {
            padding: 1rem 0;
            overflow-y: auto;
            height: calc(100vh - 120px);
        }

        .nav-item {
            margin: 0.2rem 0;
        }

        .nav-link {
            padding: 1rem 1.5rem;
            color: #e9ecef;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 0;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            background: linear-gradient(90deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 215, 0, 0.2) 100%);
            color: #ffd700;
            border-left: 3px solid #ffd700;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-text {
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
            visibility: hidden;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 1rem 0.8rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 70px;
        }

        /* Top Bar */
        .topbar {
            background: rgba(13, 17, 23, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--bs-border-color);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .page-title {
            color: #ffd700;
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }

        /* Cards */
        .card {
            background: rgba(30, 30, 30, 0.8);
            border: 1px solid var(--bs-border-color);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(255, 215, 0, 0.1);
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(0, 153, 255, 0.1) 100%);
            border: 1px solid rgba(255, 215, 0, 0.2);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #000;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            border: none;
            color: #000;
            font-weight: 600;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ffed4e 0%, #ffd700 100%);
            color: #000;
            transform: translateY(-2px);
        }

        /* Tables */
        .table-dark {
            background: rgba(30, 30, 30, 0.5);
            border-radius: 10px;
            overflow: hidden;
        }

        .table-dark th {
            background: rgba(255, 215, 0, 0.1);
            border-color: var(--bs-border-color);
            color: #ffd700;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block !important;
            }
        }

        .mobile-toggle {
            display: none;
        }

        /* Custom Scrollbar */
        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 215, 0, 0.3);
            border-radius: 3px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 215, 0, 0.5);
        }

        /* Badges */
        .badge-gold {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #000;
        }

        /* Alerts */
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <i class="fas fa-crown"></i>
                <span class="nav-text">BonusBoss</span>
            </a>
        </div>
        
        <nav class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="content/" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/content/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-cogs"></i>
                        <span class="nav-text">Hizmetler</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="portfolio/" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/portfolio/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-briefcase"></i>
                        <span class="nav-text">Portfolio</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="gallery/" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/gallery/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i>
                        <span class="nav-text">Galeri</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-envelope"></i>
                        <span class="nav-text">Mesajlar</span>
                        <span class="badge badge-gold ms-auto">3</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="users/" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/users/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Kullanıcılar</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="settings/" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/settings/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span class="nav-text">Ayarlar</span>
                    </a>
                </li>
                
                <li class="nav-item mt-3">
                    <hr style="border-color: var(--bs-border-color);">
                </li>
                
                <li class="nav-item">
                    <a href="../index.php" class="nav-link" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="nav-text">Siteyi Görüntüle</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="nav-text">Çıkış Yap</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-light me-3 mobile-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?php echo $page_title ?? 'Admin Panel'; ?></h1>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="settings/"><i class="fas fa-cog me-2"></i>Ayarlar</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap</a></li>
                    </ul>
                </div>
                
                <button class="btn btn-outline-warning" onclick="toggleSidebar()">
                    <i class="fas fa-compress-alt"></i>
                </button>
            </div>
        </div>

        <!-- Page Content -->
        <div class="container-fluid p-4">