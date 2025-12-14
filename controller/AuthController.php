<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../controller/usercontroller.php';

class AuthController {
    private $userController;
    private $maxAttempts = 5; // Nombre maximum de tentatives
    private $lockoutTime = 900; // 15 minutes en secondes

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->userController = new UserController();
    }

    // 🆕 MÉTHODE POUR GÉOLOCALISATION IP (ipapi.co)
    private function getIpGeolocation($ip) {
        try {
            // API ipapi.co - simple et gratuit
            $url = "https://ipapi.co/{$ip}/json/";
            
            // Utiliser file_get_contents (plus simple)
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3, // Timeout de 3 secondes
                    'header' => "User-Agent: SafeSpace-App/1.0\r\n"
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                
                // Vérifier si l'API a retourné une erreur
                if (isset($data['error'])) {
                    error_log("❌ Erreur ipapi.co: " . ($data['reason'] ?? 'Unknown error'));
                    return null;
                }
                
                return [
                    'ip' => $data['ip'] ?? $ip,
                    'city' => $data['city'] ?? 'Inconnu',
                    'region' => $data['region'] ?? 'Inconnu',
                    'country' => $data['country_name'] ?? 'Inconnu',
                    'country_code' => $data['country_code'] ?? 'XX',
                    'timezone' => $data['timezone'] ?? 'UTC',
                    'currency' => $data['currency'] ?? 'EUR',
                    'languages' => $data['languages'] ?? 'fr',
                    'isp' => $data['org'] ?? 'Inconnu',
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null
                ];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("❌ Exception géolocalisation: " . $e->getMessage());
            return null;
        }
    }

    // 🆕 MÉTHODE POUR DÉTECTER LE NAVIGATEUR
    private function detectBrowser() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edg') === false) {
            return 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) {
            return 'Safari';
        } elseif (stripos($userAgent, 'Edg') !== false) {
            return 'Edge';
        } elseif (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'OPR') !== false) {
            return 'Opera';
        } else {
            return 'Navigateur';
        }
    }

    // 🆕 MÉTHODE POUR DÉTECTER LE SYSTÈME D'EXPLOITATION
    private function detectOS() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (stripos($userAgent, 'Windows') !== false) {
            return 'Windows';
        } elseif (stripos($userAgent, 'Mac') !== false) {
            return 'macOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            return 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            return 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            return 'iOS';
        } else {
            return 'OS';
        }
    }

    // MÉTHODE REGISTER
    public function register($nom, $email, $password, $role = 'membre') {
        if (empty($nom) || empty($email) || empty($password)) {
            return "Tous les champs sont obligatoires.";
        }

        $email = trim($email);
        $email = strtolower($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Format d'email invalide.";
        }

        if (strlen($password) < 6) {
            return "Le mot de passe doit contenir au moins 6 caractères.";
        }

        $allowedRoles = ['membre', 'conseilleur', 'admin'];
        if (!in_array($role, $allowedRoles)) {
            $role = 'membre';
        }

        try {
            $existing = $this->userController->getUserByEmail($email);
            if ($existing) {
                return "Cet email est déjà utilisé.";
            }

            $user = new User($nom, $email, $password, $role, "en attente");

            if ($this->userController->addUser($user)) {
                return true;
            } else {
                return "Erreur lors de l'inscription.";
            }

        } catch (PDOException $e) {
            error_log("❌ Erreur PDO register: " . $e->getMessage());
            return "Erreur de base de données.";
        } catch (Exception $e) {
            error_log("❌ Erreur register: " . $e->getMessage());
            return "Erreur lors de l'inscription.";
        }
    }

    // MÉTHODE LOGIN AVEC GÉOLOCALISATION ET LIMITATION
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return "Email et mot de passe requis.";
        }

        try {
            $email = trim($email);
            $email = strtolower($email);

            // Vérifier les tentatives dans la session
            $ip = $_SERVER['REMOTE_ADDR'];
            $attemptKey = 'login_attempts_' . md5($email . $ip);
            
            // Initialiser le compteur si non existant
            if (!isset($_SESSION[$attemptKey])) {
                $_SESSION[$attemptKey] = [
                    'count' => 0,
                    'last_attempt' => time(),
                    'blocked_until' => 0
                ];
            }

            $attempts = $_SESSION[$attemptKey];

            // Vérifier si bloqué
            if ($attempts['blocked_until'] > time()) {
                $remaining = $attempts['blocked_until'] - time();
                $minutes = ceil($remaining / 60);
                return "Trop de tentatives. Réessayez dans " . $minutes . " minute(s)";
            }

            $userData = $this->userController->getUserByEmail($email);
            
            if (!$userData) {
                // Incrémenter les tentatives
                $_SESSION[$attemptKey]['count']++;
                $_SESSION[$attemptKey]['last_attempt'] = time();
                
                // Bloquer après 5 tentatives
                if ($_SESSION[$attemptKey]['count'] >= $this->maxAttempts) {
                    $_SESSION[$attemptKey]['blocked_until'] = time() + $this->lockoutTime;
                    return "Trop de tentatives échouées. Compte bloqué pour 15 minutes.";
                }
                
                $remainingAttempts = $this->maxAttempts - $_SESSION[$attemptKey]['count'];
                return "Email ou mot de passe incorrect. Tentatives restantes: " . $remainingAttempts;
            }

            if (!password_verify($password, $userData['password'])) {
                // Incrémenter les tentatives
                $_SESSION[$attemptKey]['count']++;
                $_SESSION[$attemptKey]['last_attempt'] = time();
                
                // Bloquer après 5 tentatives
                if ($_SESSION[$attemptKey]['count'] >= $this->maxAttempts) {
                    $_SESSION[$attemptKey]['blocked_until'] = time() + $this->lockoutTime;
                    return "Trop de tentatives échouées. Compte bloqué pour 15 minutes.";
                }
                
                $remainingAttempts = $this->maxAttempts - $_SESSION[$attemptKey]['count'];
                return "Email ou mot de passe incorrect. Tentatives restantes: " . $remainingAttempts;
            }

            // ✅ CONNEXION RÉUSSIE - Récupérer les infos de géolocalisation
            
            // 1. Détecter navigateur et OS
            $browser = $this->detectBrowser();
            $os = $this->detectOS();
            
            // 2. Récupérer la géolocalisation (asynchrone pour ne pas ralentir)
            $geoData = $this->getIpGeolocation($ip);

            // 3. Si succès, réinitialiser les tentatives
            unset($_SESSION[$attemptKey]);

            // Vérifier le statut - standardisé sur 'actif'
            if ($userData['status'] !== 'actif') {
                // Pour l'admin, on autorise même si en attente
                if ($userData['role'] === 'admin') {
                    // Activer automatiquement l'admin
                    $this->userController->updateUserStatus($userData['id'], 'actif');
                    $userData['status'] = 'actif';
                } else {
                    return "Votre compte est en attente d'approbation.";
                }
            }

            // Stocker les informations de session (avec géolocalisation)
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['user_role'] = $userData['role'];
            $_SESSION['fullname'] = $userData['nom'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_status'] = $userData['status'];
            $_SESSION['last_login'] = time();
            $_SESSION['login_ip'] = $ip;
            $_SESSION['login_browser'] = $browser;
            $_SESSION['login_os'] = $os;
            
            // Stocker la géolocalisation si disponible
            if ($geoData) {
                $_SESSION['login_geo'] = $geoData;
            }

            // Redirection selon le rôle
            if ($userData['role'] === 'admin') {
                header('Location: /SAFEProject/view/backoffice/index.php');
                exit();
            } elseif ($userData['role'] === 'conseilleur') {
                header('Location: /SAFEProject/view/backoffice/adviser_dashboard.php');
                exit();
            } elseif ($userData['role'] === 'membre') {
                header('Location: /SAFEProject/view/backoffice/member_dashboard.php');
                exit();
            } else {
                header('Location: /SAFEProject/view/backoffice/member_dashboard.php');
                exit();
            }
            
        } catch (PDOException $e) {
            error_log("❌ Erreur PDO login: " . $e->getMessage());
            return "Erreur de connexion: " . $e->getMessage();
        } catch (Exception $e) {
            error_log("❌ Erreur login: " . $e->getMessage());
            return "Erreur lors de la connexion.";
        }
    }

    // 🆕 MÉTHODE POUR RÉCUPÉRER LA DERNIÈRE CONNEXION
    public function getLastLoginInfo() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        // Si la géolocalisation n'est pas en session, on essaie de la récupérer
        if (!isset($_SESSION['login_geo']) && isset($_SESSION['login_ip'])) {
            $_SESSION['login_geo'] = $this->getIpGeolocation($_SESSION['login_ip']);
            
            // Si pas encore détectés, détecter navigateur et OS
            if (!isset($_SESSION['login_browser'])) {
                $_SESSION['login_browser'] = $this->detectBrowser();
            }
            if (!isset($_SESSION['login_os'])) {
                $_SESSION['login_os'] = $this->detectOS();
            }
        }
        
        return [
            'ip' => $_SESSION['login_ip'] ?? null,
            'browser' => $_SESSION['login_browser'] ?? null,
            'os' => $_SESSION['login_os'] ?? null,
            'geo' => $_SESSION['login_geo'] ?? null,
            'time' => $_SESSION['last_login'] ?? null
        ];
    }

    // 🆕 MÉTHODE POUR AFFICHER UN MESSAGE PERSONNALISÉ
    public function getWelcomeMessage() {
        if (!$this->isLoggedIn()) {
            return "Bienvenue sur SafeSpace";
        }
        
        $geo = $_SESSION['login_geo'] ?? null;
        $browser = $_SESSION['login_browser'] ?? null;
        
        if ($geo && $geo['city'] !== 'Inconnu') {
            return "Vous êtes connecté depuis " . $geo['city'] . ", " . $geo['country'] . 
                   " via " . $browser;
        } elseif ($browser) {
            return "Bienvenue sur SafeSpace ! Connexion via " . $browser;
        } else {
            return "Bienvenue sur SafeSpace !";
        }
    }

    // 🆕 MÉTHODE POUR AFFICHER UNE ALERTE DE SÉCURITÉ
    public function getSecurityAlert() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $geo = $_SESSION['login_geo'] ?? null;
        $lastLogin = $_SESSION['last_login'] ?? null;
        
        if ($geo && $lastLogin) {
            // Vérifier si la connexion est récente (< 1 heure)
            $timeDiff = time() - $lastLogin;
            if ($timeDiff < 3600) {
                return [
                    'type' => 'info',
                    'message' => "Nouvelle connexion détectée depuis " . $geo['city'] . ", " . $geo['country'],
                    'time' => date('H:i', $lastLogin)
                ];
            }
        }
        
        return null;
    }

    // MÉTHODE LOGOUT
    public function logout() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        header('Location: /SAFEProject/view/frontoffice/login.php');
        exit();
    }

    // MÉTHODES DE VÉRIFICATION
    public function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return $this->isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public function isConseilleur() {
        return $this->isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'conseilleur';
    }

    public function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return $this->userController->getUserById($_SESSION['user_id']);
    }

    public function getCurrentUserInfo() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'fullname' => $_SESSION['fullname'] ?? 'Utilisateur',
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }

    // MÉTHODES DE SÉCURITÉ
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: /SAFEProject/view/frontoffice/login.php');
            exit();
        }
    }

    public function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            header('Location: /SAFEProject/view/frontoffice/index.php');
            exit();
        }
    }

    public function requireConseilleur() {
        $this->requireAuth();
        if (!$this->isConseilleur() && !$this->isAdmin()) {
            header('Location: /SAFEProject/view/frontoffice/index.php');
            exit();
        }
    }

    // MÉTHODE POUR CRÉER UN ADMIN PAR DÉFAUT
    public function createDefaultAdmin() {
        try {
            $adminEmail = 'admin@safespace.com';
            
            $existing = $this->userController->getUserByEmail($adminEmail);
            
            if (!$existing) {
                $adminPassword = 'admin123';
                $user = new User('Administrateur', $adminEmail, $adminPassword, 'admin', 'actif');
                
                if ($this->userController->addUser($user)) {
                    return "Admin créé avec succès. Email: $adminEmail, Mot de passe: $adminPassword";
                } else {
                    return "Erreur lors de la création de l'admin.";
                }
            }
            
            return "L'administrateur existe déjà.";
            
        } catch (Exception $e) {
            error_log("❌ Erreur createDefaultAdmin: " . $e->getMessage());
            return "Erreur: " . $e->getMessage();
        }
    }
    
    // MÉTHODE POUR RÉINITIALISER LES TENTATIVES
    public function resetLoginAttempts($email) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $attemptKey = 'login_attempts_' . md5($email . $ip);
        unset($_SESSION[$attemptKey]);
        return true;
    }
}
?>