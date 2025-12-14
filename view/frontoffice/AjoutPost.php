<?php
/**
 * ==========================================
 * MODÉRATION DE CONTENU AVEC GEMINI API
 * ==========================================
 * 
 * IMPORTANT: Avant d'utiliser ce script:
 * 1. Allez sur https://aistudio.google.com
 * 2. Cliquez "Get API Key"
 * 3. Créez un nouveau projet
 * 4. Copiez la clé API ENTIÈREMENT
 * 5. Remplacez la valeur de $gemini_api_key ci-dessous
 */

include '../../controller/PostC.php';

// =====================================================
// 🔑 CONFIGURATION OBLIGATOIRE - À REMPLIR ABSOLUMENT
// =====================================================
// Votre clé API Gemini (obtenue sur https://aistudio.google.com)
// La clé commence par: AIzaSy...
$gemini_api_key = 'AIzaSyC8jINuXEdInD0HbRpe6WYaHHAHqasVNJ0'; 

// Modes
$debug_mode = true; // Mettez à false en production
$log_file = __DIR__ . '/moderation_debug.log';

// =====================================================

// Récupération des données
$user_id = $_POST['user_id'] ?? null;
$author = $_POST['author'] ?? '';
$message = $_POST['message'] ?? '';  
$time = $_POST['currentTime'] ?? date('Y-m-d H:i:s');
$image_path = null;
$status = "en attente";
$error_message = '';

// ========== LOGGING ==========
function log_debug($msg) {
    global $log_file, $debug_mode;
    if ($debug_mode) {
        $ts = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$ts] $msg\n", FILE_APPEND);
    }
}

// ========== VÉRIFICATION CONTENU AVEC GEMINI ==========
function check_gemini_moderation($text, $apiKey) {
    global $log_file;
    
    log_debug("\n========== VÉRIFICATION GEMINI LANCÉE ==========");
    log_debug("Texte à analyser: " . substr($text, 0, 80) . "...");
    
    // ⚠️ ÉTAPE 1: Vérifier la clé API
    if (empty($apiKey)) {
        log_debug("❌ ERREUR: Clé API est vide!");
        log_debug("Rendez-vous sur https://aistudio.google.com et copiez votre clé API");
        return "ERREUR_API";
    }
    
    if ($apiKey === 'VOTRE_CLE_API_GEMINI_ICI') {
        log_debug("❌ ERREUR: Clé API par défaut! Vous devez la remplacer!");
        log_debug("📌 Guide: Allez sur https://aistudio.google.com");
        return "ERREUR_API";
    }
    
    if (strlen($apiKey) < 30) {
        log_debug("❌ ERREUR: Clé API trop courte ($apiKey)");
        log_debug("📌 Une vraie clé fait environ 39 caractères");
        return "ERREUR_API";
    }
    
    if (strpos($apiKey, 'AIzaSy') !== 0) {
        log_debug("❌ ERREUR: Clé API invalide (ne commence pas par AIzaSy)");
        return "ERREUR_API";
    }
    
    log_debug("✅ Clé API valide");
    
    // ⚠️ ÉTAPE 2: Vérifier curl
    if (!function_exists('curl_init')) {
        log_debug("❌ ERREUR: Extension curl non active");
        log_debug("📌 Contactez votre hébergeur pour activer curl");
        return "ERREUR_API";
    }
    
    log_debug("✅ curl disponible");
    
    // ⚠️ ÉTAPE 3: Construire la requête
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . urlencode($apiKey);
    
    $prompt = "You are a strict content moderator. Analyze this text for: vulgarity, insults, hate speech, threats, offensive language, disrespect.\n\nRespond with ONLY ONE WORD:\n- YES if the text contains inappropriate content\n- NO if the text is acceptable\n\nText: \"" . addslashes($text) . "\"";
    
    $payload = json_encode([
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ],
        "generationConfig" => [
            "temperature" => 0.1,
            "maxOutputTokens" => 5
        ]
    ]);
    
    log_debug("Payload JSON préparé (" . strlen($payload) . " bytes)");
    
    // ⚠️ ÉTAPE 4: Faire la requête
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    log_debug("Envoi vers Gemini API...");
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // ⚠️ ÉTAPE 5: Vérifier la réponse
    log_debug("Réponse HTTP: $httpCode");
    
    if ($response === false) {
        log_debug("❌ ERREUR CURL: $curlError");
        return "ERREUR_API";
    }
    
    if ($httpCode === 401) {
        log_debug("❌ ERREUR 401: Clé API invalide ou expirée");
        log_debug("Réponse: " . substr($response, 0, 200));
        return "ERREUR_API";
    }
    
    if ($httpCode === 403) {
        log_debug("❌ ERREUR 403: Accès refusé");
        log_debug("Réponse: " . substr($response, 0, 200));
        return "ERREUR_API";
    }
    
    if ($httpCode === 429) {
        log_debug("❌ ERREUR 429: Trop de requêtes (quota dépassé)");
        return "ERREUR_API";
    }
    
    if ($httpCode !== 200) {
        log_debug("❌ ERREUR HTTP $httpCode");
        log_debug("Réponse: " . substr($response, 0, 300));
        return "ERREUR_API";
    }
    
    // ⚠️ ÉTAPE 6: Parser JSON
    $data = json_decode($response, true);
    
    if ($data === null) {
        log_debug("❌ ERREUR: JSON invalide");
        log_debug("Réponse brute: " . substr($response, 0, 300));
        return "ERREUR_API";
    }
    
    log_debug("✅ JSON parsé");
    
    // ⚠️ ÉTAPE 7: Extraire la réponse
    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        log_debug("❌ ERREUR: Structure JSON incorrecte");
        log_debug("Structure reçue: " . json_encode(array_keys($data ?? [])));
        return "ERREUR_API";
    }
    
    $gemini_text = strtoupper(trim($data['candidates'][0]['content']['parts'][0]['text']));
    
    log_debug("✅ Réponse Gemini: '$gemini_text'");
    log_debug("========== VÉRIFICATION TERMINÉE ==========\n");
    
    return (strpos($gemini_text, 'YES') !== false) ? 'OUI' : 'NON';
}

