<?php
// generate_password.php - Point d'entrée pour le générateur IA

session_start();
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AIPasswordController.php';

// Vérifier l'origine de la requête (sécurité)
$allowedOrigins = ['http://localhost', 'http://127.0.0.1'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');

// Limiter le taux de requêtes (anti-bruteforce)
if (!isset($_SESSION['pwd_gen_requests'])) {
    $_SESSION['pwd_gen_requests'] = 0;
    $_SESSION['pwd_gen_last_time'] = time();
}

$_SESSION['pwd_gen_requests']++;
$timePassed = time() - $_SESSION['pwd_gen_last_time'];

if ($timePassed > 3600) { // Réinitialiser chaque heure
    $_SESSION['pwd_gen_requests'] = 1;
    $_SESSION['pwd_gen_last_time'] = time();
} elseif ($_SESSION['pwd_gen_requests'] > 50) { // Max 50 requêtes/heure
    echo json_encode([
        'success' => false,
        'message' => 'Trop de requêtes. Veuillez patienter.'
    ]);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'generate';
    
    $aiController = new AIPasswordController();
    $response = [];
    
    switch ($action) {
        case 'generate':
            $length = intval($input['length'] ?? 12);
            $includeUpper = filter_var($input['includeUpper'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $includeNumbers = filter_var($input['includeNumbers'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $includeSymbols = filter_var($input['includeSymbols'] ?? true, FILTER_VALIDATE_BOOLEAN);
            
            // Limites de sécurité
            $length = max(6, min(32, $length)); // Entre 6 et 32 caractères
            
            $result = $aiController->generateSecurePassword(
                $length,
                true, // Toujours majuscules
                true, // Toujours minuscules
                $includeNumbers,
                $includeSymbols
            );
            
            $response = $result;
            break;
            
        case 'themed':
            $theme = htmlspecialchars($input['theme'] ?? 'tech');
            $result = $aiController->generateThemedPassword($theme);
            $response = $result;
            break;
            
        case 'checkLeak':
            $password = $input['password'] ?? '';
            if (!empty($password) && strlen($password) > 3) {
                $result = $aiController->checkPasswordLeak($password);
                $response = $result;
            } else {
                $response = [
                    'leaked' => false,
                    'message' => 'Mot de passe trop court pour vérification'
                ];
            }
            break;
            
        default:
            $response = [
                'success' => false,
                'message' => 'Action non reconnue'
            ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Password Generator API Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>