<?php
$id = $_GET['id'];
$id_Post = $_GET['id_post'];
$id_Com = $_GET['id_com'];
$author = $_GET['author'];
$message = $_GET['message'];
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
                <form action="updateRes.php"  method="get" id="form">
                    <label for="author">author:</label>
                    <input type="text" id="author" name="author" placeholder="author" value="<?php echo $author; ?>">
                    <br>
                    <label for="message">message:</label>
                    <textarea type="text" id="message" name="message" id="commentText" placeholder="Add your comment here..." ><?php echo $message; ?></textarea>
                    <br>
                    <label for="currentTime">Current Time:</label>
                    <input type="text" id="currentTime" name="currentTime" readonly>
                    <br>
                    <label for="id">id:</label>
                    <input type="text" id="id" name="id" value="<?php echo $id; ?>" readonly> 
                    <br>
                    <label for="id_com">id_Com:</label>
                    <input type="text" id="id_com" name="id_com" value="<?php echo $id_Com; ?>" readonly > 
                    <br>
                    <label for="id_post">id_Post:</label>
                    <input type="text" id="id_post" name="id_post" value="<?php echo $id_Post; ?>" readonly > 
                    
                    <input type="submit" value="Modify" class="respond">
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
<script src="assets/js/script_response.js"></script>


</body>
</html>