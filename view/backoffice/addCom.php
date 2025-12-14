<?php
$id_Post=$_GET['id'];
// Activer les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
session_start();

// Inclure l'AuthController pour vérifier l'authentification
$controller_path = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/authcontroller.php';
if (file_exists($controller_path)) {
    require_once $controller_path;
    $authController = new AuthController();
} else {
    die("Erreur: Fichier contrôleur introuvable");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeSpace - Accueil</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
   
    </style>
</head>
<body class="is-preload">

<div id="page-wrapper">
    <header id="header">
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <a class="navbar-brand nav-logo text-primary" href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                    <img src="images/logo.png" alt="SafeSpace Logo" style="height: 40px; width: auto;">
                    <h1 style="margin: 0; font-size: 1.5em;">SafeSpace</h1>
                </a>
            </div>
           
            <nav>
                <a href="index.php">Accueil</a> |
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="../backoffice/index.php">Admin</a> |
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'conseilleur'): ?>
                    <a href="../backoffice/adviser_dashboard.php">Tableau de bord</a> |
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">Profil</a> |
                    <a href="logout.php">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php">Connexion</a> |
                    <a href="register.php">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Bienvenue sur SafeSpace</h2>
                <p>Envie de libérer vos émotions ? Partagez vos pensées en toute sécurité.</p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="welcome-message">
                        <h3>Bienvenue, <?= htmlspecialchars($_SESSION['fullname'] ?? 'Utilisateur') ?> !</h3>
                        <p>Votre rôle: <?= htmlspecialchars($_SESSION['user_role'] ?? 'Membre') ?></p>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="register.php" class="button primary" style="margin-right: 10px;">S'inscrire</a>
                        <a href="login.php" class="button">Se connecter</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        

        <!-- Content -->
        <div class="wrapper">
            
            <div class="inner">
                <form action="AjoutCom.php"  method="get" id="form">
                    <label for="author">author:</label>
                    <input type="text" id="author" name="author" placeholder="author">
                    <br>
                    <label for="message">message:</label>
                    <textarea type="text" id="message" name="message" id="commentText" placeholder="Add your comment here..." ></textarea>
                    <br>
                    <label for="id_post">Id_Com:</label>
                    <input type="text" id="id_post" name="id_post" value="<?php echo $id_Post ?>" readonly>
                    <br>
                    <label for="currentTime">Current Time:</label>
                    <input type="text" id="currentTime" name="currentTime" readonly>
                    <br>
                    <input type="submit" value="add" class="comment">
                    
            </div>
        </div>
    </section>

    <section id="footer">
        <div class="inner">
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p>Connecté en tant que: <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
            <?php endif; ?>
        </div>
    </section>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/script_respone.js"></script>


</body>
</html>