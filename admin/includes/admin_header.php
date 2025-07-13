THIS SHOULD BE A LINTER ERROR<?php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('index.php');
}

$current_page = basename($_SERVER['PHP_SELF']);
$site_settings = get_site_settings();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - BonusBoss</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gold: #FFD700;
            --primary-blue: #0099FF;
            --dark-blue: #003366;
            --light-gold: #FFF8DC;
            --hover-gold: #FFA500;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--dark-blue) 0%, var(--primary-blue) 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            background: rgba(255, 215, 0, 0.1);
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .sidebar-header .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--hover-gold) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5em;
            color: var(--dark-blue);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
        
        .sidebar-header h3 {
            font-size: 1.5em;
            margin-bottom: 5px;
            color: var(--primary-gold);
        }
        
        .sidebar-header .subtitle {
            font-size: 0.9em;
            opacity: 0.8;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover {
            color: var(--primary-gold);
            background: rgba(255, 215, 0, 0.1);
            border-left-color: var(--primary-gold);
        }
        
        .nav-link.active {
            color: var(--primary-gold);
            background: rgba(255, 215, 0, 0.2);
            border-left-color: var(--primary-gold);
        }
        
        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        .nav-link span {
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .nav-link span {
            opacity: 0;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar.collapsed + .main-content {
            margin-left: 70px;
        }
        
        .top-bar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.2em;
            color: var(--dark-blue);
            cursor: pointer;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .toggle-sidebar:hover {
            background: rgba(255, 215, 0, 0.1);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.9em;
        }
        
        .breadcrumb a {
            color: var(--primary-blue);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            color: var(--primary-gold);
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-dropdown {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 25px;
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--hover-gold) 100%);
            color: var(--dark-blue);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-dropdown:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
        }
        
        .user-dropdown i {
            margin-right: 8px;
        }
        
        .content-area {
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            color: var(--dark-blue);
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .page-header .subtitle {
            color: #666;
            font-size: 1.1em;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--hover-gold) 100%);
            color: var(--dark-blue);
            padding: 20px;
            border-radius: 15px 15px 0 0;
            border-bottom: none;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.3em;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--hover-gold) 100%);
            color: var(--dark-blue);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--hover-gold) 0%, var(--primary-gold) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .top-bar {
                padding: 10px 15px;
            }
            
            .content-area {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-crown"></i>
                </div>
                <h3>BonusBoss</h3>
                <div class="subtitle">Admin Panel</div>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="content/index.php" class="nav-link <?php echo (strpos($current_page, 'content') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>İçerik Yönetimi</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="portfolio/index.php" class="nav-link <?php echo (strpos($current_page, 'portfolio') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-briefcase"></i>
                        <span>Portfolio</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="gallery/index.php" class="nav-link <?php echo (strpos($current_page, 'gallery') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i>
                        <span>Galeri</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="messages/index.php" class="nav-link <?php echo (strpos($current_page, 'messages') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Mesajlar</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="texts/index.php" class="nav-link <?php echo (strpos($current_page, 'texts') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-edit"></i>
                        <span>Metin Yönetimi</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="settings/index.php" class="nav-link <?php echo (strpos($current_page, 'settings') !== false) ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Ayarlar</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Çıkış</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <main class="main-content">
            <div class="top-bar">
                <div class="d-flex align-items-center">
                    <button class="toggle-sidebar" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="breadcrumb ms-3">
                        <a href="dashboard.php">Ana Sayfa</a>
                        <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
                            <?php foreach ($breadcrumbs as $breadcrumb): ?>
                                <span class="mx-2">/</span>
                                <?php if (isset($breadcrumb['url'])): ?>
                                    <a href="<?php echo $breadcrumb['url']; ?>"><?php echo $breadcrumb['title']; ?></a>
                                <?php else: ?>
                                    <span><?php echo $breadcrumb['title']; ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="user-menu">
                    <button class="user-dropdown" onclick="showUserMenu()">
                        <i class="fas fa-user"></i>
                        <span><?php echo $_SESSION['admin_username']; ?></span>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </button>
                </div>
            </div>
            
            <div class="content-area">
                <?php if (isset($page_header) && $page_header): ?>
                    <div class="page-header">
                        <h1><?php echo $page_title ?? 'Admin Panel'; ?></h1>
                        <?php if (isset($page_subtitle)): ?>
                            <p class="subtitle"><?php echo $page_subtitle; ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['admin_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['admin_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>