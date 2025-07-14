-- BonusBoss Database Structure for SQLite
-- Yazılımcı: BERAT K
-- Tarih: 2024

PRAGMA foreign_keys = ON;

-- --------------------------------------------------------

-- Tablo yapısı: users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` TEXT NOT NULL UNIQUE,
  `email` TEXT NOT NULL UNIQUE,
  `password` TEXT NOT NULL,
  `full_name` TEXT NOT NULL,
  `avatar` TEXT DEFAULT NULL,
  `role` TEXT DEFAULT 'admin' CHECK(role IN ('admin','editor')),
  `status` TEXT DEFAULT 'active' CHECK(status IN ('active','inactive')),
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Varsayılan admin kullanıcı ekleme (şifre: admin123)
INSERT OR IGNORE INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@bonusboss.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BonusBoss Admin', 'admin');

-- --------------------------------------------------------

-- Tablo yapısı: settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `setting_key` TEXT NOT NULL UNIQUE,
  `setting_value` TEXT,
  `setting_type` TEXT DEFAULT 'text',
  `category` TEXT DEFAULT 'general',
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Varsayılan ayarlar
INSERT OR IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
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
CREATE TABLE IF NOT EXISTS `site_texts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `text_key` TEXT NOT NULL UNIQUE,
  `text_value` TEXT NOT NULL,
  `page_name` TEXT NOT NULL,
  `section` TEXT NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Site metinleri
INSERT OR IGNORE INTO `site_texts` (`text_key`, `text_value`, `page_name`, `section`, `description`) VALUES
-- Ana sayfa metinleri
('hero_title', 'BonusBoss', 'homepage', 'hero', 'Ana başlık'),
('hero_subtitle', 'Profesyonel Casino Yayıncısı', 'homepage', 'hero', 'Alt başlık'),
('hero_description', 'Kazançlı ortaklıklar için doğru adres. Profesyonel casino yayıncılığı hizmetleri ile kazancınızı artırın.', 'homepage', 'hero', 'Ana açıklama'),
('hero_button_services', 'Hizmetlerim', 'homepage', 'hero', 'Hizmetler butonu'),
('hero_button_contact', 'İletişim', 'homepage', 'hero', 'İletişim butonu'),
('about_title', 'Hakkımda', 'homepage', 'about', 'Hakkımda başlık'),
('about_subtitle', 'Profesyonel Casino Yayıncısı', 'homepage', 'about', 'Hakkımda alt başlık'),
('about_description', 'Yıllardır casino sektöründe profesyonel yayıncılık hizmetleri veriyoruz. Deneyimli kadromuz ve kaliteli hizmet anlayışımızla müşterilerimizin hedeflerine ulaşmalarını sağlıyoruz.', 'homepage', 'about', 'Hakkımda açıklama'),
('services_title', 'Hizmetlerim', 'homepage', 'services', 'Hizmetler başlık'),
('services_subtitle', 'Profesyonel Çözümler', 'homepage', 'services', 'Hizmetler alt başlık'),
('portfolio_title', 'Portföyüm', 'homepage', 'portfolio', 'Portföy başlık'),
('portfolio_subtitle', 'Başarılı Projeler', 'homepage', 'portfolio', 'Portföy alt başlık'),
('testimonials_title', 'Müşteri Yorumları', 'homepage', 'testimonials', 'Yorumlar başlık'),
('testimonials_subtitle', 'Memnun Müşteriler', 'homepage', 'testimonials', 'Yorumlar alt başlık'),
('contact_title', 'İletişim', 'homepage', 'contact', 'İletişim başlık'),
('contact_subtitle', 'Bize Ulaşın', 'homepage', 'contact', 'İletişim alt başlık'),
-- Footer metinleri
('footer_about', 'BonusBoss olarak casino sektöründe profesyonel hizmetler sunuyoruz.', 'general', 'footer', 'Footer hakkımda'),
('footer_copyright', '© 2024 BonusBoss. Tüm hakları saklıdır.', 'general', 'footer', 'Copyright'),
-- Diğer sayfalar
('services_page_title', 'Hizmetlerimiz', 'services', 'header', 'Hizmetler sayfa başlığı'),
('portfolio_page_title', 'Portföyümüz', 'portfolio', 'header', 'Portföy sayfa başlığı'),
('gallery_page_title', 'Galeri', 'gallery', 'header', 'Galeri sayfa başlığı'),
('contact_page_title', 'İletişim', 'contact', 'header', 'İletişim sayfa başlığı'),
('about_page_title', 'Hakkımızda', 'about', 'header', 'Hakkımızda sayfa başlığı'),
-- Hata mesajları
('404_title', 'Sayfa Bulunamadı', 'general', 'errors', '404 başlık'),
('404_message', 'Aradığınız sayfa bulunamadı.', 'general', 'errors', '404 mesajı');