// ========== VÉRIFICATION CONTENUR ==========
log_debug("📝 NOUVEAU POST - Auteur: $author");

$full_content = "$author | $message";
$moderation_result = check_gemini_moderation($full_content, $gemini_api_key);

log_debug("Résultat final: $moderation_result");

// Bloquer si inapproprié
if ($moderation_result === 'OUI') {
    $error_message = "❌ Votre post contient du contenu inapproprié ou offensant. Veuillez le modifier.";
    log_debug("POST REJETÉ - Contenu inapproprié");
    header('Location: index.php?error=' . urlencode($error_message));
    exit();
}

// Bloquer si erreur API
if ($moderation_result === 'ERREUR_API') {
    $error_message = "⚠️ La vérification a échoué. Vérifiez que votre clé API Gemini est correcte dans AjoutPost.php ou Vous avez atteindre Votre limite d'utilisation (allez sur https://aistudio.google.com). Consultez moderation_debug.log pour les détails.";
    log_debug("POST REJETÉ - Erreur API");
    header('Location: index.php?error=' . urlencode($error_message));
    exit();
}

log_debug("POST APPROUVÉ - Contenu acceptable");

// ========== UPLOAD IMAGE ==========
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $target_dir = "../uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $unique_file = uniqid() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $unique_file;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false && in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
            log_debug("Image uploadée: $image_path");
        } else {
            $error_message = "Erreur lors du téléchargement de l'image.";
        }
    } else {
        $error_message = "Seuls JPG, PNG, JPEG, GIF sont acceptés.";
    }
}

// ========== SAUVEGARDE EN BD ==========
if (empty($error_message)) {
    try {
        $pc = new PostC();
        $p = new Post($user_id, $author, $message, $time, $image_path, $status);
        $pc->addPost($p);
        log_debug("✅ Post sauvegardé en BD\n");
        header('Location: index.php?success=1');
        exit();
    } catch (Exception $e) {
        log_debug("❌ ERREUR BD: " . $e->getMessage());
        $error_message = "Erreur lors de l'enregistrement.";
    }
}

log_debug("❌ Erreur: $error_message\n");
header('Location: index.php?error=' . urlencode($error_message));
exit();

?>