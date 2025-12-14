<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/reaction.php';

class ReactionC {
    public function addReaction(Reaction $reaction) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('INSERT INTO reactions (id_article, user_id, reaction, date_reaction) VALUES (:id_article, :user_id, :reaction, :date_reaction)');
            $query->execute([
                'id_article' => $reaction->getIdArticle(),
                'user_id' => $reaction->getUserId(),
                'reaction' => $reaction->getReaction(),
                'date_reaction' => $reaction->getDateReaction(),
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function countReactionsByArticle(int $idArticle) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT reaction, COUNT(*) as total FROM reactions WHERE id_article = :id GROUP BY reaction");
            $stmt->execute(['id' => $idArticle]);
            $results = $stmt->fetchAll();
            $counts = ['like' => 0, 'dislike' => 0];
            foreach ($results as $row) {
                $counts[$row['reaction']] = (int)$row['total'];
            }
            return $counts;
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
            return ['like' => 0, 'dislike' => 0];
        }
    }

    public function userHasReacted(int $idArticle, int $userId) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare('SELECT reaction FROM reactions WHERE id_article = :id_article AND user_id = :user_id');
            $stmt->execute(['id_article' => $idArticle, 'user_id' => $userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
}
?>