-- --------------------------------------------------------

-- Tablo yapısı: categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `slug` TEXT NOT NULL UNIQUE,
  `description` TEXT,
  `type` TEXT NOT NULL CHECK(type IN ('service','portfolio','gallery')),
  `image` TEXT DEFAULT NULL,
  `sort_order` INTEGER DEFAULT 0,
  `status` TEXT DEFAULT 'active' CHECK(status IN ('active','inactive')),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Kategori örnekleri
INSERT OR IGNORE INTO `categories` (`name`, `slug`, `description`, `type`, `sort_order`) VALUES
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
CREATE TABLE IF NOT EXISTS `services` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL,
  `slug` TEXT NOT NULL UNIQUE,
  `short_description` TEXT,
  `full_description` TEXT,
  `icon` TEXT DEFAULT NULL,
  `image` TEXT DEFAULT NULL,
  `price` REAL DEFAULT NULL,
  `price_text` TEXT DEFAULT NULL,
  `features` TEXT,
  `category_id` INTEGER DEFAULT NULL,
  `sort_order` INTEGER DEFAULT 0,
  `status` TEXT DEFAULT 'active' CHECK(status IN ('active','inactive')),
  `seo_title` TEXT DEFAULT NULL,
  `seo_description` TEXT DEFAULT NULL,
  `seo_keywords` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
);

-- Hizmet örnekleri
INSERT OR IGNORE INTO `services` (`title`, `slug`, `short_description`, `full_description`, `icon`, `price_text`, `features`, `category_id`, `sort_order`) VALUES
('Canlı Casino Yayıncılığı', 'canli-casino-yayinciligi', 'Profesyonel canlı casino yayıncılığı hizmetleri', 'Deneyimli yayıncı kadromuz ile canlı casino yayınları gerçekleştiriyoruz. Yüksek kaliteli yayın ekipmanları ve profesyonel stüdyo ortamında hizmet veriyoruz.', 'fas fa-video', 'Paket başı fiyatlandırma', '["Profesyonel stüdyo ortamı", "HD kalitesinde yayın", "Deneyimli yayıncılar", "7/24 teknik destek"]', 1, 1),
('Sosyal Medya Yönetimi', 'sosyal-medya-yonetimi', 'Kapsamlı sosyal medya yönetimi hizmetleri', 'Sosyal medya hesaplarınızı profesyonel olarak yönetiyoruz. İçerik üretimi, reklam kampanyaları ve takipçi artırma hizmetleri sunuyoruz.', 'fas fa-hashtag', 'Aylık abonelik', '["İçerik üretimi", "Reklam kampanyaları", "Takipçi artırma", "Analiz raporları"]', 2, 2),
('Influencer Pazarlama', 'influencer-pazarlama', 'Etkili influencer pazarlama çözümleri', 'Geniş influencer ağımız ile markanızı doğru kitleye ulaştırıyoruz. Kampanya stratejisi ve uygulama süreçlerini yönetiyoruz.', 'fas fa-users', 'Kampanya başı', '["Geniş influencer ağı", "Kampanya stratejisi", "Performans takibi", "Detaylı raporlama"]', 3, 3),
('Meta Reklamları', 'meta-reklamlari', 'Facebook ve Instagram reklam yönetimi', 'Meta platformlarında etkili reklam kampanyaları yönetiyoruz. Hedef kitle analizi ve optimizasyon hizmetleri sunuyoruz.', 'fab fa-facebook', 'Reklam bütçesi + yönetim', '["Hedef kitle analizi", "Kampanya optimizasyonu", "A/B testleri", "ROI analizi"]', 3, 4),
('Telegram Grup Yönetimi', 'telegram-grup-yonetimi', 'Telegram gruplarının profesyonel yönetimi', 'Telegram gruplarınızı aktif tutuyoruz. Üye artırma, içerik paylaşımı ve grup yönetimi hizmetleri veriyoruz.', 'fab fa-telegram', 'Grup başı aylık', '["Üye artırma", "İçerik paylaşımı", "Grup moderasyonu", "Aktivite raporları"]', 2, 5),
('SMS/Email Kampanyaları', 'sms-email-kampanyalari', 'Toplu SMS ve email kampanyaları', 'Hedef kitlenize ulaşmak için SMS ve email kampanyaları düzenliyoruz. Yüksek açılım oranları ve etkili sonuçlar garantiliyoruz.', 'fas fa-envelope', 'Gönderim başı', '["Toplu SMS gönderimi", "Email kampanyaları", "Hedef kitle segmentasyonu", "Başarı raporları"]', 3, 6);

