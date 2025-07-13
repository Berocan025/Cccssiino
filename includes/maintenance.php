<?php
/**
 * BonusBoss Portfolio Website - Maintenance Mode
 * Yazılımcı: BERAT K
 * Version: 1.0
 * Date: 2024
 */

// Doğrudan erişimi engelle
if (!defined('DB_HOST')) {
    die('Direct access not allowed');
}

// Maintenance mode sayfası
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Bakımda - <?php echo escape_output(get_setting('site_title', 'BonusBoss')); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .maintenance-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            width: 100%;
        }
        
        .maintenance-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .maintenance-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .maintenance-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .contact-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            margin-top: 2rem;
        }
        
        .contact-info h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .contact-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .contact-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .contact-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .loading-dots {
            display: inline-block;
            margin-left: 0.5rem;
        }
        
        .loading-dots span {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            margin: 0 2px;
            animation: loading 1.4s infinite ease-in-out;
        }
        
        .loading-dots span:nth-child(1) { animation-delay: -0.32s; }
        .loading-dots span:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes loading {
            0%, 80%, 100% { opacity: 0.3; }
            40% { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .maintenance-title {
                font-size: 2rem;
            }
            
            .maintenance-message {
                font-size: 1rem;
            }
            
            .contact-links {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            🔧
        </div>
        
        <h1 class="maintenance-title">
            Site Bakımda
            <span class="loading-dots">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </h1>
        
        <p class="maintenance-message">
            <?php echo escape_output(get_setting('maintenance_message', 'Sitemiz şu anda bakım çalışması nedeniyle geçici olarak erişime kapalıdır. En kısa sürede tekrar hizmetinizdeyiz.')); ?>
        </p>
        
        <div class="contact-info">
            <h3>Acil Durumlar İçin</h3>
            <div class="contact-links">
                <?php if (get_setting('site_email')): ?>
                <a href="mailto:<?php echo escape_output(get_setting('site_email')); ?>" class="contact-link">
                    📧 E-posta
                </a>
                <?php endif; ?>
                
                <?php if (get_setting('site_phone')): ?>
                <a href="tel:<?php echo escape_output(get_setting('site_phone')); ?>" class="contact-link">
                    📞 Telefon
                </a>
                <?php endif; ?>
                
                <?php if (get_setting('social_telegram')): ?>
                <a href="<?php echo escape_output(get_setting('social_telegram')); ?>" target="_blank" rel="noopener noreferrer" class="contact-link">
                    📱 Telegram
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>