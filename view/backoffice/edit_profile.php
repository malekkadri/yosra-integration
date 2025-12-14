<?php
// DÉMARRER LA SESSION EN PREMIER
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/AuthController.php';

$userController = new UserController();
$authController = new AuthController();

// Récupérer l'utilisateur connecté
$user = $userController->getUserById($_SESSION['user_id']);
$message = '';
$messageType = '';

// Configuration upload - CORRIGÉ
$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/view/frontoffice/assets/images/uploads/';
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
$maxFileSize = 2 * 1024 * 1024; // 2MB

// Créer le dossier d'upload s'il n'existe pas
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $specialite = $_POST['specialite'] ?? '';
    
    // Gestion de l'upload de photo - SIMPLIFIÉ
    $profile_picture = $user->getProfilePicture() ?? 'default-avatar.png';
    
    // Gestion de la suppression de photo
    if (isset($_POST['remove_profile_picture']) && $_POST['remove_profile_picture'] == '1') {
        $oldPicture = $user->getProfilePicture() ?? 'default-avatar.png';
        if ($oldPicture !== 'default-avatar.png' && file_exists($uploadDir . $oldPicture)) {
            unlink($uploadDir . $oldPicture);
        }
        $profile_picture = 'default-avatar.png';
    }
    // Gestion de l'upload de nouvelle photo - SIMPLIFIÉ
    elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validation rapide du fichier
        if (!in_array($fileExt, $allowedTypes)) {
            $message = "Type de fichier non autorisé. Formats acceptés: JPG, JPEG, PNG, GIF.";
            $messageType = 'error';
        } elseif ($fileSize > $maxFileSize) {
            $message = "Fichier trop volumineux. Taille maximum: 2MB.";
            $messageType = 'error';
        } else {
            // Générer un nom unique
            $newFileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Supprimer l'ancienne photo si ce n'est pas l'avatar par défaut
                $oldPicture = $user->getProfilePicture() ?? 'default-avatar.png';
                if ($oldPicture !== 'default-avatar.png' && file_exists($uploadDir . $oldPicture)) {
                    unlink($uploadDir . $oldPicture);
                }
                $profile_picture = $newFileName;
            } else {
                $message = "Erreur lors de l'upload de la photo. Vérifiez les permissions du dossier.";
                $messageType = 'error';
            }
        }
    }
    
    // Validation des autres champs
    if (empty($message)) {
        if (empty($fullname) || empty($email)) {
            $message = "Tous les champs obligatoires doivent être remplis.";
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "L'adresse email n'est pas valide.";
            $messageType = 'error';
        } else {
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $existingUser = $userController->getUserByEmail($email);
            if ($existingUser && $existingUser['id'] != $user->getId()) {
                $message = "Cette adresse email est déjà utilisée par un autre compte.";
                $messageType = 'error';
            } else {
                // Vérifier le mot de passe actuel si changement demandé
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $message = "Veuillez saisir votre mot de passe actuel pour changer le mot de passe.";
                        $messageType = 'error';
                    } elseif (!$userController->verifyPassword($current_password, $user->getPassword())) {
                        $message = "Le mot de passe actuel est incorrect.";
                        $messageType = 'error';
                    } elseif (strlen($new_password) < 6) {
                        $message = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
                        $messageType = 'error';
                    }
                }
                
                if (empty($message)) {
                    // Créer un objet User pour la mise à jour
                    $userObj = new User(
                        $fullname,
                        $email,
                        $new_password, // Si vide, ne changera pas le mot de passe
                        $user->getRole(),
                        $user->getStatus()
                    );
                    $userObj->setId($user->getId())
                            ->setProfilePicture($profile_picture)
                            ->setDateNaissance($date_naissance)
                            ->setTelephone($telephone)
                            ->setAdresse($adresse)
                            ->setBio($bio)
                            ->setSpecialite($specialite);

                    if ($userController->updateUser($user->getId(), $userObj)) {
                        $_SESSION['fullname'] = $fullname;
                        $_SESSION['user_email'] = $email;
                        $message = "Votre profil a été mis à jour avec succès !";
                        $messageType = 'success';
                        
                        // Recharger les données utilisateur
                        $user = $userController->getUserById($_SESSION['user_id']);
                        
                        // FORCER le rechargement de la page pour éviter les problèmes de cache
                        echo '<script>setTimeout(function(){ window.location.href = "edit_profile.php"; }, 1000);</script>';
                    } else {
                        $message = "Une erreur est survenue lors de la mise à jour du profil.";
                        $messageType = 'error';
                    }
                }
            }
        }
    }
}