-- --------------------------------------------------------

-- Tablo yapısı: portfolio
CREATE TABLE IF NOT EXISTS `portfolio` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL,
  `slug` TEXT NOT NULL UNIQUE,
  `short_description` TEXT,
  `full_description` TEXT,
  `client_name` TEXT DEFAULT NULL,
  `project_date` DATE DEFAULT NULL,
  `project_url` TEXT DEFAULT NULL,
  `image` TEXT DEFAULT NULL,
  `gallery_images` TEXT,
  `technologies` TEXT DEFAULT NULL,
  `category_id` INTEGER DEFAULT NULL,
  `sort_order` INTEGER DEFAULT 0,
  `featured` TEXT DEFAULT 'no' CHECK(featured IN ('yes','no')),
  `status` TEXT DEFAULT 'active' CHECK(status IN ('active','inactive')),
  `seo_title` TEXT DEFAULT NULL,
  `seo_description` TEXT DEFAULT NULL,
  `seo_keywords` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
);

-- Portfolio örnekleri
INSERT OR IGNORE INTO `portfolio` (`title`, `slug`, `short_description`, `full_description`, `client_name`, `project_date`, `image`, `category_id`, `sort_order`, `featured`) VALUES
('Royal Casino Yayın Projesi', 'royal-casino-yayin-projesi', 'Lüks casino ortamında profesyonel yayın projesi', 'Royal Casino için gerçekleştirdiğimiz kapsamlı yayın projesi. Yüksek kaliteli stüdyo ortamında 24/7 canlı yayın hizmeti verdik.', 'Royal Casino', '2023-12-01', 'portfolio1.jpg', 4, 1, 'yes'),
('Sosyal Medya Büyüme Kampanyası', 'sosyal-medya-buyume-kampanyasi', 'Instagram ve TikTok büyüme stratejisi', 'Sosyal medya hesaplarında organik büyüme sağlayan kapsamlı kampanya. 6 ay içinde 100K+ takipçi artışı gerçekleştirdik.', 'Confidential', '2023-10-15', 'portfolio2.jpg', 6, 2, 'yes'),
('Mega Casino Influencer Kampanyası', 'mega-casino-influencer-kampanyasi', 'Geniş çaplı influencer pazarlama projesi', 'Mega Casino markası için 50+ influencer ile gerçekleştirilen büyük çaplı pazarlama kampanyası. Yüksek engagement oranları elde edildi.', 'Mega Casino', '2023-09-20', 'portfolio3.jpg', 6, 3, 'no'),
('Telegram Topluluk Yönetimi', 'telegram-topluluk-yonetimi', 'Aktif telegram topluluk yönetimi', '10K+ üyeli telegram grubunun profesyonel yönetimi. Günlük aktivite artışı ve üye memnuniyeti sağlandı.', 'Bet Community', '2023-08-10', 'portfolio4.jpg', 5, 4, 'no');

-- --------------------------------------------------------

-- Tablo yapısı: gallery_photos
CREATE TABLE IF NOT EXISTS `gallery_photos` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL,
  `description` TEXT,
  `image` TEXT NOT NULL,
  `thumbnail` TEXT DEFAULT NULL,
  `alt_text` TEXT DEFAULT NULL,
  `category_id` INTEGER DEFAULT NULL,
  `sort_order` INTEGER DEFAULT 0,
  `status` TEXT DEFAULT 'active' CHECK(status IN ('active','inactive')),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
);

