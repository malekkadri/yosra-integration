<?php 
$user_id =$_GET['user_id']
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeSpace - Accueil</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <noscript><link rel="stylesheet" href="assets/css/noscript.css"></noscript>
</head>
<body class="is-preload">

<div id="page-wrapper">

    <!-- Header -->
    <header id="header">
        <h1><a href="index.php">SafeSpace</a></h1>
        <nav>
            <a href="index.php">Accueil</a> |
            <a href="../backoffice/index.php">Admin</a> |
            <a href="profile.php">Profil</a> |
            <a href="login.php">Connexion</a> |
            <a href="register.php">Inscription</a>
        </nav>
    </header>

    <!-- Wrapper -->
    <section id="wrapper">
        <header>
            <div class="inner">
                <h2>Bienvenue sur SafeSpace</h2>
                <p>Envie de libérer vos émotions ? Partagez vos pensées en toute sécurité.</p>
            </div>
        </header>

        

        <!-- Content -->
        <div class="wrapper">
            
            <div class="inner">
                <?php
                // Bloc d'affichage des erreurs
                if (isset($_GET['error'])) {
                    $error_message = htmlspecialchars($_GET['error']);
                    echo '<p style="color: red; font-weight: bold; margin-bottom: 15px; border: 1px solid #ffdddd; padding: 10px; border-radius: 5px; background-color: #ffeaea;">⚠️ ' . $error_message . '</p>';
                }
                ?>
                
                <form action="AjoutPost.php" method="post" enctype="multipart/form-data" id="form">
                    <label for="user_id">User_ID:</label>
                    <input type="text" id="user_id" name="user_id" placeholder="user_id" value="<?php echo $user_id; ?>" readonly>
                    <br>
                    <label for="author">Title:</label>
                    <input type="text" id="author" name="author" placeholder="title">
                    <br>
                    <label for="message">message:</label>
                    <textarea type="text" id="message" name="message" id="commentText" placeholder="Add your comment here..." ></textarea>
                    <br>
                    <label for="currentTime">Current Time:</label>
                    <input type="text" id="currentTime" name="currentTime" readonly>
                    <br>
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image">
                    <br>
                    <input type="submit" value="add" class="comment">
                </form>
                
            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="footer">
        <div class="inner">
            <p>Protégeons ensemble, agissons avec bienveillance.</p>
        </div>
    </section>

</div>

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/jquery.scrollex.min.js"></script>
<script src="assets/js/browser.min.js"></script>
<script src="assets/js/breakpoints.min.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/script_post.js"></script>

</body>
</html>