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

    public function listArticles(?string $status = null, ?string $orderBy = null) {
        $db = config::getConnexion();
        try {
            $orderClause = 'ORDER BY date_creation DESC';
            if ($orderBy === 'views') {
                $orderClause = 'ORDER BY view_count DESC, date_creation DESC';
            } elseif ($orderBy === 'title') {
                $orderClause = 'ORDER BY titre ASC';
            }

            if ($status) {
                $stmt = $db->prepare("SELECT * FROM articles WHERE status = :status {$orderClause}");
                $stmt->execute(['status' => $status]);
            } else {
                $stmt = $db->query("SELECT * FROM articles {$orderClause}");
            }
            return $stmt->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function searchArticles(string $query, ?int $categoryId = null, string $status = 'approved') {
        $db = config::getConnexion();
        try {
            $sql = 'SELECT * FROM articles WHERE status = :status AND (titre LIKE :q OR contenu LIKE :q)';
            $params = ['status' => $status, 'q' => "%{$query}%"];

            if ($categoryId) {
                $sql .= ' AND id_categorie = :category';
                $params['category'] = $categoryId;
            }

            $sql .= ' ORDER BY date_creation DESC';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
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
            $query = $db->prepare('UPDATE articles SET titre = :titre, contenu = :contenu, id_categorie = :id_categorie, image_path = :image_path, status = :status, view_count = :view_count WHERE id_article = :id');
            $query->execute([
                'titre' => $article->getTitre(),
                'contenu' => $article->getContenu(),
                'id_categorie' => $article->getIdCategorie(),
                'image_path' => $article->getImagePath(),
                'status' => $article->getStatus(),
                'view_count' => $article->getViewCount(),
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

    public function incrementViewCount(int $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('UPDATE articles SET view_count = view_count + 1 WHERE id_article = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function getTopViewedArticles(int $limit = 5, string $status = 'approved') {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare('SELECT * FROM articles WHERE status = :status ORDER BY view_count DESC, date_creation DESC LIMIT :limit');
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function getPopularArticles(int $limit = 5) {
        $db = config::getConnexion();
        try {
            $sql = "SELECT a.*, 
                        SUM(CASE WHEN r.reaction = 'like' THEN 1 ELSE 0 END) AS likes,
                        SUM(CASE WHEN r.reaction = 'dislike' THEN 1 ELSE 0 END) AS dislikes,
                        COUNT(DISTINCT ca.id_comment) AS comments
                    FROM articles a
                    LEFT JOIN reactions r ON r.id_article = a.id_article
                    LEFT JOIN comment_articles ca ON ca.id_article = a.id_article
                    WHERE a.status = 'approved'
                    GROUP BY a.id_article
                    ORDER BY likes DESC, a.view_count DESC, a.date_creation DESC
                    LIMIT :limit";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
}
?>
