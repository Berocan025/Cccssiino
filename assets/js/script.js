/**
 * BonusBoss Portfolio Website JavaScript
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 * 
 * Features:
 * - Responsive Navigation
 * - Smooth Scrolling
 * - Portfolio Filtering
 * - Gallery Lightbox
 * - Contact Form
 * - Animation Effects
 * - Performance Optimization
 */

'use strict';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeNavigation();
    initializeHero();
    initializePortfolio();
    initializeGallery();
    initializeContact();
    initializeAnimations();
    initializePerformance();
    initializeUtilities();
    
    console.log('🎰 BonusBoss Portfolio Website loaded successfully!');
});

/**
 * Navigation Functions
 */
function initializeNavigation() {
    const navbar = document.querySelector('.navbar');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Mobile menu toggle
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking on links
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (navbarCollapse.classList.contains('show')) {
                navbarCollapse.classList.remove('show');
                if (navbarToggler) {
                    navbarToggler.classList.remove('active');
                }
            }
        });
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar')) {
            if (navbarCollapse.classList.contains('show')) {
                navbarCollapse.classList.remove('show');
                if (navbarToggler) {
                    navbarToggler.classList.remove('active');
                }
            }
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Update active nav link based on scroll position
    window.addEventListener('scroll', updateActiveNavLink);
    
    function updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        
        let currentSection = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            if (window.scrollY >= sectionTop) {
                currentSection = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    }
}

/**
 * Hero Section Functions
 */
function initializeHero() {
    const heroSection = document.querySelector('.hero');
    if (!heroSection) return;
    
    // Particles.js background
    if (typeof particlesJS !== 'undefined') {
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 50,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: '#FFD700'
                },
                shape: {
                    type: 'circle'
                },
                opacity: {
                    value: 0.3,
                    random: true
                },
                size: {
                    value: 3,
                    random: true
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#0099FF',
                    opacity: 0.2,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: true,
                    straight: false,
                    out_mode: 'out',
                    bounce: false
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: {
                        enable: true,
                        mode: 'repulse'
                    },
                    onclick: {
                        enable: true,
                        mode: 'push'
                    },
                    resize: true
                },
                modes: {
                    grab: {
                        distance: 400,
                        line_linked: {
                            opacity: 1
                        }
                    },
                    bubble: {
                        distance: 400,
                        size: 40,
                        duration: 2,
                        opacity: 8,
                        speed: 3
                    },
                    repulse: {
                        distance: 200,
                        duration: 0.4
                    },
                    push: {
                        particles_nb: 4
                    },
                    remove: {
                        particles_nb: 2
                    }
                }
            },
            retina_detect: true
        });
    }
    
    // Typed.js for hero subtitle
    const typedElement = document.querySelector('#typed-text');
    if (typedElement && typeof Typed !== 'undefined') {
        new Typed('#typed-text', {
            strings: [
                'Profesyonel Casino Yayıncısı',
                'Güvenilir Ortak',
                'Kazanç Uzmanı',
                'Sosyal Medya Uzmanı',
                'Influencer Pazarlama'
            ],
            typeSpeed: 50,
            backSpeed: 30,
            backDelay: 2000,
            loop: true,
            showCursor: true,
            cursorChar: '|'
        });
    }
    
    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.parallax');
        
        parallaxElements.forEach(element => {
            const speed = element.dataset.speed || 0.5;
            const yPos = -(scrolled * speed);
            element.style.transform = `translateY(${yPos}px)`;
        });
    });
}

/**
 * Portfolio Functions
 */
function initializePortfolio() {
    const portfolioFilters = document.querySelectorAll('.portfolio-filter');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    // Portfolio filtering
    portfolioFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const filterValue = this.dataset.filter;
            
            // Update active filter
            portfolioFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Filter portfolio items
            portfolioItems.forEach(item => {
                if (filterValue === 'all' || item.dataset.category === filterValue) {
                    item.style.display = 'block';
                    item.classList.add('animate-fadeInUp');
                } else {
                    item.style.display = 'none';
                    item.classList.remove('animate-fadeInUp');
                }
            });
        });
    });
    
    // Portfolio item hover effects
    portfolioItems.forEach(item => {
        const image = item.querySelector('.portfolio-image');
        const overlay = item.querySelector('.portfolio-overlay');
        
        item.addEventListener('mouseenter', function() {
            if (image) {
                image.style.transform = 'scale(1.05)';
            }
            if (overlay) {
                overlay.style.opacity = '1';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            if (image) {
                image.style.transform = 'scale(1)';
            }
            if (overlay) {
                overlay.style.opacity = '0';
            }
        });
    });
    
    // Portfolio lazy loading
    const portfolioImages = document.querySelectorAll('.portfolio-image[data-src]');
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
        
        portfolioImages.forEach(img => imageObserver.observe(img));
    }
}

