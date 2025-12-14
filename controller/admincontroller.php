<?php
require_once __DIR__ . '/../controller/usercontroller.php';

class AdminController {
    private $userController;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ../frontoffice/login.php');
            exit();
        }

        $this->userController = new UserController();
    }

    // RETOURNE DES OBJETS USER
    public function getAllUsers() {
        return $this->userController->listUsers();
    }

    // RETOURNE DES TABLEAUX
    public function getAllUsersArray() {
        return $this->userController->listUsersAsArray();
    }

    public function approveUser($user_id) {
        // 1. Approuver l'utilisateur
        $result = $this->userController->approveUser($user_id);
        
        if ($result) {
            // 2. Récupérer les infos de l'utilisateur
            $user = $this->userController->getUserById($user_id);
            
            if ($user) {
                // 3. Envoyer un email d'approbation
                $this->sendApprovalEmail($user);
                
                // 4. Notifier l'admin (optionnel)
                $this->notifyAdminApproval($user);
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * Envoyer un email d'approbation à l'utilisateur
     */
    private function sendApprovalEmail($user) {
        try {
            require_once 'MailController.php';
            $mailController = new MailController();
            
            // Récupérer le nom et l'email selon la structure de l'objet User
            $fullname = '';
            $email = '';
            
            // Si c'est un objet User
            if (is_object($user)) {
                // Vérifiez les méthodes disponibles dans votre classe User
                if (method_exists($user, 'getFullname')) {
                    $fullname = $user->getFullname();
                } elseif (method_exists($user, 'getNom')) {
                    $fullname = $user->getNom();
                } elseif (property_exists($user, 'fullname')) {
                    $fullname = $user->fullname;
                } elseif (property_exists($user, 'nom')) {
                    $fullname = $user->nom;
                }
                
                if (method_exists($user, 'getEmail')) {
                    $email = $user->getEmail();
                } elseif (property_exists($user, 'email')) {
                    $email = $user->email;
                }
            }
            
            if (empty($email)) {
                error_log("AdminController: Email non trouvé pour l'utilisateur ID: " . (method_exists($user, 'getId') ? $user->getId() : 'N/A'));
                return false;
            }
            
            // Si pas de nom, utiliser l'email
            if (empty($fullname)) {
                $fullname = explode('@', $email)[0];
            }
            
            $subject = "✅ Votre compte SafeSpace a été approuvé !";
            
            $body = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #1cc88a; color: white; padding: 25px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background: #f8f9fc; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; padding: 12px 24px; background: #1cc88a; color: white; 
                            text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: 600; }
                    .info-box { background: white; border-left: 4px solid #1cc88a; padding: 15px; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🎉 Compte Approuvé !</h1>
                    </div>
                    <div class="content">
                        <h2>Bonjour ' . htmlspecialchars($fullname) . ',</h2>
                        <p>Nous avons le plaisir de vous informer que <strong>votre compte SafeSpace a été approuvé</strong> !</p>
                        
                        <div class="info-box">
                            <h3>📋 Informations de compte :</h3>
                            <p><strong>Statut :</strong> ✅ <span style="color: #1cc88a;">Actif et approuvé</span></p>
                            <p><strong>Date d\'approbation :</strong> ' . date('d/m/Y à H:i') . '</p>
                            <p><strong>Email :</strong> ' . htmlspecialchars($email) . '</p>
                        </div>
                        
                        <p>Vous pouvez maintenant accéder à toutes les fonctionnalités de SafeSpace :</p>
                        <ul>
                            <li>✅ Compléter votre profil</li>
                            <li>✅ Consulter les ressources</li>
                            <li>✅ Prendre rendez-vous avec nos conseillers</li>
                            <li>✅ Participer à la communauté</li>
                        </ul>
                        
                        <div style="text-align: center;">
                            <a href="http://localhost/SAFEProject/view/frontoffice/login.php" class="button">
                                🚀 Me connecter à SafeSpace
                            </a>
                        </div>
                        
                        <p>Si vous avez des questions ou besoin d\'aide, n\'hésitez pas à nous contacter.</p>
                        
                        <p>Cordialement,<br>
                        <strong>L\'équipe SafeSpace</strong> 💚</p>
                    </div>
                    <div class="footer">
                        <p>© ' . date('Y') . ' SafeSpace. Tous droits réservés.</p>
                        <p>Cet email a été envoyé automatiquement.</p>
                    </div>
                </div>
            </body>
            </html>';
            
            return $mailController->sendEmail($email, $subject, $body, $fullname);
            
        } catch (Exception $e) {
            error_log("AdminController::sendApprovalEmail() - Erreur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notifier l'admin de l'approbation (CORRIGÉE)
     */
    private function notifyAdminApproval($user) {
        try {
            require_once 'MailController.php';
            $mailController = new MailController();
            
            $admin_email = "admin@safespace.com"; // À configurer
            
            // CORRECTION : Traiter $user comme un objet, pas un tableau
            $fullname = '';
            $email = '';
            $user_id = '';
            
            if (is_object($user)) {
                // Récupérer le nom
                if (method_exists($user, 'getFullname')) {
                    $fullname = $user->getFullname();
                } elseif (method_exists($user, 'getNom')) {
                    $fullname = $user->getNom();
                } elseif (property_exists($user, 'fullname')) {
                    $fullname = $user->fullname;
                } elseif (property_exists($user, 'nom')) {
                    $fullname = $user->nom;
                }
                
                // Récupérer l'email
                if (method_exists($user, 'getEmail')) {
                    $email = $user->getEmail();
                } elseif (property_exists($user, 'email')) {
                    $email = $user->email;
                }
                
                // Récupérer l'ID
                if (method_exists($user, 'getId')) {
                    $user_id = $user->getId();
                } elseif (property_exists($user, 'id')) {
                    $user_id = $user->id;
                }
            }
            
            // Si vide, utiliser des valeurs par défaut
            if (empty($fullname)) $fullname = 'N/A';
            if (empty($email)) $email = 'N/A';
            if (empty($user_id)) $user_id = 'N/A';
            
            $subject = "📋 Compte approuvé - SafeSpace Admin";
            $body = "
            <h2>Compte utilisateur approuvé</h2>
            <p><strong>Utilisateur :</strong> " . htmlspecialchars($fullname) . "</p>
            <p><strong>Email :</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Date d'approbation :</strong> " . date('d/m/Y H:i') . "</p>
            <p><strong>Action effectuée par :</strong> Administrateur</p>
            ";
            
            $mailController->sendEmail($admin_email, $subject, $body, "Administrateur SafeSpace");
            
        } catch (Exception $e) {
            error_log("Erreur notification admin: " . $e->getMessage());
        }
    }

    public function blockUser($user_id) {
        // 1. Bloquer l'utilisateur
        $result = $this->userController->blockUser($user_id);
        
        if ($result) {
            // 2. Récupérer les infos de l'utilisateur
            $user = $this->userController->getUserById($user_id);
            
            if ($user) {
                // 3. Envoyer un email de blocage
                $this->sendBlockEmail($user);
                
                // 4. Notifier l'admin (optionnel) - CORRIGÉE
                $this->notifyAdminBlock($user);
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * Envoyer un email de blocage à l'utilisateur
     */
    private function sendBlockEmail($user) {
        try {
            require_once 'MailController.php';
            $mailController = new MailController();
            
            // Récupérer le nom et l'email selon la structure de l'objet User
            $fullname = '';
            $email = '';
            
            // Si c'est un objet User
            if (is_object($user)) {
                // Vérifiez les méthodes disponibles dans votre classe User
                if (method_exists($user, 'getFullname')) {
                    $fullname = $user->getFullname();
                } elseif (method_exists($user, 'getNom')) {
                    $fullname = $user->getNom();
                } elseif (property_exists($user, 'fullname')) {
                    $fullname = $user->fullname;
                } elseif (property_exists($user, 'nom')) {
                    $fullname = $user->nom;
                }
                
                if (method_exists($user, 'getEmail')) {
                    $email = $user->getEmail();
                } elseif (property_exists($user, 'email')) {
                    $email = $user->email;
                }
            }
            
            if (empty($email)) {
                error_log("AdminController: Email non trouvé pour l'utilisateur ID: " . (method_exists($user, 'getId') ? $user->getId() : 'N/A'));
                return false;
            }
            
            // Si pas de nom, utiliser l'email
            if (empty($fullname)) {
                $fullname = explode('@', $email)[0];
            }
            
            $subject = "⚠️ Votre compte SafeSpace a été bloqué";
            
            $body = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #e74a3b; color: white; padding: 25px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background: #f8f9fc; border-radius: 0 0 10px 10px; }
                    .info-box { background: white; border-left: 4px solid #e74a3b; padding: 15px; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                    .contact-info { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>⚠️ Compte Bloqué</h1>
                    </div>
                    <div class="content">
                        <h2>Bonjour ' . htmlspecialchars($fullname) . ',</h2>
                        <p>Votre compte SafeSpace a été <strong>temporairement bloqué</strong> par notre équipe d\'administration.</p>
                        
                        <div class="info-box">
                            <h3>📋 Informations :</h3>
                            <p><strong>Statut du compte :</strong> 🔴 <span style="color: #e74a3b;">Bloqué</span></p>
                            <p><strong>Date du blocage :</strong> ' . date('d/m/Y à H:i') . '</p>
                            <p><strong>Email :</strong> ' . htmlspecialchars($email) . '</p>
                        </div>
                        
                        <div class="contact-info">
                            <h4>📞 Pourquoi mon compte est-il bloqué ?</h4>
                            <p>Les comptes peuvent être bloqués pour plusieurs raisons :</p>
                            <ul>
                                <li>Violation des conditions d\'utilisation</li>
                                <li>Activité suspecte détectée</li>
                                <li>Signalement par d\'autres utilisateurs</li>
                                <li>Problème technique nécessitant vérification</li>
                            </ul>
                        </div>
                        
                        <h3>🔓 Comment débloquer mon compte ?</h3>
                        <p>Pour demander le déblocage de votre compte ou obtenir plus d\'informations :</p>
                        <ol>
                            <li>Contactez notre support à <strong>support@safespace.com</strong></li>
                            <li>Précisez votre adresse email : ' . htmlspecialchars($email) . '</li>
                            <li>Expliquez brièvement la situation</li>
                        </ol>
                        
                        <p>Notre équipe examinera votre demande dans les plus brefs délais.</p>
                        
                        <p>Cordialement,<br>
                        <strong>L\'équipe de modération SafeSpace</strong> 🔒</p>
                    </div>
                    <div class="footer">
                        <p>© ' . date('Y') . ' SafeSpace. Tous droits réservés.</p>
                        <p>Cet email a été envoyé automatiquement.</p>
                    </div>
                </div>
            </body>
            </html>';
            
            return $mailController->sendEmail($email, $subject, $body, $fullname);
            
        } catch (Exception $e) {
            error_log("AdminController::sendBlockEmail() - Erreur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notifier l'admin du blocage (CORRIGÉE)
     */
    private function notifyAdminBlock($user) {
        try {
            require_once 'MailController.php';
            $mailController = new MailController();
            
            $admin_email = "admin@safespace.com"; // À configurer
            
            // CORRECTION : Traiter $user comme un objet, pas un tableau
            $fullname = '';
            $email = '';
            $user_id = '';
            
            if (is_object($user)) {
                // Récupérer le nom
                if (method_exists($user, 'getFullname')) {
                    $fullname = $user->getFullname();
                } elseif (method_exists($user, 'getNom')) {
                    $fullname = $user->getNom();
                } elseif (property_exists($user, 'fullname')) {
                    $fullname = $user->fullname;
                } elseif (property_exists($user, 'nom')) {
                    $fullname = $user->nom;
                }
                
                // Récupérer l'email
                if (method_exists($user, 'getEmail')) {
                    $email = $user->getEmail();
                } elseif (property_exists($user, 'email')) {
                    $email = $user->email;
                }
                
                // Récupérer l'ID
                if (method_exists($user, 'getId')) {
                    $user_id = $user->getId();
                } elseif (property_exists($user, 'id')) {
                    $user_id = $user->id;
                }
            }
            
            // Si vide, utiliser des valeurs par défaut
            if (empty($fullname)) $fullname = 'N/A';
            if (empty($email)) $email = 'N/A';
            if (empty($user_id)) $user_id = 'N/A';
            
            $subject = "🚫 Compte bloqué - SafeSpace Admin";
            $body = "
            <h2>Compte utilisateur bloqué</h2>
            <p><strong>Utilisateur :</strong> " . htmlspecialchars($fullname) . "</p>
            <p><strong>Email :</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>ID Utilisateur :</strong> #" . htmlspecialchars($user_id) . "</p>
            <p><strong>Date de blocage :</strong> " . date('d/m/Y H:i') . "</p>
            <p><strong>Action effectuée par :</strong> Administrateur</p>
            ";
            
            $mailController->sendEmail($admin_email, $subject, $body, "Administrateur SafeSpace");
            
        } catch (Exception $e) {
            error_log("Erreur notification admin blocage: " . $e->getMessage());
        }
    }

    public function deleteUser($id) {
        return $this->userController->deleteUser($id);
    }

    public function getUser($id) {
        return $this->userController->getUser($id);
    }

    public function getRatingStats() {
        try {
            $conn = $this->userController->getConnection();
            
            $stmt = $conn->query("SHOW TABLES LIKE 'ratings'");
            if (!$stmt->fetch()) {
                return $this->getDemoRatingStats();
            }
            
            $stats = [];
            
            $sql1 = "SELECT COUNT(*) as total_ratings FROM ratings";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->execute();
            $totalRatings = $stmt1->fetch(PDO::FETCH_ASSOC);
            $stats['total_ratings'] = $totalRatings['total_ratings'] ?? 0;
            
            $sql2 = "SELECT AVG(rating) as average_rating FROM ratings";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->execute();
            $averageRating = $stmt2->fetch(PDO::FETCH_ASSOC);
            $stats['average_rating'] = round($averageRating['average_rating'] ?? 0, 1);
            
            $sql3 = "SELECT rating, COUNT(*) as count FROM ratings GROUP BY rating ORDER BY rating";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->execute();
            $stats['distribution'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);
            
            $sql4 = "SELECT COUNT(DISTINCT user_id) as users_with_ratings FROM ratings";
            $stmt4 = $conn->prepare($sql4);
            $stmt4->execute();
            $usersWithRatings = $stmt4->fetch(PDO::FETCH_ASSOC);
            $stats['users_with_ratings'] = $usersWithRatings['users_with_ratings'] ?? 0;
            
            $stats['demo_data'] = false;
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("AdminController::getRatingStats() - Erreur: " . $e->getMessage());
            return $this->getDemoRatingStats();
        }
    }

    private function getDemoRatingStats() {
        return [
            'total_ratings' => 42,
            'average_rating' => 4.2,
            'distribution' => [
                ['rating' => 5, 'count' => 20],
                ['rating' => 4, 'count' => 15],
                ['rating' => 3, 'count' => 5],
                ['rating' => 2, 'count' => 1],
                ['rating' => 1, 'count' => 1]
            ],
            'users_with_ratings' => 38,
            'demo_data' => true
        ];
    }
}
?>