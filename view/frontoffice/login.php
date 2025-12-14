<?php
// Activer l'affichage des erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../backoffice/index.php');
    } else {
        header('Location: meme.php');
    }
    exit();
}

// Chemin absolu vers le contrôleur
$controller_path = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
if (file_exists($controller_path)) {
    require_once $controller_path;
} else {
    // Fallback au chemin relatif
    require_once '../../controller/AuthController.php';
}

$authController = new AuthController();
$error = '';

// ⭐⭐⭐ CLÉS reCAPTCHA - À MODIFIER AVEC LES VÔTRES ⭐⭐⭐
$recaptcha_site_key = "6LeXWCgsAAAAANEGd1QzF3TFKjqWWGrIOyLYPkfa"; // VOTRE CLÉ SITE ICI
$recaptcha_secret_key = "6LeXWCgsAAAAALngdk9wHBfBBZCogaCNHtqNXzuO"; // VOTRE CLÉ SECRÈTE ICI

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // ⭐⭐⭐ VÉRIFICATION reCAPTCHA ⭐⭐⭐
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    if (empty($recaptcha_response)) {
        $error = "Veuillez valider le CAPTCHA.";
    } else {
        // Vérifier avec Google
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_data = [
            'secret' => $recaptcha_secret_key,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $recaptcha_options = [
            'http' => [
                'method' => 'POST',
                'content' => http_build_query($recaptcha_data),
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
            ]
        ];
        
        $recaptcha_context = stream_context_create($recaptcha_options);
        $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
        $recaptcha_json = json_decode($recaptcha_result);
        
        if (!$recaptcha_json->success) {
            $error = "Échec de la vérification CAPTCHA. Veuillez réessayer.";
        } else {
            // CAPTCHA réussi, procéder à la connexion
            $result = $authController->login($email, $password);
            
            if ($result === true) {
                // La redirection est gérée par AuthController
                exit();
            } else {
                $error = $result;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SafeSpace</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- ⭐⭐⭐ SCRIPT reCAPTCHA ⭐⭐⭐ -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <style>
        /* Styles spécifiques pour la page de login */
        .recaptcha-container {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            text-align: center;
        }
        
        .recaptcha-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .recaptcha-info i {
            color: #4e73df;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .admin-only {
            background: #4e73df;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        .windows-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid #e3e6f0;
            text-align: center;
        }
        
        .windows-icon {
            font-size: 2.5rem;
            color: #0078d4;
            margin-bottom: 15px;
        }
        
        .biometric-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-windows {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            text-decoration: none;
        }
        
        .btn-windows:hover {
            background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.3);
            color: white;
        }
        
        .security-tips {
            margin-top: 30px;
            padding: 20px;
            background: #f0f7ff;
            border-radius: 8px;
            border-left: 4px solid #4e73df;
        }
        
        .security-tips h4 {
            color: #224abe !important;
            margin-bottom: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .security-tips ul {
            color: #5a5c69 !important;
            font-size: 0.9rem;
            line-height: 1.6;
            padding-left: 20px;
        }
        
        .security-tips li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .security-tips i {
            color: #4e73df;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e3e6f0;
        }
        
        .links a {
            color: #4e73df;
            text-decoration: none;
            margin: 0 10px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .links a:hover {
            text-decoration: underline;
            color: #224abe;
        }
        
        .divider {
            height: 1px;
            background: #e3e6f0;
            margin: 25px 0;
            position: relative;
        }
        
        .divider-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 15px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .password-field {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .windows-section {
                padding: 15px;
            }
            
            .btn-windows {
                width: 100%;
                justify-content: center;
            }
            
            .links {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .links a {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body class="is-preload">

<div id="page-wrapper">

    <header id="header">
        <h1><a href="index.php">SafeSpace</a></h1>
        <nav>
            <a href="index.php">Accueil</a> |
            <a href="login.php">Connexion</a> |
            <a href="register.php">Inscription</a>
        </nav>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Connexion</h2>
                <p>Accédez à votre compte SafeSpace</p>
            </div>
        </header>

        <!-- Content -->
        <div class="wrapper">
            <div class="inner">

                <?php if (isset($_GET['registered']) && $_GET['registered'] == '1'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Inscription réussie ! Votre compte est en attente d'approbation.
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="" id="loginForm">
                    <div class="fields">
                        <div class="field">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Adresse email
                            </label>
                            <input type="email" name="email" id="email" 
                                   placeholder="exemple@safespace.com" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required />
                        </div>
                        
                        <div class="field">
                            <label for="password">
                                <i class="fas fa-lock"></i> Mot de passe
                            </label>
                            <div class="password-field">
                                <input type="password" name="password" id="password" 
                                       placeholder="Votre mot de passe" 
                                       required />
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Section reCAPTCHA -->
                        <div class="field">
                            <div class="recaptcha-container">
                                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptcha_site_key) ?>"></div>
                                <div class="recaptcha-info">
                                    <i class="fas fa-robot"></i> Cette vérification permet de protéger votre compte contre les robots.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <ul class="actions">
                        <li>
                            <input type="submit" value="Se connecter" class="primary" />
                        </li>
                        <li>
                            <a href="register.php" class="button">
                                <i class="fas fa-user-plus"></i> Créer un compte
                            </a>
                        </li>
                    </ul>
                </form>
                
                <!-- Section Windows Hello -->
                <div class="windows-section">
                    <div class="windows-icon">
                        <i class="fab fa-windows"></i>
                    </div>
                    <h3 style="color: #005a9e; margin-bottom: 15px; font-size: 1.2rem;">
                        Connexion Admin sécurisée
                        <span class="admin-only">Admin only</span>
                    </h3>
                    <p style="color: #5a5c69; margin-bottom: 15px; font-size: 0.95rem;">
                        Windows Hello réservé à l'administrateur pour une authentification biométrique.
                    </p>
                    
                    <a href="fingerprint-login.php" class="btn-windows">
                        <i class="fas fa-fingerprint"></i> Windows Hello (Admin)
                    </a>
                    
                    <div class="biometric-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Réservé à l'administrateur • Empreinte • Reconnaissance faciale • PIN</span>
                    </div>
                </div>
                
                <!-- Liens utiles -->
                <div class="links">
                    <a href="forgot_password.php">
                        <i class="fas fa-key"></i> Mot de passe oublié ?
                    </a>
                    <span style="color: #e3e6f0">|</span>
                    <a href="index.php">
                        <i class="fas fa-home"></i> Retour à l'accueil
                    </a>
                </div>
                
              

            </div>
        </div>
    </section>

    <!-- Footer identique à register.php -->
    <section id="footer">
        <div class="inner">
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
            <p style="font-size: 0.85rem; margin-top: 10px; color: #6c757d;">
                <i class="fas fa-shield-alt"></i> Protégé par Google reCAPTCHA
            </p>
        </div>
    </section>

</div>

<!-- Scripts identiques à register.php -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/register.js"></script>

<script src="assets/js/script_post.js"></script>


<script>
// Afficher/masquer le mot de passe
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Validation du CAPTCHA avant soumission
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const recaptchaResponse = document.querySelector('[name="g-recaptcha-response"]');
            if (!recaptchaResponse || recaptchaResponse.value === '') {
                e.preventDefault();
                alert('Veuillez valider le CAPTCHA avant de continuer.');
                return false;
            }
        });
    }
    
    // Focus sur l'email
    document.getElementById('email').focus();
});

// Animation de chargement pour le bouton de soumission
document.getElementById('loginForm')?.addEventListener('submit', function() {
    const submitBtn = this.querySelector('input[type="submit"]');
    if (submitBtn) {
        submitBtn.value = 'Connexion en cours...';
        submitBtn.disabled = true;
    }
});
</script>

</body>
</html>