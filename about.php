<?php
/**
 * BonusBoss Portfolio Website - Hakkımda Sayfası
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Config dosyasını dahil et
require_once 'includes/config.php';

// Sayfa değişkenleri
$page_title = 'Hakkımda';
$current_page = 'about';

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
                
                <h1 class="page-title"><?php echo escape_output(get_site_text('about_page_title', 'Hakkımda')); ?></h1>
                <p class="page-subtitle"><?php echo escape_output(get_site_text('about_page_subtitle', 'Profesyonel Casino Yayıncısı')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="section section-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="about-image-wrapper">
                    <img src="assets/images/about-profile.jpg" alt="BonusBoss Profile" class="img-fluid rounded">
                    <div class="about-overlay">
                        <div class="about-stats">
                            <div class="stat-item">
                                <span class="stat-number">5+</span>
                                <span class="stat-label">Yıl Deneyim</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">100+</span>
                                <span class="stat-label">Mutlu Müşteri</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-content">
                    <h2><?php echo escape_output(get_site_text('about_story_title', 'Hikayem')); ?></h2>
                    <p><?php echo escape_output(get_site_text('about_story_content', 'Casino sektöründe 5 yıllık tecrübem ile size en iyi hizmeti sunuyorum. Güvenilir ortaklıklar kurup kazancınızı artırmanız için buradayım.')); ?></p>
                    
                    <div class="about-highlights">
                        <div class="highlight-item">
                            <i class="fas fa-check-circle text-primary"></i>
                            <span>Profesyonel casino yayıncılığı</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-check-circle text-primary"></i>
                            <span>Sosyal medya uzmanı</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-check-circle text-primary"></i>
                            <span>Influencer pazarlama</span>
                        </div>
                        <div class="highlight-item">
                            <i class="fas fa-check-circle text-primary"></i>
                            <span>24/7 destek</span>
                        </div>
                    </div>
                    
                    <div class="about-contact">
                        <h5>Benimle İletişime Geç</h5>
                        <div class="contact-buttons">
                            <a href="contact.php" class="btn btn-primary">
                                <i class="fas fa-envelope"></i>
                                Mesaj Gönder
                            </a>
                            <a href="tel:<?php echo escape_output(get_setting('site_phone')); ?>" class="btn btn-outline">
                                <i class="fas fa-phone"></i>
                                Hemen Ara
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Experience Section -->
<section class="section section-dark">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php echo escape_output(get_site_text('about_experience_title', 'Deneyimlerim')); ?></h2>
            <p>Profesyonel casino yayıncılığı alanında edindiğim deneyimler</p>
        </div>
        
        <div class="timeline" data-aos="fade-up">
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <h5>2019 - 2024</h5>
                    <h4>Freelance Casino Yayıncısı</h4>
                    <p>Çeşitli casino platformlarında freelance yayıncı olarak çalıştım. Sosyal medya hesaplarımda 10K+ takipçi kitlesi oluşturdum.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <h5>2020 - Devam</h5>
                    <h4>Sosyal Medya Uzmanı</h4>
                    <p>Instagram, TikTok, YouTube ve Telegram kanallarında aktif içerik üretimi ve topluluk yönetimi yapıyorum.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <h5>2021 - Devam</h5>
                    <h4>Influencer Pazarlama</h4>
                    <p>Çeşitli markaların influencer pazarlama kampanyalarını yönetiyor ve stratejilerini geliştiriyorum.</p>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <h5>2022 - Devam</h5>
                    <h4>BonusBoss Markası</h4>
                    <p>Kendi markamı kurarak profesyonel casino yayıncılığı hizmetleri sunmaya başladım.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Skills Section -->
<section class="section section-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2><?php echo escape_output(get_site_text('about_skills_title', 'Yeteneklerim')); ?></h2>
            <p>Profesyonel alanlarımdaki uzmanlık seviyelerim</p>
        </div>
        
        <div class="row">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="skills-content">
                    <h4>Teknik Yetenekler</h4>
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-title">Canlı Yayın</span>
                            <span class="skill-percentage">95%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 95%"></div>
                        </div>
                    </div>
                    
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-title">Sosyal Medya</span>
                            <span class="skill-percentage">90%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 90%"></div>
                        </div>
                    </div>
                    
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-title">İçerik Üretimi</span>
                            <span class="skill-percentage">85%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 85%"></div>
                        </div>
                    </div>
                    
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-title">Topluluk Yönetimi</span>
                            <span class="skill-percentage">92%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 92%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-left">
                <div class="skills-content">
                    <h4>Profesyonel Yetenekler</h4>
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-title">Pazarlama Stratejisi</span>
                            <span class="skill-percentage">88%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 88%"></div>
                        </div>
                    </div>
                    
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-title">Müşteri İletişimi</span>
                            <span class="skill-percentage">96%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 96%"></div>
                        </div>
                    </div>
                    
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-title">Proje Yönetimi</span>
                            <span class="skill-percentage">80%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 80%"></div>
                        </div>
                    </div>
                    
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-title">Analitik & Raporlama</span>
                            <span class="skill-percentage">83%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 83%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="section section-dark">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Değerlerim</h2>
            <p>Çalışma hayatımda benimsediğim temel prensipler</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h5>Güvenilirlik</h5>
                    <p>Tüm projelerimde şeffaflık ve güvenilirlik ilkesini benimserim. Müşterilerimle uzun vadeli ilişkiler kurmayı hedeflerim.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h5>Kalite</h5>
                    <p>Her projede en yüksek kalite standartlarını sağlarım. Detaylara önem verir ve mükemmel sonuçlar için çalışırım.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h5>İnovasyon</h5>
                    <p>Sürekli öğrenme ve gelişim halindeyim. Yeni teknolojiler ve trendleri takip ederek hizmetlerimi geliştiririm.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section section-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="text-gradient">Birlikte Çalışalım!</h2>
                <p class="lead mb-4">Profesyonel casino yayıncılığı hizmetleri için benimle iletişime geçin. Size özel çözümler sunmaktan memnuniyet duyarım.</p>
                
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope"></i>
                        İletişime Geç
                    </a>
                    <a href="portfolio.php" class="btn btn-outline btn-lg">
                        <i class="fas fa-briefcase"></i>
                        Portföyüm
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>