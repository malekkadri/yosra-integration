<?php
// DÉMARRER LA SESSION EN PREMIER
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/admincontroller.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

$adminController = new AdminController();

// SOLUTION 1 : Utiliser la méthode qui retourne un tableau
// Si vous avez la méthode getAllUsersArray() qui retourne un tableau
if (method_exists($adminController, 'getAllUsersArray')) {
    $users = $adminController->getAllUsersArray();
} else {
    // SOLUTION 2 : Convertir les objets en tableaux manuellement
    $userObjects = $adminController->getAllUsers();
    $users = [];
    
    foreach ($userObjects as $user) {
        if (is_object($user)) {
            // Vérifier si c'est un objet User avec des getters ou des propriétés publiques
            if (method_exists($user, 'toArray')) {
                $users[] = $user->toArray();
            } elseif (method_exists($user, 'getId')) {
                // Utiliser les getters si disponibles
                $users[] = [
                    'id' => $user->getId(),
                    'fullname' => method_exists($user, 'getFullname') ? $user->getFullname() : '',
                    'email' => method_exists($user, 'getEmail') ? $user->getEmail() : '',
                    'role' => method_exists($user, 'getRole') ? $user->getRole() : '',
                    'status' => method_exists($user, 'getStatus') ? $user->getStatus() : '',
                    'created_at' => method_exists($user, 'getCreatedAt') ? $user->getCreatedAt() : ''
                ];
            } else {
                // Utiliser les propriétés publiques directement
                $users[] = [
                    'id' => isset($user->id) ? $user->id : null,
                    'fullname' => isset($user->fullname) ? $user->fullname : '',
                    'email' => isset($user->email) ? $user->email : '',
                    'role' => isset($user->role) ? $user->role : '',
                    'status' => isset($user->status) ? $user->status : '',
                    'created_at' => isset($user->created_at) ? $user->created_at : ''
                ];
            }
        } else {
            // Si déjà un tableau, l'ajouter directement
            $users[] = $user;
        }
    }
}

// Vérifier que $users est bien un tableau
if (!is_array($users)) {
    $users = [];
}

// Statistiques pour les graphiques
$totalUsers = count($users);
$approvedUsers = count(array_filter($users, function($user) {
    return isset($user['status']) && $user['status'] === 'approved';
}));
$pendingUsers = count(array_filter($users, function($user) {
    return isset($user['status']) && $user['status'] === 'en attente';
}));
$blockedUsers = count(array_filter($users, function($user) {
    return isset($user['status']) && $user['status'] === 'blocked';
}));

// Statistiques par rôle
$adminUsers = count(array_filter($users, function($user) {
    return isset($user['role']) && $user['role'] === 'admin';
}));
$conseilleurUsers = count(array_filter($users, function($user) {
    return isset($user['role']) && $user['role'] === 'conseilleur';
}));
$membreUsers = count(array_filter($users, function($user) {
    return isset($user['role']) && $user['role'] === 'membre';
}));

// Obtenir les statistiques des étoiles
$ratingStats = $adminController->getRatingStats();

// Initialiser la distribution avec des zéros
$distributionData = array_fill(1, 5, 0);

// Remplir la distribution avec les données réelles
if (!empty($ratingStats['distribution']) && is_array($ratingStats['distribution'])) {
    foreach ($ratingStats['distribution'] as $dist) {
        if (isset($dist['rating'])) {
            $distributionData[$dist['rating']] = $dist['count'];
        }
    }
}

// Vérifier si ce sont des données de démo
$isDemoData = isset($ratingStats['demo_data']) ? $ratingStats['demo_data'] : false;

// S'assurer que toutes les clés existent
if (!isset($ratingStats['total_ratings'])) $ratingStats['total_ratings'] = 0;
if (!isset($ratingStats['average_rating'])) $ratingStats['average_rating'] = 0;
if (!isset($ratingStats['users_with_ratings'])) $ratingStats['users_with_ratings'] = 0;