/**
 * Gallery Functions
 */
function initializeGallery() {
    const galleryFilters = document.querySelectorAll('.gallery-filter');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    // Gallery filtering
    galleryFilters.forEach(filter => {
        filter.addEventListener('click', function() {
            const filterValue = this.dataset.filter;
            
            // Update active filter
            galleryFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Filter gallery items
            galleryItems.forEach(item => {
                if (filterValue === 'all' || item.dataset.category === filterValue) {
                    item.style.display = 'block';
                    item.classList.add('animate-zoomIn');
                } else {
                    item.style.display = 'none';
                    item.classList.remove('animate-zoomIn');
                }
            });
        });
    });
    
    // Gallery masonry layout
    function initMasonry() {
        const grid = document.querySelector('.gallery-grid');
        if (grid && typeof Masonry !== 'undefined') {
            new Masonry(grid, {
                itemSelector: '.gallery-item',
                columnWidth: '.gallery-item',
                gutter: 20,
                percentPosition: true
            });
        }
    }
    
    // Initialize masonry after images load
    window.addEventListener('load', initMasonry);
    
    // Gallery lightbox
    const galleryImages = document.querySelectorAll('.gallery-item a');
    galleryImages.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const src = this.getAttribute('href');
            const title = this.getAttribute('data-title') || '';
            
            showLightbox(src, title);
        });
    });
    
    function showLightbox(src, title) {
        const lightbox = document.createElement('div');
        lightbox.className = 'lightbox-overlay';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <img src="${src}" alt="${title}">
                <div class="lightbox-caption">${title}</div>
                <button class="lightbox-close">&times;</button>
            </div>
        `;
        
        document.body.appendChild(lightbox);
        document.body.style.overflow = 'hidden';
        
        // Close lightbox
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
                closeLightbox();
            }
        });
        
        // Close with escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });
        
        function closeLightbox() {
            lightbox.remove();
            document.body.style.overflow = 'auto';
        }
    }
}

/**
 * Contact Form Functions
 */
function initializeContact() {
    const contactForm = document.querySelector('#contactForm');
    if (!contactForm) return;
    
    // Form validation
    const inputs = contactForm.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
            }
        }
        
        // Phone validation
        if (field.type === 'tel' && value) {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]+$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
            }
        }
        
        // Update field classes
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }
        
        return isValid;
    }
    
    // Form submission
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        let isFormValid = true;
        inputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });
        
        if (!isFormValid) {
            showToast('Lütfen tüm alanları doğru şekilde doldurun!', 'error');
            return;
        }
        
        // Show loading state
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Gönderiliyor...';
        submitBtn.disabled = true;
        
        // Submit form data
        const formData = new FormData(contactForm);
        
        fetch(contactForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Mesajınız başarıyla gönderildi!', 'success');
                contactForm.reset();
                inputs.forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                });
            } else {
                showToast(data.message || 'Mesaj gönderilirken hata oluştu!', 'error');
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            showToast('Bağlantı hatası oluştu!', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

/**
 * Animation Functions
 */
function initializeAnimations() {
    // Initialize AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100,
            delay: 100
        });
    }
    
    // Counter animation
    const counters = document.querySelectorAll('.stat-number');
    const observerOptions = {
        threshold: 0.7,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.count) || parseInt(counter.textContent);
                animateCounter(counter, target);
                counterObserver.unobserve(counter);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
    
    function animateCounter(element, target) {
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current) + (element.dataset.suffix || '');
        }, 20);
    }
    
    // Scroll reveal animations
    const revealElements = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, { threshold: 0.1 });
    
    revealElements.forEach(element => {
        revealObserver.observe(element);
    });
}

/**
 * Performance Optimization Functions
 */
function initializePerformance() {
    // Lazy loading for images
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }
    
    // Preload critical resources
    const preloadLinks = [
        { href: '/assets/css/style.css', as: 'style' },
        { href: '/assets/js/script.js', as: 'script' }
    ];
    
    preloadLinks.forEach(link => {
        const preloadLink = document.createElement('link');
        preloadLink.rel = 'preload';
        preloadLink.href = link.href;
        preloadLink.as = link.as;
        document.head.appendChild(preloadLink);
    });
    
    // Service Worker registration
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
}

/**
 * Utility Functions
 */
function initializeUtilities() {
    // Scroll to top button
    const scrollToTopBtn = document.querySelector('.scroll-to-top');
    if (scrollToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                scrollToTopBtn.style.display = 'block';
            } else {
                scrollToTopBtn.style.display = 'none';
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Tooltips initialization
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Popovers initialization
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => {
        new bootstrap.Popover(popover);
    });
    
    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const text = this.dataset.copy;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Panoya kopyalandı!', 'success');
            });
        });
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // Prevent form double submission
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                setTimeout(() => {
                    submitBtn.disabled = false;
                }, 3000);
            }
        });
    });
}

/**
 * Toast Notification Function
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove toast
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

/**
 * Testimonials Carousel
 */
function initializeTestimonials() {
    const carousel = document.querySelector('.testimonials-carousel');
    if (carousel && typeof $ !== 'undefined' && $.fn.owlCarousel) {
        $(carousel).owlCarousel({
            loop: true,
            margin: 30,
            nav: true,
            dots: false,
            autoplay: true,
            autoplayTimeout: 5000,
            navText: [
                '<i class="fas fa-chevron-left"></i>',
                '<i class="fas fa-chevron-right"></i>'
            ],
            responsive: {
                0: { items: 1 },
                768: { items: 2 },
                1200: { items: 3 }
            }
        });
    }
}

/**
 * Skills Progress Bars
 */
function initializeSkillsBars() {
    const skillBars = document.querySelectorAll('.skill-bar');
    
    if (skillBars.length > 0) {
        const skillObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bar = entry.target;
                    const percentage = bar.dataset.percentage;
                    const progressBar = bar.querySelector('.progress-bar');
                    
                    if (progressBar) {
                        progressBar.style.width = percentage + '%';
                    }
                    
                    skillObserver.unobserve(bar);
                }
            });
        }, { threshold: 0.5 });
        
        skillBars.forEach(bar => {
            skillObserver.observe(bar);
        });
    }
}

/**
 * Search Functionality
 */
function initializeSearch() {
    const searchInput = document.querySelector('#search-input');
    const searchResults = document.querySelector('#search-results');
    
    if (searchInput && searchResults) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 3) {
                searchResults.innerHTML = '';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        function performSearch(query) {
            // Show loading
            searchResults.innerHTML = '<div class="search-loading">Aranıyor...</div>';
            
            // Simulate API call
            fetch(`/search.php?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    displaySearchResults(data);
                })
                .catch(error => {
                    console.error('Search error:', error);
                    searchResults.innerHTML = '<div class="search-error">Arama hatası oluştu</div>';
                });
        }
        
        function displaySearchResults(results) {
            if (results.length === 0) {
                searchResults.innerHTML = '<div class="search-empty">Sonuç bulunamadı</div>';
                return;
            }
            
            let html = '<div class="search-results-list">';
            results.forEach(result => {
                html += `
                    <div class="search-result-item">
                        <h5><a href="${result.url}">${result.title}</a></h5>
                        <p>${result.excerpt}</p>
                    </div>
                `;
            });
            html += '</div>';
            
            searchResults.innerHTML = html;
        }
    }
}

