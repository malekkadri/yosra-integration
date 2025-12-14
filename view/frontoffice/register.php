<?php
session_start();

// Inclure le contrôleur d'authentification avec un chemin fiable
$controller_path = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
if (file_exists($controller_path)) {
    require_once $controller_path;
} else {
    require_once __DIR__ . '/../../controller/AuthController.php';
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role'] ?? 'membre');

    // Validation des champs
    if (empty($firstname) || empty($lastname)) {
        $errors[] = "Nom et prénom requis.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email valide requis.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Mot de passe requis (minimum 6 caractères).";
    }
    
    // Validation du rôle
    $allowedRoles = ['membre', 'conseilleur'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'membre'; // Valeur par défaut sécurisée
    }

    if (empty($errors)) {
        $authController = new AuthController();
        
        $fullname = trim($firstname . ' ' . $lastname);
        $result = $authController->register($fullname, $email, $password, $role);
        
        if ($result === true) {
            $_SESSION['success'] = "Inscription réussie ! Votre compte est en attente de validation.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - SafeSpace</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Styles pour le générateur IA - ADAPTÉ À VOTRE STYLE */
        .ai-password-section {
            background: #f8f9fc;
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            padding: 0;
            margin: 25px 0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .ai-header {
            background: #4e73df;
            color: white;
            padding: 18px 25px;
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .ai-header i {
            font-size: 1.3rem;
        }
        
        .ai-badge {
            background: #1cc88a;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .ai-content {
            padding: 25px;
        }
        
        /* Style pour l'affichage du mot de passe généré */
        .password-display-box {
            background: white;
            border: 2px solid #d1d3e2;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
            font-size: 1.3rem;
            text-align: center;
            letter-spacing: 1px;
            position: relative;
            color: #5a5c69 !important;
            font-weight: 600;
        }
        
        .copy-btn {
            position: absolute;
            right: 15px;
            top: 15px;
            background: #4e73df;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .copy-btn:hover {
            background: #2e59d9;
            transform: translateY(-2px);
        }
        
        /* Barre de force du mot de passe */
        .strength-container {
            margin: 20px 0;
        }
        
        .strength-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #5a5c69;
            font-size: 0.9rem;
        }
        
        .strength-bar {
            height: 10px;
            background: #eaecf4;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.5s ease;
        }
        
        .strength-weak { background: #e74a3b; }
        .strength-medium { background: #f6c23e; }
        .strength-good { background: #1cc88a; }
        .strength-strong { background: #36b9cc; }
        .strength-very-strong { background: #4e73df; }
        
        /* Options de génération */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        
        .option-group {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
        }
        
        .option-group h4 {
            color: #5a5c69 !important;
            margin-bottom: 15px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        .option-group h4 i {
            color: #4e73df !important;
        }
        
        /* Curseur de longueur */
        .length-control {
            margin: 20px 0;
        }
        
        .length-slider {
            width: 100%;
            height: 8px;
            -webkit-appearance: none;
            background: #eaecf4;
            border-radius: 4px;
            outline: none;
        }
        
        .length-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            background: #4e73df;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .length-value {
            display: inline-block;
            background: #4e73df;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: bold;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        /* Cases à cocher */
        .checkboxes {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #4e73df;
        }
        
        .checkbox-item label {
            color: #5a5c69 !important;
            font-size: 0.95rem;
            cursor: pointer;
            font-weight: 500;
        }
        
        /* Boutons d'action */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin: 25px 0;
        }
        
        .btn-ai {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
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
        }
        
        .btn-ai:hover {
            background: linear-gradient(135deg, #2e59d9 0%, #1c3ca0 100%);
            transform: translateY(-2px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.3);
        }
        
        .btn-ai-secondary {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }
        
        .btn-ai-secondary:hover {
            background: linear-gradient(135deg, #17a673 0%, #0e6b4a 100%);
        }
        
        /* Boutons de thèmes */
        .theme-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }
        
        .theme-btn {
            background: white;
            border: 2px solid #d1d3e2;
            padding: 10px 18px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #5a5c69 !important;
            font-weight: 500;
        }
        
        .theme-btn:hover {
            border-color: #4e73df;
            background: #f8f9fe;
            transform: translateY(-2px);
            color: #4e73df !important;
        }
        
        .theme-btn i {
            font-size: 0.9rem;
            color: #4e73df;
        }
        
        /* Titre des thèmes */
        .theme-title {
            color: #5a5c69 !important;
            margin-bottom: 15px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .theme-title i {
            color: #4e73df !important;
            margin-right: 8px;
        }
        
        /* Indice et informations */
        .password-hint {
            background: #fff3cd;
            border-left: 4px solid #f6c23e;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #856404 !important;
            font-size: 0.9rem;
        }
        
        .password-hint i {
            margin-right: 10px;
            color: #f6c23e;
        }
        
        /* Résultat vérification fuite */
        .leak-check-result {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            display: none;
            font-size: 0.9rem;
        }
        
        .leak-safe {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46 !important;
        }
        
        .leak-warning {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b !important;
        }
        
        /* Feedback pour le champ mot de passe */
        .password-feedback {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            display: none;
        }
        
        .feedback-good {
            background: #d1fae5;
            color: #065f46 !important;
            border: 1px solid #a7f3d0;
        }
        
        .feedback-warning {
            background: #fff3cd;
            color: #856404 !important;
            border: 1px solid #ffeaa7;
        }
        
        .feedback-error {
            background: #f8d7da;
            color: #721c24 !important;
            border: 1px solid #f5c6cb;
        }
        
        /* Champ mot de passe avec icône */
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
        
        /* Conseils de sécurité */
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
        }
        
        .security-tips ul {
            color: #5a5c69 !important;
            font-size: 0.9rem;
            line-height: 1.6;
            padding-left: 20px;
        }
        
        .security-tips li {
            margin-bottom: 8px;
        }
        
        .security-tips i {
            color: #4e73df;
            margin-right: 8px;
        }
        
        /* Pour s'assurer que tous les textes sont visibles */
        #passwordGenerator,
        #passwordGenerator * {
            color: #5a5c69 !important;
        }
        
        #passwordGenerator .ai-header,
        #passwordGenerator .ai-header * {
            color: white !important;
        }
        
        #passwordGenerator .btn-ai,
        #passwordGenerator .btn-ai * {
            color: white !important;
        }
        
        #passwordGenerator .length-value,
        #passwordGenerator .length-value * {
            color: white !important;
        }
        
        #passwordGenerator .copy-btn,
        #passwordGenerator .copy-btn * {
            color: white !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .ai-content {
                padding: 15px;
            }
            
            .options-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-ai {
                width: 100%;
                justify-content: center;
            }
            
            .theme-buttons {
                flex-direction: column;
            }
            
            .theme-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Badge IA pour le label */
        label .ai-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            margin-left: 5px;
            vertical-align: middle;
        }
    </style>
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
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
                <h2>Inscription</h2>
                <p>Rejoignez la communauté SafeSpace</p>
            </div>
        </header>

        <!-- Content -->
        <div class="wrapper">
            <div class="inner">

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="success">
                        <p><?= htmlspecialchars($_SESSION['success']) ?></p>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if(!empty($errors)): ?>
                    <div class="error">
                        <?php foreach($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="" id="registerForm">
                    <div class="fields">
                        <div class="field half">
                            <label for="firstname">Prénom</label>
                            <input type="text" name="firstname" id="firstname" 
                                   placeholder="Votre prénom" 
                                   value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="field half">
                            <label for="lastname">Nom</label>
                            <input type="text" name="lastname" id="lastname" 
                                   placeholder="Votre nom" 
                                   value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>" 
                                   required />
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" 
                                   placeholder="exemple@mail.com" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                   required />
                        </div>
                        
                        <!-- Champ Mot de passe avec générateur IA -->
                        <div class="field">
                            <label for="password">
                                <i class="fas fa-lock"></i> Mot de passe 
                                <span class="ai-badge">IA</span>
                            </label>
                            
                            <div class="password-field">
                                <input type="password" name="password" id="password" 
                                       placeholder="Créez un mot de passe sécurisé" 
                                       minlength="6" required
                                       oninput="checkPasswordStrength(this.value)" />
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            
                            <!-- Indicateur de force -->
                            <div id="passwordStrength" style="display: none;" class="strength-container">
                                <div class="strength-label">
                                    <span>Force du mot de passe :</span>
                                    <span id="strengthText"></span>
                                </div>
                                <div class="strength-bar">
                                    <div id="strengthBar" class="strength-fill"></div>
                                </div>
                                <div id="passwordFeedback" class="password-feedback"></div>
                            </div>
                            
                            <button type="button" class="btn-ai" onclick="showPasswordGenerator()" style="margin-top: 15px;">
                                <i class="fas fa-robot"></i> Générer un mot de passe avec IA
                            </button>
                        </div>
                      
                        <!-- Champ Rôle -->
                        <div class="field">
                            <label for="role">Rôle</label>
                            <select name="role" id="role" required>
                                <option value="membre" <?= ($_POST['role'] ?? '') == 'membre' ? 'selected' : '' ?>>Membre</option>
                                <option value="conseilleur" <?= ($_POST['role'] ?? '') == 'conseilleur' ? 'selected' : '' ?>>Conseilleur</option>
                            </select>
                            <p style="font-size: 0.85rem; color: #6c757d; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> 
                                Les comptes conseillers nécessitent une validation par l'administrateur
                            </p>
                        </div>
                    </div>
                    
                    <!-- Générateur de mot de passe IA (caché par défaut) -->
                    <div class="ai-password-section" id="passwordGenerator" style="display: none;">
                        <div class="ai-header">
                            <i class="fas fa-robot"></i>
                            <span>Générateur IA de mots de passe</span>
                            <span class="ai-badge">INTELLIGENCE ARTIFICIELLE</span>
                        </div>
                        
                        <div class="ai-content">
                            <!-- Mot de passe généré -->
                            <div class="password-display-box">
                                <div id="generatedPasswordText">Cliquez sur "Générer" pour créer un mot de passe sécurisé</div>
                                <button type="button" class="copy-btn" onclick="copyGeneratedPassword()" id="copyBtn" style="display: none;">
                                    <i class="fas fa-copy"></i> Copier
                                </button>
                            </div>
                            
                            <!-- Options de génération -->
                            <div class="options-grid">
                                <div class="option-group">
                                    <h4><i class="fas fa-ruler"></i> Longueur du mot de passe</h4>
                                    <div class="length-control">
                                        <input type="range" id="pwdLength" min="8" max="32" value="12" 
                                               class="length-slider" 
                                               oninput="updateLengthValue(this.value)">
                                        <div style="text-align: center; margin-top: 10px;">
                                            <span class="length-value" id="lengthValue">12 caractères</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="option-group">
                                    <h4><i class="fas fa-sliders-h"></i> Type de caractères</h4>
                                    <div class="checkboxes">
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="includeUpper" checked>
                                            <label for="includeUpper">Majuscules (A-Z)</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="includeNumbers" checked>
                                            <label for="includeNumbers">Chiffres (0-9)</label>
                                        </div>
                                        <div class="checkbox-item">
                                            <input type="checkbox" id="includeSymbols" checked>
                                            <label for="includeSymbols">Symboles (!@#$%)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Boutons de génération -->
                            <div class="action-buttons">
                                <button type="button" class="btn-ai" onclick="generateAIPassword('strong')">
                                    <i class="fas fa-shield-alt"></i> Générer un mot de passe fort
                                </button>
                                <button type="button" class="btn-ai btn-ai-secondary" onclick="generateAIPassword('memorable')">
                                    <i class="fas fa-brain"></i> Générer un mot de passe mémorable
                                </button>
                            </div>
                            
                            <!-- Thèmes optionnels -->
                            <div style="text-align: center; margin: 25px 0;">
                                <h4 class="theme-title">
                                    <i class="fas fa-palette"></i> Thèmes optionnels
                                </h4>
                                <div class="theme-buttons">
                                    <button type="button" class="theme-btn" onclick="generateThemedPassword('tech')">
                                        <i class="fas fa-laptop-code"></i> Tech
                                    </button>
                                    <button type="button" class="theme-btn" onclick="generateThemedPassword('nature')">
                                        <i class="fas fa-leaf"></i> Nature
                                    </button>
                                    <button type="button" class="theme-btn" onclick="generateThemedPassword('food')">
                                        <i class="fas fa-utensils"></i> Nourriture
                                    </button>
                                    <button type="button" class="theme-btn" onclick="generateThemedPassword('fantasy')">
                                        <i class="fas fa-dragon"></i> Fantaisie
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Actions sur le mot de passe généré -->
                            <div class="action-buttons" id="passwordActions" style="display: none;">
                                <button type="button" class="btn-ai" onclick="useGeneratedPassword()" id="usePasswordBtn">
                                    <i class="fas fa-check-circle"></i> Utiliser ce mot de passe
                                </button>
                                <button type="button" class="btn-ai btn-ai-secondary" onclick="checkPasswordLeak()" id="checkLeakBtn">
                                    <i class="fas fa-shield-check"></i> Vérifier les fuites
                                </button>
                            </div>
                            
                            <!-- Indice mnémotechnique -->
                            <div class="password-hint" id="passwordHint" style="display: none;">
                                <i class="fas fa-lightbulb"></i>
                                <span id="hintText"></span>
                            </div>
                            
                            <!-- Résultat vérification fuite -->
                            <div class="leak-check-result" id="leakCheckResult"></div>
                            
                            <!-- Conseils de sécurité -->
                            <div class="security-tips">
                                <h4><i class="fas fa-tips"></i> Conseils de sécurité</h4>
                                <ul>
                                    <li>Utilisez un mot de passe différent pour chaque site</li>
                                    <li>Évitez les informations personnelles (date de naissance, nom)</li>
                                    <li>Changez votre mot de passe tous les 3 mois</li>
                                    <li>Activez l'authentification à deux facteurs si disponible</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <ul class="actions">
                        <li>
                            <input type="submit" value="S'inscrire" class="primary" 
                                   onclick="return validateForm()" />
                        </li>
                        <li>
                            <a href="login.php" class="button">
                                <i class="fas fa-sign-in-alt"></i> J'ai déjà un compte
                            </a>
                        </li>
                    </ul>
                </form>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
        </div>
    </section>

</div>

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/register.js"></script>
<script src="assets/js/script_post.js"></script>


<!-- Script pour le générateur IA -->
<script>
//let currentGeneratedPassword = '';
let passwordGenerated = false;

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

// Afficher le générateur de mot de passe
function showPasswordGenerator() {
    const generator = document.getElementById('passwordGenerator');
    generator.style.display = 'block';
    generator.classList.add('fade-in');
    generator.scrollIntoView({ behavior: 'smooth' });
}

// Mettre à jour l'affichage de la longueur
function updateLengthValue(value) {
    document.getElementById('lengthValue').textContent = value + ' caractères';
}

// Vérifier la force du mot de passe en temps réel
function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const feedback = document.getElementById('passwordFeedback');
    
    if (password.length === 0) {
        strengthDiv.style.display = 'none';
        return;
    }
    
    strengthDiv.style.display = 'block';
    
    let score = 0;
    let messages = [];
    
    // Calcul du score
    if (password.length >= 12) score += 40;
    else if (password.length >= 8) score += 25;
    else if (password.length >= 6) score += 10;
    else messages.push('Trop court (minimum 6 caractères)');
    
    // Complexité
    if (/[a-z]/.test(password)) score += 10;
    else messages.push('Ajoutez des minuscules');
    
    if (/[A-Z]/.test(password)) score += 10;
    else messages.push('Ajoutez des majuscules');
    
    if (/[0-9]/.test(password)) score += 10;
    else messages.push('Ajoutez des chiffres');
    
    if (/[^a-zA-Z0-9]/.test(password)) score += 15;
    else messages.push('Ajoutez des symboles');
    
    // Pénalités
    if (/(.)\1{2,}/.test(password)) {
        score -= 10;
        messages.push('Évitez les répétitions');
    }
    
    if (/^(123|abc|qwe|azerty|password|admin|123456)/i.test(password)) {
        score -= 20;
        messages.push('Évitez les mots de passe courants');
    }
    
    score = Math.max(0, Math.min(100, score));
    
    // Mettre à jour la barre de force
    strengthBar.style.width = score + '%';
    
    // Changer la couleur selon le score
    if (score >= 80) {
        strengthBar.className = 'strength-fill strength-very-strong';
        strengthText.textContent = 'Très Fort';
        strengthText.style.color = '#4e73df';
    } else if (score >= 60) {
        strengthBar.className = 'strength-fill strength-strong';
        strengthText.textContent = 'Fort';
        strengthText.style.color = '#36b9cc';
    } else if (score >= 40) {
        strengthBar.className = 'strength-fill strength-good';
        strengthText.textContent = 'Bon';
        strengthText.style.color = '#1cc88a';
    } else if (score >= 20) {
        strengthBar.className = 'strength-fill strength-medium';
        strengthText.textContent = 'Moyen';
        strengthText.style.color = '#f6c23e';
    } else {
        strengthBar.className = 'strength-fill strength-weak';
        strengthText.textContent = 'Faible';
        strengthText.style.color = '#e74a3b';
    }
    
    // Afficher les conseils
    feedback.style.display = 'block';
    if (messages.length > 0 && score < 80) {
        feedback.textContent = '💡 ' + messages[0];
        feedback.className = 'password-feedback feedback-warning';
    } else if (score >= 80) {
        feedback.textContent = '✅ Excellent mot de passe !';
        feedback.className = 'password-feedback feedback-good';
    } else {
        feedback.textContent = '⚠️ Améliorez votre mot de passe';
        feedback.className = 'password-feedback feedback-error';
    }
}

// Générer un mot de passe avec IA
async function generateAIPassword(type = 'strong') {
    try {
        const length = document.getElementById('pwdLength').value;
        const includeUpper = document.getElementById('includeUpper').checked;
        const includeNumbers = document.getElementById('includeNumbers').checked;
        const includeSymbols = document.getElementById('includeSymbols').checked;
        
        // Afficher un indicateur de chargement
        document.getElementById('generatedPasswordText').innerHTML = 
            '<i class="fas fa-spinner fa-spin"></i> Génération IA en cours...';
        
        // Envoyer la requête au serveur
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'generate',
                length: parseInt(length),
                includeUpper: includeUpper,
                includeNumbers: includeNumbers,
                includeSymbols: includeSymbols,
                type: type
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentGeneratedPassword = data.password;
            passwordGenerated = true;
            
            // Afficher le mot de passe généré
            document.getElementById('generatedPasswordText').textContent = currentGeneratedPassword;
            document.getElementById('copyBtn').style.display = 'block';
            document.getElementById('passwordActions').style.display = 'flex';
            
            // Afficher l'indice si disponible
            if (data.hint) {
                document.getElementById('hintText').textContent = data.hint;
                document.getElementById('passwordHint').style.display = 'block';
            }
            
            // Vérifier la force
            checkPasswordStrength(currentGeneratedPassword);
            
        } else {
            document.getElementById('generatedPasswordText').textContent = 
                'Erreur : ' + (data.message || 'Impossible de générer le mot de passe');
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        // Fallback côté client
        generateFallbackPassword();
    }
}

// Générer un mot de passe thématique
async function generateThemedPassword(theme) {
    try {
        document.getElementById('generatedPasswordText').innerHTML = 
            '<i class="fas fa-spinner fa-spin"></i> Génération du thème ' + theme + '...';
        
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'themed',
                theme: theme,
                length: 14
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentGeneratedPassword = data.password;
            passwordGenerated = true;
            
            document.getElementById('generatedPasswordText').textContent = currentGeneratedPassword;
            document.getElementById('copyBtn').style.display = 'block';
            document.getElementById('passwordActions').style.display = 'flex';
            
            // Mettre à jour l'indice avec le thème
            document.getElementById('hintText').textContent = 
                'Mot de passe généré avec le thème "' + theme + '"';
            document.getElementById('passwordHint').style.display = 'block';
            
            checkPasswordStrength(currentGeneratedPassword);
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        generateFallbackPassword();
    }
}

// Copier le mot de passe généré
function copyGeneratedPassword() {
    if (!currentGeneratedPassword) return;
    
    navigator.clipboard.writeText(currentGeneratedPassword).then(() => {
        const copyBtn = document.getElementById('copyBtn');
        const originalHTML = copyBtn.innerHTML;
        
        copyBtn.innerHTML = '<i class="fas fa-check"></i> Copié!';
        copyBtn.style.background = '#1cc88a';
        
        setTimeout(() => {
            copyBtn.innerHTML = originalHTML;
            copyBtn.style.background = '#4e73df';
        }, 2000);
        
    }).catch(err => {
        alert('Erreur lors de la copie : ' + err);
    });
}

// Utiliser le mot de passe généré
function useGeneratedPassword() {
    if (!currentGeneratedPassword) return;
    
    document.getElementById('password').value = currentGeneratedPassword;
    document.getElementById('password').type = 'text';
    document.getElementById('toggleIcon').className = 'fas fa-eye-slash';
    
    // Vérifier la force
    checkPasswordStrength(currentGeneratedPassword);
    
    // Afficher un message de succès
    const useBtn = document.getElementById('usePasswordBtn');
    const originalHTML = useBtn.innerHTML;
    
    useBtn.innerHTML = '<i class="fas fa-check-double"></i> Utilisé!';
    useBtn.style.background = '#1cc88a';
    
    setTimeout(() => {
        useBtn.innerHTML = originalHTML;
        useBtn.style.background = '';
    }, 2000);
    
    // Fermer le générateur
    document.getElementById('passwordGenerator').style.display = 'none';
}

// Vérifier les fuites de mot de passe
async function checkPasswordLeak() {
    if (!currentGeneratedPassword) return;
    
    try {
        const leakResult = document.getElementById('leakCheckResult');
        leakResult.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vérification des fuites en cours...';
        leakResult.style.display = 'block';
        leakResult.className = 'leak-check-result';
        
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'checkLeak',
                password: currentGeneratedPassword
            })
        });
        
        const data = await response.json();
        
        if (data.leaked) {
            leakResult.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${data.message}`;
            leakResult.className = 'leak-check-result leak-warning';
        } else {
            leakResult.innerHTML = `<i class="fas fa-shield-check"></i> ${data.message}`;
            leakResult.className = 'leak-check-result leak-safe';
        }
        
    } catch (error) {
        console.error('Erreur vérification fuite:', error);
        leakResult.innerHTML = 
            '<i class="fas fa-exclamation-circle"></i> Impossible de vérifier les fuites';
        leakResult.className = 'leak-check-result';
    }
}

// Fallback côté client
function generateFallbackPassword() {
    const length = document.getElementById('pwdLength').value;
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    for (let i = 0; i < length; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    currentGeneratedPassword = password;
    passwordGenerated = true;
    
    document.getElementById('generatedPasswordText').textContent = password;
    document.getElementById('copyBtn').style.display = 'block';
    document.getElementById('passwordActions').style.display = 'flex';
    
    document.getElementById('hintText').textContent = '💡 Mot de passe généré localement';
    document.getElementById('passwordHint').style.display = 'block';
    
    checkPasswordStrength(password);
}

// Validation du formulaire
function validateForm() {
    const password = document.getElementById('password').value;
    
    if (password.length < 6) {
        alert('Le mot de passe doit contenir au moins 6 caractères.');
        document.getElementById('password').focus();
        return false;
    }
    
    // Vérifier la force si l'utilisateur n'a pas généré de mot de passe
    if (!passwordGenerated) {
        const strengthText = document.getElementById('strengthText').textContent;
        if (strengthText === 'Faible' || strengthText === 'Très Faible') {
            if (!confirm('Votre mot de passe semble faible. Souhaitez-vous utiliser notre générateur IA pour en créer un plus sécurisé ?')) {
                return true; // L'utilisateur veut continuer malgré tout
            } else {
                showPasswordGenerator();
                return false;
            }
        }
    }
    
    return true;
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier la force du mot de passe initial s'il y en a un
    const initialPassword = document.getElementById('password').value;
    if (initialPassword) {
        checkPasswordStrength(initialPassword);
    }
});
</script>

</body>
</html>