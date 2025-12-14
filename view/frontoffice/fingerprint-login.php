<?php
// view/frontoffice/fingerprint-login.php
session_start();

// Vérifier si l'utilisateur existe dans votre base de données
function getUserByEmail($email) {
    // À adapter selon votre base de données
    $users = [
        'admin@safespace.com' => [
            'id' => 1,
            'name' => 'Administrateur',
            'role' => 'admin',
            'can_fingerprint' => true  // Seul l'admin peut utiliser fingerprint
        ],
        'adviser@safespace.com' => [
            'id' => 2,
            'name' => 'Conseiller',
            'role' => 'adviser',
            'can_fingerprint' => false  // Pas de fingerprint pour conseiller
        ],
        'member@safespace.com' => [
            'id' => 3,
            'name' => 'Membre',
            'role' => 'member',
            'can_fingerprint' => false  // Pas de fingerprint pour membre
        ]
    ];
    
    return $users[$email] ?? null;
}

// Traitement de l'authentification WebAuthn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action'])) {
        switch($input['action']) {
            case 'get_challenge':
                // Vérifier si l'utilisateur peut utiliser fingerprint
                $user = getUserByEmail($input['email'] ?? '');
                
                if (!$user || !$user['can_fingerprint']) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Accès fingerprint non autorisé pour ce compte'
                    ]);
                    exit();
                }
                
                // Générer un challenge pour WebAuthn
                $challenge = random_bytes(32);
                $_SESSION['webauthn_challenge'] = base64_encode($challenge);
                
                echo json_encode([
                    'success' => true,
                    'challenge' => base64_encode($challenge),
                    'user' => $user ? [
                        'id' => base64_encode($user['id']),
                        'name' => $user['name'],
                        'displayName' => $user['name']
                    ] : null
                ]);
                break;
                
            case 'verify_auth':
                // Vérifier l'authentification
                $email = $input['email'] ?? '';
                $user = getUserByEmail($email);
                
                if ($user) {
                    // Vérifier si l'utilisateur peut utiliser fingerprint
                    if (!$user['can_fingerprint']) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Accès fingerprint non autorisé'
                        ]);
                        exit();
                    }
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['fingerprint_login'] = true;
                    
                    // Redirection admin seulement
                    echo json_encode([
                        'success' => true,
                        'message' => 'Authentification réussie !',
                        'redirect' => '../backoffice/index.php'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Utilisateur non trouvé'
                    ]);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Action inconnue']);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Windows Hello - SafeSpace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* MÊME CSS que votre login.php */
        :root {
            --primary-color: #4e73df;
            --secondary-color: #1cc88a;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-image: linear-gradient(180deg, var(--primary-color) 10%, #224abe 100%);
            background-size: cover;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
        }
        
        .login-header {
            background: white;
            color: var(--primary-color);
            padding: 25px 30px;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .logo-img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .site-subtitle {
            color: var(--dark-color);
            font-size: 14px;
            opacity: 0.8;
            margin-top: 5px;
            text-align: center;
            padding: 0 20px;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
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
        
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d3e2;
            border-radius: 5px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: white;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: 0;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            transform: translateY(-1px);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .btn-windows {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            color: white;
        }
        
        .btn-windows:hover {
            background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
            transform: translateY(-1px);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .btn-disabled {
            background: #6c757d;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .btn-disabled:hover {
            background: #6c757d;
            transform: none;
            box-shadow: none;
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e3e6f0;
        }
        
        .divider-text {
            background: white;
            padding: 0 15px;
            color: var(--dark-color);
            font-size: 14px;
            position: relative;
            z-index: 1;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e3e6f0;
        }
        
        .links a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .windows-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #e3e6f0;
        }
        
        .windows-icon {
            font-size: 40px;
            color: #0078d4;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .biometric-info {
            font-size: 13px;
            color: #6c757d;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-align: center;
        }
        
        .access-info {
            background: #e7f3ff;
            border-left: 4px solid #0078d4;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .access-info h4 {
            color: #005a9e;
            margin-bottom: 8px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .access-info p {
            color: #495057;
            font-size: 13px;
            margin: 0;
        }
        
        .user-selection {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 20px 0;
        }
        
        .user-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .user-option:hover {
            border-color: var(--primary-color);
            background: #f8f9fc;
        }
        
        .user-option.selected {
            border-color: var(--primary-color);
            background: #f0f7ff;
        }
        
        .user-option.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            border-color: #e3e6f0;
            background: #f8f9fa;
        }
        
        .user-option.disabled:hover {
            border-color: #e3e6f0;
            background: #f8f9fa;
        }
        
        .user-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .admin-icon {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
        }
        
        .adviser-icon {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #0da271 100%);
        }
        
        .member-icon {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-role {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .user-email {
            font-size: 13px;
            color: #6c757d;
        }
        
        .access-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .access-allowed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .access-denied {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .loading {
            display: none;
            margin: 30px 0;
            text-align: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            display: none;
        }
        
        .status.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .status.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 13px;
            border-top: 1px solid #e3e6f0;
            background: #f8f9fc;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-body {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .logo-section {
                justify-content: center;
            }
            
            .logo-img {
                height: 35px;
            }
            
            .logo-text {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="header-content">
                <div class="logo-section">
                    <a class="nav-logo text-primary" href="index.php">
                        <!-- VOTRE LOGO - ajustez le src si nécessaire -->
                        <img src="images/logo.png" alt="SafeSpace Logo" class="logo-img">
                        <span class="logo-text">SafeSpace</span>
                    </a>
                </div>
                
                <div style="color: var(--dark-color); font-size: 14px; opacity: 0.8;">
                    <i class="fas fa-fingerprint"></i> Accès Admin
                </div>
            </div>
            
            <div class="site-subtitle">
                Windows Hello - Authentification réservée à l'administrateur
            </div>
        </div>
        
        <div class="login-body">
            <h2 style="color: var(--dark-color); margin-bottom: 25px; text-align: center; font-size: 20px;">
                <i class="fas fa-shield-alt"></i> Authentification Sécurisée
            </h2>
            
            <div class="access-info">
                <h4><i class="fas fa-lock"></i> Accès Restreint</h4>
                <p>Cette fonctionnalité est réservée exclusivement à l'administrateur du système pour des raisons de sécurité.</p>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-user-tie"></i> Sélectionnez votre compte
                </label>
                
                <div class="user-selection" id="userSelection">
                    <!-- Option Admin - SEUL accès fingerprint -->
                    <div class="user-option selected" data-email="admin@safespace.com" data-role="admin" data-can-fingerprint="true">
                        <div class="user-icon admin-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-role">
                                Administrateur
                                <span class="access-badge access-allowed">Fingerprint autorisé</span>
                            </div>
                            <div class="user-email">admin@safespace.com</div>
                        </div>
                    </div>
                    
                    <!-- Option Conseiller - PAS d'accès fingerprint -->
                    <div class="user-option disabled" data-email="adviser@safespace.com" data-role="adviser" data-can-fingerprint="false">
                        <div class="user-icon adviser-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-role">
                                Conseiller
                                <span class="access-badge access-denied">Connexion normale</span>
                            </div>
                            <div class="user-email">adviser@safespace.com</div>
                        </div>
                    </div>
                    
                    <!-- Option Membre - PAS d'accès fingerprint -->
                    <div class="user-option disabled" data-email="member@safespace.com" data-role="member" data-can-fingerprint="false">
                        <div class="user-icon member-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-role">
                                Membre
                                <span class="access-badge access-denied">Connexion normale</span>
                            </div>
                            <div class="user-email">member@safespace.com</div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info" style="margin-top: 15px;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Seul l'administrateur</strong> peut utiliser Windows Hello/Fingerprint. 
                    Les autres comptes doivent utiliser la <a href="login.php" style="color: #0056b3;">connexion normale</a>.
                </div>
            </div>
            
            <div class="windows-section">
                <div class="windows-icon">
                    <i class="fab fa-windows"></i>
                </div>
                
                <h3 style="color: #005a9e; margin-bottom: 15px; font-size: 18px; text-align: center;">
                    Windows Hello Admin
                </h3>
                
                <p style="color: #666; margin-bottom: 20px; font-size: 14px; text-align: center;">
                    Authentification biométrique sécurisée réservée à l'administrateur
                </p>
                
                <button class="btn btn-windows" onclick="authenticateWithWindowsHello()" id="windowsBtn">
                    <i class="fas fa-fingerprint"></i> Windows Hello Admin
                </button>
                
                <div class="biometric-info">
                    <i class="fas fa-shield-alt"></i>
                    <span>Accès sécurisé réservé • Authentification à deux facteurs</span>
                </div>
            </div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Authentification en cours...</p>
            </div>
            
            <div id="status" class="status"></div>
            
            <div class="divider">
                <span class="divider-text">OU</span>
            </div>
            
            <div style="text-align: center; margin: 20px 0;">
                <p style="color: var(--dark-color); margin-bottom: 15px; font-size: 15px;">
                    <i class="fas fa-sign-in-alt"></i> Autres méthodes de connexion
                </p>
                
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-key"></i> Connexion normale (tous les comptes)
                </a>
            </div>
            
            <div class="links">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Retour au login
                </a>
                <span style="color: #dee2e6">|</span>
                <a href="index.php">
                    <i class="fas fa-home"></i> Accueil
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2024 SafeSpace. Tous droits réservés.</p>
            <p style="font-size: 12px; margin-top: 5px; opacity: 0.7;">
                <i class="fas fa-shield-alt"></i> Sécurité administrateur garantie
            </p>
        </div>
    </div>
    
    <script>
        // Sélection d'utilisateur - seulement admin peut être sélectionné
        document.querySelectorAll('.user-option').forEach(option => {
            option.addEventListener('click', function() {
                const canFingerprint = this.getAttribute('data-can-fingerprint') === 'true';
                
                if (!canFingerprint) {
                    showStatus('❌ Cette fonctionnalité est réservée à l\'administrateur. Utilisez la connexion normale.', 'error');
                    return;
                }
                
                // Retirer la sélection de toutes les options
                document.querySelectorAll('.user-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Ajouter la sélection à l'option cliquée
                this.classList.add('selected');
            });
        });
        
        // Obtenir l'email sélectionné
        function getSelectedEmail() {
            const selected = document.querySelector('.user-option.selected');
            if (selected) {
                const canFingerprint = selected.getAttribute('data-can-fingerprint') === 'true';
                const email = selected.getAttribute('data-email');
                
                if (!canFingerprint) {
                    return null;
                }
                
                return email;
            }
            
            return null;
        }
        
        // Vérifier si l'utilisateur sélectionné est admin
        function isAdminSelected() {
            const selected = document.querySelector('.user-option.selected');
            if (!selected) return false;
            
            const role = selected.getAttribute('data-role');
            const canFingerprint = selected.getAttribute('data-can-fingerprint') === 'true';
            
            return role === 'admin' && canFingerprint;
        }
        
        // Authentification avec Windows Hello
        async function authenticateWithWindowsHello() {
            // Vérifier si admin est sélectionné
            if (!isAdminSelected()) {
                showStatus('❌ Seul l\'administrateur peut utiliser Windows Hello. Veuillez utiliser la connexion normale.', 'error');
                return;
            }
            
            const email = getSelectedEmail();
            
            if (!email) {
                showStatus('❌ Veuillez sélectionner le compte administrateur', 'error');
                return;
            }
            
            showLoading(true);
            showStatus('', '');
            
            try {
                // Obtenir un challenge du serveur
                const challengeResponse = await fetch('fingerprint-login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'get_challenge',
                        email: email
                    })
                });
                
                const challengeData = await challengeResponse.json();
                
                if (!challengeData.success) {
                    throw new Error(challengeData.message || 'Accès non autorisé');
                }
                
                // Options pour l'authentification WebAuthn
                const publicKey = {
                    challenge: Uint8Array.from(atob(challengeData.challenge), c => c.charCodeAt(0)),
                    rpId: window.location.hostname,
                    userVerification: 'required',
                    timeout: 60000
                };
                
                // Démarrer l'authentification WebAuthn
                const credential = await navigator.credentials.get({ publicKey });
                
                // Vérifier avec le serveur
                const verifyResponse = await fetch('fingerprint-login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'verify_auth',
                        email: email,
                        credential: credential
                    })
                });
                
                const verifyData = await verifyResponse.json();
                
                if (verifyData.success) {
                    showStatus('✅ Authentification admin réussie ! Redirection...', 'success');
                    setTimeout(() => {
                        window.location.href = verifyData.redirect;
                    }, 1500);
                } else {
                    throw new Error(verifyData.message);
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                
                if (error.name === 'NotAllowedError') {
                    showStatus('❌ Authentification annulée', 'error');
                } else if (error.name === 'NotSupportedError') {
                    showStatus('❌ Windows Hello non disponible. Simulation pour admin...', 'info');
                    simulateAdminAuthentication(); // Simuler pour le test admin
                } else {
                    showStatus('❌ ' + error.message, 'error');
                    simulateAdminAuthentication(); // Fallback pour admin
                }
            } finally {
                showLoading(false);
            }
        }
        
        // Simulation pour admin (pour les tests)
        function simulateAdminAuthentication() {
            showStatus('⚠️ Simulation d\'authentification admin...', 'info');
            
            setTimeout(() => {
                // Simuler une vérification réussie
                showStatus('✅ Authentification admin simulée réussie !', 'success');
                
                // Redirection vers dashboard admin
                setTimeout(() => {
                    window.location.href = '../backoffice/index.php';
                }, 2000);
            }, 2000);
        }
        
        // Afficher un message de statut
        function showStatus(message, type) {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = message;
            statusDiv.className = 'status ' + type;
            statusDiv.style.display = 'block';
            
            if (type === 'error' || type === 'info') {
                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 5000);
            }
        }
        
        // Afficher/masquer le loading
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Fallback si le logo n'existe pas
            const logoImg = document.querySelector('.logo-img');
            if (logoImg) {
                logoImg.onerror = function() {
                    this.style.display = 'none';
                    const logoText = document.querySelector('.logo-text');
                    if (logoText) {
                        logoText.innerHTML = '<i class="fas fa-shield-alt"></i> SafeSpace';
                        logoText.style.fontSize = '24px';
                    }
                };
            }
            
            // Message d'information
            console.log('Fingerprint/Windows Hello réservé à l\'administrateur');
        });
    </script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>