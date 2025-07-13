    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="container">
                <div class="row">
                    <!-- About Section -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="footer-section">
                            <h5 class="footer-title">
                                <?php echo escape_output(get_site_text('footer_about_title', 'BonusBoss')); ?>
                            </h5>
                            <p class="footer-description">
                                <?php echo escape_output(get_site_text('footer_about_text', 'Profesyonel casino yayıncılığı hizmetleri ile kazancınızı artırın. Güvenilir ortaklıklar için doğru adres.')); ?>
                            </p>
                            <div class="footer-social">
                                <?php if (get_setting('social_facebook')): ?>
                                <a href="<?php echo escape_output(get_setting('social_facebook')); ?>" target="_blank" rel="noopener noreferrer" class="social-link">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (get_setting('social_twitter')): ?>
                                <a href="<?php echo escape_output(get_setting('social_twitter')); ?>" target="_blank" rel="noopener noreferrer" class="social-link">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (get_setting('social_instagram')): ?>
                                <a href="<?php echo escape_output(get_setting('social_instagram')); ?>" target="_blank" rel="noopener noreferrer" class="social-link">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (get_setting('social_telegram')): ?>
                                <a href="<?php echo escape_output(get_setting('social_telegram')); ?>" target="_blank" rel="noopener noreferrer" class="social-link">
                                    <i class="fab fa-telegram"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (get_setting('social_youtube')): ?>
                                <a href="<?php echo escape_output(get_setting('social_youtube')); ?>" target="_blank" rel="noopener noreferrer" class="social-link">
                                    <i class="fab fa-youtube"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="footer-section">
                            <h5 class="footer-title">
                                <?php echo escape_output(get_site_text('footer_links_title', 'Hızlı Linkler')); ?>
                            </h5>
                            <ul class="footer-links">
                                <li><a href="<?php echo SITE_URL; ?>"><?php echo escape_output(get_site_text('nav_home', 'Ana Sayfa')); ?></a></li>
                                <li><a href="<?php echo SITE_URL; ?>/about.php"><?php echo escape_output(get_site_text('nav_about', 'Hakkımda')); ?></a></li>
                                <li><a href="<?php echo SITE_URL; ?>/services.php"><?php echo escape_output(get_site_text('nav_services', 'Hizmetler')); ?></a></li>
                                <li><a href="<?php echo SITE_URL; ?>/portfolio.php"><?php echo escape_output(get_site_text('nav_portfolio', 'Portföy')); ?></a></li>
                                <li><a href="<?php echo SITE_URL; ?>/gallery.php"><?php echo escape_output(get_site_text('nav_gallery', 'Galeri')); ?></a></li>
                                <li><a href="<?php echo SITE_URL; ?>/contact.php"><?php echo escape_output(get_site_text('nav_contact', 'İletişim')); ?></a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Services Links -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="footer-section">
                            <h5 class="footer-title">
                                <?php echo escape_output(get_site_text('nav_services', 'Hizmetler')); ?>
                            </h5>
                            <ul class="footer-links">
                                <?php
                                $services = get_services(5);
                                foreach ($services as $service):
                                ?>
                                <li><a href="<?php echo SITE_URL; ?>/services.php#service-<?php echo $service['id']; ?>"><?php echo escape_output($service['title']); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="footer-section">
                            <h5 class="footer-title">
                                <?php echo escape_output(get_site_text('footer_contact_title', 'İletişim')); ?>
                            </h5>
                            <div class="contact-info">
                                <?php if (get_setting('site_email')): ?>
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo escape_output(get_setting('site_email')); ?>">
                                        <?php echo escape_output(get_setting('site_email')); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (get_setting('site_phone')): ?>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <a href="tel:<?php echo escape_output(get_setting('site_phone')); ?>">
                                        <?php echo escape_output(get_setting('site_phone')); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (get_setting('site_address')): ?>
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo escape_output(get_setting('site_address')); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (get_setting('social_telegram')): ?>
                                <div class="contact-item">
                                    <i class="fab fa-telegram"></i>
                                    <a href="<?php echo escape_output(get_setting('social_telegram')); ?>" target="_blank" rel="noopener noreferrer">
                                        Telegram Kanalı
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright-text">
                            &copy; <?php echo date('Y'); ?> 
                            <strong><?php echo escape_output(get_setting('site_title', 'BonusBoss')); ?></strong>. 
                            <?php echo escape_output(get_site_text('footer_copyright', 'Tüm hakları saklıdır.')); ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="powered-by">
                            <?php echo escape_output(get_site_text('footer_powered_by', 'Yazılımcı: BERAT K')); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float Button -->
    <?php if (get_setting('site_phone')): ?>
    <div class="whatsapp-float">
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', get_setting('site_phone')); ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    <?php endif; ?>

    <!-- Telegram Float Button -->
    <?php if (get_setting('social_telegram')): ?>
    <div class="telegram-float">
        <a href="<?php echo escape_output(get_setting('social_telegram')); ?>" target="_blank" rel="noopener noreferrer" class="telegram-btn">
            <i class="fab fa-telegram"></i>
        </a>
    </div>
    <?php endif; ?>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.0.12/typed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
    
    <!-- Inline Scripts -->
    <script>
        // Site configuration
        const siteConfig = {
            url: '<?php echo SITE_URL; ?>',
            ajaxUrl: '<?php echo SITE_URL; ?>/ajax/',
            csrfToken: '<?php echo $_SESSION['csrf_token']; ?>',
            loadingText: '<?php echo escape_output(get_site_text('loading_text', 'Yükleniyor...')); ?>',
            noResultsText: '<?php echo escape_output(get_site_text('no_results', 'Sonuç bulunamadı')); ?>'
        };
        
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });
        
        // Initialize Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'fadeDuration': 300,
            'imageFadeDuration': 300
        });
        
        // Preloader
        $(window).on('load', function() {
            $('#preloader').fadeOut(500);
        });
        
        // Scroll to top
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('#scrollToTop').fadeIn();
            } else {
                $('#scrollToTop').fadeOut();
            }
        });
        
        $('#scrollToTop').click(function() {
            $('html, body').animate({scrollTop: 0}, 800);
            return false;
        });
        
        // Navbar scroll effect
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('.navbar').addClass('scrolled');
            } else {
                $('.navbar').removeClass('scrolled');
            }
        });
        
        // Smooth scrolling for anchor links
        $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(event) {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    event.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 1000);
                }
            }
        });
        
        // Form validation
        function validateForm(formId) {
            let isValid = true;
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
                
                if (input.type === 'email' && input.value.trim()) {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(input.value)) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    }
                }
            });
            
            return isValid;
        }
        
        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = `
                <div class="toast-notification toast-${type}">
                    <div class="toast-content">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                        <span>${message}</span>
                    </div>
                </div>
            `;
            
            $('body').append(toast);
            
            setTimeout(() => {
                $('.toast-notification').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
        
        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
        
        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Panoya kopyalandı!');
            }).catch(() => {
                showToast('Kopyalama hatası!', 'error');
            });
        }
        
        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.now();
            console.log(`Page loaded in ${loadTime.toFixed(2)}ms`);
            
            // Send performance data to server (optional)
            if (loadTime > 3000) {
                console.warn('Page load time is slow:', loadTime);
            }
        });
        
        // Error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
            // Send error to server for logging (optional)
        });
        
        // Mobile menu handling
        $('.navbar-toggler').click(function() {
            $(this).toggleClass('active');
        });
        
        // Close mobile menu when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('.navbar').length) {
                $('.navbar-collapse').removeClass('show');
                $('.navbar-toggler').removeClass('active');
            }
        });
        
        // Prevent form double submission
        $('form').submit(function() {
            $(this).find('button[type="submit"]').prop('disabled', true);
            setTimeout(() => {
                $(this).find('button[type="submit"]').prop('disabled', false);
            }, 3000);
        });
        
        // Auto-hide alerts
        setTimeout(() => {
            $('.alert').fadeOut(300);
        }, 5000);
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Initialize popovers
        $('[data-bs-toggle="popover"]').popover();
        
        // Page specific scripts
        const pageName = '<?php echo $page_name; ?>';
        
        if (pageName === 'index') {
            // Homepage specific scripts
            initializeHomepage();
        } else if (pageName === 'portfolio') {
            // Portfolio specific scripts
            initializePortfolio();
        } else if (pageName === 'gallery') {
            // Gallery specific scripts
            initializeGallery();
        } else if (pageName === 'contact') {
            // Contact specific scripts
            initializeContact();
        }
        
        // Homepage initialization
        function initializeHomepage() {
            // Typed.js for hero section
            if ($('#typed-text').length) {
                new Typed('#typed-text', {
                    strings: ['Profesyonel Casino Yayıncısı', 'Güvenilir Ortak', 'Kazanç Uzmanı'],
                    typeSpeed: 50,
                    backSpeed: 30,
                    loop: true
                });
            }
            
            // Testimonials carousel
            if ($('.testimonials-carousel').length) {
                $('.testimonials-carousel').owlCarousel({
                    loop: true,
                    margin: 30,
                    nav: true,
                    dots: false,
                    autoplay: true,
                    autoplayTimeout: 5000,
                    responsive: {
                        0: { items: 1 },
                        768: { items: 2 },
                        1200: { items: 3 }
                    }
                });
            }
        }
        
        // Portfolio initialization
        function initializePortfolio() {
            // Portfolio filtering
            $('.portfolio-filter').click(function() {
                const filter = $(this).data('filter');
                $('.portfolio-filter').removeClass('active');
                $(this).addClass('active');
                
                if (filter === 'all') {
                    $('.portfolio-item').show();
                } else {
                    $('.portfolio-item').hide();
                    $('.portfolio-item[data-category="' + filter + '"]').show();
                }
            });
        }
        
        // Gallery initialization
        function initializeGallery() {
            // Gallery filtering
            $('.gallery-filter').click(function() {
                const filter = $(this).data('filter');
                $('.gallery-filter').removeClass('active');
                $(this).addClass('active');
                
                if (filter === 'all') {
                    $('.gallery-item').show();
                } else {
                    $('.gallery-item').hide();
                    $('.gallery-item[data-category="' + filter + '"]').show();
                }
            });
        }
        
        // Contact initialization
        function initializeContact() {
            // Contact form handling
            $('#contactForm').submit(function(e) {
                e.preventDefault();
                
                if (!validateForm('contactForm')) {
                    showToast('Lütfen tüm alanları doldurun!', 'error');
                    return;
                }
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: siteConfig.url + '/contact.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showToast('Mesajınız başarıyla gönderildi!');
                            $('#contactForm')[0].reset();
                        } else {
                            showToast(response.message || 'Hata oluştu!', 'error');
                        }
                    },
                    error: function() {
                        showToast('Bağlantı hatası!', 'error');
                    }
                });
            });
        }
        
        // Console log
        console.log('%c🎰 BonusBoss Portfolio Website', 'color: #FFD700; font-size: 16px; font-weight: bold;');
        console.log('%cYazılımcı: BERAT K', 'color: #0099FF; font-size: 12px;');
        console.log('%cVersion: 1.0', 'color: #003366; font-size: 12px;');
    </script>
</body>
</html>
<?php
// Output buffer'ı temizle
ob_end_flush();
?>