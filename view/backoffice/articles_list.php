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
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['article_action'])) {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $idCategorie = (int)($_POST['id_categorie'] ?? 0);
    $imagePath = trim($_POST['image_path'] ?? '');
    $uploadDir = __DIR__ . '/../frontoffice/uploads/articles';
    $hasUploadError = false;

    if (!empty($_FILES['image_upload']) && $_FILES['image_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileError = $_FILES['image_upload']['error'];

        if ($fileError === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION));
            if (in_array($extension, $allowedExtensions, true)) {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $fileName = uniqid('article_', true) . '.' . $extension;
                $destination = $uploadDir . '/' . $fileName;

                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $destination)) {
                    $imagePath = '../frontoffice/uploads/articles/' . $fileName;
                } else {
                    $message = "Échec du téléchargement de l'image.";
                    $messageType = 'danger';
                    $hasUploadError = true;
                }
            } else {
                $message = "Format d'image non pris en charge. Utilisez jpg, jpeg, png ou gif.";
                $messageType = 'danger';
                $hasUploadError = true;
            }
        } else {
            $message = "Une erreur est survenue lors de l'envoi de l'image.";
            $messageType = 'danger';
            $hasUploadError = true;
        }
    }
    $status = $_POST['status'] ?? 'pending';

    if ($titre && $contenu && $idCategorie && !$hasUploadError) {
        $article = new Article($titre, $contenu, $idCategorie, $imagePath, null, $status, $_SESSION['user_id']);
        if ($_POST['article_action'] === 'create') {
            $articleC->addArticle($article);
            $message = 'Article ajouté avec succès.';
        } elseif ($_POST['article_action'] === 'update' && isset($_POST['id_article'])) {
            $articleC->updateArticle((int)$_POST['id_article'], $article);
            $message = 'Article mis à jour.';
        }
    } elseif (!$hasUploadError) {
        $message = 'Merci de remplir tous les champs obligatoires.';
        $messageType = 'danger';
    }
}

