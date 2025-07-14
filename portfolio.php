<?php
/**
 * BonusBoss Portfolio Website - Portföy Sayfası
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Config dosyasını dahil et
require_once 'includes/config.php';

// Sayfa değişkenleri
$page_title = 'Portföy';
$current_page = 'portfolio';

// Verileri al
$portfolio = get_portfolio();
$categories = get_categories('portfolio');

// Breadcrumb oluştur
$breadcrumb = generate_breadcrumb($page_title);

// Header dahil et
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumb as $item): ?>
                        <li class="breadcrumb-item <?php echo isset($item['active']) ? 'active' : ''; ?>">
                            <?php if (isset($item['active'])): ?>
                            <?php echo escape_output($item['title']); ?>
                            <?php else: ?>
                            <a href="<?php echo escape_output($item['url']); ?>"><?php echo escape_output($item['title']); ?></a>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                
                <h1 class="page-title"><?php echo escape_output(get_site_text('portfolio_page_title', 'Portföyüm')); ?></h1>
                <p class="page-subtitle"><?php echo escape_output(get_site_text('portfolio_page_subtitle', 'Başarılı Projelerim')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Filters -->
<section class="section section-light">
    <div class="container">
        <?php if (!empty($categories)): ?>
        <div class="portfolio-filters" data-aos="fade-up">
            <button class="portfolio-filter active" data-filter="all">
                <?php echo escape_output(get_site_text('portfolio_filter_all', 'Tümü')); ?>
            </button>
            <?php foreach ($categories as $category): ?>
            <button class="portfolio-filter" data-filter="<?php echo $category['id']; ?>">
                <?php echo escape_output($category['name']); ?>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Portfolio Grid -->
        <div class="portfolio-grid">
            <?php foreach ($portfolio as $item): ?>
            <div class="portfolio-item" data-category="<?php echo $item['category_id']; ?>" data-aos="fade-up">
                <div class="portfolio-card">
                    <div class="portfolio-image-wrapper">
                        <img src="assets/uploads/portfolio/<?php echo escape_output($item['image']); ?>" 
                             alt="<?php echo escape_output($item['title']); ?>" 
                             class="portfolio-image">
                        
                        <div class="portfolio-overlay">
                            <div class="portfolio-links">
                                <a href="assets/uploads/portfolio/<?php echo escape_output($item['image']); ?>" 
                                   class="portfolio-link" 
                                   data-lightbox="portfolio"
                                   data-title="<?php echo escape_output($item['title']); ?>">
                                    <i class="fas fa-search-plus"></i>
                                </a>
                                <?php if ($item['project_url']): ?>
                                <a href="<?php echo escape_output($item['project_url']); ?>" 
                                   class="portfolio-link" 
                                   target="_blank" 
                                   rel="noopener noreferrer">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <?php endif; ?>
                                <a href="portfolio-detail.php?id=<?php echo $item['id']; ?>" 
                                   class="portfolio-link">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="portfolio-content">
                        <div class="portfolio-meta">
                            <div class="portfolio-category">
                                <i class="fas fa-folder"></i>
                                <?php echo escape_output($item['category_name']); ?>
                            </div>
                            <?php if ($item['project_date']): ?>
                            <div class="portfolio-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo format_date($item['project_date']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="portfolio-title">
                            <a href="portfolio-detail.php?id=<?php echo $item['id']; ?>">
                                <?php echo escape_output($item['title']); ?>
                            </a>
                        </h5>
                        
                        <p class="portfolio-description">
                            <?php echo escape_output($item['short_description']); ?>
                        </p>
                        
                        <?php if ($item['client_name']): ?>
                        <div class="portfolio-client">
                            <i class="fas fa-user"></i>
                            <span><?php echo escape_output($item['client_name']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($item['technologies']): ?>
                        <div class="portfolio-tech">
                            <i class="fas fa-code"></i>
                            <span><?php echo escape_output($item['technologies']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($item['featured'] == 'yes'): ?>
                        <div class="portfolio-featured">
                            <i class="fas fa-star"></i>
                            <span>Öne Çıkan</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($portfolio)): ?>
        <div class="text-center py-5">
            <i class="fas fa-briefcase text-muted" style="font-size: 3rem;"></i>
            <h4 class="mt-3">Henüz proje eklenmemiş</h4>
            <p class="text-muted">Portföy projelerimiz yakında eklenecek.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Skills Section -->
<section class="section section-dark">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Kullandığım Teknolojiler</h2>
            <p>Projelerimde kullandığım araçlar ve teknolojiler</p>
        </div>
        
        <div class="tech-grid">
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="tech-item">
                        <i class="fab fa-instagram"></i>
                        <span>Instagram</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="tech-item">
                        <i class="fab fa-tiktok"></i>
                        <span>TikTok</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="tech-item">
                        <i class="fab fa-youtube"></i>
                        <span>YouTube</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="tech-item">
                        <i class="fab fa-telegram"></i>
                        <span>Telegram</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="tech-item">
                        <i class="fab fa-facebook"></i>
                        <span>Facebook</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="tech-item">
                        <i class="fab fa-twitter"></i>
                        <span>Twitter</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="700">
                    <div class="tech-item">
                        <i class="fas fa-video"></i>
                        <span>Canlı Yayın</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="800">
                    <div class="tech-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Analitik</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="section section-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="text-gradient">Projenizi Hayata Geçirelim</h2>
                <p class="lead mb-4">Benzer bir proje için benimle iletişime geçin. Size özel çözümler sunmaktan memnuniyet duyarım.</p>
                
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope"></i>
                        Proje Teklifi Al
                    </a>
                    <a href="services.php" class="btn btn-outline btn-lg">
                        <i class="fas fa-cogs"></i>
                        Hizmetlerim
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>