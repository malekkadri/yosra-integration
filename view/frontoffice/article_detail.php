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
$trendingArticles = $articleC->getTopViewedArticles(4);
$popularArticles = $articleC->getPopularArticles(3);

if (!empty($_GET['id'])) {
    $currentArticle = $articleC->getArticle((int)$_GET['id']);
}

if (!$currentArticle && !empty($articles)) {
    $currentArticle = $articles[0];
}

if ($currentArticle) {
    if (!isset($_SESSION['viewed_articles'])) {
        $_SESSION['viewed_articles'] = [];
    }

    if (empty($_SESSION['viewed_articles'][$currentArticle['id_article']])) {
        $articleC->incrementViewCount((int)$currentArticle['id_article']);
        $_SESSION['viewed_articles'][$currentArticle['id_article']] = true;
        $currentArticle = $articleC->getArticle((int)$currentArticle['id_article']);
    }
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
$commentCount = count($comments);

$relatedArticles = [];
if ($currentArticle) {
    $relatedArticles = array_values(array_filter($articles, function ($article) use ($currentArticle) {
        return $article['id_article'] !== $currentArticle['id_article'] && $article['id_categorie'] === $currentArticle['id_categorie'];
    }));
    $relatedArticles = array_slice($relatedArticles, 0, 4);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .hero-banner {
            background: linear-gradient(135deg, rgba(103, 64, 186, 0.8), rgba(45, 150, 233, 0.8));
            padding: 30px;
            border-radius: 14px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 20px;
        }

        .article-card, .sidebar-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
        }

        .sidebar-card ul li a {
            display: block;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.03);
            margin-bottom: 8px;
        }

        .article-image {
            width: 100%;
            border-radius: 12px;
            margin: 15px 0;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.25);
        }

        .meta-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            color: #cfd8dc;
            font-size: 0.95em;
        }

        .pill {
            background: rgba(255, 255, 255, 0.08);
            padding: 6px 12px;
            border-radius: 30px;
        }

        .comments-section {
            margin-top: 30px;
        }

        .comment-item {
            padding: 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 10px;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            color: #cfd8dc;
        }

        .empty-state {
            padding: 18px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px dashed rgba(255, 255, 255, 0.25);
            text-align: center;
            color: #cfd8dc;
        }

        @media (max-width: 980px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
            <div class="hero-banner">
                <h1 style="margin: 0 0 6px 0;">Articles SafeSpace</h1>
                <p style="color:#e0f7fa; margin:0;">Découvrez, réagissez et partagez vos idées dans un espace chaleureux.</p>
                <?php if ($currentArticle): ?>
                    <div class="meta-row" style="margin-top:10px;">
                        <span class="pill">👍 <?php echo $reactionCounts['like'] ?? 0; ?> • 👎 <?php echo $reactionCounts['dislike'] ?? 0; ?></span>
                        <span class="pill">💬 <?php echo $commentCount; ?> commentaire<?php echo $commentCount > 1 ? 's' : ''; ?></span>
                        <span class="pill">👁️ <?php echo (int)($currentArticle['view_count'] ?? 0); ?> vues</span>
                        <a class="pill" href="#commentaires">Aller aux commentaires ↓</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="content-grid">
                <aside class="sidebar-card">
                    <h3 style="margin-bottom: 12px;">Articles disponibles</h3>
                    <ul>
                        <?php foreach ($articles as $art): ?>
                            <li><a href="article_detail.php?id=<?php echo $art['id_article']; ?>"><?php echo htmlspecialchars($art['titre']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($relatedArticles): ?>
                        <hr>
                        <h4 style="margin: 12px 0 8px;">Dans la même catégorie</h4>
                        <ul>
                            <?php foreach ($relatedArticles as $related): ?>
                                <li><a href="article_detail.php?id=<?php echo $related['id_article']; ?>"><?php echo htmlspecialchars($related['titre']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ($trendingArticles): ?>
                        <hr>
                        <h4 style="margin: 12px 0 8px;">Plus consultés</h4>
                        <ul>
                            <?php foreach ($trendingArticles as $trending): ?>
                                <li>
                                    <a href="article_detail.php?id=<?php echo $trending['id_article']; ?>"><?php echo htmlspecialchars($trending['titre']); ?></a>
                                    <small class="meta-row" style="display:block; color:#b0bec5;">👁️ <?php echo (int)($trending['view_count'] ?? 0); ?> vues</small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </aside>
                <main>
                    <?php if ($currentArticle): ?>
                        <?php $cat = $categorieC->getCategorie((int)$currentArticle['id_categorie']); ?>
                        <article class="article-card">
                            <header style="margin-bottom: 12px;">
                                <h2 style="margin-bottom: 8px;"><?php echo htmlspecialchars($currentArticle['titre']); ?></h2>
                                <div class="meta-row">
                                    <span class="pill">Catégorie : <?php echo htmlspecialchars($cat['nom_categorie'] ?? ''); ?></span>
                                    <span class="pill">Publié le <?php echo htmlspecialchars($currentArticle['date_creation']); ?></span>
                                    <span class="pill">💬 <?php echo $commentCount; ?></span>
                                    <span class="pill">👁️ <?php echo (int)($currentArticle['view_count'] ?? 0); ?> vues</span>
                                </div>
                            </header>

                            <?php if (!empty($currentArticle['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($currentArticle['image_path']); ?>" alt="Image de l'article" class="article-image" />
                            <?php endif; ?>

                            <p><?php echo nl2br(htmlspecialchars($currentArticle['contenu'])); ?></p>

                            <div class="meta-row" style="margin: 12px 0;">
                                <span class="pill">👍 <?php echo $reactionCounts['like'] ?? 0; ?> mentions J'aime</span>
                                <span class="pill">👎 <?php echo $reactionCounts['dislike'] ?? 0; ?> avis négatifs</span>
                                <span class="pill">👁️ <?php echo (int)($currentArticle['view_count'] ?? 0); ?> lectures</span>
                            </div>

                            <div class="comments-section">
                                <h3>Réactions</h3>
                                <p class="meta-row" style="margin: 8px 0 12px 0;">👍 <?php echo $reactionCounts['like'] ?? 0; ?> | 👎 <?php echo $reactionCounts['dislike'] ?? 0; ?></p>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php if ($userReaction): ?>
                                        <p class="pill">Vous avez déjà réagi : <?php echo htmlspecialchars($userReaction['reaction']); ?></p>
                                    <?php else: ?>
                                        <form method="POST" style="display:flex; gap:10px; flex-wrap: wrap;">
                                            <button type="submit" name="reaction" value="like" class="button primary">J'aime</button>
                                            <button type="submit" name="reaction" value="dislike" class="button">Je n'aime pas</button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p><a href="login.php">Connectez-vous</a> pour réagir.</p>
                                <?php endif; ?>
                            </div>

                            <div class="comments-section" id="commentaires">
                                <h3>Commentaires</h3>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form method="POST" style="margin-bottom:15px;">
                                        <textarea name="commentaire" rows="3" class="form-control" placeholder="Votre commentaire" required></textarea>
                                        <button type="submit" class="button primary" style="margin-top:10px;">Publier</button>
                                    </form>
                                <?php else: ?>
                                    <p><a href="login.php">Connectez-vous</a> pour commenter cet article.</p>
                                <?php endif; ?>
                                <?php if ($comments): ?>
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
                                <?php else: ?>
                                    <div class="empty-state">Aucun commentaire pour le moment. Soyez le premier à réagir !</div>
                                <?php endif; ?>
                            </div>

                            <?php if ($popularArticles): ?>
                                <div class="comments-section" style="margin-top: 20px;">
                                    <h3>Articles populaires</h3>
                                    <div class="meta-row" style="margin-bottom: 10px;">Une sélection basée sur les likes et les vues.</div>
                                    <ul>
                                        <?php foreach ($popularArticles as $popular): ?>
                                            <?php $popularCat = $categorieC->getCategorie((int)$popular['id_categorie']); ?>
                                            <?php $popularCatName = $popularCat['nom_categorie'] ?? 'Non classé'; ?>
                                            <li class="comment-item" style="list-style:none;">
                                                <div class="comment-header">
                                                    <span class="author"><?php echo htmlspecialchars($popular['titre']); ?></span>
                                                    <span class="time">👁️ <?php echo (int)($popular['view_count'] ?? 0); ?> • 👍 <?php echo (int)($popular['likes'] ?? 0); ?></span>
                                                </div>
                                                <div class="message" style="margin-bottom: 6px;">Catégorie : <?php echo htmlspecialchars($popularCatName); ?> | Commentaires : <?php echo (int)($popular['comments'] ?? 0); ?></div>
                                                <a class="button primary" href="article_detail.php?id=<?php echo $popular['id_article']; ?>">Lire</a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php else: ?>
                        <div class="empty-state">Aucun article disponible pour le moment.</div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>
</div>
</body>
</html>
