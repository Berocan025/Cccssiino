<?php
require_once '../includes/config.php';

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    redirect('dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, status FROM users WHERE username = ? AND role = 'admin'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'active') {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    // Son giriş zamanını güncelle
                    $stmt = $pdo->prepare("UPDATE users SET last_login = datetime('now') WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Remember me özelliği
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/admin/'); // 30 gün
                        
                        // Token'ı veritabanına kaydet (remember_tokens tablosu gerekir)
                    }
                    
                    write_log("Admin login: " . $user['username'], 'info');
                    redirect('dashboard.php');
                    exit();
                } else {
                    $error = 'Hesabınız aktif değil.';
                }
            } else {
                $error = 'Kullanıcı adı veya şifre hatalı.';
            }
        } catch (PDOException $e) {
            $error = 'Veritabanı hatası oluştu.';
            write_log("Login error: " . $e->getMessage(), 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Giriş - BonusBoss</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gold: #ffd700;
            --primary-blue: #0099ff;
            --dark-bg: #1a1a1a;
            --card-bg: rgba(30, 30, 30, 0.9);
        }

        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" style="stop-color:%23ffd700;stop-opacity:0.1"/><stop offset="100%" style="stop-color:%23ffd700;stop-opacity:0"/></radialGradient></defs><circle cx="200" cy="200" r="150" fill="url(%23a)"><animate attributeName="cx" values="200;800;200" dur="20s" repeatCount="indefinite"/></circle><circle cx="800" cy="800" r="200" fill="url(%23a)"><animate attributeName="cy" values="800;200;800" dur="25s" repeatCount="indefinite"/></circle><circle cx="500" cy="500" r="100" fill="url(%23a)"><animate attributeName="r" values="100;300;100" dur="15s" repeatCount="indefinite"/></circle></svg>');
            background-size: cover;
            z-index: -1;
            animation: backgroundShift 30s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(5deg); }
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 20px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.5),
                0 0 80px rgba(255, 215, 0, 0.1);
            width: 100%;
            max-width: 450px;
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-gold) 0%, var(--primary-blue) 100%);
            animation: shimmer 2s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-section {
            text-align: center;
            padding: 3rem 2rem 2rem;
            position: relative;
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-gold) 0%, #ffed4e 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: #000;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-gold) 0%, #ffed4e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .brand-subtitle {
            color: #adb5bd;
            font-size: 1rem;
            margin-bottom: 0;
        }

        .form-section {
            padding: 0 2rem 3rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            color: #fff;
            padding: 1rem;
            height: auto;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-gold);
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
            color: #fff;
        }

        .form-control::placeholder {
            color: #6c757d;
        }

        .form-label {
            color: #adb5bd;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: var(--primary-gold);
            padding-left: 1rem;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-gold) 0%, #ffed4e 100%);
            border: none;
            color: #000;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 12px;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #ffed4e 0%, var(--primary-gold) 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
            color: #000;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .form-check {
            margin: 1.5rem 0;
        }

        .form-check-input:checked {
            background-color: var(--primary-gold);
            border-color: var(--primary-gold);
        }

        .form-check-label {
            color: #adb5bd;
            font-size: 0.9rem;
        }

        .alert {
            border: none;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            padding: 1rem 1.25rem;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .footer-links {
            text-align: center;
            padding: 1rem 2rem 2rem;
            border-top: 1px solid rgba(255, 215, 0, 0.1);
            margin-top: 2rem;
        }

        .footer-links a {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-gold);
        }

        /* Loading Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid #000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn-login.loading .spinner {
            display: inline-block;
        }

        .btn-login.loading .btn-text {
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 1rem;
            }
            
            .brand-section {
                padding: 2rem 1.5rem 1.5rem;
            }
            
            .form-section {
                padding: 0 1.5rem 2rem;
            }
            
            .brand-title {
                font-size: 2rem;
            }
        }

        /* Security Badge */
        .security-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        /* Particles effect */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 215, 0, 0.6);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) translateX(0px); opacity: 0; }
            50% { transform: translateY(-20px) translateX(10px); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="security-badge">
                <i class="fas fa-shield-alt me-1"></i>
                Güvenli Giriş
            </div>
            
            <div class="brand-section">
                <div class="brand-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h1 class="brand-title">BonusBoss</h1>
                <p class="brand-subtitle">Admin Panel</p>
            </div>

            <div class="form-section">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Kullanıcı Adı" required 
                               value="<?php echo escape_output($_POST['username'] ?? ''); ?>">
                        <label for="username">
                            <i class="fas fa-user me-2"></i>Kullanıcı Adı
                        </label>
                    </div>

                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Şifre" required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Şifre
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Beni hatırla
                        </label>
                    </div>

                    <button type="submit" class="btn btn-login" id="loginBtn">
                        <span class="spinner"></span>
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Giriş Yap
                        </span>
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Varsayılan: <code>admin</code> / <code>admin123</code>
                    </p>
                </div>
            </div>

            <div class="footer-links">
                <a href="../index.php" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i>
                    Siteyi Görüntüle
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });

        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 20;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (4 + Math.random() * 4) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Initialize particles
        createParticles();

        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });

        // Show/hide password functionality
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'h') {
                e.preventDefault();
                const passwordField = document.getElementById('password');
                passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
            }
        });

        // Prevent multiple form submissions
        let formSubmitted = false;
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (formSubmitted) {
                e.preventDefault();
                return;
            }
            formSubmitted = true;
        });
    </script>
</body>
</html>