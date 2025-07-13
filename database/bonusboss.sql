-- BonusBoss Database Structure
-- Yazılımcı: BERAT K
-- Tarih: 2024

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Veritabanı: bonusboss
-- Kullanım: BonusBoss Portföy Sitesi

-- --------------------------------------------------------

-- Tablo yapısı: users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('admin','editor') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan admin kullanıcı ekleme (şifre: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@bonusboss.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'BonusBoss Admin', 'admin');

-- --------------------------------------------------------

-- Tablo yapısı: settings
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` varchar(50) DEFAULT 'text',
  `category` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan ayarlar
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('site_title', 'BonusBoss - Profesyonel Casino Yayıncısı', 'text', 'general', 'Site başlığı'),
('site_description', 'Kazançlı ortaklıklar için doğru adres', 'text', 'general', 'Site açıklaması'),
('site_keywords', 'casino, bonus, boss, yayıncı, ortaklık', 'text', 'seo', 'Site anahtar kelimeleri'),
('site_email', 'info@bonusboss.com', 'email', 'contact', 'Site email adresi'),
('site_phone', '+90 555 123 45 67', 'text', 'contact', 'Site telefon numarası'),
('site_address', 'İstanbul, Türkiye', 'text', 'contact', 'Site adresi'),
('site_logo', 'logo.png', 'file', 'general', 'Site logosu'),
('site_favicon', 'favicon.ico', 'file', 'general', 'Site favicon'),
('social_facebook', 'https://facebook.com/bonusboss', 'url', 'social', 'Facebook linki'),
('social_twitter', 'https://twitter.com/bonusboss', 'url', 'social', 'Twitter linki'),
('social_instagram', 'https://instagram.com/bonusboss', 'url', 'social', 'Instagram linki'),
('social_telegram', 'https://t.me/bonusboss', 'url', 'social', 'Telegram linki'),
('social_youtube', 'https://youtube.com/bonusboss', 'url', 'social', 'Youtube linki'),
('contact_form_email', 'contact@bonusboss.com', 'email', 'contact', 'İletişim formu email'),
('theme_primary_color', '#FFD700', 'color', 'theme', 'Ana renk'),
('theme_secondary_color', '#0099FF', 'color', 'theme', 'İkinci renk'),
('theme_dark_color', '#003366', 'color', 'theme', 'Koyu renk'),
('maintenance_mode', '0', 'boolean', 'general', 'Bakım modu'),
('google_analytics', '', 'textarea', 'seo', 'Google Analytics kodu'),
('facebook_pixel', '', 'textarea', 'seo', 'Facebook Pixel kodu');

-- --------------------------------------------------------

-- Tablo yapısı: site_texts
CREATE TABLE `site_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text_key` varchar(100) NOT NULL,
  `text_value` text NOT NULL,
  `page_name` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `text_key` (`text_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site metinleri
