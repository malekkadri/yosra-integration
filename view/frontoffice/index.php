<?php
// Activer les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
session_start();

// Inclure l'AuthController pour vérifier l'authentification
$controller_path = $_SERVER['DOCUMENT_ROOT'] . '/SAFEProject/controller/AuthController.php';
if (file_exists($controller_path)) {
    require_once $controller_path;
    $authController = new AuthController();
} else {
    die("Erreur: Fichier contrôleur introuvable");
}

//for Post
require_once $_SERVER['DOCUMENT_ROOT'].'/SAFEProject/controller/usercontroller.php';

$userController = new UserController();
$users = $userController->listUsers();

include '../../controller/PostC.php';
include '../../controller/CommentC.php';
include '../../controller/RespondC.php';
include '../../controller/ArticleC.php';
include '../../controller/CategorieC.php';
include '../../controller/ReactionC.php';
include '../../controller/CommentArticleC.php';

$pc = new PostC();
$list_Post = $pc->listPostProuver();

$cc = new CommentC();
$rc = new RespondC();
$articleC = new ArticleC();
$categorieC = new CategorieC();
$reactionC = new ReactionC();
$commentArticleC = new CommentArticleC();
$articles = $articleC->listArticles('approved');

// Create a function to get user fullname by ID
function getUserFullnameById($userId, $userController) {
    $user = $userController->getUser($userId);
    if ($user) {
        return $user->getNom();
    }
    return "Utilisateur inconnu";
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
                    <a href="../backoffice/index.php">Dashboard</a> |
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

        <div class="wrapper">
            <div class="inner">
                <section class="features">
                    <div class="feature">
                        <h3 class="major">🔒 Sécurisé</h3>
                        <p>Vos données sont protégées et votre anonymat préservé</p>
                    </div>
                    <div class="feature">
                        <h3 class="major">🤝 Bienveillant</h3>
                        <p>Une communauté respectueuse et à l'écoute</p>
                    </div>
                    <div class="feature">
                        <h3 class="major">💬 Libre</h3>
                        <p>Exprimez-vous sans jugement dans un espace safe</p>
                    </div>
                </section>
                <section class="articles-section">
                    <div style="display:flex; align-items:center; justify-content: space-between;">
                        <h2>Articles approuvés</h2>
                        <div>
                            <a class="button" href="article_detail.php">Découvrir</a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a class="button primary" href="addArticle.php">Proposer un article</a>
                            <?php else: ?>
                                <a class="button" href="login.php">Connectez-vous pour proposer</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="comments-section">
                        <ul class="comments-list">
                            <?php foreach ($articles as $article): ?>
                                <?php
                                $cat = $categorieC->getCategorie((int)$article['id_categorie']);
                                $categoryName = $cat['nom_categorie'] ?? 'Non classé';
                                $counts = $reactionC->countReactionsByArticle((int)$article['id_article']);
                                ?>
                                <li class="comment-item">
                                    <div class="comment-header">
                                        <span class="author"><?php echo htmlspecialchars($categoryName); ?></span>
                                        <span class="time"><?php echo htmlspecialchars($article['date_creation']); ?></span>
                                    </div>
                                    <div class="message"><strong><?php echo htmlspecialchars($article['titre']); ?></strong><br><?php echo nl2br(htmlspecialchars(substr($article['contenu'],0,160))); ?>...</div>
                                    <div class="comment-footer">
                                        <div class="comment-actions">
                                            <a class="button" href="article_detail.php?id=<?php echo $article['id_article']; ?>">Lire l'article</a>
                                            <span class="id">👍 <?php echo $counts['like'] ?? 0; ?> | 👎 <?php echo $counts['dislike'] ?? 0; ?></span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
                <!-- Section Postes approuvees -->
                <div class="comments-section">
                    <h2>les Postes approuvees</h2>
                    <ul class="comments-list">
                        <?php
                        foreach($list_Post as $post){
                            // Get user fullname instead of showing id_user
                            $userFullname = getUserFullnameById($post['id_user'], $userController);
                        ?>
                        <li class="comment-item">
                            <div class="comment-header">
                                <span class="author"><?php echo htmlspecialchars($userFullname); ?></span>
                                <span class="author"><?php echo htmlspecialchars($post['author']); ?></span>
                                <span class="time"><?php echo htmlspecialchars($post['time']); ?></span>
                            </div>
                            
                            <div class="message"><?php echo nl2br(htmlspecialchars($post['message'])); ?></div>
                            
                            <!-- Image Display Section -->
                            <?php if (!empty($post['image']) && file_exists($post['image'])): ?>
                                <div class="image-container">
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>" 
                                        alt="Post image" 
                                        class="post-image"
                                        onerror="this.style.display='none'">
                                </div>
                            <?php elseif (!empty($post['image'])): ?>
                                <div class="no-image">
                                    Image not found: <?php echo htmlspecialchars(basename($post['image'])); ?>
                                </div>
                            <?php else: ?>
                                <div class="no-image">No image</div>
                            <?php endif; ?>
                            
                            <div class="comment-footer">
                                <span class="id">ID: <?php echo $post['id']; ?></span>
                                <div class="comment-actions">
                                    <!-- You can add other actions here -->
                    
                                    <button class="btn-respond" onclick="window.location.href='addCom.php?id=<?=$post['id']; ?>'">
                                        Ajouter Commentaire
                                    </button>
                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <button class="btn-update" onclick="window.location.href='modifierPost.php?id=<?=$post['id']; ?>&author=<?=$post['author']; ?>&message=<?=$post['message']; ?>'">
                                            Modifier Post
                                        </button>
                                        <button class="btn-delete" onclick="window.location.href='deletePost.php?id=<?=$post['id']; ?>'">
                                            Supprimer Post
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <!-- comments-list -->
                    <div class="response-section">
                        <h3>les Commentaires</h3>
                        <ul class="response-list">
                            <?php
                            
                            $list_Com = $cc->listComment($post['id']);
                            foreach($list_Com as $comment){
                            ?>
                            <li class="response-item">
                                <div class="response-header">
                                    <span class="author"><?php echo htmlspecialchars($comment['author']); ?></span>
                                    <span class="time"><?php echo htmlspecialchars($comment['time']); ?></span>
                                </div>
                                <div class="message"><?php echo htmlspecialchars($comment['message']); ?></div>
                                <div class="comment-footer">
                                <span class="id">ID: <?php echo $comment['id']; ?></span>
                                <div class="response-actions">
                                    
                                    <button class="btn-respond" onclick="window.location.href='addRes.php?id_Com=<?=$comment['id']; ?>&id_post=<?=$comment['id_post']; ?>'">
                                        Ajouter Reponse
                                    </button>
                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <button class="btn-delete" onclick="window.location.href='deleteCom.php?id=<?=$comment['id']; ?>'">
                                            Supprimer Commentaire
                                        </button>
                                        <button class="btn-update" onclick="window.location.href='modifierCom.php?id=<?=$comment['id']; ?>&id_post=<?=$comment['id_post']; ?>&author=<?=$comment['author']; ?>&message=<?=$comment['message']; ?>'">
                                            Modifier Commentaire
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            </li>
                    <!-- respond-list -->
                    <div class="response-section">
                        <h3>les Reponses</h3>
                        <ul class="response-list">
                            <?php
                            
                            $list_Res = $rc->listRespond($comment['id']);
                            foreach($list_Res as $respond){
                            ?>
                            <li class="response-item">
                                <div class="response-header">
                                    <span class="author"><?php echo htmlspecialchars($respond['author']); ?></span>
                                    <span class="time"><?php echo htmlspecialchars($respond['time']); ?></span>
                                </div>
                                <div class="message"><?php echo htmlspecialchars($respond['message']); ?></div>
                                <div class="comment-footer">
                                <span class="id">ID: <?php echo $respond['id']; ?></span>
                                <div class="response-actions">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <button class="btn-delete" onclick="window.location.href='deleteRes.php?id=<?=$respond['id']; ?>'">
                                        Supprimer
                                    </button>
                                    <button class="btn-update" onclick="window.location.href='modifierRes.php?id=<?=$respond['id']; ?>&id_post=<?=$respond['id_post']; ?>&id_com=<?=$respond['id_com']; ?>&author=<?=$respond['author']; ?>&message=<?=$respond['message']; ?>'">
                                        Modifier
                                    </button>
                                <?php endif; ?>
                                </div>
                            </div>
                            </li>
                            <?php } ?>    
                        </ul>
                    </div>
                            <?php } ?>    
                        </ul>
                    </div>
                        <?php } ?>    
                    </ul>
                </div>
                <!-- fin section posts -->
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
<script src="assets/js/register.js"></script>
<script src="assets/js/register.js"></script>

<script src="assets/js/script_post.js"></script>


</body>
</html>