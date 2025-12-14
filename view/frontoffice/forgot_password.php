<?php
session_start();

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

$message = '';
$message_type = ''; // 'success' ou 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = "Veuillez entrer votre adresse email";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Adresse email invalide";
        $message_type = 'error';
    } else {
        $userController = new UserController();
        
        // Vérifier si l'email existe
        $user = $userController->getUserByEmail($email);
        
        if ($user) {
            // ⭐⭐ CORRECTION ICI : Récupérer le nom correctement ⭐⭐
            // Selon votre objet User, ça pourrait être :
            // $fullname = $user->getFullname(); // Si méthode getter
            // $fullname = $user->getNom();      // Si méthode getNom()
            // $fullname = $user->fullname;      // Si propriété publique
            // $fullname = $user['fullname'];    // Si tableau
            
            // Essayons différentes méthodes :
            $fullname = '';
            
            if (is_object($user)) {
                if (method_exists($user, 'getFullname')) {
                    $fullname = $user->getFullname();
                } elseif (method_exists($user, 'getNom')) {
                    $fullname = $user->getNom();
                } elseif (method_exists($user, 'getName')) {
                    $fullname = $user->getName();
                } elseif (isset($user->fullname)) {
                    $fullname = $user->fullname;
                } elseif (isset($user->nom)) {
                    $fullname = $user->nom;
                }
            } elseif (is_array($user)) {
                $fullname = $user['fullname'] ?? $user['nom'] ?? '';
            }
            
            // Si pas de nom trouvé, utiliser l'email ou une valeur par défaut
            if (empty($fullname)) {
                $fullname = explode('@', $email)[0]; // Utiliser la partie avant @
            }
            
            // Générer un token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            try {
                // Sauvegarder le token en base
                $db = $userController->getConnection();
                $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
                $stmt = $db->prepare($sql);
                
                if ($stmt->execute([$email, $token, $expires_at])) {
                    // Envoyer l'email
                    require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/MailController.php';
                    $mailController = new MailController();
                    
                    if ($mailController->sendPasswordResetEmail($email, $fullname, $token)) {
                        $message = "Un email de réinitialisation a été envoyé à <strong>$email</strong>. Vérifiez votre boîte de réception (et les spams).";
                        $message_type = 'success';
                    } else {
                        $message = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
                        $message_type = 'error';
                    }
                } else {
                    $message = "Erreur technique. Veuillez réessayer.";
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                $message = "Erreur: " . $e->getMessage();
                $message_type = 'error';
            }
        } else {
            // Pour des raisons de sécurité, on ne dit pas si l'email existe ou non
            $message = "Si votre email existe dans notre système, vous recevrez un lien de réinitialisation.";
            $message_type = 'success';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - SafeSpace</title>
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
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
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
        
        input[type="email"] {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus {
            outline: none;
            border-color: #4e73df;
        }
        
        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
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
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .links a {
            color: #4e73df;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin: 0 10px;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: #224abe;
            text-decoration: underline;
        }
        
        .info-box {
            background: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding: 15px;
            margin-top: 20px;
            border-radius: 0 5px 5px 0;
            font-size: 13px;
            color: #666;
        }
        
        .info-box ul {
            margin-left: 20px;
            margin-top: 10px;
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
            <h1>🔐 Mot de passe oublié ?</h1>
            <p>Pas de panique ! Nous allons vous aider.</p>
        </div>
        
        <div class="content">
            <?php if($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Adresse email associée à votre compte :</label>
                    <input type="email" id="email" name="email" 
                           placeholder="exemple@email.com" 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                           required>
                </div>
                
                <button type="submit" name="reset_password" class="btn-primary">
                    Envoyer le lien de réinitialisation
                </button>
            </form>
            
            <div class="info-box">
                <p><strong>💡 Comment ça marche :</strong></p>
                <ul>
                    <li>Entrez votre adresse email ci-dessus</li>
                    <li>Recevez un lien sécurisé par email</li>
                    <li>Créez votre nouveau mot de passe</li>
                    <li>Le lien est valable 1 heure</li>
                </ul>
            </div>
            
            <div class="links">
                <a href="login.php">← Retour à la connexion</a>
                <a href="register.php">Créer un compte</a>
            </div>
        </div>
    </div>
    
    <script>
        // Focus sur le champ email au chargement
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
        
        // Validation côté client
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide.');
                return false;
            }
        });
    </script>
</body>
</html>