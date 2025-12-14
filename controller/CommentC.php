<?php
require_once '../../config.php';
include '../../model/Comment.php';
class CommentC{
    public function addComment($comment){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
    INSERT INTO comments (id_post, author, message, time)
    VALUES (:i, :a, :m, :t)');

            $req->execute([
                'i' => $comment->getId_Post(),
                'a' => $comment->getAuthor(),
                'm' => $comment->getMessageCom(),
                't' => $comment->getTime()
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

     public function listComment($id_Post){
    $db = config::getConnexion();

    try {
        $req = $db->prepare('SELECT * FROM comments WHERE id_post = :id_post');
        $req->execute([':id_post' => $id_Post]);
        
        $comments = $req->fetchAll();
        return $comments;
    } catch (Exception $e) {
        echo 'Erreur: '.$e->getMessage();
        return []; // Return empty array on error
    }
    }

    public function modifyComment($comment,$id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
    UPDATE comments
    SET id_post = :i, author = :a, message = :m, time = :t 
    WHERE id = :id
    ');

            $req->execute([
                'i' => $comment->getId_Post(),
                'a' => $comment->getAuthor(),
                'm' => $comment->getMessageCom(),
                't' => $comment->getTime(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }


    public function deleteComment($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM comments WHERE id = :id
');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function deleteComPost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM comments WHERE id_post = :id
');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

}
