<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once __DIR__ . '/../../controller/CommentArticleC.php';
require_once __DIR__ . '/../../controller/ArticleC.php';

$commentC = new CommentArticleC();
$articleC = new ArticleC();

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $commentC->deleteComment((int)$_GET['id']);
}

$selectedArticle = (int)($_GET['article'] ?? 0);
$search = trim($_GET['q'] ?? '');
$comments = $commentC->listAllComments();
$articles = $articleC->listArticles();

$articlesById = [];
foreach ($articles as $article) {
    $articlesById[$article['id_article']] = $article['titre'];
}

if ($selectedArticle) {
    $comments = array_filter($comments, function ($c) use ($selectedArticle) {
        return (int)$c['id_article'] === $selectedArticle;
    });
}

if ($search) {
    $comments = array_filter($comments, function ($comment) use ($search) {
        return stripos($comment['contenu'], $search) !== false || stripos($comment['id_user'], $search) !== false || stripos($comment['titre'] ?? '', $search) !== false;
    });
}

$totalComments = count($comments);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commentaires d'articles</title>
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <div class="sidebar-brand d-flex align-items-center justify-content-center" style="padding: 1rem;">
            <a class="navbar-brand nav-logo" href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <img src="assets/logo.png" alt="SafeSpace Logo" style="height: 40px; width: auto;">
            </a>
            <div class="sidebar-brand-text mx-3 text-white">SafeSpace <sup>Admin</sup></div>
        </div>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Gestion</div>
        <li class="nav-item"><a class="nav-link" href="users_list.php"><i class="fas fa-fw fa-users"></i><span>Utilisateurs</span></a></li>
        <li class="nav-item"><a class="nav-link" href="posts_list.php"><i class="fas fa-fw fa-comment"></i><span>Posts</span></a></li>
        <li class="nav-item"><a class="nav-link" href="articles_list.php"><i class="fas fa-fw fa-newspaper"></i><span>Articles</span></a></li>
        <li class="nav-item"><a class="nav-link" href="categories_list.php"><i class="fas fa-fw fa-folder-open"></i><span>Catégories</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="comment_articles_list.php"><i class="fas fa-fw fa-comments"></i><span>Commentaires d'articles</span></a></li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Navigation</div>
        <li class="nav-item"><a class="nav-link" href="../frontoffice/index.php"><i class="fas fa-fw fa-globe"></i><span>Site Public</span></a></li>
        <li class="nav-item"><a class="nav-link" href="edit_profile.php"><i class="fas fa-fw fa-user"></i><span>Profil</span></a></li>
        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle"></button></div>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="h3 mb-0 text-gray-800">Commentaires sur les articles</h1>
                <span class="badge badge-primary p-2"><?php echo $totalComments; ?> commentaire<?php echo $totalComments > 1 ? 's' : ''; ?></span>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Liste des commentaires</h6>
                    <form class="form-inline" method="GET">
                        <select class="form-control mr-2 mb-2" name="article">
                            <option value="0">Tous les articles</option>
                            <?php foreach ($articles as $article): ?>
                                <option value="<?php echo $article['id_article']; ?>" <?php echo $selectedArticle === (int)$article['id_article'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($article['titre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="q" placeholder="Rechercher par contenu ou utilisateur" value="<?php echo htmlspecialchars($search); ?>">
                            <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div>
                        </div>
                    </form>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Article</th><th>Utilisateur</th><th>Contenu</th><th>Date</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>
                        <?php if (!$comments): ?>
                            <tr><td colspan="5" class="text-center text-muted">Aucun commentaire trouvé.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?php echo htmlspecialchars($comment['titre'] ?? ($articlesById[$comment['id_article']] ?? '')); ?></div>
                                    <div class="text-muted small">ID article: <?php echo htmlspecialchars($comment['id_article']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($comment['id_user']); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($comment['contenu']); ?></div>
                                    <span class="badge badge-light"><i class="fas fa-hashtag mr-1"></i><?php echo strlen($comment['contenu']); ?> caractères</span>
                                </td>
                                <td><span class="text-muted"><i class="far fa-clock mr-1"></i><?php echo htmlspecialchars($comment['date_comment']); ?></span></td>
                                <td class="text-right">
                                    <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?php echo $comment['id_comment']; ?>" onclick="return confirm('Supprimer ce commentaire ?');" title="Supprimer"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sb-admin-2.min.js"></script>
</body>
</html>