// Déterminer le dashboard de redirection selon le rôle
$dashboard_url = '';
switch ($_SESSION['user_role']) {
    case 'admin':
        $dashboard_url = 'index.php';
        break;
    case 'conseilleur':
        $dashboard_url = 'adviser_dashboard.php';
        break;
    case 'membre':
        $dashboard_url = 'member_dashboard.php';
        break;
    default:
        $dashboard_url = 'member_dashboard.php';
}

// CORRECTION : Fonction pour générer l'URL de la photo avec timestamp anti-cache
function getProfilePictureUrl($user, $default = 'default-avatar.png') {
    $baseUrl = '../frontoffice/assets/images/uploads/';
    $defaultUrl = '../frontoffice/assets/images/default-avatar.png';
    
    $profilePicture = $user->getProfilePicture();
    if (!empty($profilePicture) && $profilePicture !== 'default-avatar.png') {
        $filePath = $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/view/frontoffice/assets/images/uploads/' . $profilePicture;
        if (file_exists($filePath)) {
            // Ajouter un timestamp pour éviter le cache
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
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SafeSpace - Modifier mon profil</title>

    <!-- Custom fonts for this template-->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .profile-card {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 4px solid #4e73df;
        }
        .password-section {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
            border-left: 4px solid #e74a3b;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid transparent;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .form-label {
            font-weight: bold;
            color: #5a5c69;
        }
        .user-info-badge {
            background: #e74a3b;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .profile-picture-container {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #4e73df;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }
        .photo-preview {
            display: none;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 1rem;
            border: 3px solid #4e73df;
            background: #f8f9fa;
        }
        .file-upload {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .file-upload-label {
            background: #4e73df;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            transition: background 0.3s;
        }
        .file-upload-label:hover {
            background: #2e59d9;
        }
        .upload-help {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.85em;
            color: #6c757d;
        }
        .photo-actions {
            margin-top: 1rem;
        }
        .remove-photo {
            background: #e74a3b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
        }
        .remove-photo:hover {
            background: #d52a1a;
        }
        .loading-spinner {
            display: none;
            color: #4e73df;
            font-size: 1.2em;
        }
    </style>
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand avec VOTRE LOGO -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= $dashboard_url ?>">
                <div class="sidebar-brand-icon">
                    <!-- VOTRE LOGO - chemin ajusté pour backoffice -->
                    <img src="../frontoffice/images/logo.png" 
                         alt="SafeSpace Logo" 
                         class="sidebar-logo"
                         style="height: 40px; width: auto;"
                         onerror="this.onerror=null; this.style.display='none'; 
                                  document.getElementById('logo-fallback').style.display='block';">
                    <!-- Fallback si le logo n'existe pas -->
                    <div id="logo-fallback" class="sidebar-brand-icon rotate-n-15" style="display: none;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                
                <div class="sidebar-brand-text mx-3">SafeSpace 
                    <sup style="font-size: 0.6em; vertical-align: super;">
                        <?php 
                        switch ($_SESSION['user_role']) {
                            case 'admin': echo '👑 Admin'; break;
                            case 'conseilleur': echo '💼 Conseiller'; break;
                            case 'membre': echo '👤 Membre'; break;
                        }
                        ?>
                    </sup>
                </div>
            </a>
            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="<?= $dashboard_url ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Gestion du profil
            </div>

            <!-- Nav Item - Edit Profile -->
            <li class="nav-item active">
                <a class="nav-link" href="edit_profile.php">
                    <i class="fas fa-fw fa-user-edit"></i>
                    <span>Modifier mon profil</span></a>
            </li>

            <!-- Nav Item - View Profile -->
            <li class="nav-item">
                <a class="nav-link" href="../frontoffice/profile.php">
                    <i class="fas fa-fw fa-eye"></i>
                    <span>Voir mon profil</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Navigation
            </div>

            <!-- Nav Item - Public Site -->
            <li class="nav-item">
                <a class="nav-link" href="../frontoffice/index.php">
                    <i class="fas fa-fw fa-globe"></i>
                    <span>Site Public</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggler (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($_SESSION['fullname'] ?? 'Utilisateur') ?></span>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <i class="fas fa-user-shield fa-fw"></i>
                                <?php elseif ($_SESSION['user_role'] === 'conseilleur'): ?>
                                    <i class="fas fa-user-tie fa-fw"></i>
                                <?php else: ?>
                                    <i class="fas fa-user fa-fw"></i>
                                <?php endif; ?>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="edit_profile.php">
                                    <i class="fas fa-user-edit fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Modifier le profil
                                </a>
                                <a class="dropdown-item" href="../frontoffice/profile.php">
                                    <i class="fas fa-eye fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Voir mon profil
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
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Modifier mon profil</h1>
                        <a href="<?= $dashboard_url ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour au dashboard
                        </a>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <div class="col-lg-8">
                            <?php if($message): ?>
                                <div class="message <?= $messageType ?>">
                                    <?= htmlspecialchars($message) ?>
                                    <?php if($messageType === 'success'): ?>
                                        <div class="loading-spinner mt-2">
                                            <i class="fas fa-spinner fa-spin"></i> Redirection...
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Profile Information Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-user fa-fw"></i> Informations personnelles
                                        <span class="user-info-badge">
                                            <?php 
                                            switch ($user->getRole()) {
                                                case 'admin': echo '👑 Administrateur'; break;
                                                case 'conseilleur': echo '💼 Conseiller'; break;
                                                case 'membre': echo '👤 Membre'; break;
                                            }
                                            ?>
                                        </span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="" enctype="multipart/form-data" id="profileForm">
                                        <input type="hidden" name="update_profile" value="1">
                                        <input type="hidden" name="remove_profile_picture" id="remove_profile_picture" value="0">
                                        
                                        <!-- Photo de profil - CORRIGÉ avec anti-cache -->
                                        <div class="profile-picture-container">
                                            <?php
                                            // UTILISATION DE LA FONCTION ANTI-CACHE
                                            $currentPicture = getProfilePictureUrl($user);
                                            $hasCustomPhoto = (!empty($user->getProfilePicture()) && $user->getProfilePicture() !== 'default-avatar.png');
                                            ?>
                                            
                                            <img src="<?= $currentPicture ?>" 
                                                 alt="Photo de profil" 
                                                 class="profile-picture"
                                                 id="currentPhoto"
                                                 onerror="this.src='../frontoffice/assets/images/default-avatar.png'">
                                            
                                            <img src="" alt="Aperçu" class="photo-preview" id="photoPreview">
                                            
                                            <div class="file-upload">
                                                <label class="file-upload-label">
                                                    <i class="fas fa-camera mr-2"></i>Changer la photo
                                                    <input type="file" name="profile_picture" id="profile_picture" 
                                                           class="file-upload-input" accept="image/*">
                                                </label>
                                            </div>
                                            
                                            <span class="upload-help">Formats: JPG, PNG, GIF - Max: 2MB</span>
                                            
                                            <!-- Bouton pour supprimer la photo -->
                                            <?php if ($hasCustomPhoto): ?>
                                            <div class="photo-actions">
                                                <button type="button" class="remove-photo" onclick="removeProfilePicture()">
                                                    <i class="fas fa-trash mr-1"></i>Supprimer la photo
                                                </button>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label" for="fullname">Nom complet *</label>
                                            <input type="text" name="fullname" id="fullname" class="form-control"
                                                   value="<?= htmlspecialchars($user->getNom()) ?>" 
                                                   required 
                                                   placeholder="Votre nom complet" />
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label" for="email">Adresse email *</label>
                                            <input type="email" name="email" id="email" class="form-control"
                                                   value="<?= htmlspecialchars($user->getEmail()) ?>" 
                                                   required 
                                                   placeholder="votre@email.com" />
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="date_naissance">Date de naissance</label>
                                                    <input type="date" name="date_naissance" id="date_naissance" class="form-control"
                                                           value="<?= htmlspecialchars($user->getDateNaissance() ?? '') ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="telephone">Téléphone</label>
                                                    <input type="tel" name="telephone" id="telephone" class="form-control"
                                                           value="<?= htmlspecialchars($user->getTelephone() ?? '') ?>" 
                                                           placeholder="+33 1 23 45 67 89" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="adresse">Adresse</label>
                                            <textarea name="adresse" id="adresse" class="form-control" 
                                                      rows="2" placeholder="Votre adresse complète"><?= htmlspecialchars($user->getAdresse() ?? '') ?></textarea>
                                        </div>

                                        <?php if ($_SESSION['user_role'] === 'conseilleur'): ?>
                                        <div class="form-group">
                                            <label class="form-label" for="specialite">Spécialité</label>
                                            <input type="text" name="specialite" id="specialite" class="form-control"
                                                   value="<?= htmlspecialchars($user->getSpecialite() ?? '') ?>" 
                                                   placeholder="Votre domaine de spécialisation" />
                                        </div>
                                        <?php endif; ?>

                                        <div class="form-group">
                                            <label class="form-label" for="bio">Biographie</label>
                                            <textarea name="bio" id="bio" class="form-control" 
                                                      rows="4" placeholder="Parlez-nous de vous..."><?= htmlspecialchars($user->getBio() ?? '') ?></textarea>
                                            <small class="form-text text-muted">Décrivez-vous en quelques mots.</small>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Rôle</label>
                                            <input type="text" class="form-control" 
                                                   value="<?= htmlspecialchars(ucfirst($user->getRole())) ?>" 
                                                   disabled readonly />
                                            <small class="form-text text-muted">Le rôle ne peut pas être modifié.</small>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Statut du compte</label>
                                            <input type="text" class="form-control" 
                                                   value="<?= htmlspecialchars(ucfirst($user->getStatus())) ?>" 
                                                   disabled readonly />
                                        </div>

                                        <!-- Password Change Section -->
                                        <div class="password-section">
                                            <h5 class="text-danger"><i class="fas fa-lock fa-fw"></i> Changer le mot de passe</h5>
                                            <p class="text-muted">Laissez ces champs vides si vous ne souhaitez pas changer votre mot de passe.</p>
                                            
                                            <div class="form-group">
                                                <label class="form-label" for="current_password">Mot de passe actuel</label>
                                                <input type="password" name="current_password" id="current_password" class="form-control"
                                                       placeholder="Saisissez votre mot de passe actuel" />
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label" for="new_password">Nouveau mot de passe</label>
                                                <input type="password" name="new_password" id="new_password" class="form-control"
                                                       placeholder="Au moins 6 caractères" 
                                                       minlength="6" />
                                                <small class="form-text text-muted">Le mot de passe doit contenir au moins 6 caractères.</small>
                                            </div>
                                        </div>

                                        <div class="form-group text-center">
                                            <button type="submit" class="btn btn-success btn-icon-split" id="submitBtn">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-save"></i>
                                                </span>
                                                <span class="text">Enregistrer les modifications</span>
<<<<<<< HEAD:SAFEProject/view/backoffice/edit_profile.php
=======
                                            </button>
>>>>>>> origin/main:view/backoffice/edit_profile.php
                                            <a href="<?= $dashboard_url ?>" class="btn btn-secondary btn-icon-split">
                                                <span class="icon text-white-50">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                                <span class="text">Annuler</span>
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Informations complémentaires -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle fa-fw"></i> Informations</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;" 
<<<<<<< HEAD:SAFEProject/view/backoffice/edit_profile.php
                                             src="../frontoffice/assets/img/profile.svg" alt="Image profil">
=======
                                             src="../frontoffice/assets/images/profile.svg" alt="Image profil">
>>>>>>> origin/main:view/backoffice/edit_profile.php
                                    </div>
                                    <p><strong>ID Utilisateur :</strong> #<?= htmlspecialchars($user->getId()) ?></p>
                                    <p><strong>Dernière mise à jour :</strong> 
                                        <?php 
                                        $updatedAt = $user->getUpdatedAt();
                                        echo (!empty($updatedAt) && $updatedAt !== '0000-00-00 00:00:00') 
                                            ? date('d/m/Y à H:i', strtotime($updatedAt)) 
                                            : 'Jamais';
                                        ?>
                                    </p>
                                    <p><strong>Compte créé le :</strong> <?= date('d/m/Y', strtotime($user->getCreatedAt())) ?></p>
                                    <hr>
                                    <p class="small text-muted">
                                        <i class="fas fa-lightbulb text-warning"></i> 
                                        Pensez à mettre à jour régulièrement vos informations pour maintenir votre profil à jour.
                                    </p>
                                    <p class="small text-muted">
                                        <i class="fas fa-camera text-info"></i> 
                                        Pour une meilleure qualité, utilisez une photo carrée de préférence.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; SafeSpace <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/js/sb-admin-2.min.js"></script>

    <script>
        // Preview de la photo SIMPLIFIÉ - sans Canvas pour éviter les lags
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Vérification rapide de la taille
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('Le fichier est trop volumineux. Taille maximum: 2MB.');
                    this.value = ''; // Reset le input file
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    // SIMPLIFICATION : utilisation directe de l'URL sans redimensionnement
                    document.getElementById('photoPreview').src = e.target.result;
                    document.getElementById('photoPreview').style.display = 'block';
                    document.getElementById('currentPhoto').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // Fonction pour supprimer la photo
        function removeProfilePicture() {
            if (confirm('Voulez-vous vraiment supprimer votre photo de profil ?')) {
                // Mettre à jour le champ caché
                document.getElementById('remove_profile_picture').value = '1';
                
                // Réinitialiser l'affichage
                document.getElementById('currentPhoto').src = '../frontoffice/assets/images/default-avatar.png';
                document.getElementById('photoPreview').style.display = 'none';
                document.getElementById('currentPhoto').style.display = 'block';
                
                // Masquer le bouton de suppression
                const removeBtn = document.querySelector('.remove-photo');
                if (removeBtn) {
                    removeBtn.style.display = 'none';
                }
                
                // Reset le input file
                document.getElementById('profile_picture').value = '';
            }
        }

        // Validation des mots de passe en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            const newPassword = document.getElementById('new_password');
            const currentPassword = document.getElementById('current_password');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('profileForm');
            
            newPassword.addEventListener('input', function() {
                if (this.value.length > 0 && currentPassword.value.length === 0) {
                    currentPassword.setCustomValidity('Veuillez saisir votre mot de passe actuel');
                } else {
                    currentPassword.setCustomValidity('');
                }
            });
            
            currentPassword.addEventListener('input', function() {
                if (newPassword.value.length > 0 && this.value.length === 0) {
                    this.setCustomValidity('Veuillez saisir votre mot de passe actuel');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Éviter les doubles soumissions
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="icon text-white-50"><i class="fas fa-spinner fa-spin"></i></span><span class="text">Enregistrement...</span>';
            });

            // Validation de la taille du fichier côté client
            form.addEventListener('submit', function(e) {
                const fileInput = document.getElementById('profile_picture');
                if (fileInput.files.length > 0) {
                    const fileSize = fileInput.files[0].size;
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    if (fileSize > maxSize) {
                        e.preventDefault();
                        alert('Le fichier est trop volumineux. Taille maximum: 2MB.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<span class="icon text-white-50"><i class="fas fa-save"></i></span><span class="text">Enregistrer les modifications</span>';
                    }
                }
            });
        });

        // Auto-redirection après succès
        <?php if($messageType === 'success'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.location.href = 'edit_profile.php';
            }, 2000);
        });
        <?php endif; ?>
    </script>

</body>
</html>