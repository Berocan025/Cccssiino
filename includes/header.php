<?php
/**
 * BonusBoss Portfolio Website Header
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Doğrudan erişimi engelle
if (!defined('DB_HOST')) {
    die('Direct access not allowed');
}

// Sayfa SEO bilgilerini al
$page_name = basename($_SERVER['PHP_SELF'], '.php');
$seo = get_page_seo($page_name);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?php echo escape_output($seo['title']); ?></title>
    <meta name="description" content="<?php echo escape_output($seo['description']); ?>">
    <meta name="keywords" content="<?php echo escape_output($seo['keywords']); ?>">
    <meta name="author" content="<?php echo escape_output(get_setting('site_author', 'BERAT K')); ?>">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Turkish">
    <meta name="revisit-after" content="7 days">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo escape_output($seo['title']); ?>">
    <meta property="og:description" content="<?php echo escape_output($seo['description']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo escape_output(get_current_url()); ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">
    <meta property="og:site_name" content="<?php echo escape_output(get_setting('site_title', 'BonusBoss')); ?>">
    <meta property="og:locale" content="tr_TR">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo escape_output($seo['title']); ?>">
    <meta name="twitter:description" content="<?php echo escape_output($seo['description']); ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_URL; ?>/assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>/assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>/assets/images/favicon-16x16.png">
    <link rel="manifest" href="<?php echo SITE_URL; ?>/assets/images/site.webmanifest">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "<?php echo escape_output(get_setting('site_title', 'BonusBoss')); ?>",
        "description": "<?php echo escape_output(get_setting('site_description', '')); ?>",
        "url": "<?php echo SITE_URL; ?>",
        "image": "<?php echo SITE_URL; ?>/assets/images/logo.png",
        "jobTitle": "Profesyonel Casino Yayıncısı",
        "worksFor": {
            "@type": "Organization",
            "name": "BonusBoss"
        },
        "sameAs": [
            "<?php echo escape_output(get_setting('social_facebook', '')); ?>",
            "<?php echo escape_output(get_setting('social_twitter', '')); ?>",
            "<?php echo escape_output(get_setting('social_instagram', '')); ?>",
            "<?php echo escape_output(get_setting('social_telegram', '')); ?>",
            "<?php echo escape_output(get_setting('social_youtube', '')); ?>"
        ]
    }
    </script>
    
    <!-- Google Analytics -->
    <?php 
    $google_analytics = get_setting('google_analytics', '');
    if ($google_analytics) {
        echo $google_analytics;
    }
    ?>
    
    <!-- Facebook Pixel -->
    <?php 
    $facebook_pixel = get_setting('facebook_pixel', '');
    if ($facebook_pixel) {
        echo $facebook_pixel;
    }
    ?>
    
    <!-- CSS Variables -->
    <style>
        :root {
            --primary-color: <?php echo get_setting('theme_primary_color', '#FFD700'); ?>;
            --secondary-color: <?php echo get_setting('theme_secondary_color', '#0099FF'); ?>;
            --dark-color: <?php echo get_setting('theme_dark_color', '#003366'); ?>;
            --gradient-primary: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            --gradient-dark: linear-gradient(45deg, var(--dark-color), #001a33);
            --gradient-bg: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo escape_output(get_setting('site_title', 'BonusBoss')); ?>">
            </div>
            <div class="preloader-spinner">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden"><?php echo escape_output(get_site_text('loading_text', 'Yükleniyor...')); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo escape_output(get_setting('site_title', 'BonusBoss')); ?>" class="logo">
                <span class="logo-text">
                    <span class="logo-bonus">Bonus</span><span class="logo-boss">Boss</span>
                </span>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page_name == 'index') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                            <i class="fas fa-home"></i> 
                            <?php echo escape_output(get_site_text('nav_home', 'Ana Sayfa')); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page_name == 'about') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/about.php">
                            <i class="fas fa-user"></i> 
                            <?php echo escape_output(get_site_text('nav_about', 'Hakkımda')); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page_name == 'services') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/services.php">
                            <i class="fas fa-cogs"></i> 
                            <?php echo escape_output(get_site_text('nav_services', 'Hizmetler')); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page_name == 'portfolio') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/portfolio.php">
                            <i class="fas fa-briefcase"></i> 
                            <?php echo escape_output(get_site_text('nav_portfolio', 'Portföy')); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page_name == 'gallery') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/gallery.php">
                            <i class="fas fa-images"></i> 
                            <?php echo escape_output(get_site_text('nav_gallery', 'Galeri')); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page_name == 'contact') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/contact.php">
                            <i class="fas fa-envelope"></i> 
                            <?php echo escape_output(get_site_text('nav_contact', 'İletişim')); ?>
                        </a>
                    </li>
                </ul>
                
                <!-- Social Media Icons -->
                <ul class="navbar-nav ms-3">
                    <?php if (get_setting('social_facebook')): ?>
                    <li class="nav-item">
                        <a class="nav-link social-link" href="<?php echo escape_output(get_setting('social_facebook')); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_twitter')): ?>
                    <li class="nav-item">
                        <a class="nav-link social-link" href="<?php echo escape_output(get_setting('social_twitter')); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_instagram')): ?>
                    <li class="nav-item">
                        <a class="nav-link social-link" href="<?php echo escape_output(get_setting('social_instagram')); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_telegram')): ?>
                    <li class="nav-item">
                        <a class="nav-link social-link" href="<?php echo escape_output(get_setting('social_telegram')); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-telegram"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (get_setting('social_youtube')): ?>
                    <li class="nav-item">
                        <a class="nav-link social-link" href="<?php echo escape_output(get_setting('social_youtube')); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Main Content -->
    <main class="main-content">