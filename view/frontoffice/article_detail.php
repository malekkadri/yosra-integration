<?php
session_start();

require_once __DIR__ . '/../../controller/ArticleC.php';
require_once __DIR__ . '/../../controller/CategorieC.php';
require_once __DIR__ . '/../../controller/CommentArticleC.php';
require_once __DIR__ . '/../../controller/ReactionC.php';
require_once __DIR__ . '/../../model/comment_article.php';
require_once __DIR__ . '/../../model/reaction.php';

$articleC = new ArticleC();
$categorieC = new CategorieC();
$commentC = new CommentArticleC();
$reactionC = new ReactionC();

$articles = $articleC->listArticles('approved');
$currentArticle = null;

if (!empty($_GET['id'])) {
    $currentArticle = $articleC->getArticle((int)$_GET['id']);
}

if (!$currentArticle && !empty($articles)) {
    $currentArticle = $articles[0];
}

if ($currentArticle && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['commentaire']) && isset($_SESSION['user_id'])) {
        $contenu = trim($_POST['commentaire']);
        if ($contenu !== '') {
            $comment = new CommentArticle((int)$currentArticle['id_article'], (int)$_SESSION['user_id'], $contenu);
            $commentC->addComment($comment);
        }
    }

    if (isset($_POST['reaction']) && isset($_SESSION['user_id'])) {
        $existing = $reactionC->userHasReacted((int)$currentArticle['id_article'], (int)$_SESSION['user_id']);
        if (!$existing) {
            $reaction = new Reaction((int)$currentArticle['id_article'], (int)$_SESSION['user_id'], $_POST['reaction']);
            $reactionC->addReaction($reaction);
        }
    }

    header('Location: article_detail.php?id=' . $currentArticle['id_article']);
    exit();
}

$comments = $currentArticle ? $commentC->listCommentsByArticle((int)$currentArticle['id_article']) : [];
$reactionCounts = $currentArticle ? $reactionC->countReactionsByArticle((int)$currentArticle['id_article']) : ['like' => 0, 'dislike' => 0];
$userReaction = ($currentArticle && isset($_SESSION['user_id'])) ? $reactionC->userHasReacted((int)$currentArticle['id_article'], (int)$_SESSION['user_id']) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="is-preload">
<div id="page-wrapper">
    <header id="header">
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <a class="navbar-brand nav-logo text-primary" href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <img src="images/logo.png" alt="SafeSpace Logo" style="height: 40px; width: auto;">
                <h1 style="margin: 0; font-size: 1.5em;">SafeSpace</h1>
            </a>
            <nav>
                <a href="index.php">Accueil</a> |
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="../backoffice/index.php">Dashboard</a> |
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

    <div class="wrapper">
        <div class="inner">
            <div style="display:flex; gap:20px; align-items:flex-start;">
                <aside style="width:25%;">
                    <h3>Articles disponibles</h3>
                    <ul>
                        <?php foreach ($articles as $art): ?>
                            <li><a href="article_detail.php?id=<?php echo $art['id_article']; ?>"><?php echo htmlspecialchars($art['titre']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </aside>
                <main style="width:75%;">
                    <?php if ($currentArticle): ?>
                        <?php $cat = $categorieC->getCategorie((int)$currentArticle['id_categorie']); ?>
                        <h2><?php echo htmlspecialchars($currentArticle['titre']); ?></h2>
                        <p><em>Catégorie : <?php echo htmlspecialchars($cat['nom_categorie'] ?? ''); ?> | Publié le <?php echo htmlspecialchars($currentArticle['date_creation']); ?></em></p>
                        <?php if (!empty($currentArticle['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($currentArticle['image_path']); ?>" alt="Image de l'article" style="max-width:100%; height:auto;" />
                        <?php endif; ?>
                        <p><?php echo nl2br(htmlspecialchars($currentArticle['contenu'])); ?></p>

                        <div class="comments-section">
                            <h3>Réactions</h3>
                            <p>👍 <?php echo $reactionCounts['like'] ?? 0; ?> | 👎 <?php echo $reactionCounts['dislike'] ?? 0; ?></p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($userReaction): ?>
                                    <p>Vous avez déjà réagi : <?php echo htmlspecialchars($userReaction['reaction']); ?></p>
                                <?php else: ?>
                                    <form method="POST" style="display:flex; gap:10px;">
                                        <button type="submit" name="reaction" value="like" class="button primary">J'aime</button>
                                        <button type="submit" name="reaction" value="dislike" class="button">Je n'aime pas</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <p><a href="login.php">Connectez-vous</a> pour réagir.</p>
                            <?php endif; ?>
                        </div>

                        <div class="comments-section">
                            <h3>Commentaires</h3>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="POST" style="margin-bottom:15px;">
                                    <textarea name="commentaire" rows="3" class="form-control" placeholder="Votre commentaire" required></textarea>
                                    <button type="submit" class="button primary" style="margin-top:10px;">Publier</button>
                                </form>
                            <?php else: ?>
                                <p><a href="login.php">Connectez-vous</a> pour commenter cet article.</p>
                            <?php endif; ?>
                            <ul class="comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <li class="comment-item">
                                        <div class="comment-header">
                                            <span class="author"><?php echo htmlspecialchars($comment['user_name'] ?? ('Utilisateur #' . $comment['id_user'])); ?></span>
                                            <span class="time"><?php echo htmlspecialchars($comment['date_comment']); ?></span>
                                        </div>
                                        <div class="message"><?php echo nl2br(htmlspecialchars($comment['contenu'])); ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p>Aucun article disponible pour le moment.</p>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>
</div>
</body>
</html>
