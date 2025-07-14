<?php
/**
 * BonusBoss Portfolio Website - Ana Sayfa
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Config dosyasını dahil et
require_once 'includes/config.php';

// Sayfa değişkenleri
$page_title = 'Ana Sayfa';
$current_page = 'home';

// Verileri al
$services = get_services(6);
$portfolio = get_portfolio(6, 0, true);
$testimonials = get_testimonials(6);
$stats = get_site_stats();

// Header dahil et
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" id="home">
    <div id="particles-js"></div>
    <div class="container">
        <div class="row min-vh-100 align-items-center">
            <div class="col-lg-12">
                <div class="hero-content" data-aos="fade-up">
                    <h1 class="hero-title">
                        <?php echo escape_output(get_site_text('hero_title', 'BonusBoss')); ?>
                    </h1>
                    <div class="hero-subtitle">
                        <span id="typed-text"></span>
                    </div>
                    <p class="hero-description">
                        <?php echo escape_output(get_site_text('hero_description', 'Kazançlı ortaklıklar için doğru adres. Profesyonel casino yayıncılığı hizmetleri ile kazancınızı artırın.')); ?>
                    </p>
                    <div class="hero-buttons">
                        <a href="#services" class="btn btn-primary">
                            <i class="fas fa-cogs"></i>
                            <?php echo escape_output(get_site_text('hero_button_services', 'Hizmetlerim')); ?>
                        </a>
                        <a href="#contact" class="btn btn-secondary">
                            <i class="fas fa-envelope"></i>
                            <?php echo escape_output(get_site_text('hero_button_contact', 'İletişim')); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section section-light" id="about">
    <div class="container">
        <div class="row">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="about-content">
                    <div class="section-title text-start">
                        <h2><?php echo escape_output(get_site_text('about_title', 'Hakkımda')); ?></h2>
                        <p><?php echo escape_output(get_site_text('about_subtitle', 'Profesyonel Casino Yayıncısı')); ?></p>
                    </div>
                    <p><?php echo escape_output(get_site_text('about_description', 'Yıllarca casino sektöründe edindiğim tecrübe ile size en iyi hizmeti sunuyorum. Güvenilir ortaklıklar kurup kazancınızı artırmanız için buradayım.')); ?></p>
                    
                    <div class="about-features">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="feature-item">
                                    <i class="fas fa-trophy text-primary"></i>
                                    <span>5+ Yıl Deneyim</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="feature-item">
                                    <i class="fas fa-users text-primary"></i>
                                    <span>100+ Mutlu Müşteri</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="feature-item">
                                    <i class="fas fa-chart-line text-primary"></i>
                                    <span>Garanti Başarı</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="feature-item">
                                    <i class="fas fa-shield-alt text-primary"></i>
                                    <span>Güvenli Ortaklık</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="about-buttons">
                        <a href="about.php" class="btn btn-primary">
                            <i class="fas fa-user"></i>
                            <?php echo escape_output(get_site_text('btn_read_more', 'Devamını Oku')); ?>
                        </a>
                        <a href="contact.php" class="btn btn-outline">
                            <i class="fas fa-envelope"></i>
                            İletişim
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-image">
                    <img src="assets/images/about-image.jpg" alt="BonusBoss" class="img-fluid rounded">
                    <div class="about-badge">
                        <i class="fas fa-crown"></i>
                        <span>Profesyonel</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="section section-dark" id="services">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php echo escape_output(get_site_text('services_title', 'Hizmetlerim')); ?></h2>
            <p><?php echo escape_output(get_site_text('services_subtitle', 'Profesyonel Casino Yayıncılığı')); ?></p>
        </div>
        
        <div class="row">
            <?php foreach ($services as $service): ?>
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $service['sort_order'] * 100; ?>">
                <div class="card service-card" id="service-<?php echo $service['id']; ?>">
                    <div class="card-icon">
                        <i class="<?php echo escape_output($service['icon']); ?>"></i>
                    </div>
                    <h5 class="card-title"><?php echo escape_output($service['title']); ?></h5>
                    <p class="card-text"><?php echo escape_output($service['short_description']); ?></p>
                    
                    <?php if ($service['features']): ?>
                    <ul class="service-features">
                        <?php 
                        $features = json_decode($service['features'], true);
                        if ($features) {
                            foreach ($features as $feature): ?>
                        <li><?php echo escape_output($feature); ?></li>
                        <?php endforeach;
                        } ?>
                    </ul>
                    <?php endif; ?>
                    
                    <?php if ($service['price_text']): ?>
                    <div class="service-price">
                        <?php echo escape_output($service['price_text']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <a href="services.php#service-<?php echo $service['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i>
                        Detay
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="services.php" class="btn btn-outline">
                <i class="fas fa-list"></i>
                <?php echo escape_output(get_site_text('btn_view_all', 'Tüm Hizmetler')); ?>
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section section-light" id="stats">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php echo escape_output(get_site_text('stats_title', 'Başarılarım')); ?></h2>
            <p>Rakamlarla BonusBoss başarısı</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-number" data-count="<?php echo str_replace('+', '', get_site_text('stats_followers', '10K+')); ?>">0</div>
                <div class="stat-label"><?php echo escape_output(get_site_text('stats_followers_label', 'Takipçi')); ?></div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-number" data-count="<?php echo str_replace('+', '', get_site_text('stats_projects', '50+')); ?>">0</div>
                <div class="stat-label"><?php echo escape_output(get_site_text('stats_projects_label', 'Proje')); ?></div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-number" data-count="<?php echo str_replace('+', '', get_site_text('stats_partners', '25+')); ?>">0</div>
                <div class="stat-label"><?php echo escape_output(get_site_text('stats_partners_label', 'Ortak')); ?></div>
            </div>
            <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-number" data-count="<?php echo str_replace('+', '', get_site_text('stats_experience', '5+')); ?>">0</div>
                <div class="stat-label"><?php echo escape_output(get_site_text('stats_experience_label', 'Yıl Deneyim')); ?></div>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Section -->
<section class="section section-dark" id="portfolio">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php echo escape_output(get_site_text('portfolio_title', 'Portföyüm')); ?></h2>
            <p><?php echo escape_output(get_site_text('portfolio_subtitle', 'Başarılı Projelerim')); ?></p>
        </div>
        
        <div class="portfolio-grid">
            <?php foreach ($portfolio as $item): ?>
            <div class="portfolio-item" data-aos="fade-up" data-aos-delay="<?php echo $item['sort_order'] * 100; ?>">
                <img src="assets/uploads/portfolio/<?php echo escape_output($item['image']); ?>" 
                     alt="<?php echo escape_output($item['title']); ?>" 
                     class="portfolio-image">
                
                <div class="portfolio-overlay">
                    <div class="portfolio-links">
                        <a href="assets/uploads/portfolio/<?php echo escape_output($item['image']); ?>" 
                           class="portfolio-link" 
                           data-lightbox="portfolio"
                           data-title="<?php echo escape_output($item['title']); ?>">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php if ($item['project_url']): ?>
                        <a href="<?php echo escape_output($item['project_url']); ?>" 
                           class="portfolio-link" 
                           target="_blank" 
                           rel="noopener noreferrer">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="portfolio-content">
                    <div class="portfolio-category"><?php echo escape_output($item['category_name']); ?></div>
                    <h5 class="portfolio-title"><?php echo escape_output($item['title']); ?></h5>
                    <p class="portfolio-description"><?php echo escape_output($item['short_description']); ?></p>
                    
                    <?php if ($item['client_name']): ?>
                    <div class="portfolio-client">
                        <i class="fas fa-user"></i>
                        <span><?php echo escape_output($item['client_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($item['project_date']): ?>
                    <div class="portfolio-date">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo format_date($item['project_date']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="portfolio.php" class="btn btn-outline">
                <i class="fas fa-briefcase"></i>
                <?php echo escape_output(get_site_text('btn_view_all', 'Tüm Portföy')); ?>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section section-light" id="testimonials">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php echo escape_output(get_site_text('testimonials_title', 'Müşteri Yorumları')); ?></h2>
            <p><?php echo escape_output(get_site_text('testimonials_subtitle', 'Memnun Müşterilerim')); ?></p>
        </div>
        
        <div class="testimonials-carousel owl-carousel" data-aos="fade-up">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="testimonial-card">
                <?php if ($testimonial['avatar']): ?>
                <img src="assets/uploads/testimonials/<?php echo escape_output($testimonial['avatar']); ?>" 
                     alt="<?php echo escape_output($testimonial['name']); ?>" 
                     class="testimonial-avatar">
                <?php else: ?>
                <div class="testimonial-avatar" style="background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: var(--dark-color);">
                    <?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?>
                </div>
                <?php endif; ?>
                
                <div class="testimonial-rating">
                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                    <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                
                <p class="testimonial-text"><?php echo escape_output($testimonial['testimonial']); ?></p>
                
                <div class="testimonial-author"><?php echo escape_output($testimonial['name']); ?></div>
                
                <?php if ($testimonial['position'] || $testimonial['company']): ?>
                <div class="testimonial-position">
                    <?php 
                    if ($testimonial['position'] && $testimonial['company']) {
                        echo escape_output($testimonial['position'] . ' - ' . $testimonial['company']);
                    } elseif ($testimonial['position']) {
                        echo escape_output($testimonial['position']);
                    } elseif ($testimonial['company']) {
                        echo escape_output($testimonial['company']);
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section section-dark" id="cta">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="text-gradient">Hemen Başlayalım!</h2>
                <p class="lead mb-4">Profesyonel casino yayıncılığı hizmetleri ile kazancınızı artırın. Güvenilir ortaklıklar için doğru adrestesiniz.</p>
                
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope"></i>
                        İletişime Geç
                    </a>
                    <a href="tel:<?php echo escape_output(get_setting('site_phone')); ?>" class="btn btn-outline btn-lg">
                        <i class="fas fa-phone"></i>
                        Hemen Ara
                    </a>
                </div>
                
                <div class="cta-social mt-4">
                    <p class="text-muted">Sosyal medyada takip et:</p>
                    <div class="social-links">
                        <?php if (get_setting('social_facebook')): ?>
                        <a href="<?php echo escape_output(get_setting('social_facebook')); ?>" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (get_setting('social_twitter')): ?>
                        <a href="<?php echo escape_output(get_setting('social_twitter')); ?>" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (get_setting('social_instagram')): ?>
                        <a href="<?php echo escape_output(get_setting('social_instagram')); ?>" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (get_setting('social_telegram')): ?>
                        <a href="<?php echo escape_output(get_setting('social_telegram')); ?>" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (get_setting('social_youtube')): ?>
                        <a href="<?php echo escape_output(get_setting('social_youtube')); ?>" class="social-link" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>