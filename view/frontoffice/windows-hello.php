<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Windows Hello - SafeSpace</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: #4e73df;
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .windows-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .status-box {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .status-success {
            background: #d1fae5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        
        .status-error {
            background: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }
        
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        
        .btn-primary {
            background: #4e73df;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2e59d9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
        }
        
        .btn-secondary {
            background: #1cc88a;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #17a673;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(28, 200, 138, 0.3);
        }
        
        .btn i {
            font-size: 18px;
        }
        
        .manual-login {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #e3e6f0;
        }
        
        .manual-login h3 {
            color: #4e73df;
            margin-bottom: 15px;
            font-size: 16px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #5a5c69;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d3e2;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4e73df;
            box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.2);
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
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            font-size: 14px;
            display: none;
        }
        
        .message-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            display: block;
        }
        
        .message-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            display: block;
        }
        
        .loader {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #4e73df;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">
            <div class="windows-icon">
                <i class="fab fa-windows"></i>
            </div>
            <h1>Windows Hello</h1>
            <p>Admin SafeSpace</p>
        </div>
        
        <div class="content">
            <div class="status-box" id="compatibility-check">
                Vérification...
            </div>
            
            <div id="windows-hello-section" class="hidden">
                <button class="btn btn-primary" onclick="startWindowsHello()" id="windows-hello-btn">
                    <i class="fas fa-fingerprint"></i> Windows Hello
                </button>
                
                <div id="auth-status" class="hidden">
                    <div class="loader"></div>
                    <p style="text-align: center; margin-top: 10px; color: #5a5c69; font-size: 14px;">
                        Connexion...
                    </p>
                </div>
            </div>
            
            <div class="manual-login">
                <h3>Connexion admin</h3>
                <form id="login-form" onsubmit="return manualLogin()">
                    <div class="form-group">
                        <label for="email">Email admin</label>
                        <input type="email" id="email" required placeholder="admin@safespace.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" required placeholder="Votre mot de passe">
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </button>
                </form>
            </div>
            
            <div id="message" class="message"></div>
            
            <div class="links">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Vérifier Windows Hello
        function checkWindowsHello() {
            const checkEl = document.getElementById('compatibility-check');
            const isWindows = navigator.userAgent.includes('Windows');
            const hasWebAuthn = !!(navigator.credentials && navigator.credentials.create);
            
            if (isWindows && hasWebAuthn) {
                checkEl.innerHTML = '<i class="fas fa-check-circle"></i> Windows Hello OK';
                checkEl.className = 'status-box status-success';
                document.getElementById('windows-hello-section').classList.remove('hidden');
            } else {
                checkEl.innerHTML = '<i class="fas fa-times-circle"></i> Non disponible';
                checkEl.className = 'status-box status-error';
            }
        }
        
        // Démarrer Windows Hello
        async function startWindowsHello() {
            try {
                document.getElementById('windows-hello-btn').classList.add('hidden');
                document.getElementById('auth-status').classList.remove('hidden');
                
                // Simulation
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                showMessage('✅ Authentification réussie', 'success');
                
                setTimeout(() => {
                    window.location.href = '../backoffice/index.php';
                }, 1500);
                
            } catch (error) {
                console.error('Erreur:', error);
                showMessage('❌ Erreur', 'error');
                resetWindowsHelloUI();
            }
        }
        
        // Connexion manuelle
        function manualLogin() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (email && password) {
                if (email.includes('admin')) {
                    showMessage('Connexion admin...', 'success');
                    
                    setTimeout(() => {
                        window.location.href = '../backoffice/index.php';
                    }, 1500);
                } else {
                    showMessage('Accès admin uniquement', 'error');
                }
            } else {
                showMessage('Remplir tous les champs', 'error');
            }
            
            return false;
        }
        
        // Afficher message
        function showMessage(text, type) {
            const msgEl = document.getElementById('message');
            msgEl.textContent = text;
            msgEl.className = 'message message-' + type;
            
            setTimeout(() => {
                msgEl.className = 'message';
            }, 3000);
        }
        
        // Réinitialiser UI
        function resetWindowsHelloUI() {
            document.getElementById('windows-hello-btn').classList.remove('hidden');
            document.getElementById('auth-status').classList.add('hidden');
        }
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            checkWindowsHello();
            document.getElementById('email').focus();
        });
    </script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/brands.min.js"></script>
</body>
</html>