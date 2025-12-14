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
$categories = $categorieC->listCategories();
$selectedCategory = (int)($_GET['category'] ?? 0);
$articleSearch = trim($_GET['q'] ?? '');

// allow simple filtering from the landing page
if ($selectedCategory) {
    $articles = array_filter($articles, function ($article) use ($selectedCategory) {
        return (int)$article['id_categorie'] === $selectedCategory;
    });
}

if ($articleSearch !== '') {
    $articles = array_filter($articles, function ($article) use ($articleSearch) {
        return stripos($article['titre'], $articleSearch) !== false || stripos($article['contenu'], $articleSearch) !== false;
    });
}

$articleCount = count($articles);

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

<div id="page-wrapper" class="page-shell">
    <header id="header" class="frosted-bar">
        <div class="header-inner">
            <a class="navbar-brand nav-logo text-primary" href="index.php" aria-label="Retour à l'accueil">
                <img src="images/logo.png" alt="SafeSpace Logo" class="brand-logo">
                <div>
                    <p class="eyebrow">Espace bienveillant</p>
                    <h1 class="brand-title">SafeSpace</h1>
                </div>
            </a>

            <nav class="nav-links" aria-label="Navigation principale">
                <a href="index.php" class="nav-link">Accueil</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="../backoffice/index.php" class="nav-link">Dashboard</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'conseilleur'): ?>
                    <a href="../backoffice/adviser_dashboard.php" class="nav-link">Tableau de bord</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link">Profil</a>
                    <a href="logout.php" class="nav-link">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Connexion</a>
                    <a href="register.php" class="nav-link accent">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section id="wrapper">
        <header class="hero">
            <div class="inner">
                <div class="hero-grid">
                    <div>
                        <p class="pill">Communauté positive</p>
                        <h2>Bienvenue sur SafeSpace</h2>
                        <p>Envie de libérer vos émotions ? Partagez vos pensées en toute sécurité et découvrez des ressources adaptées.</p>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="welcome-card">
                                <h3>Bonjour, <?= htmlspecialchars($_SESSION['fullname'] ?? 'Utilisateur') ?> 👋</h3>
                                <p>Rôle: <?= htmlspecialchars($_SESSION['user_role'] ?? 'Membre') ?></p>
                                <div class="hero-actions">
                                    <a href="addPost.php" class="button primary">Partager un post</a>
                                    <a href="#articles" class="button subtle">Voir les articles</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="hero-actions">
                                <a href="register.php" class="button primary">S'inscrire</a>
                                <a href="login.php" class="button">Se connecter</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-grid">
                        <div class="stat-card">
                            <p class="label">Articles validés</p>
                            <p class="value"><?php echo $articleCount; ?></p>
                            <p class="hint">contenus vérifiés par l'équipe</p>
                        </div>
                        <div class="stat-card">
                            <p class="label">Soutien actif</p>
                            <p class="value">24/7</p>
                            <p class="hint">Réponses bienveillantes</p>
                        </div>
                        <div class="stat-card">
                            <p class="label">Communauté</p>
                            <p class="value"><?php echo count($list_Post); ?>+</p>
                            <p class="hint">partages inspirants</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="wrapper">
            <div class="inner">
                <section class="features feature-grid">
                    <div class="feature feature-card">
                        <div class="icon-badge">🔒</div>
                        <h3 class="major">Sécurisé</h3>
                        <p>Vos données sont protégées et votre anonymat préservé.</p>
                    </div>
                    <div class="feature feature-card">
                        <div class="icon-badge">🤝</div>
                        <h3 class="major">Bienveillant</h3>
                        <p>Une communauté respectueuse et à l'écoute.</p>
                    </div>
                    <div class="feature feature-card">
                        <div class="icon-badge">💬</div>
                        <h3 class="major">Libre</h3>
                        <p>Exprimez-vous sans jugement dans un espace safe.</p>
                    </div>
                </section>

                <section class="articles-section" id="articles">
                    <div class="section-header">
                        <div>
                            <p class="pill">📚 <?php echo $articleCount; ?> article<?php echo $articleCount > 1 ? 's' : ''; ?> approuvés</p>
                            <h2>Articles approuvés</h2>
                            <p class="hint">Filtrez par catégorie ou mot-clé pour trouver rapidement ce dont vous avez besoin.</p>
                        </div>
                        <div class="filter-bar">
                            <form method="GET" class="filter-form">
                                <input type="text" name="q" placeholder="Rechercher un titre ou une idée" value="<?php echo htmlspecialchars($articleSearch); ?>">
                                <select name="category">
                                    <option value="0">Toutes les catégories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id_categorie']; ?>" <?php echo $selectedCategory === (int)$cat['id_categorie'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nom_categorie']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="button primary" type="submit">Filtrer</button>
                                <?php if ($selectedCategory || $articleSearch !== ''): ?>
                                    <a class="button subtle" href="index.php">Réinitialiser</a>
                                <?php endif; ?>
                            </form>
                            <div class="filter-actions">
                                <a class="button" href="article_detail.php">Découvrir</a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a class="button primary" href="addArticle.php">Proposer un article</a>
                                <?php else: ?>
                                    <a class="button" href="login.php">Connectez-vous pour proposer</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-grid">
                        <?php if (!$articles): ?>
                            <div class="empty-card">Aucun article ne correspond à vos filtres. Essayez une autre recherche.</div>
                        <?php endif; ?>
                        <?php foreach ($articles as $article): ?>
                            <?php
                            $cat = $categorieC->getCategorie((int)$article['id_categorie']);
                            $categoryName = $cat['nom_categorie'] ?? 'Non classé';
                            $counts = $reactionC->countReactionsByArticle((int)$article['id_article']);
                            ?>
                            <article class="card">
                                <div class="card-header">
                                    <span class="pill light"><?php echo htmlspecialchars($categoryName); ?></span>
                                    <span class="time"><?php echo htmlspecialchars($article['date_creation']); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($article['titre']); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars(substr($article['contenu'],0,160))); ?>...</p>
                                <div class="card-footer">
                                    <div class="reactions">👍 <?php echo $counts['like'] ?? 0; ?> · 👎 <?php echo $counts['dislike'] ?? 0; ?></div>
                                    <a class="button ghost" href="article_detail.php?id=<?php echo $article['id_article']; ?>">Lire l'article</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="comments-section posts-section">
                    <div class="section-header">
                        <div>
                            <p class="pill">Communauté</p>
                            <h2>Les posts approuvés</h2>
                            <p class="hint">Explorez les partages récents et rejoignez la conversation.</p>
                        </div>
                        <div class="filter-actions">
                            <a class="button primary" href="AjoutPost.php">Publier un post</a>
                            <a class="button subtle" href="addCom.php">Réagir</a>
                        </div>
                    </div>
                    <ul class="comments-list modern">
                        <?php
                        foreach($list_Post as $post){
                            $userFullname = getUserFullnameById($post['id_user'], $userController);
                        ?>
                        <li class="comment-item enhanced">
                            <div class="comment-header">
                                <div class="author-stack">
                                    <span class="author"><?php echo htmlspecialchars($userFullname); ?></span>
                                    <span class="sub">par <?php echo htmlspecialchars($post['author']); ?></span>
                                </div>
                                <span class="time"><?php echo htmlspecialchars($post['time']); ?></span>
                            </div>

                            <div class="message"><?php echo nl2br(htmlspecialchars($post['message'])); ?></div>

                            <?php if (!empty($post['image']) && file_exists($post['image'])): ?>
                                <div class="image-container">
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>"
                                        alt="Illustration du post"
                                        class="post-image"
                                        onerror="this.style.display='none'">
                                </div>
                            <?php elseif (!empty($post['image'])): ?>
                                <div class="no-image">Image introuvable : <?php echo htmlspecialchars(basename($post['image'])); ?></div>
                            <?php else: ?>
                                <div class="no-image">Pas d'image</div>
                            <?php endif; ?>

                            <div class="comment-footer">
                                <span class="id">ID: <?php echo $post['id']; ?></span>
                                <div class="comment-actions">
                                    <button class="button ghost" onclick="window.location.href='addCom.php?id=<?=$post['id']; ?>'">
                                        Ajouter un commentaire
                                    </button>
                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <button class="button subtle" onclick="window.location.href='modifierPost.php?id=<?=$post['id']; ?>&author=<?=$post['author']; ?>&message=<?=$post['message']; ?>'">
                                            Modifier
                                        </button>
                                        <button class="button danger" onclick="window.location.href='deletePost.php?id=<?=$post['id']; ?>'">
                                            Supprimer
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="response-section">
                                <h3>Commentaires</h3>
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
                                                <button class="button ghost" onclick="window.location.href='addRes.php?id_Com=<?=$comment['id']; ?>&id_post=<?=$comment['id_post']; ?>'">
                                                    Répondre
                                                </button>
                                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                                    <button class="button danger" onclick="window.location.href='deleteCom.php?id=<?=$comment['id']; ?>'">
                                                        Supprimer
                                                    </button>
                                                    <button class="button subtle" onclick="window.location.href='modifierCom.php?id=<?=$comment['id']; ?>&id_post=<?=$comment['id_post']; ?>&author=<?=$comment['author']; ?>&message=<?=$comment['message']; ?>'">
                                                        Modifier
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="response-section nested">
                                            <h4>Réponses</h4>
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
                                                            <button class="button danger" onclick="window.location.href='deleteRes.php?id=<?=$respond['id']; ?>'">
                                                                Supprimer
                                                            </button>
                                                            <button class="button subtle" onclick="window.location.href='modifierRes.php?id=<?=$respond['id']; ?>&id_post=<?=$respond['id_post']; ?>&id_com=<?=$respond['id_com']; ?>&author=<?=$respond['author']; ?>&message=<?=$respond['message']; ?>'">
                                                                Modifier
                                                            </button>
                                                        <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>
                </section>
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