function resolveImagePath(?string $path): string {
    if (empty($path)) {
        return '';
    }

    if (strpos($path, '../') === 0 || strpos($path, '/') === 0 || strpos($path, 'http') === 0) {
        return $path;
    }

    return '../frontoffice/' . ltrim($path, '/');
}

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    switch ($_GET['action']) {
        case 'delete':
            $articleC->deleteArticle($id);
            $message = 'Article supprimé.';
            break;
        case 'approve':
            $articleC->approveArticle($id);
            $message = 'Article approuvé !';
            break;
        case 'reject':
            $articleC->rejectArticle($id);
            $message = 'Article rejeté.';
            break;
        case 'edit':
            $editingArticle = $articleC->getArticle($id);
            break;
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$categoryFilter = (int)($_GET['category'] ?? 0);
$searchQuery = trim($_GET['q'] ?? '');

$articles = $articleC->listArticles($statusFilter !== 'all' ? $statusFilter : null);

if ($categoryFilter) {
    $articles = array_filter($articles, function ($article) use ($categoryFilter) {
        return (int)$article['id_categorie'] === $categoryFilter;
    });
}

if ($searchQuery) {
    $articles = array_filter($articles, function ($article) use ($searchQuery) {
        return stripos($article['titre'], $searchQuery) !== false || stripos($article['contenu'], $searchQuery) !== false;
    });
}

$totalArticles = count($articles);
$approvedCount = count(array_filter($articles, function ($a) { return $a['status'] === 'approved'; }));
$pendingCount = count(array_filter($articles, function ($a) { return $a['status'] === 'pending'; }));
$rejectedCount = count(array_filter($articles, function ($a) { return $a['status'] === 'rejected'; }));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des articles</title>
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .status-chip { padding: 0.35rem 0.75rem; border-radius: 999px; font-weight: 600; font-size: 0.85rem; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .filter-pill { border-radius: 50px; padding: 0.35rem 0.9rem; }
        .table td { vertical-align: middle; }
        .muted { color: #6c757d; }
    </style>
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
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="h3 text-gray-800 mb-0">Gestion des articles</h1>
                <a href="articles_list.php" class="btn btn-light btn-sm"><i class="fas fa-sync-alt mr-1"></i>Actualiser</a>
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
                <div class="col-lg-3 mb-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalArticles; ?> articles</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mb-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approuvés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $approvedCount; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mb-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En attente</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingCount; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mb-3">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejetés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $rejectedCount; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap" role="alert">
                <div>
                    <strong>Filtres actifs :</strong>
                    <span class="badge badge-primary mr-2">Statut : <?php echo htmlspecialchars($statusFilter); ?></span>
                    <span class="badge badge-secondary mr-2">Catégorie : <?php echo $categoryFilter ? htmlspecialchars($categoryFilter) : 'Toutes'; ?></span>
                    <?php if ($searchQuery): ?><span class="badge badge-light">Recherche : "<?php echo htmlspecialchars($searchQuery); ?>"</span><?php endif; ?>
                </div>
                <div class="d-flex flex-wrap align-items-center" style="gap:6px;">
                    <a class="btn btn-sm btn-outline-primary" href="?status=approved"><i class="fas fa-check mr-1"></i>Approuvés</a>
                    <a class="btn btn-sm btn-outline-warning" href="?status=pending"><i class="fas fa-hourglass-half mr-1"></i>En attente</a>
                    <a class="btn btn-sm btn-outline-danger" href="?status=rejected"><i class="fas fa-times mr-1"></i>Rejetés</a>
                    <a class="btn btn-sm btn-light" href="articles_list.php"><i class="fas fa-broom mr-1"></i>Réinitialiser</a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"><?php echo $editingArticle ? 'Modifier' : 'Ajouter'; ?> un article</h6>
                            <?php if ($editingArticle): ?>
                                <a href="articles_list.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i>Annuler</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
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
                                    <label>Image (chemin ou téléversement)</label>
                                    <input type="text" name="image_path" class="form-control" value="<?php echo htmlspecialchars($editingArticle['image_path'] ?? ''); ?>" placeholder="/uploads/image.jpg">
                                    <div class="custom-file mt-2">
                                        <input type="file" class="custom-file-input" id="imageUpload" name="image_upload" accept="image/*">
                                        <label class="custom-file-label" for="imageUpload">Choisir une image…</label>
                                    </div>
                                    <small class="form-text text-muted">Formats acceptés : jpg, jpeg, png, gif.</small>
                                    <?php if (!empty($editingArticle['image_path'])): ?>
                                        <div class="mt-2"><img src="<?php echo htmlspecialchars(resolveImagePath($editingArticle['image_path'])); ?>" alt="aperçu" class="img-fluid rounded"></div>
                                    <?php endif; ?>
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
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Enregistrer</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Liste des articles</h6>
                            <form class="form-inline" method="GET">
                                <div class="input-group mr-2 mb-2">
                                    <input type="text" class="form-control" name="q" placeholder="Rechercher..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                                <select name="status" class="form-control mr-2 mb-2 filter-pill">
                                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                                    <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approuvés</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejetés</option>
                                </select>
                                <select name="category" class="form-control mr-2 mb-2 filter-pill">
                                    <option value="0" <?php echo !$categoryFilter ? 'selected' : ''; ?>>Toutes les catégories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id_categorie']; ?>" <?php echo $categoryFilter === (int)$cat['id_categorie'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nom_categorie']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-outline-primary mb-2"><i class="fas fa-filter mr-1"></i>Filtrer</button>
                            </form>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover table-borderless align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Titre</th>
                                        <th>Catégorie</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Auteur</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!$articles): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Aucun article trouvé avec ces filtres.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($articles as $article): ?>
                                    <?php
                                    $catName = '';
                                    foreach ($categories as $cat) {
                                        if ($cat['id_categorie'] == $article['id_categorie']) {
                                            $catName = $cat['nom_categorie'];
                                            break;
                                        }
                                    }
                                    $statusClass = $article['status'] === 'approved' ? 'status-approved' : ($article['status'] === 'rejected' ? 'status-rejected' : 'status-pending');
                                    ?>
                                    <tr class="shadow-sm border rounded mb-2">
                                        <td>
                                            <div class="font-weight-bold mb-1"><?php echo htmlspecialchars($article['titre']); ?></div>
                                            <div class="text-muted small mb-1">#<?php echo $article['id_article']; ?> • <?php echo strlen($article['contenu']) > 80 ? htmlspecialchars(substr($article['contenu'],0,80)) . '…' : htmlspecialchars($article['contenu']); ?></div>
                                            <?php if (!empty($article['image_path'])): ?>
                                                <span class="badge badge-light"><i class="fas fa-image mr-1"></i>Image liée</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($catName); ?></td>
                                        <td><span class="status-chip <?php echo $statusClass; ?>"><?php echo htmlspecialchars($article['status']); ?></span></td>
                                        <td><span class="muted"><i class="far fa-calendar-alt mr-1"></i><?php echo htmlspecialchars($article['date_creation'] ?? ''); ?></span></td>
                                        <td><?php echo htmlspecialchars($article['author_id'] ?? ''); ?></td>
                                        <td class="text-right">
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-outline-info" href="?action=edit&id=<?php echo $article['id_article']; ?>"><i class="fas fa-edit"></i></a>
                                                <a class="btn btn-sm btn-outline-secondary" href="../frontoffice/article_detail.php?id=<?php echo $article['id_article']; ?>" target="_blank" title="Voir sur le site"><i class="fas fa-external-link-alt"></i></a>
                                                <a class="btn btn-sm btn-outline-success" href="?action=approve&id=<?php echo $article['id_article']; ?>" title="Approuver"><i class="fas fa-check"></i></a>
                                                <a class="btn btn-sm btn-outline-warning" href="?action=reject&id=<?php echo $article['id_article']; ?>" title="Rejeter"><i class="fas fa-times"></i></a>
                                                <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?php echo $article['id_article']; ?>" onclick="return confirm('Supprimer cet article ?');" title="Supprimer"><i class="fas fa-trash"></i></a>
                                            </div>
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
