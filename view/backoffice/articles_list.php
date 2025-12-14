<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once __DIR__ . '/../../controller/ArticleC.php';
require_once __DIR__ . '/../../controller/CategorieC.php';
require_once __DIR__ . '/../../model/article.php';

$articleC = new ArticleC();
$categorieC = new CategorieC();
$categories = $categorieC->listCategories();

$editingArticle = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_action'])) {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $idCategorie = (int)($_POST['id_categorie'] ?? 0);
    $imagePath = trim($_POST['image_path'] ?? '');
    $status = $_POST['status'] ?? 'pending';

    if ($titre && $contenu && $idCategorie) {
        $article = new Article($titre, $contenu, $idCategorie, $imagePath, null, $status, $_SESSION['user_id']);
        if ($_POST['article_action'] === 'create') {
            $articleC->addArticle($article);
        } elseif ($_POST['article_action'] === 'update' && isset($_POST['id_article'])) {
            $articleC->updateArticle((int)$_POST['id_article'], $article);
        }
    }
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    switch ($_GET['action']) {
        case 'delete':
            $articleC->deleteArticle($id);
            break;
        case 'approve':
            $articleC->approveArticle($id);
            break;
        case 'reject':
            $articleC->rejectArticle($id);
            break;
        case 'edit':
            $editingArticle = $articleC->getArticle($id);
            break;
    }
}

$articles = $articleC->listArticles();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des articles</title>
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
        <li class="nav-item">
            <a class="nav-link" href="index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Gestion</div>
        <li class="nav-item"><a class="nav-link" href="users_list.php"><i class="fas fa-fw fa-users"></i><span>Utilisateurs</span></a></li>
        <li class="nav-item"><a class="nav-link" href="posts_list.php"><i class="fas fa-fw fa-comment"></i><span>Posts</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="articles_list.php"><i class="fas fa-fw fa-newspaper"></i><span>Articles</span></a></li>
        <li class="nav-item"><a class="nav-link" href="categories_list.php"><i class="fas fa-fw fa-folder-open"></i><span>Catégories</span></a></li>
        <li class="nav-item"><a class="nav-link" href="comment_articles_list.php"><i class="fas fa-fw fa-comments"></i><span>Commentaires d'articles</span></a></li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Navigation</div>
        <li class="nav-item"><a class="nav-link" href="../frontoffice/index.php"><i class="fas fa-fw fa-globe"></i><span>Site Public</span></a></li>
        <li class="nav-item"><a class="nav-link" href="edit_profile.php"><i class="fas fa-fw fa-user"></i><span>Profil</span></a></li>
        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle"></button></div>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="p-4">
            <h1 class="h3 mb-4 text-gray-800">Gestion des articles</h1>
            <div class="row">
                <div class="col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary"><?php echo $editingArticle ? 'Modifier' : 'Ajouter'; ?> un article</h6></div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="article_action" value="<?php echo $editingArticle ? 'update' : 'create'; ?>">
                                <?php if ($editingArticle): ?>
                                    <input type="hidden" name="id_article" value="<?php echo htmlspecialchars($editingArticle['id_article']); ?>">
                                <?php endif; ?>
                                <div class="form-group">
                                    <label>Titre</label>
                                    <input type="text" name="titre" class="form-control" required value="<?php echo htmlspecialchars($editingArticle['titre'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Contenu</label>
                                    <textarea name="contenu" class="form-control" rows="5" required><?php echo htmlspecialchars($editingArticle['contenu'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Catégorie</label>
                                    <select name="id_categorie" class="form-control" required>
                                        <option value="">-- Choisir --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id_categorie']; ?>" <?php echo ($editingArticle && $editingArticle['id_categorie'] == $cat['id_categorie']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nom_categorie']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Image (chemin)</label>
                                    <input type="text" name="image_path" class="form-control" value="<?php echo htmlspecialchars($editingArticle['image_path'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="status" class="form-control">
                                        <?php $currentStatus = $editingArticle['status'] ?? 'pending'; ?>
                                        <option value="pending" <?php echo $currentStatus === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                        <option value="approved" <?php echo $currentStatus === 'approved' ? 'selected' : ''; ?>>Approuvé</option>
                                        <option value="rejected" <?php echo $currentStatus === 'rejected' ? 'selected' : ''; ?>>Rejeté</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Liste des articles</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered">
                                <thead><tr><th>Titre</th><th>Catégorie</th><th>Statut</th><th>Auteur</th><th>Actions</th></tr></thead>
                                <tbody>
                                <?php foreach ($articles as $article): ?>
                                    <?php
                                    $catName = '';
                                    foreach ($categories as $cat) {
                                        if ($cat['id_categorie'] == $article['id_categorie']) { $catName = $cat['nom_categorie']; break; }
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($article['titre']); ?></td>
                                        <td><?php echo htmlspecialchars($catName); ?></td>
                                        <td><span class="badge badge-<?php echo $article['status'] === 'approved' ? 'success' : ($article['status'] === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo htmlspecialchars($article['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($article['author_id'] ?? ''); ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-info" href="?action=edit&id=<?php echo $article['id_article']; ?>">Modifier</a>
                                            <a class="btn btn-sm btn-success" href="?action=approve&id=<?php echo $article['id_article']; ?>">Approuver</a>
                                            <a class="btn btn-sm btn-warning" href="?action=reject&id=<?php echo $article['id_article']; ?>">Rejeter</a>
                                            <a class="btn btn-sm btn-danger" href="?action=delete&id=<?php echo $article['id_article']; ?>" onclick="return confirm('Supprimer cet article ?');">Supprimer</a>
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
    </div>
</div>
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sb-admin-2.min.js"></script>
</body>
</html>
