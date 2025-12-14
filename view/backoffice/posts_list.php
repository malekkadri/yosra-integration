<?php
// DÉMARRER LA SESSION EN PREMIER
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/admincontroller.php';
include '../../controller/PostC.php';
include '../../controller/CommentC.php';
include '../../controller/RespondC.php';

$cc = new CommentC();
$rc = new RespondC();
$pc = new PostC();
$list_Post = $pc->listPost();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

$adminController = new AdminController();

// Function to send email notification
function sendPostApprovalEmail($userEmail, $userName, $postTitle) {
    $to = $userEmail;
    $subject = "Your Post Has Been Approved - SafeSpace";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Post Approved!</h1>
            </div>
            <div class='content'>
                <h2>Hello " . htmlspecialchars($userName) . ",</h2>
                <p>Great news! Your post has been approved by our moderation team and is now live on SafeSpace.</p>
                <p><strong>Post Title:</strong> " . htmlspecialchars($postTitle) . "</p>
                <p>Your contribution helps make SafeSpace a better community for everyone. Thank you for sharing!</p>
                <a href='http://localhost/SAFEProject/view/frontoffice/index.php' class='button'>View Your Post</a>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " SafeSpace. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: SafeSpace <noreply@safespace.com>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Gestion des actions pour users
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    switch ($action) {
        case 'approve':
            $adminController->approveUser($id);
            break;
        case 'block':
            $adminController->blockUser($id);
            break;
        case 'delete':
            $adminController->deleteUser($id);
            $pc->deleteUserPost($id);
            $cc->deleteComPost($id);
            $rc->deleteResComPost($id);
            break;
    }

    header("Location: users_list.php" . (isset($_GET['search']) ? '?search=' . urlencode($_GET['search']) : ''));
    exit();
}

// Gestion des actions pour posts
if (isset($_GET['post_action'], $_GET['post_id'])) {
    $id = intval($_GET['post_id']);
    $action = $_GET['post_action'];
    
    switch ($action) {
        case 'approve':
            $pc->ProuverPost($id);
            
            // Get post details and user email for notification
            foreach ($list_Post as $post) {
                if ($post['id'] == $id) {
                    // Find the user by id_user or email
                    $users = $adminController->getAllUsers();
                    foreach ($users as $user) {
                        if ($user->getId() == ($post['id_user'] ?? '') || $user->getEmail() == ($post['author'] ?? '')) {
                            // Send email notification
                            $emailSent = sendPostApprovalEmail(
                                $user->getEmail(),
                                $user->getNom(),
                                $post['author'] ?? 'Your Post'
                            );
                            
                            // Optional: Store notification status in session
                            if ($emailSent) {
                                $_SESSION['notification'] = "Post approved and user notified via email.";
                            } else {
                                $_SESSION['notification'] = "Post approved but email notification failed.";
                            }
                            break;
                        }
                    }
                    break;
                }
            }
            break;
        case 'block':
            $pc->BlockPost($id);
            break;
    }
    header("Location: posts_list.php" . (isset($_GET['search']) ? '?search=' . urlencode($_GET['search']) : ''));
    exit();
}

// Exportation Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // getAllUsers() retourne des objets User
    $users = $adminController->getAllUsers();
    
    // En-têtes pour le téléchargement Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=utilisateurs_safespace_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Statut</th>
        </tr>";
    
    foreach($users as $user) {
        echo "<tr>";
        echo "<td>" . $user->getId() . "</td>"; // Utiliser getter
        echo "<td>" . $user->getNom() . "</td>"; // Utiliser getter
        echo "<td>" . $user->getEmail() . "</td>"; // Utiliser getter
        echo "<td>" . $user->getRole() . "</td>"; // Utiliser getter
        echo "<td>" . $user->getStatus() . "</td>"; // Utiliser getter
        echo "</tr>";
    }
    echo "</table>";
    exit();
}

// Recherche d'utilisateurs
$search = $_GET['search'] ?? '';
// getAllUsers() retourne des objets User
$allUsers = $adminController->getAllUsers();

