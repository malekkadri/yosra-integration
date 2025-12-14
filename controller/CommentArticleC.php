<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/comment_article.php';

class CommentArticleC {
    public function addComment(CommentArticle $comment) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('INSERT INTO comment_articles (id_article, id_user, contenu, date_comment) VALUES (:id_article, :id_user, :contenu, :date_comment)');
            $query->execute([
                'id_article' => $comment->getIdArticle(),
                'id_user' => $comment->getIdUser(),
                'contenu' => $comment->getContenu(),
                'date_comment' => $comment->getDateComment(),
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function listCommentsByArticle(int $idArticle) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare('SELECT ca.*, u.nom as user_name FROM comment_articles ca LEFT JOIN users u ON ca.id_user = u.id WHERE ca.id_article = :id ORDER BY date_comment DESC');
            $stmt->execute(['id' => $idArticle]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function listAllComments() {
        $db = config::getConnexion();
        try {
            $stmt = $db->query('SELECT ca.*, a.titre FROM comment_articles ca LEFT JOIN articles a ON ca.id_article = a.id_article ORDER BY date_comment DESC');
            return $stmt->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function deleteComment(int $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM comment_articles WHERE id_comment = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
}
?>