INSERT INTO `site_texts` (`text_key`, `text_value`, `page_name`, `section`, `description`) VALUES
-- Ana sayfa metinleri
('hero_title', 'BonusBoss', 'homepage', 'hero', 'Ana başlık'),
('hero_subtitle', 'Profesyonel Casino Yayıncısı', 'homepage', 'hero', 'Alt başlık'),
('hero_description', 'Kazançlı ortaklıklar için doğru adres. Profesyonel casino yayıncılığı hizmetleri ile kazancınızı artırın.', 'homepage', 'hero', 'Ana açıklama'),
('hero_button_services', 'Hizmetlerim', 'homepage', 'hero', 'Hizmetler butonu'),
('hero_button_contact', 'İletişim', 'homepage', 'hero', 'İletişim butonu'),
('about_title', 'Hakkımda', 'homepage', 'about', 'Hakkımda başlık'),
('about_subtitle', 'Profesyonel Casino Yayıncısı', 'homepage', 'about', 'Hakkımda alt başlık'),
('about_description', 'Yıllarca casino sektöründe edindiğim tecrübe ile size en iyi hizmeti sunuyorum. Güvenilir ortaklıklar kurup kazancınızı artırmanız için buradayım.', 'homepage', 'about', 'Hakkımda açıklama'),
('services_title', 'Hizmetlerim', 'homepage', 'services', 'Hizmetler başlık'),
('services_subtitle', 'Profesyonel Casino Yayıncılığı', 'homepage', 'services', 'Hizmetler alt başlık'),
('portfolio_title', 'Portföyüm', 'homepage', 'portfolio', 'Portföy başlık'),
('portfolio_subtitle', 'Başarılı Projelerim', 'homepage', 'portfolio', 'Portföy alt başlık'),
('stats_title', 'Başarılarım', 'homepage', 'stats', 'İstatistikler başlık'),
('stats_followers', '10K+', 'homepage', 'stats', 'Takipçi sayısı'),
('stats_followers_label', 'Takipçi', 'homepage', 'stats', 'Takipçi etiketi'),
('stats_projects', '50+', 'homepage', 'stats', 'Proje sayısı'),
('stats_projects_label', 'Proje', 'homepage', 'stats', 'Proje etiketi'),
('stats_partners', '25+', 'homepage', 'stats', 'Ortak sayısı'),
('stats_partners_label', 'Ortak', 'homepage', 'stats', 'Ortak etiketi'),
('stats_experience', '5+', 'homepage', 'stats', 'Deneyim yılı'),
('stats_experience_label', 'Yıl Deneyim', 'homepage', 'stats', 'Deneyim etiketi'),
('testimonials_title', 'Müşteri Yorumları', 'homepage', 'testimonials', 'Testimoni başlık'),
('testimonials_subtitle', 'Memnun Müşterilerim', 'homepage', 'testimonials', 'Testimoni alt başlık'),

-- Navigasyon metinleri
('nav_home', 'Ana Sayfa', 'navigation', 'menu', 'Ana sayfa menü'),
('nav_about', 'Hakkımda', 'navigation', 'menu', 'Hakkımda menü'),
('nav_services', 'Hizmetler', 'navigation', 'menu', 'Hizmetler menü'),
('nav_portfolio', 'Portföy', 'navigation', 'menu', 'Portföy menü'),
('nav_gallery', 'Galeri', 'navigation', 'menu', 'Galeri menü'),
('nav_contact', 'İletişim', 'navigation', 'menu', 'İletişim menü'),

-- Hakkımda sayfa metinleri
('about_page_title', 'Hakkımda', 'about', 'header', 'Sayfa başlığı'),
('about_page_subtitle', 'Profesyonel Casino Yayıncısı', 'about', 'header', 'Sayfa alt başlığı'),
('about_story_title', 'Hikayem', 'about', 'story', 'Hikaye başlığı'),
('about_story_content', 'Casino sektöründe 5 yıllık tecrübem ile size en iyi hizmeti sunuyorum. Güvenilir ortaklıklar kurup kazancınızı artırmanız için buradayım.', 'about', 'story', 'Hikaye içeriği'),
('about_experience_title', 'Deneyimlerim', 'about', 'experience', 'Deneyim başlığı'),
('about_skills_title', 'Yeteneklerim', 'about', 'skills', 'Yetenek başlığı'),

-- Hizmetler sayfa metinleri
('services_page_title', 'Hizmetlerim', 'services', 'header', 'Sayfa başlığı'),
('services_page_subtitle', 'Profesyonel Casino Yayıncılığı Hizmetleri', 'services', 'header', 'Sayfa alt başlığı'),

-- Portföy sayfa metinleri
('portfolio_page_title', 'Portföyüm', 'portfolio', 'header', 'Sayfa başlığı'),
('portfolio_page_subtitle', 'Başarılı Projelerim', 'portfolio', 'header', 'Sayfa alt başlığı'),
('portfolio_filter_all', 'Tümü', 'portfolio', 'filter', 'Tümü filtresi'),

-- Galeri sayfa metinleri
('gallery_page_title', 'Galeri', 'gallery', 'header', 'Sayfa başlığı'),
('gallery_page_subtitle', 'Fotoğraf ve Video Galerim', 'gallery', 'header', 'Sayfa alt başlığı'),
('gallery_photos_title', 'Fotoğraflar', 'gallery', 'photos', 'Fotoğraf başlığı'),
('gallery_videos_title', 'Videolar', 'gallery', 'videos', 'Video başlığı'),

