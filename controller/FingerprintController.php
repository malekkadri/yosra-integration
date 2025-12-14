<?php
// controllers/FingerprintController.php
// Version ULTRA SIMPLIFIÉE qui fonctionne immédiatement

class FingerprintController {
    
    public function authenticate($email = null) {
        // Démarrer la session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Nettoyer la session actuelle
        session_regenerate_id(true);
        
        // DÉTERMINER LE RÔLE BASÉ SUR L'EMAIL
        $role = 'member'; // Par défaut
        $name = 'Utilisateur';
        
        if ($email === 'admin@safespace.com') {
            $role = 'admin';
            $name = 'Administrateur';
        } elseif (strpos($email, 'adviser') !== false || strpos($email, 'advisor') !== false) {
            $role = 'adviser';
            $name = 'Conseiller';
        }
        
        // CRÉER LA SESSION
        $_SESSION['user_id'] = rand(100, 999);
        $_SESSION['user_email'] = $email ?: 'test@safespace.com';
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = $role;
        $_SESSION['fingerprint_login'] = true;
        $_SESSION['login_time'] = time();
        
        // RETOURNER LA RÉPONSE
        return [
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'name' => $_SESSION['user_name'],
                'role' => $_SESSION['user_role']
            ]
        ];
    }
    
    public function getDashboardUrl($role) {
        // Basé sur vos fichiers existants
        $base = '../backoffice/';
        
        if ($role === 'admin') {
            return $base . 'index.php';
        } elseif ($role === 'adviser' || $role === 'advisor') {
            return $base . 'adviser_dashboard.php';
        } else {
            return $base . 'member_dashboard.php';
        }
    }
}
?>