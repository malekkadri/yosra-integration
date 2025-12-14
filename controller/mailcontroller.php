<?php
// Inclure PHPMailer depuis vendor
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/vendor/PHPMailer-master/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/vendor/PHPMailer-master/src/SMTP.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/vendor/PHPMailer-master/src/Exception.php';

class MailController {
    
    // Configuration SMTP - REMPLACEZ LE PASSWORD
    private $config = [
        'host' => 'smtp.gmail.com',
        'username' => 'fatimaghabbara18@gmail.com',
        'password' => 'olsn idjd yhgt imya', // ⚠️ MOT DE PASSE D'APPLICATION
        'port' => 587,
        'from_email' => 'fatimaghabbara18@gmail.com',
        'from_name' => 'SafeSpace'
    ];
    
    /**
     * Envoyer un email
     */
    public function sendEmail($to, $subject, $body, $toName = '', $attachments = []) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuration du serveur
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['port'];
            $mail->CharSet = 'UTF-8';
            
            // Destinataires
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to, $toName);
            
            // Pièces jointes
            foreach ($attachments as $attachment) {
                if (isset($attachment['path'])) {
                    $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                }
            }
            
            // Contenu
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("MailController Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ⭐⭐ AJOUTEZ CETTE MÉTHODE MANQUANTE ⭐⭐
    /**
     * Tester la connexion Gmail (alias pour testSMTPConnection)
     */
    public function testGmailConnection() {
        return $this->testSMTPConnection();
    }
    
    /**
     * Tester la connexion SMTP
     */
    public function testSMTPConnection() {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['port'];
            $mail->Timeout = 10;
            
            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                return [
                    'success' => true,
                    'message' => '✅ Connexion SMTP réussie !'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '❌ Erreur SMTP: ' . $e->getMessage()
            ];
        }
        
        return [
            'success' => false,
            'message' => '❌ Impossible de se connecter au serveur SMTP'
        ];
    }
    
    /**
     * Tester avec affichage détaillé (debug)
     */
    public function testWithDebug() {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Activer le debug
            $mail->SMTPDebug = 2; // Niveau de debug maximal
            $mail->Debugoutput = function($str, $level) {
                echo "<pre style='background: #f0f0f0; padding: 10px; margin: 5px;'>[$level] $str</pre>";
            };
            
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['port'];
            
            echo "<h3>Connexion en cours...</h3>";
            
            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                return "✅ Connexion SMTP réussie !";
            }
            
        } catch (Exception $e) {
            return "❌ Exception: " . $e->getMessage();
        }
        
        return "❌ Connexion échouée";
    }
    
    /**
     * Email de bienvenue
     */
    public function sendWelcomeEmail($userEmail, $userName) {
        $subject = "🎉 Bienvenue sur SafeSpace, $userName !";
        
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4e73df; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 30px; background: #f8f9fc; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 12px 24px; background: #4e73df; color: white; 
                         text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Bienvenue sur SafeSpace !</h1>
                </div>
                <div class="content">
                    <h2>Bonjour ' . htmlspecialchars($userName) . ',</h2>
                    <p>Merci de vous être inscrit sur <strong>SafeSpace</strong>. Votre compte a été créé avec succès !</p>
                    
                    <p>Vous pouvez maintenant :</p>
                    <ul>
                        <li>✅ Compléter votre profil</li>
                        <li>✅ Prendre rendez-vous avec nos conseillers</li>
                        <li>✅ Partager vos expériences en toute sécurité</li>
                    </ul>
                    
                    <div style="text-align: center;">
                        <a href="http://localhost/SAFEProject/view/frontoffice/login.php" class="button">
                            Accéder à mon compte
                        </a>
                    </div>
                    
                    <p>Si vous avez des questions, n\'hésitez pas à nous contacter.</p>
                    
                    <p>Cordialement,<br>
                    <strong>L\'équipe SafeSpace</strong> ❤️</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' SafeSpace. Tous droits réservés.</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $this->sendEmail($userEmail, $subject, $body, $userName);
    }
    
    /**
     * Email de réinitialisation de mot de passe
     */
    public function sendPasswordResetEmail($userEmail, $userName, $resetToken) {
        $resetLink = "http://localhost/SAFEProject/view/frontoffice/reset_password.php?token=" . urlencode($resetToken);
        
        $subject = "🔒 Réinitialisation de votre mot de passe SafeSpace";
        
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #e74a3b; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 30px; background: #f8f9fc; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 12px 24px; background: #e74a3b; color: white; 
                         text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .code { background: #f1f1f1; padding: 10px; border-radius: 5px; font-family: monospace; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Réinitialisation de mot de passe</h1>
                </div>
                <div class="content">
                    <h2>Bonjour ' . htmlspecialchars($userName) . ',</h2>
                    <p>Vous avez demandé à réinitialiser votre mot de passe SafeSpace.</p>
                    
                    <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                    
                    <div style="text-align: center;">
                        <a href="' . $resetLink . '" class="button">
                            Réinitialiser mon mot de passe
                        </a>
                    </div>
                    
                    <p>Ou copiez ce lien :</p>
                    <div class="code">' . $resetLink . '</div>
                    
                    <p><strong>⚠️ Important :</strong> Ce lien expirera dans 1 heure.</p>
                    
                    <p>Si vous n\'avez pas demandé cette réinitialisation, ignorez simplement cet email.</p>
                    
                    <p>Cordialement,<br>
                    <strong>L\'équipe SafeSpace</strong> 🔐</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' SafeSpace. Tous droits réservés.</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $this->sendEmail($userEmail, $subject, $body, $userName);
    }
    
    /**
     * Email de confirmation de consultation
     */
    public function sendConsultationConfirmation($userEmail, $userName, $advisorName, $consultationDate, $consultationType) {
        $subject = "✅ Confirmation de votre consultation avec $advisorName";
        
        $body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1cc88a; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 30px; background: #f8f9fc; border-radius: 0 0 10px 10px; }
                .info-box { background: white; border-left: 4px solid #1cc88a; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Consultation confirmée !</h1>
                </div>
                <div class="content">
                    <h2>Bonjour ' . htmlspecialchars($userName) . ',</h2>
                    <p>Votre consultation a été confirmée avec succès.</p>
                    
                    <div class="info-box">
                        <h3>📋 Détails de la consultation :</h3>
                        <p><strong>Conseiller :</strong> ' . htmlspecialchars($advisorName) . '</p>
                        <p><strong>Date :</strong> ' . htmlspecialchars($consultationDate) . '</p>
                        <p><strong>Type :</strong> ' . htmlspecialchars($consultationType) . '</p>
                    </div>
                    
                    <p><strong>💡 Conseil :</strong> Préparez vos questions à l\'avance pour tirer le meilleur parti de votre séance.</p>
                    
                    <p>Pour modifier ou annuler votre rendez-vous, connectez-vous à votre compte.</p>
                    
                    <p>Cordialement,<br>
                    <strong>L\'équipe SafeSpace</strong> 💼</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' SafeSpace. Tous droits réservés.</p>
                    <p>Cet email a été envoyé automatiquement.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $this->sendEmail($userEmail, $subject, $body, $userName);
    }
    
    /**
     * Changer la configuration SMTP
     */
    public function setSMTPConfig($host, $username, $password, $port = 587) {
        $this->config['host'] = $host;
        $this->config['username'] = $username;
        $this->config['password'] = $password;
        $this->config['port'] = $port;
    }
}
?>