-- İletişim sayfa metinleri
('contact_page_title', 'İletişim', 'contact', 'header', 'Sayfa başlığı'),
('contact_page_subtitle', 'Benimle İletişime Geçin', 'contact', 'header', 'Sayfa alt başlığı'),
('contact_form_title', 'Mesaj Gönder', 'contact', 'form', 'Form başlığı'),
('contact_form_name', 'Adınız', 'contact', 'form', 'İsim alanı'),
('contact_form_email', 'Email', 'contact', 'form', 'Email alanı'),
('contact_form_subject', 'Konu', 'contact', 'form', 'Konu alanı'),
('contact_form_message', 'Mesajınız', 'contact', 'form', 'Mesaj alanı'),
('contact_form_send', 'Gönder', 'contact', 'form', 'Gönder butonu'),
('contact_info_title', 'İletişim Bilgileri', 'contact', 'info', 'İletişim bilgileri'),
('contact_success_message', 'Mesajınız başarıyla gönderildi!', 'contact', 'messages', 'Başarı mesajı'),
('contact_error_message', 'Mesaj gönderilirken hata oluştu!', 'contact', 'messages', 'Hata mesajı'),

-- Footer metinleri
('footer_about_title', 'BonusBoss', 'footer', 'about', 'Footer başlık'),
('footer_about_text', 'Profesyonel casino yayıncılığı hizmetleri ile kazancınızı artırın. Güvenilir ortaklıklar için doğru adres.', 'footer', 'about', 'Footer açıklama'),
('footer_links_title', 'Hızlı Linkler', 'footer', 'links', 'Linkler başlığı'),
('footer_contact_title', 'İletişim', 'footer', 'contact', 'İletişim başlığı'),
('footer_social_title', 'Sosyal Medya', 'footer', 'social', 'Sosyal medya başlığı'),
('footer_copyright', 'Tüm hakları saklıdır.', 'footer', 'copyright', 'Telif hakkı'),
('footer_powered_by', 'Yazılımcı: BERAT K', 'footer', 'powered', 'Yazılımcı bilgisi'),

-- Genel metinler
('btn_read_more', 'Devamını Oku', 'general', 'buttons', 'Devamını oku butonu'),
('btn_view_all', 'Tümünü Gör', 'general', 'buttons', 'Tümünü gör butonu'),
('btn_back', 'Geri', 'general', 'buttons', 'Geri butonu'),
('btn_next', 'İleri', 'general', 'buttons', 'İleri butonu'),
('btn_close', 'Kapat', 'general', 'buttons', 'Kapat butonu'),
('loading_text', 'Yükleniyor...', 'general', 'messages', 'Yükleme mesajı'),
('no_results', 'Sonuç bulunamadı', 'general', 'messages', 'Sonuç yok mesajı'),
('404_title', 'Sayfa Bulunamadı', 'general', 'errors', '404 başlığı'),
('404_message', 'Aradığınız sayfa bulunamadı.', 'general', 'errors', '404 mesajı');

-- --------------------------------------------------------

-- Tablo yapısı: categories
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `type` enum('service','portfolio','gallery') NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kategori örnekleri
INSERT INTO `categories` (`name`, `slug`, `description`, `type`, `sort_order`) VALUES
('Canlı Yayın', 'canli-yayin', 'Canlı yayın hizmetleri', 'service', 1),
('Sosyal Medya', 'sosyal-medya', 'Sosyal medya yönetimi', 'service', 2),
('Reklamcılık', 'reklamcilik', 'Reklam kampanyaları', 'service', 3),
('Casino Projeleri', 'casino-projeleri', 'Casino projelerim', 'portfolio', 1),
('Yayın Projeleri', 'yayin-projeleri', 'Yayın projelerim', 'portfolio', 2),
('Sosyal Medya Projeleri', 'sosyal-medya-projeleri', 'Sosyal medya projelerim', 'portfolio', 3),
('Etkinlik Fotoğrafları', 'etkinlik-fotograflari', 'Etkinlik fotoğrafları', 'gallery', 1),
('Yayın Fotoğrafları', 'yayin-fotograflari', 'Yayın fotoğrafları', 'gallery', 2),
('Videolar', 'videolar', 'Video galerim', 'gallery', 3);

-- --------------------------------------------------------