// Formater la moyenne des étoiles
$averageRating = number_format($ratingStats['average_rating'], 1);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SafeSpace - Statistiques</title>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom fonts for this template-->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .star-rating {
            color: #FFD700;
            font-size: 1.5em;
        }
        .rating-card {
            border-left: 4px solid #FFD700;
        }
        .distribution-bar {
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            margin: 5px 0;
            overflow: hidden;
        }
        .distribution-fill {
            height: 100%;
            background: linear-gradient(90deg, #FFD700, #FFA500);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        .chart-container {
            position: relative;
            height: 300px;
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
            <li class="nav-item">
                <a class="nav-link" href="users_list.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Utilisateurs</span></a>
            </li>

            <!-- Nav Item - Charts -->
            <li class="nav-item active">
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

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></span>
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
                    <h1 class="h3 mb-2 text-gray-800">Statistiques</h1>
                    <p class="mb-4">Vue d'ensemble des données de SafeSpace</p>

                    <!-- Cartes de statistiques des étoiles -->
                    <div class="row">
                        <?php if ($isDemoData): ?>
                        <div class="col-12">
                            <div class="alert alert-warning mb-4" role="alert">
                                <i class="fas fa-info-circle"></i> Statistiques des étoiles en démonstration
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Total des évaluations -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 rating-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Évaluations totales</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $ratingStats['total_ratings'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-star fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Moyenne des étoiles -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 rating-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Note moyenne
                                                <?php if ($isDemoData): ?>
                                                <span class="badge badge-warning ml-1">Démo</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $averageRating ?>
                                                <span class="star-rating">
                                                    <?php 
                                                    $avg = $ratingStats['average_rating'];
                                                    $fullStars = floor($avg);
                                                    $halfStar = ($avg - $fullStars) >= 0.5;
                                                    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                                    
                                                    echo str_repeat('★', $fullStars);
                                                    if ($halfStar) echo '½';
                                                    echo str_repeat('☆', $emptyStars);
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-star-half-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Utilisateurs ayant noté -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 rating-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Utilisateurs ayant noté</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $ratingStats['users_with_ratings'] ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Taux de participation -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 rating-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Taux de participation</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $totalUsers > 0 ? round(($ratingStats['users_with_ratings'] / $totalUsers) * 100, 1) : 0 ?>%
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <div class="col-xl-8 col-lg-7">

                            <!-- Distribution des étoiles -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Distribution des Étoiles</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="starDistributionChart"></canvas>
                                    </div>
                                    <div class="mt-3">
                                        <?php 
                                        $totalRatingsForPercentage = $ratingStats['total_ratings'] > 0 ? $ratingStats['total_ratings'] : 1;
                                        for($i = 5; $i >= 1; $i--): 
                                            $count = $distributionData[$i];
                                            $percentage = round(($count / $totalRatingsForPercentage) * 100, 1);
                                        ?>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>
                                                    <?= str_repeat('★', $i) ?>
                                                    <?= str_repeat('☆', 5 - $i) ?>
                                                    (<?= $i ?> étoiles)
                                                </span>
                                                <span><?= $count ?> évaluations (<?= $percentage ?>%)</span>
                                            </div>
                                            <div class="distribution-bar">
                                                <div class="distribution-fill" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Bar Chart des statuts -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Utilisateurs par Statut</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="statusBarChart"></canvas>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Donut Chart des rôles -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Répartition par Rôle</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="rolePieChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Admins
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Conseilleurs
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-info"></i> Membres
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Summary -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Résumé des Statuts</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="statusPieChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Approuvés
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-warning"></i> En attente
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-danger"></i> Bloqués
                                        </span>
                                    </div>
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
    <script src="assets/vendor/chart.js/Chart.min.js"></script>

    <script>
        // Fonction pour confirmer la déconnexion
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

        // Données pour les graphiques
        const userData = {
            total: <?= $totalUsers ?>,
            approved: <?= $approvedUsers ?>,
            pending: <?= $pendingUsers ?>,
            blocked: <?= $blockedUsers ?>,
            admin: <?= $adminUsers ?>,
            conseilleur: <?= $conseilleurUsers ?>,
            membre: <?= $membreUsers ?>
        };

        // Données pour la distribution des étoiles
        const ratingData = {
            distribution: [<?= $distributionData[5] ?>, <?= $distributionData[4] ?>, <?= $distributionData[3] ?>, <?= $distributionData[2] ?>, <?= $distributionData[1] ?>],
            total: <?= $ratingStats['total_ratings'] ?>,
            average: <?= $ratingStats['average_rating'] ?>
        };

        // Graphique en barres pour la distribution des étoiles
        var ctxStar = document.getElementById("starDistributionChart");
        if (ctxStar) {
            var starDistributionChart = new Chart(ctxStar, {
                type: 'bar',
                data: {
                    labels: ["5 étoiles", "4 étoiles", "3 étoiles", "2 étoiles", "1 étoile"],
                    datasets: [{
                        label: "Nombre d'évaluations",
                        backgroundColor: ["#FFD700", "#FFC107", "#FF9800", "#FF5722", "#F44336"],
                        hoverBackgroundColor: ["#FFC107", "#FFA000", "#FF6F00", "#E64A19", "#D32F2F"],
                        borderColor: "#FFD700",
                        data: ratingData.distribution,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1
                            }
                        }]
                    },
                },
            });
        }

        // Graphique circulaire des rôles
        var ctx = document.getElementById("rolePieChart");
        if (ctx) {
            var rolePieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ["Admins", "Conseilleurs", "Membres"],
                    datasets: [{
                        data: [userData.admin, userData.conseilleur, userData.membre],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var total = dataset.data.reduce(function(previousValue, currentValue) {
                                    return previousValue + currentValue;
                                });
                                var currentValue = dataset.data[tooltipItem.index];
                                var percentage = Math.floor(((currentValue/total) * 100)+0.5);         
                                return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
                            }
                        }
                    },
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 70,
                },
            });
        }

        // Graphique circulaire des statuts
        var ctx2 = document.getElementById("statusPieChart");
        if (ctx2) {
            var statusPieChart = new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: ["Approuvés", "En attente", "Bloqués"],
                    datasets: [{
                        data: [userData.approved, userData.pending, userData.blocked],
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                        hoverBackgroundColor: ['#17a673', '#dda20a', '#be2617'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var total = dataset.data.reduce(function(previousValue, currentValue) {
                                    return previousValue + currentValue;
                                });
                                var currentValue = dataset.data[tooltipItem.index];
                                var percentage = Math.floor(((currentValue/total) * 100)+0.5);         
                                return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
                            }
                        }
                    },
                    legend: {
                        display: false
                    },
                },
            });
        }

        // Graphique en barres des statuts
        var ctx3 = document.getElementById("statusBarChart");
        if (ctx3) {
            var statusBarChart = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: ["Approuvés", "En attente", "Bloqués"],
                    datasets: [{
                        label: "Nombre d'utilisateurs",
                        backgroundColor: ["#1cc88a", "#f6c23e", "#e74a3b"],
                        hoverBackgroundColor: ["#17a673", "#dda20a", "#be2617"],
                        borderColor: "#4e73df",
                        data: [userData.approved, userData.pending, userData.blocked],
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                stepSize: 1
                            }
                        }]
                    },
                },
            });
        }

        // Redimensionner les graphiques lorsque la fenêtre change de taille
        $(window).resize(function(){
            if (starDistributionChart) starDistributionChart.resize();
            if (rolePieChart) rolePieChart.resize();
            if (statusPieChart) statusPieChart.resize();
            if (statusBarChart) statusBarChart.resize();
        });
    </script>

</body>
</html>