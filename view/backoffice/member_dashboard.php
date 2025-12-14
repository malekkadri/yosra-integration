<?php
// DÉMARRER LA SESSION EN PREMIER
session_start();
// Après session_start() et avant require_once
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AuthController.php';

// Après la création de $userController
$authController = new AuthController();
$welcomeMessage = $authController->getWelcomeMessage();
$securityAlert = $authController->getSecurityAlert();
$loginInfo = $authController->getLastLoginInfo();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

$userController = new UserController();
$user = $userController->getUserById($_SESSION['user_id']);

// Déterminer le dashboard selon le rôle
$userRole = $_SESSION['user_role'];
$roleName = ucfirst($userRole);

// Obtenir l'URL de la photo de profil
function getProfilePictureUrl($user, $default = 'default-avatar.png') {
    $baseUrl = '../frontoffice/assets/images/uploads/';
<<<<<<< HEAD:SAFEProject/view/backoffice/member_dashboard.php
    $defaultUrl = '../frontoffice/images/default-avatar.png';
=======
    $defaultUrl = '../frontoffice/assets/images/default-avatar.png';
>>>>>>> origin/main:view/backoffice/member_dashboard.php
    
    $profilePicture = $user->getProfilePicture();
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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SafeSpace - Dashboard <?= $roleName ?></title>
    <!-- Dans la section principale après le titre -->
<div class="container-fluid">
    <?php if ($welcomeMessage): ?>
    <div class="alert alert-primary mb-4">
        <i class="fas fa-map-marker-alt mr-2"></i>
        <?= htmlspecialchars($welcomeMessage) ?>
    </div>
    <?php endif; ?>
    
    <!-- Le reste de votre code existant -->
</div>
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        /* Style professionnel élégant */
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', 'Nunito', sans-serif;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .profile-header-elegant {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8eef5;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header-elegant::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
        }
        
        .profile-avatar-elegant {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f8fafc;
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.15);
            margin-bottom: 20px;
            background: white;
        }
        
        .profile-name-elegant {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .profile-email-elegant {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .role-badge-elegant {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            margin-bottom: 20px;
        }
        
        .action-buttons-elegant {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn-elegant-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }
        
        .btn-elegant-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(52, 152, 219, 0.3);
            text-decoration: none;
        }
        
        .btn-elegant-secondary {
            background: white;
            color: #3498db;
            border: 2px solid #3498db;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .btn-elegant-secondary:hover {
            background: #f8fafc;
            color: #2980b9;
            border-color: #2980b9;
            text-decoration: none;
        }
        
        .info-card-elegant {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border: 1px solid #e8eef5;
            transition: transform 0.3s ease;
        }
        
        .info-card-elegant:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        
        .info-item-elegant {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
        }
        
        .info-item-elegant:last-child {
            border-bottom: none;
        }
        
        .info-icon-elegant {
            width: 40px;
            height: 40px;
            background: #f8fafc;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #3498db;
            flex-shrink: 0;
            border: 1px solid #e8eef5;
        }
        
        .info-content-elegant h5 {
            color: #34495e;
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 0.95rem;
        }
        
        .info-content-elegant p {
            color: #7f8c8d;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .section-title-elegant {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 12px;
        }
        
        .section-title-elegant::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
            border-radius: 2px;
        }
        
        .bio-card-elegant {
            background: #f8fafc;
            border-radius: 10px;
            padding: 25px;
            border-left: 4px solid #3498db;
            margin-top: 20px;
        }
        
        .bio-card-elegant h4 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .bio-card-elegant p {
            color: #5d6d7e;
            line-height: 1.6;
            margin: 0;
        }
        
        .quick-actions-card-elegant {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
            border: 1px solid #e8eef5;
        }
        
        .btn-action-elegant {
            width: 100%;
            margin-bottom: 12px;
            padding: 14px;
            border-radius: 8px;
            font-weight: 500;
            text-align: left;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            background: #f8fafc;
            color: #34495e;
            border: 1px solid #e8eef5;
        }
        
        .btn-action-elegant i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            color: #3498db;
        }
        
        .btn-action-elegant:hover {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }
        
        .btn-action-elegant:hover i {
            color: white;
        }
        
        .welcome-card-elegant {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 6px 20px rgba(44, 62, 80, 0.15);
        }
        
        .welcome-card-elegant h2 {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1.6rem;
        }
        
        .welcome-card-elegant p {
            opacity: 0.9;
            margin: 0;
            font-size: 1rem;
        }
        
        .account-info-elegant {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e8eef5;
        }
        
        .account-info-elegant h6 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }
        
        .account-info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }
        
        .account-info-label {
            color: #7f8c8d;
        }
        
        .account-info-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        /* Animation subtile */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .profile-header-elegant {
            animation: fadeIn 0.5s ease-out;
        }
        
        .info-card-elegant {
            animation: fadeIn 0.6s ease-out 0.1s both;
        }
        
        .quick-actions-card-elegant {
            animation: fadeIn 0.6s ease-out 0.2s both;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SafeSpace <sup><?= $roleName ?></sup></div>
            </a>

            <hr class="sidebar-divider my-0">
            
            <li class="nav-item active">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Interface</div>
            
            <li class="nav-item">
                <a class="nav-link" href="edit_profile.php">
                    <i class="fas fa-fw fa-user-edit"></i>
                    <span>Modifier mon profil</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="my_consultations.php">
                    <i class="fas fa-fw fa-comments"></i>
                    <span>Mes Consultations</span>
                </a>
            </li>
            
            <?php if ($userRole === 'conseilleur'): ?>
            <li class="nav-item">
                <a class="nav-link" href="manage_consultations.php">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Gérer Consultations</span>
                </a>
            </li>
            <?php endif; ?>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Navigation</div>
            
            <li class="nav-item">
                <a class="nav-link" href="../frontoffice/index.php">
                    <i class="fas fa-fw fa-globe"></i>
                    <span>Site Public</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="../frontoffice/logout.php">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?= htmlspecialchars($_SESSION['fullname'] ?? $roleName) ?>
                                </span>
                                <i class="fas fa-user fa-fw"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="#profile-section">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Voir mon profil
                                </a>
                                <a class="dropdown-item" href="edit_profile.php">
                                    <i class="fas fa-user-edit fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Modifier profil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../frontoffice/logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Déconnexion
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Page Content -->
                <div class="container-fluid">
                    <div class="dashboard-container">
                        <!-- Message de bienvenue élégant -->
                        <div class="welcome-card-elegant">
                            <h2>Bienvenue, <?= htmlspecialchars($user->getNom()) ?> !</h2>
                            <p>Vous êtes connecté en tant que <?= $roleName ?></p>
                        </div>

                        <!-- PROFIL ÉLÉGANT -->
                        <div id="profile-section" class="profile-header-elegant">
                            <div class="text-center">
                                <img src="<?= getProfilePictureUrl($user) ?>" 
                                     alt="Photo de profil" 
                                     class="profile-avatar-elegant"
                                     onerror="this.src='../frontoffice/assets/images/default-avatar.png'">
                                
                                <h1 class="profile-name-elegant"><?= htmlspecialchars($user->getNom()) ?></h1>
                                <p class="profile-email-elegant"><?= htmlspecialchars($user->getEmail()) ?></p>
                                
                                <div class="role-badge-elegant">
                                    <?php 
                                    switch($userRole) {
                                        case 'admin': echo '👑 Administrateur'; break;
                                        case 'conseilleur': echo '💼 Conseiller Professionnel'; break;
                                        case 'membre': echo '👤 Membre'; break;
                                        default: echo $roleName;
                                    }
                                    ?>
                                </div>
                                
                                <div class="action-buttons-elegant">
                                    <a href="edit_profile.php" class="btn-elegant-primary">
                                        <i class="fas fa-edit mr-2"></i> Modifier mon profil
                                    </a>
                                    <a href="../frontoffice/index.php" class="btn-elegant-secondary">
                                        <i class="fas fa-globe mr-2"></i> Visiter le site
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Contenu principal -->
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Informations personnelles -->
                                <div class="info-card-elegant">
                                    <h3 class="section-title-elegant">Informations personnelles</h3>
                                    
                                    <div class="info-item-elegant">
                                        <div class="info-icon-elegant">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="info-content-elegant">
                                            <h5>Adresse email</h5>
                                            <p><?= htmlspecialchars($user->getEmail()) ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if(!empty($user->getTelephone())): ?>
                                    <div class="info-item-elegant">
                                        <div class="info-icon-elegant">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div class="info-content-elegant">
                                            <h5>Téléphone</h5>
                                            <p><?= htmlspecialchars($user->getTelephone()) ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($user->getDateNaissance())): ?>
                                    <div class="info-item-elegant">
                                        <div class="info-icon-elegant">
                                            <i class="fas fa-birthday-cake"></i>
                                        </div>
                                        <div class="info-content-elegant">
                                            <h5>Date de naissance</h5>
                                            <p><?= date('d/m/Y', strtotime($user->getDateNaissance())) ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($user->getAdresse())): ?>
                                    <div class="info-item-elegant">
                                        <div class="info-icon-elegant">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="info-content-elegant">
                                            <h5>Adresse</h5>
                                            <p><?= htmlspecialchars($user->getAdresse()) ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if($userRole === 'conseilleur' && !empty($user->getSpecialite())): ?>
                                    <div class="info-item-elegant">
                                        <div class="info-icon-elegant">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <div class="info-content-elegant">
                                            <h5>Spécialité</h5>
                                            <p><?= htmlspecialchars($user->getSpecialite()) ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-item-elegant">
                                        <div class="info-icon-elegant">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                        <div class="info-content-elegant">
                                            <h5>Membre depuis</h5>
                                            <p><?= date('d/m/Y', strtotime($user->getCreatedAt())) ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item-elegant">
                                        <div class="info-icon-elegant">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="info-content-elegant">
                                            <h5>Statut du compte</h5>
                                            <p><?= ucfirst($user->getStatus()) ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Biographie -->
                                <?php if(!empty($user->getBio())): ?>
                                <div class="bio-card-elegant">
                                    <h4>À propos de moi</h4>
                                    <p><?= nl2br(htmlspecialchars($user->getBio())) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Actions rapides -->
                            <div class="col-lg-4">
                                <div class="quick-actions-card-elegant">
                                    <h3 class="section-title-elegant">Actions rapides</h3>
                                    <div class="d-grid">
                                        <?php if ($userRole === 'membre'): ?>
                                            <button class="btn-action-elegant" onclick="window.location.href='new_consultation.php'">
                                                <i class="fas fa-plus"></i> Nouvelle consultation
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn-action-elegant" onclick="window.location.href='my_consultations.php'">
                                            <i class="fas fa-comments"></i> Mes consultations
                                        </button>
                                        
                                        <?php if ($userRole === 'conseilleur'): ?>
                                            <button class="btn-action-elegant" onclick="window.location.href='manage_consultations.php'">
                                                <i class="fas fa-tasks"></i> Gérer consultations
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn-action-elegant" onclick="window.location.href='../frontoffice/index.php'">
                                            <i class="fas fa-globe"></i> Site public
                                        </button>
                                    </div>
                                    
                                    <!-- Informations compte -->
                                    <div class="account-info-elegant">
                                        <h6>Informations compte</h6>
                                        <div class="account-info-item">
                                            <span class="account-info-label">ID Utilisateur</span>
                                            <span class="account-info-value">#<?= htmlspecialchars($user->getId()) ?></span>
                                        </div>
                                        <div class="account-info-item">
                                            <span class="account-info-label">Rôle</span>
                                            <span class="account-info-value"><?= $roleName ?></span>
                                        </div>
                                        <div class="account-info-item">
                                            <span class="account-info-label">Dernière mise à jour</span>
                                            <span class="account-info-value"><?= date('d/m/Y') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; SafeSpace <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="assets/js/sb-admin-2.min.js"></script>
    
    <script>
        // Animation et interactions
        $(document).ready(function() {
            // Scroll doux vers le profil
            $('a[href="#profile-section"]').click(function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $("#profile-section").offset().top - 70
                }, 800);
            });
            
            // Effet hover sur les boutons
            $('.btn-action-elegant').hover(
                function() {
                    $(this).css({
                        'transform': 'translateY(-2px)',
                        'box-shadow': '0 6px 16px rgba(52, 152, 219, 0.25)'
                    });
                },
                function() {
                    $(this).css({
                        'transform': 'translateY(0)',
                        'box-shadow': '0 4px 12px rgba(52, 152, 219, 0.2)'
                    });
                }
            );
        });
    </script>
</body>
</html>