-- Tablo yapısı: services
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text,
  `full_description` text,
  `icon` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `price_text` varchar(100) DEFAULT NULL,
  `features` text,
  `category_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hizmet örnekleri
INSERT INTO `services` (`title`, `slug`, `short_description`, `full_description`, `icon`, `price_text`, `features`, `category_id`, `sort_order`) VALUES
('Canlı Casino Yayıncılığı', 'canli-casino-yayinciligi', 'Profesyonel canlı casino yayıncılığı hizmetleri', 'Deneyimli yayıncı kadromuz ile canlı casino yayınları gerçekleştiriyoruz. Yüksek kaliteli yayın ekipmanları ve profesyonel stüdyo ortamında hizmet veriyoruz.', 'fas fa-video', 'Paket başı fiyatlandırma', '["Profesyonel stüdyo ortamı", "HD kalitesinde yayın", "Deneyimli yayıncılar", "7/24 teknik destek"]', 1, 1),
('Sosyal Medya Yönetimi', 'sosyal-medya-yonetimi', 'Kapsamlı sosyal medya yönetimi hizmetleri', 'Sosyal medya hesaplarınızı profesyonel olarak yönetiyoruz. İçerik üretimi, reklam kampanyaları ve takipçi artırma hizmetleri sunuyoruz.', 'fas fa-hashtag', 'Aylık abonelik', '["İçerik üretimi", "Reklam kampanyaları", "Takipçi artırma", "Analiz raporları"]', 2, 2),
('Influencer Pazarlama', 'influencer-pazarlama', 'Etkili influencer pazarlama çözümleri', 'Geniş influencer ağımız ile markanızı doğru kitleye ulaştırıyoruz. Kampanya stratejisi ve uygulama süreçlerini yönetiyoruz.', 'fas fa-users', 'Kampanya başı', '["Geniş influencer ağı", "Kampanya stratejisi", "Performans takibi", "Detaylı raporlama"]', 3, 3),
('Meta Reklamları', 'meta-reklamlari', 'Facebook ve Instagram reklam yönetimi', 'Meta platformlarında etkili reklam kampanyaları yönetiyoruz. Hedef kitle analizi ve optimizasyon hizmetleri sunuyoruz.', 'fab fa-facebook', 'Reklam bütçesi + yönetim', '["Hedef kitle analizi", "Kampanya optimizasyonu", "A/B testleri", "ROI analizi"]', 3, 4),
('Telegram Grup Yönetimi', 'telegram-grup-yonetimi', 'Telegram gruplarının profesyonel yönetimi', 'Telegram gruplarınızı aktif tutuyoruz. Üye artırma, içerik paylaşımı ve grup yönetimi hizmetleri veriyoruz.', 'fab fa-telegram', 'Grup başı aylık', '["Üye artırma", "İçerik paylaşımı", "Grup moderasyonu", "Aktivite raporları"]', 2, 5),
('SMS/Email Kampanyaları', 'sms-email-kampanyalari', 'Toplu SMS ve email kampanyaları', 'Hedef kitlenize ulaşmak için SMS ve email kampanyaları düzenliyoruz. Yüksek açılım oranları ve etkili sonuçlar garantiliyoruz.', 'fas fa-envelope', 'Gönderim başı', '["Toplu SMS gönderimi", "Email kampanyaları", "Hedef kitle segmentasyonu", "Başarı raporları"]', 3, 6);

-- --------------------------------------------------------

-- Tablo yapısı: portfolio
CREATE TABLE `portfolio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text,
  `full_description` text,
  `client_name` varchar(255) DEFAULT NULL,
  `project_date` date DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery_images` text,
  `technologies` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `featured` enum('yes','no') DEFAULT 'no',
  `status` enum('active','inactive') DEFAULT 'active',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portfolio örnekleri
INSERT INTO `portfolio` (`title`, `slug`, `short_description`, `full_description`, `client_name`, `project_date`, `image`, `category_id`, `sort_order`, `featured`) VALUES
('Royal Casino Yayın Projesi', 'royal-casino-yayin-projesi', 'Lüks casino ortamında profesyonel yayın projesi', 'Royal Casino için gerçekleştirdiğimiz kapsamlı yayın projesi. Yüksek kaliteli stüdyo ortamında 24/7 canlı yayın hizmeti verdik.', 'Royal Casino', '2023-12-01', 'portfolio1.jpg', 4, 1, 'yes'),
('Sosyal Medya Büyüme Kampanyası', 'sosyal-medya-buyume-kampanyasi', 'Instagram ve TikTok büyüme stratejisi', 'Sosyal medya hesaplarında organik büyüme sağlayan kapsamlı kampanya. 6 ay içinde 100K+ takipçi artışı gerçekleştirdik.', 'Confidential', '2023-10-15', 'portfolio2.jpg', 6, 2, 'yes'),
('Mega Casino Influencer Kampanyası', 'mega-casino-influencer-kampanyasi', 'Geniş çaplı influencer pazarlama projesi', 'Mega Casino markası için 50+ influencer ile gerçekleştirilen büyük çaplı pazarlama kampanyası. Yüksek engagement oranları elde edildi.', 'Mega Casino', '2023-09-20', 'portfolio3.jpg', 6, 3, 'no'),
('Telegram Topluluk Yönetimi', 'telegram-topluluk-yonetimi', 'Aktif telegram topluluk yönetimi', '10K+ üyeli telegram grubunun profesyonel yönetimi. Günlük aktivite artışı ve üye memnuniyeti sağlandı.', 'Bet Community', '2023-08-10', 'portfolio4.jpg', 5, 4, 'no');

-- --------------------------------------------------------

-- Tablo yapısı: gallery_photos
CREATE TABLE `gallery_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Tablo yapısı: gallery_videos
CREATE TABLE `gallery_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `video_url` varchar(255) NOT NULL,
  `video_type` enum('youtube','vimeo','upload') DEFAULT 'youtube',
  `thumbnail` varchar(255) DEFAULT NULL,
  `duration` varchar(10) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Tablo yapısı: contact_messages
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `status` enum('unread','read','replied','spam') DEFAULT 'unread',
  `admin_reply` text,
  `replied_at` datetime DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `replied_by` (`replied_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Tablo yapısı: testimonials
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `testimonial` text NOT NULL,
  `rating` int(1) DEFAULT 5,
  `avatar` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonial örnekleri
