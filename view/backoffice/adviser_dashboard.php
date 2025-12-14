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

// Vérifier si l'utilisateur est connecté et est un conseiller
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'conseilleur') {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

$userController = new UserController();
$user = $userController->getUserById($_SESSION['user_id']);

// Récupérer quelques statistiques pour le conseiller
$totalClients = 0;
$pendingConsultations = 0;
$completedConsultations = 0;
$satisfactionRate = "95%";

// Fonction pour obtenir l'URL de la photo de profil
function getProfilePictureUrl($user, $default = 'default-avatar.png') {
    $baseUrl = '../frontoffice/assets/images/uploads/';
    $defaultUrl = '../frontoffice/assets/images/default-avatar.png';
    
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
    <title>SafeSpace - Dashboard Conseiller</title>
    
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        /* Style professionnel élégant pour conseiller */
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', 'Nunito', sans-serif;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .profile-header-conseiller {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8eef5;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header-conseiller::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3498db 0%, #2c3e50 100%);
        }
        
        .profile-avatar-conseiller {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f8fafc;
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.15);
            margin-bottom: 20px;
            background: white;
        }
        
        .profile-name-conseiller {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .profile-email-conseiller {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .role-badge-conseiller {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            margin-bottom: 20px;
        }
        
        .action-buttons-conseiller {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn-conseiller-primary {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
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
        
        .btn-conseiller-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1a252f 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(52, 152, 219, 0.3);
            text-decoration: none;
        }
        
        .btn-conseiller-secondary {
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
        
        .btn-conseiller-secondary:hover {
            background: #f8fafc;
            color: #2980b9;
            border-color: #2980b9;
            text-decoration: none;
        }
        
        .stats-card-conseiller {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border: 1px solid #e8eef5;
            transition: transform 0.3s ease;
            text-align: center;
        }
        
        .stats-card-conseiller:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }
        
        .stats-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
        
        .stats-value {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-card-conseiller {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border: 1px solid #e8eef5;
        }
        
        .info-item-conseiller {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
        }
        
        .info-item-conseiller:last-child {
            border-bottom: none;
        }
        
        .info-icon-conseiller {
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
        
        .info-content-conseiller h5 {
            color: #34495e;
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 0.95rem;
        }
        
        .info-content-conseiller p {
            color: #7f8c8d;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .section-title-conseiller {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 12px;
        }
        
        .section-title-conseiller::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #3498db 0%, #2c3e50 100%);
            border-radius: 2px;
        }
        
        .bio-card-conseiller {
            background: #f8fafc;
            border-radius: 10px;
            padding: 25px;
            border-left: 4px solid #3498db;
            margin-top: 20px;
        }
        
        .bio-card-conseiller h4 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .bio-card-conseiller p {
            color: #5d6d7e;
            line-height: 1.6;
            margin: 0;
        }
        
        .quick-actions-card-conseiller {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
            border: 1px solid #e8eef5;
        }
        
        .btn-action-conseiller {
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
        
        .btn-action-conseiller i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            color: #3498db;
        }
        
        .btn-action-conseiller:hover {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }
        
        .btn-action-conseiller:hover i {
            color: white;
        }
        
        .welcome-card-conseiller {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 6px 20px rgba(44, 62, 80, 0.15);
        }
        
        .welcome-card-conseiller h2 {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 1.6rem;
        }
        
        .welcome-card-conseiller p {
            opacity: 0.9;
            margin: 0;
            font-size: 1rem;
        }
        
        .account-info-conseiller {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e8eef5;
        }
        
        .account-info-conseiller h6 {
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
        
        .consultations-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }
        
        .consultations-table th {
            background: #f8fafc;
            border: none;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .consultations-table td {
            border-color: #f1f5f9;
        }
        
        .badge-confirmed {
            background: #38b2ac;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .badge-pending {
            background: #ed8936;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        /* Animation subtile */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .profile-header-conseiller {
            animation: fadeIn 0.5s ease-out;
        }
        
        .stats-card-conseiller {
            animation: fadeIn 0.6s ease-out 0.1s both;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="adviser_dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SafeSpace <sup>Conseiller</sup></div>
            </a>

            <hr class="sidebar-divider my-0">
            
            <li class="nav-item active">
                <a class="nav-link" href="adviser_dashboard.php">
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
                <a class="nav-link" href="my_clients.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Mes Clients</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="my_consultations.php">
                    <i class="fas fa-fw fa-comments"></i>
                    <span>Mes Consultations</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="calendar.php">
                    <i class="fas fa-fw fa-calendar"></i>
                    <span>Calendrier</span>
                </a>
            </li>

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
                                    <?= htmlspecialchars($_SESSION['fullname'] ?? 'Conseiller') ?>
                                </span>
                                <i class="fas fa-user-tie fa-fw"></i>
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
                        <!-- Message de bienvenue -->
                        <div class="welcome-card-conseiller">
                            <h2>Bienvenue, <?= htmlspecialchars($user->getNom()) ?> !</h2>
                            <p>Conseiller professionnel - Tableau de bord</p>
                        </div>
<!-- Après la section "Message de bienvenue élégant" -->
<?php if ($welcomeMessage): ?>
<div class="alert alert-info" style="margin-bottom: 20px; border-left: 4px solid #3498db;">
    <i class="fas fa-map-marker-alt"></i> 
    <?= htmlspecialchars($welcomeMessage) ?>
    
    <?php if ($loginInfo && $loginInfo['geo']): ?>
    <small class="d-block mt-2 text-muted">
        <i class="fas fa-info-circle"></i> 
        IP: <?= htmlspecialchars($loginInfo['ip']) ?> | 
        OS: <?= htmlspecialchars($loginInfo['os']) ?> |
        Fuseau: <?= htmlspecialchars($loginInfo['geo']['timezone']) ?>
    </small>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($securityAlert): ?>
<div class="alert alert-warning" style="margin-bottom: 20px;">
    <i class="fas fa-shield-alt"></i> 
    <strong>Sécurité :</strong> <?= htmlspecialchars($securityAlert['message']) ?> à <?= htmlspecialchars($securityAlert['time']) ?>
</div>
<?php endif; ?>
                        <!-- PROFIL CONSEILLER -->
                        <div id="profile-section" class="profile-header-conseiller">
                            <div class="text-center">
                                <img src="<?= getProfilePictureUrl($user) ?>" 
                                     alt="Photo de profil" 
                                     class="profile-avatar-conseiller"
                                     onerror="this.src='../frontoffice/assets/images/default-avatar.png'">
                                
                                <h1 class="profile-name-conseiller"><?= htmlspecialchars($user->getNom()) ?></h1>
                                <p class="profile-email-conseiller"><?= htmlspecialchars($user->getEmail()) ?></p>
                                
                                <div class="role-badge-conseiller">
                                    💼 Conseiller Professionnel
                                </div>
                                
                                <div class="action-buttons-conseiller">
                                    <a href="edit_profile.php" class="btn-conseiller-primary">
                                        <i class="fas fa-edit mr-2"></i> Modifier mon profil
                                    </a>
                                    <a href="../frontoffice/index.php" class="btn-conseiller-secondary">
                                        <i class="fas fa-globe mr-2"></i> Visiter le site
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques conseiller -->
                        <div class="row mb-4">
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="stats-card-conseiller">
                                    <div class="stats-icon" style="color: #3498db;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stats-value"><?= $totalClients ?></div>
                                    <div class="stats-label">Clients Actifs</div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="stats-card-conseiller">
                                    <div class="stats-icon" style="color: #38b2ac;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stats-value"><?= $completedConsultations ?></div>
                                    <div class="stats-label">Consultations Terminées</div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="stats-card-conseiller">
                                    <div class="stats-icon" style="color: #ed8936;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stats-value"><?= $pendingConsultations ?></div>
                                    <div class="stats-label">En Attente</div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="stats-card-conseiller">
                                    <div class="stats-icon" style="color: #9f7aea;">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="stats-value"><?= $satisfactionRate ?></div>
                                    <div class="stats-label">Satisfaction</div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenu principal -->
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Informations personnelles -->
                                <div class="info-card-conseiller">
                                    <h3 class="section-title-conseiller">Informations professionnelles</h3>
                                    
                                    <div class="info-item-conseiller">
                                        <div class="info-icon-conseiller">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="info-content-conseiller">
                                            <h5>Adresse email professionnelle</h5>
                                            <p><?= htmlspecialchars($user->getEmail()) ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if(!empty($user->getTelephone())): ?>
                                    <div class="info-item-conseiller">
                                        <div class="info-icon-conseiller">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div class="info-content-conseiller">
                                            <h5>Téléphone professionnel</h5>
                                            <p><?= htmlspecialchars($user->getTelephone()) ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($user->getSpecialite())): ?>
                                    <div class="info-item-conseiller">
                                        <div class="info-icon-conseiller">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <div class="info-content-conseiller">
                                            <h5>Spécialité</h5>
                                            <p><?= htmlspecialchars($user->getSpecialite()) ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($user->getAdresse())): ?>
                                    <div class="info-item-conseiller">
                                        <div class="info-icon-conseiller">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="info-content-conseiller">
                                            <h5>Adresse de consultation</h5>
                                            <p><?= htmlspecialchars($user->getAdresse()) ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-item-conseiller">
                                        <div class="info-icon-conseiller">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                        <div class="info-content-conseiller">
                                            <h5>Membre depuis</h5>
                                            <p><?= date('d/m/Y', strtotime($user->getCreatedAt())) ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item-conseiller">
                                        <div class="info-icon-conseiller">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="info-content-conseiller">
                                            <h5>Statut professionnel</h5>
                                            <p><?= ucfirst($user->getStatus()) ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Biographie -->
                                <?php if(!empty($user->getBio())): ?>
                                <div class="bio-card-conseiller">
                                    <h4>À propos de moi</h4>
                                    <p><?= nl2br(htmlspecialchars($user->getBio())) ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Prochaines consultations -->
                                <div class="consultations-table">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold" style="color: #2c3e50;">
                                            <i class="fas fa-calendar-alt mr-2"></i>Prochaines Consultations
                                        </h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Client</th>
                                                    <th>Date</th>
                                                    <th>Heure</th>
                                                    <th>Type</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Marie Dupont</td>
                                                    <td>15/12/2024</td>
                                                    <td>14:00</td>
                                                    <td>Visio</td>
                                                    <td><span class="badge-confirmed">Confirmé</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Pierre Martin</td>
                                                    <td>16/12/2024</td>
                                                    <td>10:30</td>
                                                    <td>Téléphone</td>
                                                    <td><span class="badge-confirmed">Confirmé</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Sophie Lambert</td>
                                                    <td>17/12/2024</td>
                                                    <td>16:00</td>
                                                    <td>Présentiel</td>
                                                    <td><span class="badge-confirmed">Confirmé</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions rapides -->
                            <div class="col-lg-4">
                                <div class="quick-actions-card-conseiller">
                                    <h3 class="section-title-conseiller">Actions rapides</h3>
                                    <div class="d-grid">
                                        <button class="btn-action-conseiller" onclick="window.location.href='my_clients.php'">
                                            <i class="fas fa-users"></i> Mes clients
                                        </button>
                                        
                                        <button class="btn-action-conseiller" onclick="window.location.href='my_consultations.php'">
                                            <i class="fas fa-comments"></i> Mes consultations
                                        </button>
                                        
                                        <button class="btn-action-conseiller" onclick="window.location.href='calendar.php'">
                                            <i class="fas fa-calendar"></i> Mon calendrier
                                        </button>
                                        
                                        <button class="btn-action-conseiller" onclick="window.location.href='new_consultation.php'">
                                            <i class="fas fa-plus-circle"></i> Nouveau rendez-vous
                                        </button>
                                        
                                        <button class="btn-action-conseiller" onclick="window.location.href='../frontoffice/index.php'">
                                            <i class="fas fa-globe"></i> Site public
                                        </button>
                                    </div>
                                    
                                    <!-- Informations professionnelles -->
                                    <div class="account-info-conseiller">
                                        <h6>Informations professionnelles</h6>
                                        <div class="account-info-item">
                                            <span class="account-info-label">ID Conseiller</span>
                                            <span class="account-info-value">#<?= htmlspecialchars($user->getId()) ?></span>
                                        </div>
                                        <div class="account-info-item">
                                            <span class="account-info-label">Spécialité</span>
                                            <span class="account-info-value"><?= !empty($user->getSpecialite()) ? htmlspecialchars($user->getSpecialite()) : 'Non spécifiée' ?></span>
                                        </div>
                                        <div class="account-info-item">
                                            <span class="account-info-label">Taux de satisfaction</span>
                                            <span class="account-info-value"><?= $satisfactionRate ?></span>
                                        </div>
                                        <div class="account-info-item">
                                            <span class="account-info-label">Statut</span>
                                            <span class="account-info-value">Actif</span>
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
        $(document).ready(function() {
            // Scroll doux vers le profil
            $('a[href="#profile-section"]').click(function(e) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $("#profile-section").offset().top - 70
                }, 800);
            });
            
            // Effet hover sur les boutons
            $('.btn-action-conseiller').hover(
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
            
            // Animation des cartes de statistiques
            $('.stats-card-conseiller').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
        });
    </script>
</body>
</html>