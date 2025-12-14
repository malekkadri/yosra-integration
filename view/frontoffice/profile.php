<?php
session_start();

// Inclure les contrôleurs
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

// Vérifier si un ID utilisateur est spécifié, sinon utiliser l'utilisateur connecté
$profileUserId = $_GET['id'] ?? ($_SESSION['user_id'] ?? null);

if (!$profileUserId) {
    header('Location: login.php');
    exit();
}

try {
    $userController = new UserController();
    $user = $userController->getUserById($profileUserId);
} catch (Exception $e) {
    // Log l'erreur pour debug (optionnel)
    error_log("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
    
    // Vider et détruire la session
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    header('Location: login.php');
    exit();
}

if (!$user) {
    // Vider et détruire la session
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    header('Location: login.php');
    exit();
}

// Vérifier si c'est le profil de l'utilisateur connecté
// CORRECTION: Utiliser les getters de l'objet User
$isOwnProfile = ($_SESSION['user_id'] ?? null) == $user->getId();

// Fonction pour obtenir l'URL de la photo
function getProfilePictureUrl($user) {
    // CORRECTION: Utiliser getter
    $profilePicture = $user->getProfilePicture();
    $baseUrl = 'assets/images/uploads/';
    $defaultUrl = 'images/default-avatar.png';
    
    if (!empty($profilePicture) && $profilePicture !== 'default-avatar.png') {
        $filePath = $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/view/frontoffice/assets/images/uploads/' . $profilePicture;
        if (file_exists($filePath)) {
            return $baseUrl . $profilePicture . '?t=' . filemtime($filePath);
        }
    }
    return $defaultUrl;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CORRECTION: Utiliser getter -->
    <title>Profil de <?= htmlspecialchars($user->getNom()) ?> - SafeSpace</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sky-blue: #87CEEB;
            --light-sky-blue: #A7D9F2;
            --very-light-sky: #C7E8F9;
            --bright-sky: #6BC5F0;
            --text-dark: #2c3e50;
            --text-light: #5D6D7E;
        }
        
        body {
            background-color: var(--very-light-sky);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--sky-blue) 0%, var(--bright-sky) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(135, 206, 235, 0.3);
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.8);
            object-fit: cover;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            background: white;
        }
        .rating-stars {
            color: #ffd700;
            font-size: 1.2rem;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .rating-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(135, 206, 235, 0.2);
            margin-bottom: 2rem;
            border: 2px solid var(--light-sky-blue);
            backdrop-filter: blur(10px);
        }
        .rating-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--bright-sky);
            line-height: 1;
        }
        .rating-scale {
            color: var(--text-light);
            font-size: 1rem;
        }
        .info-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(135, 206, 235, 0.15);
            border: 2px solid var(--light-sky-blue);
            backdrop-filter: blur(10px);
        }
        .verified-badge {
            background: linear-gradient(135deg, #28a745, #2ecc71);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85em;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);
        }
        .role-badge {
            background: linear-gradient(135deg, var(--bright-sky), var(--sky-blue));
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85em;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(135, 206, 235, 0.4);
        }
        .action-btn {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .info-item {
            padding: 1.2rem 0;
            border-bottom: 1px solid rgba(135, 206, 235, 0.3);
            transition: background-color 0.3s ease;
        }
        .info-item:hover {
            background-color: rgba(135, 206, 235, 0.05);
            border-radius: 10px;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--light-sky-blue), var(--sky-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.2rem;
            color: white;
            box-shadow: 0 3px 10px rgba(135, 206, 235, 0.3);
        }
        .site-evaluation {
            background: linear-gradient(135deg, var(--sky-blue) 0%, var(--bright-sky) 100%);
            color: white;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(135, 206, 235, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--bright-sky), var(--sky-blue));
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--sky-blue), var(--bright-sky));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(135, 206, 235, 0.4);
        }
        .btn-outline-primary {
            border: 2px solid var(--bright-sky);
            color: var(--bright-sky);
            border-radius: 25px;
            padding: 10px 28px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-outline-primary:hover {
            background: var(--bright-sky);
            border-color: var(--bright-sky);
            color: white;
            transform: translateY(-2px);
        }
        .text-primary {
            color: var(--bright-sky) !important;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(135, 206, 235, 0.2);
            border-bottom: 2px solid var(--light-sky-blue);
        }
        .navbar-brand {
            color: var(--bright-sky) !important;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Logo dans la navbar */
        .navbar-logo {
            height: 35px;
            width: auto;
            border-radius: 5px;
        }
        
        /* Navigation améliorée */
        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: var(--bright-sky) !important;
        }
        
        /* Dropdown menu */
        .dropdown-menu {
            border-radius: 15px;
            border: 2px solid var(--light-sky-blue);
            box-shadow: 0 8px 25px rgba(135, 206, 235, 0.2);
            backdrop-filter: blur(10px);
        }
        .dropdown-item {
            border-radius: 8px;
            margin: 2px 8px;
            width: auto;
        }
        .dropdown-item:hover {
            background: var(--light-sky-blue);
            color: white;
        }
        
        /* Modal styling */
        .modal-content {
            border-radius: 20px;
            border: 2px solid var(--light-sky-blue);
            box-shadow: 0 10px 30px rgba(135, 206, 235, 0.3);
        }
        .modal-header {
            background: linear-gradient(135deg, var(--light-sky-blue), var(--sky-blue));
            color: white;
            border-radius: 18px 18px 0 0;
            border-bottom: 2px solid var(--light-sky-blue);
        }
        
        /* Star rating dans le modal */
        .star-rating span {
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0 2px;
        }
        .star-rating span:hover {
            transform: scale(1.2);
        }
        
        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--sky-blue), var(--bright-sky));
            color: white;
            border-top: 3px solid rgba(255, 255, 255, 0.3);
            margin-top: 3rem;
        }
        
        /* Badges */
        .badge.bg-primary {
            background: linear-gradient(135deg, var(--bright-sky), var(--sky-blue)) !important;
        }
        
        /* Text colors */
        .text-muted {
            color: var(--text-light) !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-avatar {
                width: 120px;
                height: 120px;
            }
            .info-section, .rating-card, .site-evaluation {
                padding: 1.5rem;
                margin-left: 1rem;
                margin-right: 1rem;
            }
            .navbar-logo {
                height: 30px;
            }
            .navbar-brand {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="images/logo.png" alt="SafeSpace Logo" class="navbar-logo">
                SafeSpace
            </a>
            
            <div class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?= getProfilePictureUrl($user) ?>" 
                                 alt="Photo profil" 
                                 class="rounded-circle me-2"
                                 style="width: 32px; height: 32px; object-fit: cover; border: 2px solid var(--light-sky-blue);"
                                 onerror="this.src='assets/images/default-avatar.png'">
                            <span><?= htmlspecialchars($_SESSION['fullname'] ?? 'Utilisateur') ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Mon Profil</a></li>
                            <?php if($isOwnProfile): ?>
                            <li><a class="dropdown-item" href="../backoffice/edit_profile.php"><i class="fas fa-edit me-2"></i>Modifier Profil</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary me-2">Connexion</a>
                    <a href="register.php" class="btn btn-primary">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-auto text-center text-md-start">
                    <img src="<?= getProfilePictureUrl($user) ?>" 
                         alt="Photo de profil de <?= htmlspecialchars($user->getNom()) ?>" 
                         class="profile-avatar"
                         id="profileAvatar"
                         onerror="this.src='assets/images/default-avatar.png'">
                </div>
                <div class="col-md">
                    <div class="ms-md-4 mt-3 mt-md-0">
                        <!-- CORRECTION: Utiliser getter -->
                        <h1 class="h2 mb-2"><?= htmlspecialchars($user->getNom()) ?></h1>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="role-badge">
                                <i class="fas fa-user me-1"></i>
                                <!-- CORRECTION: Utiliser getter -->
                                <?= ucfirst($user->getRole()) ?>
                            </span>
                            <span class="verified-badge">
                                <i class="fas fa-check me-1"></i>
                                Compte vérifié
                            </span>
                        </div>
                        <p class="lead mb-3 opacity-90">
                            <!-- CORRECTION: Utiliser getter -->
                            <?= !empty($user->getBio()) ? htmlspecialchars($user->getBio()) : 'Membre de la communauté SafeSpace' ?>
                        </p>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <?php if($isOwnProfile): ?>
                                <a href="../backoffice/edit_profile.php" class="btn btn-light action-btn">
                                    <i class="fas fa-edit me-2"></i>Modifier mon profil
                                </a>
                                <a href="../backoffice/member_dashboard.php" class="btn btn-outline-light action-btn">
                                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                                </a>
                            <?php else: ?>
                                <button class="btn btn-light action-btn">
                                    <i class="fas fa-envelope me-2"></i>Envoyer un message
                                </button>
                                <button class="btn btn-outline-light action-btn">
                                    <i class="fas fa-user-plus me-2"></i>Ajouter en ami
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Left Column - Personal Information -->
            <div class="col-lg-8">
                <!-- Site Evaluation Section -->
                <div class="site-evaluation">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-3">
                                <i class="fas fa-shield-alt me-2"></i>
                                Évaluez SafeSpace
                            </h3>
                            <p class="mb-4 opacity-90">
                                Partagez votre expérience avec notre plateforme. Votre avis nous aide à nous améliorer !
                            </p>
                            <button class="btn btn-warning btn-lg action-btn" data-bs-toggle="modal" data-bs-target="#siteRatingModal">
                                <i class="fas fa-star me-2"></i>Évaluer le site
                            </button>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="rating-number">4.7</div>
                            <div class="rating-scale">/ 5.0</div>
                            <div class="rating-stars mb-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <div class="opacity-90 small">
                                342 évaluations
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="info-section">
                    <h4 class="mb-4">
                        <i class="fas fa-user-circle me-2"></i>
                        Informations Personnelles
                    </h4>
                    
                    <div class="info-item d-flex align-items-center">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Nom complet</div>
                            <!-- CORRECTION: Utiliser getter -->
                            <div><?= htmlspecialchars($user->getNom()) ?></div>
                        </div>
                    </div>

                    <div class="info-item d-flex align-items-center">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Adresse email</div>
                            <!-- CORRECTION: Utiliser getter -->
                            <div><?= htmlspecialchars($user->getEmail()) ?></div>
                        </div>
                    </div>

                    <?php if($user->getDateNaissance()): ?>
                    <div class="info-item d-flex align-items-center">
                        <div class="info-icon">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Date de naissance</div>
                            <!-- CORRECTION: Utiliser getter -->
                            <div><?= date('d/m/Y', strtotime($user->getDateNaissance())) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($user->getTelephone()): ?>
                    <div class="info-item d-flex align-items-center">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Téléphone</div>
                            <!-- CORRECTION: Utiliser getter -->
                            <div><?= htmlspecialchars($user->getTelephone()) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($user->getAdresse()): ?>
                    <div class="info-item d-flex align-items-center">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Adresse</div>
                            <!-- CORRECTION: Utiliser getter -->
                            <div><?= htmlspecialchars($user->getAdresse()) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($user->getSpecialite()): ?>
                    <div class="info-item d-flex align-items-center">
                        <div class="info-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Spécialité</div>
                            <div>
                                <!-- CORRECTION: Utiliser getter -->
                                <span class="badge bg-primary"><?= htmlspecialchars($user->getSpecialite()) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($user->getBio()): ?>
                    <div class="info-item d-flex align-items-start">
                        <div class="info-icon">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">À propos de moi</div>
                            <!-- CORRECTION: Utiliser getter -->
                            <div class="mt-1"><?= nl2br(htmlspecialchars($user->getBio())) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid gap-2">
                    <span>
                        <a href="./addPost.php?user_id=<?php echo $profileUserId ?>" class="btn btn-primary action-btn">
                            <i class="fas fa-edit me-2"></i>Ajout Posts</a>
                    </span>
                    </div>
                
                 
            </div>

            <!-- Right Column - Account Info & Actions -->
            <div class="col-lg-4">
                <!-- Account Information -->
                <div class="info-section">
                    <h4 class="mb-4">
                        <i class="fas fa-cog me-2"></i>
                        Informations du Compte
                    </h4>
                    
                    <div class="info-item">
                        <div class="fw-bold mb-1">
                            <i class="fas fa-id-card me-2"></i>ID Utilisateur
                        </div>
                        <!-- CORRECTION: Utiliser getter -->
                        <div class="text-muted">#<?= $user->getId() ?></div>
                    </div>

                    <div class="info-item">
                        <div class="fw-bold mb-1">
                            <i class="fas fa-calendar-plus me-2"></i>Membre depuis
                        </div>
                        <!-- CORRECTION: Utiliser getter -->
                        <div class="text-muted"><?= date('d/m/Y', strtotime($user->getCreatedAt())) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="fw-bold mb-1">
                            <i class="fas fa-sync me-2"></i>Dernière mise à jour
                        </div>
                        <!-- CORRECTION: Utiliser getter -->
                        <?php 
                        $updatedAt = $user->getUpdatedAt();
                        ?>
                        <div class="text-muted">
                            <?= !empty($updatedAt) && $updatedAt !== '0000-00-00 00:00:00' 
                                ? date('d/m/Y à H:i', strtotime($updatedAt)) 
                                : 'Jamais' ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="fw-bold mb-1">
                            <i class="fas fa-shield-alt me-2"></i>Statut du compte
                        </div>
                        <!-- CORRECTION: Utiliser getter -->
                        <div class="text-muted">
                            <?php 
                            $status = $user->getStatus();
                            $statusClass = ($status === 'actif') ? 'bg-success' : (($status === 'en attente') ? 'bg-warning' : 'bg-danger');
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <?php if($isOwnProfile): ?>
                <div class="info-section">
                    <h4 class="mb-4">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Actions Rapides
                    </h4>
                    <div class="d-grid gap-2">
                        <a href="../backoffice/edit_profile.php" class="btn btn-primary action-btn">
                            <i class="fas fa-edit me-2"></i>Modifier le profil
                        </a>
                        <a href="../backoffice/member_dashboard.php" class="btn btn-outline-primary action-btn">
                            <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary action-btn">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Site Rating Modal -->
    <div class="modal fade" id="siteRatingModal" tabindex="-1" aria-labelledby="siteRatingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="siteRatingModalLabel">
                        <i class="fas fa-shield-alt me-2"></i>
                        Évaluer SafeSpace
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-shield-alt fa-3x mb-3" style="color: var(--bright-sky);"></i>
                            <h4>Que pensez-vous de SafeSpace ?</h4>
                        </div>
                        
                        <div class="star-rating mb-3" id="siteStarRating" style="font-size: 2rem;">
                            <span data-rating="5">★</span>
                            <span data-rating="4">★</span>
                            <span data-rating="3">★</span>
                            <span data-rating="2">★</span>
                            <span data-rating="1">★</span>
                        </div>
                        <div id="siteRatingText" class="text-muted fw-bold">Sélectionnez une note</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Qu'avez-vous aimé ?</label>
                        <textarea class="form-control" id="siteRatingComment" placeholder="Partagez votre expérience avec SafeSpace... (optionnel)" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Suggestions d'amélioration</label>
                        <textarea class="form-control" id="siteRatingSuggestion" placeholder="Avez-vous des suggestions pour améliorer notre plateforme ? (optionnel)" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-warning action-btn" onclick="submitSiteRating()">
                        <i class="fas fa-paper-plane me-1"></i>Envoyer l'évaluation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> SafeSpace. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedSiteRating = 0;
        const siteRatingTexts = {
            1: "Mauvais - Très déçu",
            2: "Moyen - Peut mieux faire", 
            3: "Bien - Satisfait",
            4: "Très bien - Excellent service",
            5: "Exceptionnel - Site parfait !"
        };

        // Gestion des étoiles de notation du site
        document.querySelectorAll('#siteStarRating span').forEach(star => {
            star.addEventListener('click', function() {
                selectedSiteRating = parseInt(this.getAttribute('data-rating'));
                updateSiteStarsDisplay(selectedSiteRating);
                document.getElementById('siteRatingText').textContent = siteRatingTexts[selectedSiteRating];
                document.getElementById('siteRatingText').className = 'fw-bold text-warning';
            });
            
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                updateSiteStarsDisplay(rating, true);
            });
        });

        document.getElementById('siteStarRating').addEventListener('mouseleave', function() {
            updateSiteStarsDisplay(selectedSiteRating);
        });

        function updateSiteStarsDisplay(rating, isHover = false) {
            document.querySelectorAll('#siteStarRating span').forEach((star, index) => {
                const starRating = 5 - index;
                if (starRating <= rating) {
                    star.style.color = isHover ? '#ffd700' : '#ffc107';
                    star.style.textShadow = isHover ? '0 0 15px gold' : '0 1px 3px rgba(255,193,7,0.5)';
                    star.style.cursor = 'pointer';
                    star.style.transform = isHover ? 'scale(1.1)' : 'scale(1)';
                } else {
                    star.style.color = '#e4e5e9';
                    star.style.textShadow = 'none';
                    star.style.cursor = 'pointer';
                    star.style.transform = 'scale(1)';
                }
            });
        }

        function submitSiteRating() {
            if (selectedSiteRating === 0) {
                alert('Veuillez sélectionner une note pour SafeSpace');
                return;
            }
            
            const comment = document.getElementById('siteRatingComment').value;
            const suggestion = document.getElementById('siteRatingSuggestion').value;
            
            // Simulation d'envoi
            alert(`Merci pour votre évaluation de ${selectedSiteRating} étoile(s) ! Votre avis est précieux pour améliorer SafeSpace.`);
            
            // Fermer le modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('siteRatingModal'));
            modal.hide();
            
            // Réinitialiser
            selectedSiteRating = 0;
            document.getElementById('siteRatingComment').value = '';
            document.getElementById('siteRatingSuggestion').value = '';
            document.getElementById('siteRatingText').textContent = 'Sélectionnez une note';
            document.getElementById('siteRatingText').className = 'text-muted';
            updateSiteStarsDisplay(0);
        }

        // Gestion des erreurs d'images
        document.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG') {
                e.target.src = 'assets/images/default-avatar.png';
            }
        }, true);

        // Animation au scroll
        document.addEventListener('DOMContentLoaded', function() {
            const infoItems = document.querySelectorAll('.info-item');
            infoItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 100 * index);
            });
        });
    </script>
    <!-- Section Face ID dans le profil -->

</div>

<script>
function deleteFaceId() {
    if (confirm('Voulez-vous vraiment désactiver Face ID ?')) {
        fetch('../../controller/handle_faceid.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'delete'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Face ID désactivé avec succès');
                location.reload();
            } else {
                alert('Erreur: ' + data.error);
            }
        });
    }
}
</script>
</body>
</html>