// Filtrer les utilisateurs si une recherche est effectuée
if (!empty($search)) {
    $users = array_filter($allUsers, function($user) use ($search) {
        return stripos($user->getNom() ?? '', $search) !== false ||     // Utiliser getter
               stripos($user->getEmail() ?? '', $search) !== false ||   // Utiliser getter
               stripos($user->getRole() ?? '', $search) !== false ||    // Utiliser getter
               stripos($user->getStatus() ?? '', $search) !== false;    // Utiliser getter
    });
} else {
    $users = $allUsers;
}

// Statistiques pour l'affichage
$totalUsers = count($users);
$filteredCount = $totalUsers;
$totalAllUsers = count($allUsers);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SafeSpace - Gestion des Utilisateurs</title>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom fonts for this template-->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <style>
        .role-admin { color: #e74a3b; font-weight: bold; }
        .role-conseilleur { color: #36b9cc; font-weight: bold; }
        .role-membre { color: #2e59d9; font-weight: bold; }
        .status-approved { color: #1cc88a; font-weight: bold; }
        .status-pending { color: #f6c23e; font-weight: bold; }
        .status-blocked { color: #e74a3b; font-weight: bold; }
        .action-btn { margin-right: 5px; }
        .search-info {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4e73df;
        }
        .stats-badge {
            background: #4e73df;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 5px;
        }
        .table td {
            vertical-align: middle;
        }
        /* Style pour la recherche en temps réel */
        .dataTables_filter {
            display: none;
        }
        .search-highlight {
            background-color: #fff3cd;
            font-weight: bold;
        }
        /* Style pour les icônes de rôle */
        .role-icon {
            font-size: 1.2em;
            margin-right: 5px;
        }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SafeSpace <sup>Admin</sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Gestion
            </div>

            <!-- Nav Item - Users -->
            <li class="nav-item active">
                <a class="nav-link" href="users_list.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Utilisateurs</span></a>
            </li>
            <!-- Nav Item - Users -->
            <li class="nav-item">
                <a class="nav-link" href="posts_list.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Postes</span></a>
            </li>

            <!-- Nav Item - Charts -->
            <li class="nav-item">
                <a class="nav-link" href="charts.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Statistiques</span></a>
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

            <!-- Nav Item - Profile -->
            <li class="nav-item">
                <a class="nav-link" href="../frontoffice/profile.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Mon Profil</span></a>
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

                    <!-- Topbar Search -->
                    <form method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control bg-light border-0 small" 
                                   placeholder="Rechercher un utilisateur..." 
                                   value="<?= htmlspecialchars($search) ?>"
                                   aria-label="Search" id="searchInput">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['nom'] ?? 'Admin') ?></span>
                                <i class="fas fa-user-shield fa-fw"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="../frontoffice/profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profil
                                </a>
                                <a class="dropdown-item" href="../frontoffice/edit_profile.php">
                                    <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Modifier le profil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="return confirmLogout(event)">
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
                        <h1 class="h3 mb-0 text-gray-800">
                            Gestion des Utilisateurs
                            <span class="stats-badge"><?= $totalAllUsers ?> total</span>
                            <?php if (!empty($search)): ?>
                                <span class="stats-badge" style="background: #1cc88a;"><?= $filteredCount ?> résultat(s)</span>
                            <?php endif; ?>
                        </h1>
                        <div>
                            <a href="users_list.php?export=excel<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm mr-2">
                                <i class="fas fa-file-excel fa-sm text-white-50"></i> Exporter Excel
                            </a>
                            <a href="charts.php" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm">
                                <i class="fas fa-chart-bar fa-sm text-white-50"></i> Voir les stats
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($search)): ?>
                    <div class="search-info">
                        <strong>🔍 Recherche :</strong> "<?= htmlspecialchars($search) ?>"
                        <span class="text-muted">(<?= $filteredCount ?> utilisateur(s) trouvé(s))</span>
                        <a href="users_list.php" class="btn btn-sm btn-outline-secondary ml-2">
                            <i class="fas fa-times"></i> Effacer
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Content Row -->
                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Liste des utilisateurs</h6>
                                    <span class="badge badge-primary"><?= $filteredCount ?> utilisateur(s)</span>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($users)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nom complet</th>
                                                    <th>Email</th>
                                                    <th>Rôle</th>
                                                    <th>Statut</th>
                                                    <th>Actions (Utilisateur)</th>
                                                    <th>ID Post</th>
                                                    <th>Title</th>
                                                    <th>Message</th>
                                                    <th>Image</th>
                                                    <th>Statut (Post)</th>
                                                    <th>Actions (Post)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($users as $user): ?>
                                                <?php
                                                    $userPosts = [];
                                                    foreach ($list_Post as $post) {
                                                        if ($post['id_user'] == $user->getId() || $post['author'] == $user->getEmail()) {
                                                            $userPosts[] = $post;
                                                        }
                                                    }
                                                    $numPosts = count($userPosts);
                                                    $rowspan = max($numPosts, 1);
                                                    $first = true;
                                                    $status = $user->getStatus();
                                                    $userId = $user->getId();
                                                    $userName = htmlspecialchars($user->getNom());
                                                    $isCurrentUser = ($userId == $_SESSION['user_id']);
                                                    $searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
                                                    
                                                    $roleClasses = [
                                                        'admin' => 'role-admin',
                                                        'conseilleur' => 'role-conseilleur', 
                                                        'membre' => 'role-membre'
                                                    ];
                                                    $roleIcons = [
                                                        'admin' => '👑 Admin',
                                                        'conseilleur' => '💼 Conseilleur', 
                                                        'membre' => '👤 Membre' 
                                                    ];
                                                    $roleClass = $roleClasses[$user->getRole()] ?? 'role-membre';
                                                    $roleDisplay = $roleIcons[$user->getRole()] ?? '👤';
                                                    
                                                    $statusClasses = [
                                                        'actif' => 'status-approved',
                                                        'approved' => 'status-approved',
                                                        'en attente' => 'status-pending',
                                                        'pending' => 'status-pending',
                                                        'suspendu' => 'status-blocked',
                                                        'blocked' => 'status-blocked'
                                                    ];
                                                    $statusClass = $statusClasses[$user->getStatus()] ?? 'status-pending';
                                                    $statusText = $user->getStatus() ?? 'en attente';
                                                ?>
                                                <?php if ($numPosts == 0): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($user->getId()) ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($user->getNom()) ?></strong>
                                                        <?php if ($user->getId() == $_SESSION['user_id']): ?>
                                                            <span class="badge badge-info">Vous</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($user->getEmail()) ?></td>
                                                    <td>
                                                        <span class="<?= $roleClass ?>">
                                                            <?= $roleDisplay ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="<?= $statusClass ?>">
                                                            <?= htmlspecialchars(ucfirst($statusText)) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <!-- Approuver - seulement pour les utilisateurs en attente -->
                                                            <?php if ($status === 'en attente' || $status === 'pending'): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-success btn-sm action-btn" 
                                                                   title="Approuver cet utilisateur"
                                                                   onclick="confirmApprove(<?= $userId ?>, '<?= $userName ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-check"></i> Approuver
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Bloquer - pour les utilisateurs approuvés/actifs -->
                                                            <?php if (($status === 'actif' || $status === 'approved') && !$isCurrentUser): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-warning btn-sm action-btn" 
                                                                   title="Bloquer cet utilisateur"
                                                                   onclick="confirmBlock(<?= $userId ?>, '<?= $userName ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-ban"></i> Bloquer
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Débloquer - pour les utilisateurs bloqués/suspendus -->
                                                            <?php if ($status === 'suspendu' || $status === 'blocked'): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-success btn-sm action-btn" 
                                                                   title="Débloquer cet utilisateur"
                                                                   onclick="confirmUnblock(<?= $userId ?>, '<?= $userName ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-lock-open"></i> Débloquer
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Supprimer - pour tous sauf soi-même -->
                                                            <?php if (!$isCurrentUser): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-danger btn-sm action-btn" 
                                                                   title="Supprimer cet utilisateur"
                                                                   onclick="confirmDelete(<?= $userId ?>, '<?= $userName ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-trash"></i> Supprimer
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="badge badge-info">Utilisateur actuel</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td colspan="6">Aucun post</td>
                                                </tr>
                                                <?php else: ?>
                                                <?php foreach($userPosts as $post): ?>
                                                <tr>
                                                    <?php if ($first): ?>
                                                    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($user->getId()) ?></td>
                                                    <td rowspan="<?= $rowspan ?>">
                                                        <strong><?= htmlspecialchars($user->getNom()) ?></strong>
                                                        <?php if ($user->getId() == $_SESSION['user_id']): ?>
                                                            <span class="badge badge-info">Vous</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td rowspan="<?= $rowspan ?>"><?= htmlspecialchars($user->getEmail()) ?></td>
                                                    <td rowspan="<?= $rowspan ?>">
                                                        <span class="<?= $roleClass ?>">
                                                            <?= $roleDisplay ?>
                                                        </span>
                                                    </td>
                                                    <td rowspan="<?= $rowspan ?>">
                                                        <span class="<?= $statusClass ?>">
                                                            <?= htmlspecialchars(ucfirst($statusText)) ?>
                                                        </span>
                                                    </td>
                                                    <td rowspan="<?= $rowspan ?>">
                                                        <div class="btn-group">
                                                            <!-- Approuver - seulement pour les utilisateurs en attente -->
                                                            <?php if ($status === 'en attente' || $status === 'pending'): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-success btn-sm action-btn" 
                                                                   title="Approuver cet utilisateur"
                                                                   onclick="confirmApprove(<?= $userId ?>, '<?= $userName ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-check"></i> Approuver
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Bloquer - pour les utilisateurs approuvés/actifs -->
                                                            <?php if (($status === 'actif' || $status === 'approved') && !$isCurrentUser): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-warning btn-sm action-btn" 
                                                                   title="Bloquer cet utilisateur"
                                                                   onclick="confirmBlock(<?= $userId ?>, '<?= $userName ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-ban"></i> Bloquer
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Débloquer - pour les utilisateurs bloqués/suspendus -->
                                                            <?php if ($status === 'suspendu' || $status === 'blocked'): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-success btn-sm action-btn" 
                                                                   title="Débloquer cet utilisateur"
                                                                   onclick="confirmUnblock(<?= $userId ?>, '<?= $userName ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-lock-open"></i> Débloquer
                                                                </a>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Supprimer - pour tous sauf soi-même -->
                                                            <?php if (!$isCurrentUser): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-danger btn-sm action-btn" 
                                                                   title="Supprimer cet utilisateur"
                                                                   onclick="confirmDelete(<?= $userId ?>, '<?= $userName ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-trash"></i> Supprimer
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="badge badge-info">Utilisateur actuel</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <?php $first = false; ?>
                                                    <?php endif; ?>
                                                    <td><?= htmlspecialchars($post['id']) ?></td>
                                                    <td><?= htmlspecialchars($post['author'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars(substr($post['message'] ?? '', 0, 100)) . (strlen($post['message'] ?? '') > 100 ? '...' : '') ?></td>
                                                    <td>
                                                        <?php if (!empty($post['image'])): ?>
                                                            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Post image" style="max-width: 100px; max-height: 100px;">
                                                        <?php else: ?>
                                                            Aucune image
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($post['status'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <?php if (($post['status'] ?? '') !== 'approved'): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-success btn-sm action-btn" 
                                                                   title="Approuver ce post"
                                                                   onclick="confirmApprovePost(<?= $post['id'] ?>, '<?= htmlspecialchars($post['author'] ?? 'Post') ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-check"></i> Approuver
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (($post['status'] ?? '') !== 'blocked'): ?>
                                                                <a href="javascript:void(0);" 
                                                                   class="btn btn-warning btn-sm action-btn" 
                                                                   title="Bloquer ce post"
                                                                   onclick="confirmBlockPost(<?= $post['id'] ?>, '<?= htmlspecialchars($post['author'] ?? 'Post') ?>', '<?= $searchParam ?>')">
                                                                    <i class="fas fa-ban"></i> Bloquer
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="javascript:void(0);" 
                                                               class="btn btn-danger btn-sm action-btn" 
                                                               title="Supprimer ce post"
                                                               onclick="confirmDeletePost(<?= $post['id'] ?>, '<?= htmlspecialchars($post['author'] ?? 'Post') ?>', '<?= $searchParam ?>')">
                                                                <i class="fas fa-trash"></i> Supprimer
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                                            <h4 class="text-gray-500">Aucun utilisateur trouvé</h4>
                                            <?php if (!empty($search)): ?>
                                                <p class="text-muted">Aucun utilisateur ne correspond à votre recherche "<?= htmlspecialchars($search) ?>"</p>
                                                <a href="users_list.php" class="btn btn-primary">
                                                    <i class="fas fa-list"></i> Voir tous les utilisateurs
                                                </a>
                                            <?php else: ?>
                                                <p class="text-muted">Aucun utilisateur n'est inscrit pour le moment.</p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
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

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap core JavaScript-->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
        // Fonction pour confirmer la déconnexion (pour le menu déroulant)
        function confirmLogout(event) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Se déconnecter ?',
                html: 'Voulez-vous vraiment quitter <b>SafeSpace Admin</b> ?',
                icon: 'question',
                iconColor: '#4e73df',
                showCancelButton: true,
                confirmButtonText: 'Oui, déconnecter',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#4e73df',
                cancelButtonColor: '#6c757d',
                background: '#ffffff',
                color: '#333333',
                backdrop: 'rgba(248, 250, 252, 0.8)',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Afficher un loader
                    Swal.fire({
                        title: 'Déconnexion...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Rediriger vers logout.php
                    setTimeout(() => {
                        window.location.href = '../frontoffice/logout.php';
                    }, 500);
                }
            });
            
            return false;
        }

        // Fonction pour confirmer l'approbation d'un utilisateur
        function confirmApprove(userId, userName, searchParam) {
            Swal.fire({
                title: 'Approuver l\'utilisateur',
                html: `Voulez-vous approuver l'utilisateur <b>${userName}</b> ?<br>
                       <small>Il pourra se connecter et utiliser le système.</small>`,
                icon: 'question',
                iconColor: '#1cc88a',
                showCancelButton: true,
                confirmButtonText: 'Oui, approuver',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#6c757d',
                background: '#ffffff',
                color: '#333333',
                backdrop: 'rgba(248, 250, 252, 0.8)',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Afficher un loader
                    Swal.fire({
                        title: 'Traitement en cours...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Rediriger vers l'action
                    setTimeout(() => {
                        window.location.href = `posts_list.php?action=approve&id=${userId}${searchParam}`;
                    }, 500);
                }
            });
        }

        // Fonction pour confirmer le blocage d'un utilisateur
        function confirmBlock(userId, userName, searchParam) {
            Swal.fire({
                title: 'Bloquer l\'utilisateur',
                html: `Voulez-vous bloquer l'utilisateur <b>${userName}</b> ?<br>
                       <small>Il ne pourra plus se connecter au système.</small>`,
                icon: 'warning',
                iconColor: '#f6c23e',
                showCancelButton: true,
                confirmButtonText: 'Oui, bloquer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#f6c23e',
                cancelButtonColor: '#6c757d',
                background: '#ffffff',
                color: '#333333',
                backdrop: 'rgba(248, 250, 252, 0.8)',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Afficher un loader
                    Swal.fire({
                        title: 'Traitement en cours...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Rediriger vers l'action
                    setTimeout(() => {
                        window.location.href = `posts_list.php?action=block&id=${userId}${searchParam}`;
                    }, 500);
                }
            });
        }

        // Fonction pour confirmer le déblocage d'un utilisateur
        function confirmUnblock(userId, userName, searchParam) {
            Swal.fire({
                title: 'Débloquer l\'utilisateur',
                html: `Voulez-vous débloquer l'utilisateur <b>${userName}</b> ?<br>
                       <small>Il pourra à nouveau se connecter au système.</small>`,
                icon: 'question',
                iconColor: '#36b9cc',
                showCancelButton: true,
                confirmButtonText: 'Oui, débloquer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#36b9cc',
                cancelButtonColor: '#6c757d',
                background: '#ffffff',
                color: '#333333',
                backdrop: 'rgba(248, 250, 252, 0.8)',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Afficher un loader
                    Swal.fire({
                        title: 'Traitement en cours...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Rediriger vers l'action
                    setTimeout(() => {
                        window.location.href = `posts_list.php?action=approve&id=${userId}${searchParam}`;
                    }, 500);
                }
            });
        }

        // Fonction pour confirmer la suppression d'un utilisateur
        function confirmDelete(userId, userName, searchParam) {
            Swal.fire({
                title: '⚠️ Suppression définitive',
                html: `<div style="text-align: left;">
                        <p><strong>Êtes-vous sûr de vouloir supprimer cet utilisateur ?</strong></p>
                        <p><b>Nom :</b> ${userName}</p>
                        <p><b>ID :</b> ${userId}</p>
                        <div class="alert alert-danger mt-3" style="font-size: 0.9em;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention :</strong> Cette action est irréversible. Toutes les données de l'utilisateur seront définitivement supprimées.
                        </div>
                       </div>`,
                icon: 'warning',
                iconColor: '#e74a3b',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer définitivement',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#36b9cc',
                background: '#ffffff',
                color: '#333333',
                backdrop: 'rgba(0,0,0,0.4)',
                allowOutsideClick: false,
                allowEscapeKey: true,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Afficher un loader
                    Swal.fire({
                        title: 'Suppression en cours...',
                        html: 'Cette opération peut prendre quelques secondes.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Rediriger vers l'action delete
                    setTimeout(() => {
                        window.location.href = `posts_list.php?action=delete&id=${userId}${searchParam}`;
                    }, 800);
                }
            });
        }

        // Fonction pour confirmer l'approbation d'un post
        function confirmApprovePost(postId, postTitle, searchParam) {
            Swal.fire({
                title: 'Approuver le post',
                html: `Voulez-vous approuver le post <b>${postTitle}</b> ?<br>
                       <small>Il sera visible sur le site.</small>`,
                icon: 'question',
                iconColor: '#1cc88a',
                showCancelButton: true,
                confirmButtonText: 'Oui, approuver',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#6c757d',
                background: '#ffffff',
                color: '#333333',
                backdrop: 'rgba(248, 250, 252, 0.8)',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Traitement en cours...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    setTimeout(() => {
                        window.location.href = `posts_list.php?post_action=approve&post_id=${postId}${searchParam}`;
                    }, 500);
                }
            });
        }

        // Fonction pour confirmer le blocage d'un post
        function confirmBlockPost(postId, postTitle, searchParam) {
            Swal.fire({
                title: 'Bloquer le post',
                html: `Voulez-vous bloquer le post <b>${postTitle}</b> ?<br>
                       <small>Il ne sera plus visible.</small>`,
                icon: 'warning',
                iconColor: '#f6c23e',
                showCancelButton: true,
                confirmButtonText: 'Oui, bloquer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#f6c23e',
                cancelButtonColor: '#6c757d',
                background: '#ffffff',
                color: '#333333',
                backdrop: 'rgba(248, 250, 252, 0.8)',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Traitement en cours...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    setTimeout(() => {
                        window.location.href = `posts_list.php?post_action=block&post_id=${postId}${searchParam}`;
                    }, 500);
                }
            });
        }

        // Fonction pour confirmer la suppression d'un post
        function confirmDeletePost(postId, postTitle, searchParam) {
            Swal.fire({
                title: '⚠️ Suppression définitive',
                html: `<div style="text-align: left;">
                        <p><strong>Êtes-vous sûr de vouloir supprimer ce post ?</strong></p>
                        <p><b>Titre :</b> ${postTitle}</p>
                        <p><b>ID :</b> ${postId}</p>
                        <div class="alert alert-danger mt-3" style="font-size: 0.9em;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention :</strong> Cette action est irréversible.
                        </div>
                       </div>`,
                icon: 'warning',
                iconColor: '#e74a3b',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer définitivement',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#36b9cc',
                background: '#ffffff',
                color: '#333333',
                backdrop: 'rgba(0,0,0,0.4)',
                allowOutsideClick: false,
                allowEscapeKey: true,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Suppression en cours...',
                        html: 'Cette opération peut prendre quelques secondes.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    setTimeout(() => {
                        // Assuming deletePost.php exists as in the second document
                        window.location.href = `deletePost.php?id=${postId}`;
                    }, 800);
                }
            });
        }

        // Initialisation de DataTable
        $(document).ready(function() {
            // Initialiser DataTable
            var table = $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json"
                },
                "order": [[0, "desc"]],
                "pageLength": 25,
                "dom": '<"top"f>rt<"bottom"lip><"clear">'
            });

            // Synchroniser la recherche du formulaire avec DataTables
            $('#searchInput').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Si une recherche existe déjà au chargement, l'appliquer à DataTables
            var initialSearch = '<?= htmlspecialchars($search) ?>';
            if (initialSearch) {
                table.search(initialSearch).draw();
            }
        });
    </script>

</body>
</html>