<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../frontoffice/login.php');
    exit();
}

require_once __DIR__ . '/../../controller/CategorieC.php';
require_once __DIR__ . '/../../model/categorie.php';

$categorieC = new CategorieC();
$editingCategorie = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['categorie_action'])) {
    $nom = trim($_POST['nom_categorie'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($nom) {
        $categorie = new Categorie($nom, $description);
        if ($_POST['categorie_action'] === 'create') {
            $categorieC->addCategorie($categorie);
        } elseif ($_POST['categorie_action'] === 'update' && isset($_POST['id_categorie'])) {
            $categorieC->updateCategorie((int)$_POST['id_categorie'], $categorie);
        }
    }
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $categorieC->deleteCategorie($id);
    } elseif ($_GET['action'] === 'edit') {
        $editingCategorie = $categorieC->getCategorie($id);
    }
}

$categories = $categorieC->listCategories();
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
            <h1 class="h3 mb-4 text-gray-800">Catégories d'articles</h1>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary"><?php echo $editingCategorie ? 'Modifier' : 'Ajouter'; ?> une catégorie</h6></div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="categorie_action" value="<?php echo $editingCategorie ? 'update' : 'create'; ?>">
                                <?php if ($editingCategorie): ?><input type="hidden" name="id_categorie" value="<?php echo htmlspecialchars($editingCategorie['id_categorie']); ?>"><?php endif; ?>
                                <div class="form-group"><label>Nom</label><input type="text" name="nom_categorie" class="form-control" required value="<?php echo htmlspecialchars($editingCategorie['nom_categorie'] ?? ''); ?>"></div>
                                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($editingCategorie['description'] ?? ''); ?></textarea></div>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Liste des catégories</h6></div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered">
                                <thead><tr><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
                                <tbody>
                                <?php foreach ($categories as $categorie): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($categorie['nom_categorie']); ?></td>
                                        <td><?php echo htmlspecialchars($categorie['description']); ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-info" href="?action=edit&id=<?php echo $categorie['id_categorie']; ?>">Modifier</a>
                                            <a class="btn btn-sm btn-danger" href="?action=delete&id=<?php echo $categorie['id_categorie']; ?>" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</a>
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
