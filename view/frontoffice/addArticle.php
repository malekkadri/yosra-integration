<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../controller/ArticleC.php';
require_once __DIR__ . '/../../controller/CategorieC.php';
require_once __DIR__ . '/../../model/article.php';

$articleC = new ArticleC();
$categorieC = new CategorieC();
$categories = $categorieC->listCategories();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $idCategorie = (int)($_POST['id_categorie'] ?? 0);
    $imagePath = trim($_POST['image_path'] ?? '');

    if ($titre && $contenu && $idCategorie) {
        $article = new Article($titre, $contenu, $idCategorie, $imagePath, null, 'pending', (int)$_SESSION['user_id']);
        $articleC->addArticle($article);
        $message = "Votre article a été soumis et attend la validation de l'administrateur.";
    } else {
        $message = "Merci de remplir tous les champs obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Proposer un article</title>
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
                <a href="profile.php">Profil</a> |
                <a href="logout.php">Déconnexion</a>
            </nav>
        </div>
    </header>

    <div class="wrapper">
        <div class="inner">
            <h2>Proposer un nouvel article</h2>
            <?php if ($message): ?><div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="titre" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="id_categorie" required class="form-control">
                        <option value="">-- Choisir --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id_categorie']; ?>"><?php echo htmlspecialchars($cat['nom_categorie']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image (chemin)</label>
                    <input type="text" name="image_path" class="form-control">
                </div>
                <div class="form-group">
                    <label>Contenu</label>
                    <textarea name="contenu" rows="6" required class="form-control"></textarea>
                </div>
                <button type="submit" class="button primary">Envoyer pour validation</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