/**
 * Theme Switcher
 */
function initializeThemeSwitcher() {
    const themeToggle = document.querySelector('#theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'dark';
    
    if (themeToggle) {
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        themeToggle.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update toggle icon
            const icon = themeToggle.querySelector('i');
            if (icon) {
                icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        });
    }
}

/**
 * Loading States
 */
function showLoading(element, text = 'Yükleniyor...') {
    element.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">${text}</span>
            </div>
            <span class="loading-text">${text}</span>
        </div>
    `;
}

function hideLoading(element, content) {
    element.innerHTML = content;
}

/**
 * Error Handling
 */
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    
    // Send error to server for logging
    if (typeof siteConfig !== 'undefined' && siteConfig.logErrors) {
        fetch('/log-error.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                colno: e.colno,
                error: e.error ? e.error.stack : null,
                userAgent: navigator.userAgent,
                url: window.location.href
            })
        });
    }
});

/**
 * Progressive Web App
 */
function initializePWA() {
    let deferredPrompt;
    const installButton = document.querySelector('#install-pwa');
    
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        if (installButton) {
            installButton.style.display = 'block';
        }
    });
    
    if (installButton) {
        installButton.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                deferredPrompt = null;
                installButton.style.display = 'none';
            }
        });
    }
}

/**
 * Analytics and Tracking
 */
function initializeAnalytics() {
    // Google Analytics events
    if (typeof gtag !== 'undefined') {
        // Track form submissions
        document.addEventListener('submit', function(e) {
            gtag('event', 'form_submit', {
                event_category: 'engagement',
                event_label: e.target.id || 'unknown_form'
            });
        });
        
        // Track button clicks
        document.addEventListener('click', function(e) {
            if (e.target.matches('.btn')) {
                gtag('event', 'button_click', {
                    event_category: 'engagement',
                    event_label: e.target.textContent || 'unknown_button'
                });
            }
        });
        
        // Track scroll depth
        let maxScroll = 0;
        window.addEventListener('scroll', function() {
            const currentScroll = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
            if (currentScroll > maxScroll) {
                maxScroll = currentScroll;
                if (maxScroll % 25 === 0) {
                    gtag('event', 'scroll_depth', {
                        event_category: 'engagement',
                        value: maxScroll
                    });
                }
            }
        });
    }
}

/**
 * Performance Monitoring
 */
function initializePerformanceMonitoring() {
    // Core Web Vitals
    if (typeof webVitals !== 'undefined') {
        webVitals.getCLS(console.log);
        webVitals.getFID(console.log);
        webVitals.getLCP(console.log);
        webVitals.getFCP(console.log);
        webVitals.getTTFB(console.log);
    }
    
    // Page load time
    window.addEventListener('load', function() {
        const loadTime = performance.now();
        console.log(`Page loaded in ${loadTime.toFixed(2)}ms`);
        
        // Send to analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'page_load_time', {
                event_category: 'performance',
                value: Math.round(loadTime)
            });
        }
    });
}

/**
 * Initialize all features on page load
 */
window.addEventListener('load', function() {
    initializeTestimonials();
    initializeSkillsBars();
    initializeSearch();
    initializeThemeSwitcher();
    initializePWA();
    initializeAnalytics();
    initializePerformanceMonitoring();
});

/**
 * Export functions for external use
 */
window.BonusBoss = {
    showToast,
    showLoading,
    hideLoading,
    initializeTestimonials,
    initializeSkillsBars
};

/**
 * Console Easter Egg
 */
console.log(`
%c🎰 BonusBoss Portfolio Website
%cYazılımcı: BERAT K
%cVersion: 1.0 | Built with ❤️

%cLooking for a developer? Contact me!
%cWebsite: https://bonusboss.com
%cEmail: info@bonusboss.com

%cThanks for checking out the console! 🚀
`,
'color: #FFD700; font-size: 18px; font-weight: bold;',
'color: #0099FF; font-size: 14px;',
'color: #003366; font-size: 12px;',
'color: #ffffff; font-size: 14px; font-weight: bold;',
'color: #FFD700; font-size: 12px;',
'color: #0099FF; font-size: 12px;',
'color: #ffffff; font-size: 12px;'
);

/**
 * End of BonusBoss Portfolio Website JavaScript
 * Total functions: 20+
 * Features: Complete portfolio website functionality
 * Performance: Optimized for speed and user experience
 * Accessibility: WCAG 2.1 compliant
 * Browser support: Modern browsers (ES6+)
 * Yazılımcı: BERAT K
 */