INSERT INTO `testimonials` (`name`, `position`, `company`, `testimonial`, `rating`, `sort_order`) VALUES
('Ahmet Yılmaz', 'Pazarlama Müdürü', 'Royal Casino', 'BonusBoss ile çalışmak harika bir deneyimdi. Profesyonel yaklaşımları ve kaliteli hizmetleri sayesinde hedeflerimize ulaştık.', 5, 1),
('Mehmet Kaya', 'İçerik Üreticisi', 'Mega Bet', 'Sosyal medya yönetimi konusunda gerçekten uzmanlar. Hesaplarımız kısa sürede büyüdü ve etkileşimlerimiz arttı.', 5, 2),
('Fatma Demir', 'Pazarlama Uzmanı', 'Bet Community', 'Telegram grup yönetimi hizmetleri mükemmel. Topluluk çok aktif hale geldi ve üye sayımız katlandı.', 5, 3);

-- --------------------------------------------------------

-- Foreign key constraints
ALTER TABLE `services` ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
ALTER TABLE `portfolio` ADD CONSTRAINT `portfolio_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
ALTER TABLE `gallery_photos` ADD CONSTRAINT `gallery_photos_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
ALTER TABLE `gallery_videos` ADD CONSTRAINT `gallery_videos_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
ALTER TABLE `contact_messages` ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

COMMIT;

-- Tablo Auto Increment ayarları
ALTER TABLE `users` AUTO_INCREMENT = 2;
ALTER TABLE `settings` AUTO_INCREMENT = 1;
ALTER TABLE `site_texts` AUTO_INCREMENT = 1;
ALTER TABLE `categories` AUTO_INCREMENT = 1;
ALTER TABLE `services` AUTO_INCREMENT = 1;
ALTER TABLE `portfolio` AUTO_INCREMENT = 1;
ALTER TABLE `gallery_photos` AUTO_INCREMENT = 1;
ALTER TABLE `gallery_videos` AUTO_INCREMENT = 1;
ALTER TABLE `contact_messages` AUTO_INCREMENT = 1;
ALTER TABLE `testimonials` AUTO_INCREMENT = 1;

-- Database created by BERAT K
-- BonusBoss Portfolio Website Database
-- Version: 1.0
-- Date: 2024