-- --------------------------------------------------------

-- Tablo yapısı: gallery_videos
CREATE TABLE IF NOT EXISTS `gallery_videos` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL,
  `description` TEXT,
  `video_url` TEXT NOT NULL,
  `video_type` TEXT DEFAULT 'youtube' CHECK(video_type IN ('youtube','vimeo','upload')),
  `thumbnail` TEXT DEFAULT NULL,
  `duration` TEXT DEFAULT NULL,
  `category_id` INTEGER DEFAULT NULL,
  `sort_order` INTEGER DEFAULT 0,
  `status` TEXT DEFAULT 'active' CHECK(status IN ('active','inactive')),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
);

-- --------------------------------------------------------

-- Tablo yapısı: contact_messages
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `email` TEXT NOT NULL,
  `phone` TEXT DEFAULT NULL,
  `subject` TEXT NOT NULL,
  `message` TEXT NOT NULL,
  `ip_address` TEXT DEFAULT NULL,
  `user_agent` TEXT,
  `status` TEXT DEFAULT 'unread' CHECK(status IN ('unread','read','replied','spam')),
  `admin_reply` TEXT,
  `replied_at` DATETIME DEFAULT NULL,
  `replied_by` INTEGER DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
);

-- --------------------------------------------------------

-- Tablo yapısı: testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `position` TEXT DEFAULT NULL,
  `company` TEXT DEFAULT NULL,
  `testimonial` TEXT NOT NULL,
  `rating` INTEGER DEFAULT 5,
  `avatar` TEXT DEFAULT NULL,
  `sort_order` INTEGER DEFAULT 0,
  `status` TEXT DEFAULT 'active' CHECK(status IN ('active','inactive')),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Testimonial örnekleri
INSERT OR IGNORE INTO `testimonials` (`name`, `position`, `company`, `testimonial`, `rating`, `sort_order`) VALUES
('Ahmet Yılmaz', 'Pazarlama Müdürü', 'Royal Casino', 'BonusBoss ile çalışmak harika bir deneyimdi. Profesyonel yaklaşımları ve kaliteli hizmetleri sayesinde hedeflerimize ulaştık.', 5, 1),
('Mehmet Kaya', 'İçerik Üreticisi', 'Mega Bet', 'Sosyal medya yönetimi konusunda gerçekten uzmanlar. Hesaplarımız kısa sürede büyüdü ve etkileşimlerimiz arttı.', 5, 2),
('Fatma Demir', 'Pazarlama Uzmanı', 'Bet Community', 'Telegram grup yönetimi hizmetleri mükemmel. Topluluk çok aktif hale geldi ve üye sayımız katlandı.', 5, 3);

-- --------------------------------------------------------

-- Trigger oluşturma (updated_at için)
CREATE TRIGGER update_users_updated_at AFTER UPDATE ON users
BEGIN
  UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_settings_updated_at AFTER UPDATE ON settings
BEGIN
  UPDATE settings SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_site_texts_updated_at AFTER UPDATE ON site_texts
BEGIN
  UPDATE site_texts SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_categories_updated_at AFTER UPDATE ON categories
BEGIN
  UPDATE categories SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_services_updated_at AFTER UPDATE ON services
BEGIN
  UPDATE services SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_portfolio_updated_at AFTER UPDATE ON portfolio
BEGIN
  UPDATE portfolio SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_gallery_photos_updated_at AFTER UPDATE ON gallery_photos
BEGIN
  UPDATE gallery_photos SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_gallery_videos_updated_at AFTER UPDATE ON gallery_videos
BEGIN
  UPDATE gallery_videos SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_contact_messages_updated_at AFTER UPDATE ON contact_messages
BEGIN
  UPDATE contact_messages SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER update_testimonials_updated_at AFTER UPDATE ON testimonials
BEGIN
  UPDATE testimonials SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- Database created by BERAT K
-- BonusBoss Portfolio Website Database - SQLite Version
-- Version: 1.0
-- Date: 2024