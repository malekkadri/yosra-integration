<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once __DIR__ . '/../../controller/CategorieC.php';
require_once __DIR__ . '/../../controller/ArticleC.php';
require_once __DIR__ . '/../../model/categorie.php';

$categorieC = new CategorieC();
$articleC = new ArticleC();
$editingCategorie = null;
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categorie_action'])) {
    $nom = trim($_POST['nom_categorie'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($nom) {
        $categorie = new Categorie($nom, $description);
        if ($_POST['categorie_action'] === 'create') {
            $categorieC->addCategorie($categorie);
            $message = 'Catégorie ajoutée avec succès.';
        } elseif ($_POST['categorie_action'] === 'update' && isset($_POST['id_categorie'])) {
            $categorieC->updateCategorie((int)$_POST['id_categorie'], $categorie);
            $message = 'Catégorie mise à jour.';
        }
    } else {
        $message = 'Le nom de la catégorie est obligatoire.';
        $messageType = 'danger';
    }
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $categorieC->deleteCategorie($id);
        $message = 'Catégorie supprimée.';
    } elseif ($_GET['action'] === 'edit') {
        $editingCategorie = $categorieC->getCategorie($id);
    }
}

$search = trim($_GET['q'] ?? '');
$categories = $categorieC->listCategories();
$articles = $articleC->listArticles();

$categoryUsage = [];
foreach ($articles as $article) {
    $catId = (int)$article['id_categorie'];
    if (!isset($categoryUsage[$catId])) {
        $categoryUsage[$catId] = 0;
    }
    $categoryUsage[$catId]++;
}

$unusedCategories = count(array_filter($categories, function ($cat) use ($categoryUsage) {
    return ($categoryUsage[$cat['id_categorie']] ?? 0) === 0;
}));

if ($search) {
    $categories = array_filter($categories, function ($cat) use ($search) {
        return stripos($cat['nom_categorie'], $search) !== false || stripos($cat['description'], $search) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catégories des articles</title>
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
        <li class="nav-item active"><a class="nav-link" href="categories_list.php"><i class="fas fa-fw fa-folder-open"></i><span>Catégories</span></a></li>
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
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="h3 mb-0 text-gray-800">Catégories d'articles</h1>
                <a href="categories_list.php" class="btn btn-light btn-sm"><i class="fas fa-sync-alt mr-1"></i>Actualiser</a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total catégories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($categories); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Catégories utilisées</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($categories) - $unusedCategories; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sans article lié</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $unusedCategories; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"><?php echo $editingCategorie ? 'Modifier' : 'Ajouter'; ?> une catégorie</h6>
                            <?php if ($editingCategorie): ?>
                                <a href="categories_list.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i>Annuler</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="categorie_action" value="<?php echo $editingCategorie ? 'update' : 'create'; ?>">
                                <?php if ($editingCategorie): ?><input type="hidden" name="id_categorie" value="<?php echo htmlspecialchars($editingCategorie['id_categorie']); ?>"><?php endif; ?>
                                <div class="form-group"><label>Nom</label><input type="text" name="nom_categorie" class="form-control" required value="<?php echo htmlspecialchars($editingCategorie['nom_categorie'] ?? ''); ?>" placeholder="Ex: Sécurité numérique"></div>
                                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3" placeholder="Décrivez en quelques mots"><?php echo htmlspecialchars($editingCategorie['description'] ?? ''); ?></textarea></div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Enregistrer</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des catégories</h6>
                            <form class="form-inline" method="GET">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="q" placeholder="Rechercher par nom ou description" value="<?php echo htmlspecialchars($search); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="thead-light"><tr><th>Nom</th><th>Description</th><th>Articles liés</th><th class="text-right">Actions</th></tr></thead>
                                <tbody>
                                <?php if (!$categories): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Aucune catégorie trouvée.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($categories as $categorie): ?>
                                    <?php $used = $categoryUsage[$categorie['id_categorie']] ?? 0; ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($categorie['nom_categorie']); ?></strong></td>
                                        <td class="text-muted" style="max-width:280px;"> <?php echo htmlspecialchars($categorie['description']); ?></td>
                                        <td><span class="badge badge-info"><?php echo $used; ?> article<?php echo $used > 1 ? 's' : ''; ?></span></td>
                                        <td class="text-right">
                                            <a class="btn btn-sm btn-outline-info" href="?action=edit&id=<?php echo $categorie['id_categorie']; ?>" title="Modifier"><i class="fas fa-edit"></i></a>
                                            <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?php echo $categorie['id_categorie']; ?>" onclick="return confirm('Supprimer cette catégorie ?');" title="Supprimer"><i class="fas fa-trash"></i></a>
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
