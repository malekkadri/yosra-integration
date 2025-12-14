<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/article.php';

class ArticleC {
    public function addArticle(Article $article) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('INSERT INTO articles (titre, contenu, id_categorie, image_path, date_creation, status, author_id) VALUES (:titre, :contenu, :id_categorie, :image_path, :date_creation, :status, :author_id)');
            $query->execute([
                'titre' => $article->getTitre(),
                'contenu' => $article->getContenu(),
                'id_categorie' => $article->getIdCategorie(),
                'image_path' => $article->getImagePath(),
                'date_creation' => $article->getDateCreation(),
                'status' => $article->getStatus(),
                'author_id' => $article->getAuthorId(),
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function listArticles(?string $status = null) {
        $db = config::getConnexion();
        try {
            if ($status) {
                $stmt = $db->prepare('SELECT * FROM articles WHERE status = :status ORDER BY date_creation DESC');
                $stmt->execute(['status' => $status]);
            } else {
                $stmt = $db->query('SELECT * FROM articles ORDER BY date_creation DESC');
            }
            return $stmt->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function getArticle(int $id) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare('SELECT * FROM articles WHERE id_article = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function updateArticle(int $id, Article $article) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('UPDATE articles SET titre = :titre, contenu = :contenu, id_categorie = :id_categorie, image_path = :image_path, status = :status WHERE id_article = :id');
            $query->execute([
                'titre' => $article->getTitre(),
                'contenu' => $article->getContenu(),
                'id_categorie' => $article->getIdCategorie(),
                'image_path' => $article->getImagePath(),
                'status' => $article->getStatus(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function deleteArticle(int $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM articles WHERE id_article = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function approveArticle(int $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE articles SET status = 'approved' WHERE id_article = :id");
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function rejectArticle(int $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE articles SET status = 'rejected' WHERE id_article = :id");
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
}
?>
