<?php
/**
 * BonusBoss Portfolio Website - İletişim Sayfası
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Config dosyasını dahil et
require_once 'includes/config.php';

// Sayfa değişkenleri
$page_title = 'İletişim';
$current_page = 'contact';
$page_name = 'contact';

// Form gönderildi mi kontrol et
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    
    // Basit doğrulama
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Lütfen tüm zorunlu alanları doldurun.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi girin.';
    } else {
        // Mesajı kaydet
        if (save_contact_message($name, $email, $phone, $subject, $message)) {
            $success = true;
            // Formu temizle
            $name = $email = $phone = $subject = $message = '';
        } else {
            $error = 'Mesaj gönderilirken bir hata oluştu. Lütfen tekrar deneyin.';
        }
    }
}

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
                
                <h1 class="page-title"><?php echo escape_output(get_site_text('contact_page_title', 'İletişim')); ?></h1>
                <p class="page-subtitle"><?php echo escape_output(get_site_text('contact_page_subtitle', 'Benimle İletişime Geçin')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info -->
<section class="section section-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>İletişim Bilgileri</h2>
            <p>Bana ulaşmak için aşağıdaki bilgileri kullanabilirsiniz</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4>Adres</h4>
                    <p><?php echo escape_output(get_setting('contact_address', 'İstanbul, Türkiye')); ?></p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4>E-posta</h4>
                    <p>
                        <a href="mailto:<?php echo escape_output(get_setting('contact_email', 'info@bonusboss.com')); ?>">
                            <?php echo escape_output(get_setting('contact_email', 'info@bonusboss.com')); ?>
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h4>Telefon</h4>
                    <p>
                        <a href="tel:<?php echo escape_output(get_setting('contact_phone', '+90 555 123 45 67')); ?>">
                            <?php echo escape_output(get_setting('contact_phone', '+90 555 123 45 67')); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form -->
<section class="section section-dark">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="section-title" data-aos="fade-up">
                    <h2>Mesaj Gönder</h2>
                    <p>Projeleriniz hakkında konuşmak için bana mesaj gönderin</p>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" data-aos="fade-up">
                        <i class="fas fa-check-circle"></i>
                        <strong>Teşekkürler!</strong> Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağım.
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" data-aos="fade-up">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Hata!</strong> <?php echo escape_output($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="contact-form" data-aos="fade-up">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Ad Soyad <span class="required">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo escape_output($name ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">E-posta <span class="required">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo escape_output($email ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Telefon</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo escape_output($phone ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subject">Konu <span class="required">*</span></label>
                                <input type="text" id="subject" name="subject" class="form-control" 
                                       value="<?php echo escape_output($subject ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Mesaj <span class="required">*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="6" 
                                  placeholder="Projeniz hakkında detayları yazın..." required><?php echo escape_output($message ?? ''); ?></textarea>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i>
                            Mesaj Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Social Media -->
<section class="section section-light">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Sosyal Medya</h2>
            <p>Beni sosyal medya hesaplarımdan takip edebilirsiniz</p>
        </div>
        
        <div class="social-media-grid" data-aos="fade-up">
            <?php if (get_setting('social_instagram')): ?>
            <a href="<?php echo escape_output(get_setting('social_instagram')); ?>" 
               class="social-media-card" target="_blank" rel="noopener noreferrer">
                <div class="social-icon instagram">
                    <i class="fab fa-instagram"></i>
                </div>
                <h4>Instagram</h4>
                <p>Günlük paylaşımlarım</p>
            </a>
            <?php endif; ?>
            
            <?php if (get_setting('social_tiktok')): ?>
            <a href="<?php echo escape_output(get_setting('social_tiktok')); ?>" 
               class="social-media-card" target="_blank" rel="noopener noreferrer">
                <div class="social-icon tiktok">
                    <i class="fab fa-tiktok"></i>
                </div>
                <h4>TikTok</h4>
                <p>Kısa videolarım</p>
            </a>
            <?php endif; ?>
            
            <?php if (get_setting('social_youtube')): ?>
            <a href="<?php echo escape_output(get_setting('social_youtube')); ?>" 
               class="social-media-card" target="_blank" rel="noopener noreferrer">
                <div class="social-icon youtube">
                    <i class="fab fa-youtube"></i>
                </div>
                <h4>YouTube</h4>
                <p>Video içeriklerim</p>
            </a>
            <?php endif; ?>
            
            <?php if (get_setting('social_telegram')): ?>
            <a href="<?php echo escape_output(get_setting('social_telegram')); ?>" 
               class="social-media-card" target="_blank" rel="noopener noreferrer">
                <div class="social-icon telegram">
                    <i class="fab fa-telegram"></i>
                </div>
                <h4>Telegram</h4>
                <p>Anlık bildirimler</p>
            </a>
            <?php endif; ?>
            
            <?php if (get_setting('social_whatsapp')): ?>
            <a href="<?php echo escape_output(get_setting('social_whatsapp')); ?>" 
               class="social-media-card" target="_blank" rel="noopener noreferrer">
                <div class="social-icon whatsapp">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h4>WhatsApp</h4>
                <p>Direkt mesajlaşma</p>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section section-dark">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Sık Sorulan Sorular</h2>
            <p>Merak ettiğiniz soruların cevapları</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="faq-accordion" data-aos="fade-up">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <h4>Hangi hizmetleri sunuyorsunuz?</h4>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Casino streaming, oyun analizi, bonus stratejileri ve canlı yayın hizmetleri sunuyorum. Detaylar için hizmetler sayfasını ziyaret edebilirsiniz.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <h4>Canlı yayınlarınızı nereden izleyebilirim?</h4>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Canlı yayınlarımı YouTube, TikTok ve Instagram hesaplarımdan takip edebilirsiniz. Yayın saatlerini sosyal medya hesaplarımdan duyuruyorum.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <h4>İşbirliği tekliflerinizi nasıl değerlendiriyorsunuz?</h4>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Tüm işbirliği tekliflerini dikkatli bir şekilde değerlendiriyorum. Lütfen detaylı bilgileri yukarıdaki form aracılığıyla paylaşın.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <h4>Ne kadar sürede geri dönüş yapıyorsunuz?</h4>
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Genellikle 24 saat içinde tüm mesajlara yanıt vermeye çalışıyorum. Acil durumlar için WhatsApp üzerinden ulaşabilirsiniz.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// FAQ Toggle
function toggleFaq(element) {
    const faqItem = element.parentElement;
    const answer = faqItem.querySelector('.faq-answer');
    const icon = element.querySelector('i');
    
    // Close other open items
    document.querySelectorAll('.faq-item').forEach(item => {
        if (item !== faqItem) {
            item.classList.remove('active');
            item.querySelector('.faq-answer').style.maxHeight = '0';
            item.querySelector('.faq-question i').classList.remove('fa-minus');
            item.querySelector('.faq-question i').classList.add('fa-plus');
        }
    });
    
    // Toggle current item
    if (faqItem.classList.contains('active')) {
        faqItem.classList.remove('active');
        answer.style.maxHeight = '0';
        icon.classList.remove('fa-minus');
        icon.classList.add('fa-plus');
    } else {
        faqItem.classList.add('active');
        answer.style.maxHeight = answer.scrollHeight + 'px';
        icon.classList.remove('fa-plus');
        icon.classList.add('fa-minus');
    }
}

// Form validation
document.querySelector('.contact-form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();
    
    if (!name || !email || !subject || !message) {
        e.preventDefault();
        alert('Lütfen tüm zorunlu alanları doldurun.');
        return false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Lütfen geçerli bir e-posta adresi girin.');
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>