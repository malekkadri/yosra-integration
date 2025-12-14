<?php
session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

$message = '';
$message_type = '';
$show_form = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: forgot_password.php');
    exit();
}

$userController = new UserController();
$db = $userController->getConnection();

// Vérifier la validité du token
$sql = "SELECT email, expires_at FROM password_resets WHERE token = ? AND expires_at > NOW()";
$stmt = $db->prepare($sql);
$stmt->execute([$token]);
$reset_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset_data) {
    $message = "Le lien de réinitialisation a expiré ou est invalide.";
    $message_type = 'error';
    $show_form = false;
} else {
    $show_form = true;
    $email = $reset_data['email'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($new_password) || empty($confirm_password)) {
            $message = "Tous les champs sont requis";
            $message_type = 'error';
        } elseif ($new_password !== $confirm_password) {
            $message = "Les mots de passe ne correspondent pas";
            $message_type = 'error';
        } elseif (strlen($new_password) < 6) {
            $message = "Le mot de passe doit contenir au moins 6 caractères";
            $message_type = 'error';
        } else {
            try {
                // Mettre à jour le mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $sql = "UPDATE users SET password = ? WHERE email = ?";
                $stmt = $db->prepare($sql);
                
                if ($stmt->execute([$hashed_password, $email])) {
                    // Supprimer le token utilisé
                    $sql = "DELETE FROM password_resets WHERE token = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$token]);
                    
                    $message = "✅ Votre mot de passe a été réinitialisé avec succès !";
                    $message_type = 'success';
                    $show_form = false;
                    
                    // Envoyer un email de confirmation
                    require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/MailController.php';
                    $mailController = new MailController();
                    
<<<<<<< HEAD:SAFEProject/view/frontoffice/reset_password.php
                    $user = $userController->getUserByEmail($email);
                    if ($user) {
                        $subject = "✅ Votre mot de passe SafeSpace a été modifié";
                        $body = "
                        <h2>Bonjour " . htmlspecialchars($user['fullname']) . ",</h2>
=======
                    try {
                        $user = $userController->getUserByEmail($email);
                        $userFullname = "Utilisateur"; // Valeur par défaut
                        
                        if ($user) {
                            // Vérifier et obtenir le nom complet
                            if (isset($user['fullname']) && !empty($user['fullname'])) {
                                $userFullname = htmlspecialchars($user['fullname']);
                            } elseif (isset($user['username']) && !empty($user['username'])) {
                                $userFullname = htmlspecialchars($user['username']);
                            } elseif (isset($user['email'])) {
                                // Extraire le nom de l'email comme fallback
                                $emailParts = explode('@', $user['email']);
                                $userFullname = htmlspecialchars($emailParts[0]);
                            }
                        }
                        
                        $subject = "✅ Votre mot de passe SafeSpace a été modifié";
                        $body = "
                        <h2>Bonjour " . $userFullname . ",</h2>
>>>>>>> origin/main:view/frontoffice/reset_password.php
                        <p>Votre mot de passe SafeSpace a été modifié avec succès.</p>
                        <p><strong>Date :</strong> " . date('d/m/Y à H:i') . "</p>
                        <p>Si vous n'avez pas effectué cette modification, veuillez nous contacter immédiatement.</p>
                        <p>Cordialement,<br><strong>L'équipe SafeSpace</strong> 🔒</p>
                        ";
                        
<<<<<<< HEAD:SAFEProject/view/frontoffice/reset_password.php
                        $mailController->sendEmail($email, $subject, $body, $user['fullname']);
=======
                        $mailController->sendEmail($email, $subject, $body, $userFullname);
                        
                    } catch (Exception $e) {
                        // Logger l'erreur mais ne pas bloquer le processus de réinitialisation
                        error_log("Erreur lors de l'envoi de l'email de confirmation: " . $e->getMessage());
                        // Continuer sans lever d'exception pour ne pas perturber la réinitialisation
>>>>>>> origin/main:view/frontoffice/reset_password.php
                    }
                } else {
                    $message = "Erreur lors de la mise à jour du mot de passe";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                $message = "Erreur technique : " . $e->getMessage();
                $message_type = 'error';
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
    <title>Nouveau mot de passe - SafeSpace</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #1cc88a;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .strength-bar {
            height: 4px;
            flex-grow: 1;
            background: #eee;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }
        
        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(28, 200, 138, 0.4);
        }
        
        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .links a {
            color: #1cc88a;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin: 0 10px;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: #13855c;
            text-decoration: underline;
        }
        
        .password-requirements {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }
        
        .password-requirements ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
        }
        
        .requirement.valid {
            color: #1cc88a;
        }
        
        .requirement.invalid {
            color: #e74a3b;
        }
        
        @media (max-width: 480px) {
            .container {
                max-width: 100%;
            }
            
            .content {
                padding: 20px;
            }
            
            .header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Créer un nouveau mot de passe</h1>
            <p>Choisissez un mot de passe sécurisé</p>
        </div>
        
        <div class="content">
            <?php if($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <?php if($show_form): ?>
                <div class="message info">
                    <strong>Email :</strong> <?= htmlspecialchars(substr($email, 0, 3)) . '***' . substr($email, strpos($email, '@')) ?>
                </div>
                
                <form method="POST" action="" id="resetForm">
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe :</label>
                        <input type="password" id="new_password" name="new_password" 
                               placeholder="Minimum 6 caractères" 
                               minlength="6" required>
                        <div class="password-strength">
                            <span id="strengthText">Faible</span>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthBar"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe :</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Retapez votre mot de passe" 
                               minlength="6" required>
                        <div class="password-strength">
                            <span id="matchText">Non vérifié</span>
                        </div>
                    </div>
                    
                    <div class="password-requirements">
                        <p><strong>✅ Votre mot de passe doit :</strong></p>
                        <ul>
                            <li id="reqLength" class="requirement invalid">• Contenir au moins 6 caractères</li>
                            <li id="reqMatch" class="requirement invalid">• Les deux mots de passe doivent correspondre</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="new_password" class="btn-primary" id="submitBtn">
                        Réinitialiser mon mot de passe
                    </button>
                </form>
            <?php elseif($message_type === 'success'): ?>
                <div style="text-align: center; padding: 20px 0;">
                    <div style="font-size: 48px; color: #1cc88a; margin-bottom: 20px;">✅</div>
                    <h3 style="margin-bottom: 15px; color: #333;">Mot de passe modifié !</h3>
                    <p style="color: #666; margin-bottom: 25px;">Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.</p>
                    <a href="login.php" style="display: inline-block; padding: 12px 30px; background: #1cc88a; color: white; 
                       text-decoration: none; border-radius: 8px; font-weight: 600;">
                        Me connecter maintenant
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="links">
                <a href="login.php">← Retour à la connexion</a>
                <a href="forgot_password.php">Nouvelle demande</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const matchText = document.getElementById('matchText');
            const reqLength = document.getElementById('reqLength');
            const reqMatch = document.getElementById('reqMatch');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('resetForm');
            
            // Vérification de la force du mot de passe
            newPassword.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Longueur
                if (password.length >= 6) {
                    strength += 25;
                    reqLength.classList.remove('invalid');
                    reqLength.classList.add('valid');
                    reqLength.innerHTML = '✓ Contenir au moins 6 caractères';
                } else {
                    reqLength.classList.remove('valid');
                    reqLength.classList.add('invalid');
                    reqLength.innerHTML = '• Contenir au moins 6 caractères';
                }
                
                // Majuscule
                if (/[A-Z]/.test(password)) strength += 25;
                
                // Chiffre
                if (/[0-9]/.test(password)) strength += 25;
                
                // Caractère spécial
                if (/[^A-Za-z0-9]/.test(password)) strength += 25;
                
                // Mettre à jour la barre et le texte
                strengthBar.style.width = strength + '%';
                
                if (strength === 0) {
                    strengthBar.style.background = '#e74a3b';
                    strengthText.textContent = 'Faible';
                    strengthText.style.color = '#e74a3b';
                } else if (strength <= 50) {
                    strengthBar.style.background = '#f6c23e';
                    strengthText.textContent = 'Moyen';
                    strengthText.style.color = '#f6c23e';
                } else if (strength <= 75) {
                    strengthBar.style.background = '#1cc88a';
                    strengthText.textContent = 'Bon';
                    strengthText.style.color = '#1cc88a';
                } else {
                    strengthBar.style.background = '#4e73df';
                    strengthText.textContent = 'Excellent';
                    strengthText.style.color = '#4e73df';
                }
                
                // Vérifier la correspondance
                checkPasswordMatch();
            });
            
            // Vérification de la correspondance
            confirmPassword.addEventListener('input', checkPasswordMatch);
            
            function checkPasswordMatch() {
                const password = newPassword.value;
                const confirm = confirmPassword.value;
                
                if (confirm.length === 0) {
                    matchText.textContent = 'Non vérifié';
                    matchText.style.color = '#666';
                    reqMatch.classList.remove('valid');
                    reqMatch.classList.add('invalid');
                    reqMatch.innerHTML = '• Les deux mots de passe doivent correspondre';
                } else if (password === confirm) {
                    matchText.textContent = 'Correspond';
                    matchText.style.color = '#1cc88a';
                    reqMatch.classList.remove('invalid');
                    reqMatch.classList.add('valid');
                    reqMatch.innerHTML = '✓ Les deux mots de passe correspondent';
                } else {
                    matchText.textContent = 'Ne correspond pas';
                    matchText.style.color = '#e74a3b';
                    reqMatch.classList.remove('valid');
                    reqMatch.classList.add('invalid');
                    reqMatch.innerHTML = '• Les deux mots de passe doivent correspondre';
                }
                
                updateSubmitButton();
            }
            
            function updateSubmitButton() {
                const password = newPassword.value;
                const confirm = confirmPassword.value;
                const isValid = password.length >= 6 && password === confirm;
                
                submitBtn.disabled = !isValid;
                submitBtn.style.opacity = isValid ? '1' : '0.6';
            }
            
            // Validation du formulaire
            form.addEventListener('submit', function(e) {
                const password = newPassword.value;
                const confirm = confirmPassword.value;
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 6 caractères.');
                    return false;
                }
                
                if (password !== confirm) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas.');
                    return false;
                }
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Réinitialisation en cours...';
                return true;
            });
            
            // Focus sur le premier champ
            newPassword.focus();
            updateSubmitButton();
        });
    </script>
</body>
</html>