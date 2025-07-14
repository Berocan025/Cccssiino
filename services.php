<?php
/**
 * BonusBoss Portfolio Website - Hizmetler Sayfası
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Config dosyasını dahil et
require_once 'includes/config.php';

// Sayfa değişkenleri
$page_title = 'Hizmetler';
$current_page = 'services';

// Verileri al
$services = get_services();
$categories = get_categories('service');

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
                
                <h1 class="page-title"><?php echo escape_output(get_site_text('services_page_title', 'Hizmetlerim')); ?></h1>
                <p class="page-subtitle"><?php echo escape_output(get_site_text('services_page_subtitle', 'Profesyonel Casino Yayıncılığı Hizmetleri')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="section section-light">
    <div class="container">
        <!-- Service Categories -->
        <?php if (!empty($categories)): ?>
        <div class="service-categories mb-5" data-aos="fade-up">
            <div class="text-center">
                <h3>Hizmet Kategorileri</h3>
                <div class="category-filters">
                    <button class="btn btn-outline category-filter active" data-filter="all">Tümü</button>
                    <?php foreach ($categories as $category): ?>
                    <button class="btn btn-outline category-filter" data-filter="<?php echo $category['id']; ?>">
                        <?php echo escape_output($category['name']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Services Grid -->
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
            <div class="service-item" data-category="<?php echo $service['category_id']; ?>" data-aos="fade-up">
                <div class="card service-card h-100" id="service-<?php echo $service['id']; ?>">
                    <div class="card-icon">
                        <i class="<?php echo escape_output($service['icon']); ?>"></i>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo escape_output($service['title']); ?></h5>
                        <p class="card-text"><?php echo escape_output($service['short_description']); ?></p>
                        
                        <?php if ($service['full_description']): ?>
                        <div class="service-description">
                            <h6>Detaylı Bilgi</h6>
                            <p><?php echo escape_output($service['full_description']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($service['features']): ?>
                        <div class="service-features">
                            <h6>Özellikler</h6>
                            <ul>
                                <?php 
                                $features = json_decode($service['features'], true);
                                if ($features) {
                                    foreach ($features as $feature): ?>
                                <li><?php echo escape_output($feature); ?></li>
                                <?php endforeach;
                                } ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($service['price_text']): ?>
                        <div class="service-price">
                            <i class="fas fa-tag"></i>
                            <span><?php echo escape_output($service['price_text']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="service-actions">
                            <a href="contact.php?service=<?php echo $service['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-envelope"></i>
                                Teklif Al
                            </a>
                            <a href="tel:<?php echo escape_output(get_setting('site_phone')); ?>" class="btn btn-outline">
                                <i class="fas fa-phone"></i>
                                Ara
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($services)): ?>
        <div class="text-center py-5">
            <i class="fas fa-info-circle text-muted" style="font-size: 3rem;"></i>
            <h4 class="mt-3">Henüz hizmet eklenmemiş</h4>
            <p class="text-muted">Hizmetlerimiz yakında eklenecek.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Process Section -->
<section class="section section-dark">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Çalışma Sürecim</h2>
            <p>Profesyonel hizmet sürecimde izlediğim adımlar</p>
        </div>
        
        <div class="process-steps">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="process-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h5>İletişim</h5>
                            <p>İhtiyaçlarınızı detaylı olarak dinliyorum ve size en uygun çözümü belirliyoruz.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="process-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h5>Planlama</h5>
                            <p>Hedeflerinize uygun detaylı bir strateji ve çalışma planı hazırlıyorum.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="process-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h5>Uygulama</h5>
                            <p>Belirlenen stratejiye uygun olarak profesyonel hizmet sunmaya başlıyorum.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="process-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h5>Takip</h5>
                            <p>Süreç boyunca düzenli raporlar sunuyor ve gerekli optimizasyonları yapıyorum.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section section-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Sık Sorulan Sorular</h2>
            <p>Hizmetlerim hakkında merak edilen sorular</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion" data-aos="fade-up">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Hangi platformlarda yayın yapıyorsunuz?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Instagram, TikTok, YouTube, Telegram ve diğer popüler sosyal medya platformlarında aktif olarak yayın yapıyorum. Platformların seçimi projenizin hedef kitlesine göre belirlenir.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Hizmet fiyatları nasıl belirleniyor?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Fiyatlar, hizmetin kapsamı, süre, platform sayısı ve hedef kitleye göre belirlenir. Her proje için özel teklif hazırlanır ve size en uygun çözüm sunulur.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Projeler ne kadar sürede tamamlanır?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Proje süreleri, hizmetin türü ve kapsamına göre değişir. Genellikle 1-4 hafta arası sürelerde tamamlanır. Acil projeler için hızlı çözüm seçenekleri de mevcuttur.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Hangi sektörlerde çalışıyorsunuz?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ağırlıklı olarak casino ve eğlence sektöründe uzmanlaşmış durumdayım. Ancak sosyal medya pazarlama ve influencer hizmetleri için farklı sektörlerden projeler de kabul etmekteyim.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Raporlama nasıl yapılır?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Tüm projeler için detaylı performans raporları hazırlanır. Haftalık ve aylık raporlar ile proje ilerleyişi hakkında bilgi verilir. Anlık durum takibi için de 24/7 iletişim sağlanır.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section section-dark">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="text-gradient">Hizmetlerim İçin İletişime Geçin</h2>
                <p class="lead mb-4">Profesyonel casino yayıncılığı hizmetleri için size özel teklif hazırlamaktan memnuniyet duyarım. Hemen iletişime geçin!</p>
                
                <div class="cta-buttons">
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope"></i>
                        Teklif Al
                    </a>
                    <a href="tel:<?php echo escape_output(get_setting('site_phone')); ?>" class="btn btn-outline btn-lg">
                        <i class="fas fa-phone"></i>
                        Hemen Ara
                    </a>
                </div>
                
                <div class="mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i>
                        Güvenli ödeme • 24/7 destek • Memnuniyet garantisi
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Service category filtering
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilters = document.querySelectorAll('.category-filter');
    const serviceItems = document.querySelectorAll('.service-item');
    
    categoryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const filterValue = this.dataset.filter;
            
            // Update active filter
            categoryFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Filter service items
            serviceItems.forEach(item => {
                if (filterValue === 'all' || item.dataset.category === filterValue) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>