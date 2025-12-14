<?php
require_once '../../config.php';
require_once '../../model/Post.php';
class PostC{
    

    public function addPost(Post $post) {
    $db = config::getConnexion();
    try {
        $req = $db->prepare('INSERT INTO posts (id_user, author, message, time, image, status) VALUES (:id_user, :author, :message, :time, :image, :status)');
        $req->execute([
            'id_user' => $post->getId_User(),
            'author' => $post->getAuthor(),
            'message' => $post->getMessagePost(),
            'time' => $post->getTime(),
            'image' => $post->getImage(),
            'status' => $post->getStatus(),
        ]);
    } catch (Exception $e) {
        echo 'Erreur: '.$e->getMessage();
    }
    }


    public function listPost(){
        $db = config::getConnexion();

        try {
           $req = $db->query('
    SELECT * FROM posts ');

            $posts =  $req->fetchAll();
           return $posts;
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
    public function listPostUser($id_User) {
    $db = config::getConnexion();
    
    try {
        // Use prepare() instead of query() for parameter binding
        $req = $db->prepare('SELECT * FROM posts WHERE id_user = :id');
        
        // Bind the parameter (important for security and correctness)
        $req->bindParam(':id', $id_User, PDO::PARAM_INT);
        
        // Execute the prepared statement
        $req->execute();
        
        // Fetch all results as associative array
        $posts = $req->fetchAll(PDO::FETCH_ASSOC);
        
        return $posts;
        
    } catch (Exception $e) {
        // Log error instead of echoing (better practice)
        error_log('Error in listPostUser: ' . $e->getMessage());
        
        // Return empty array on error
        return [];
    }
}
    
    public function listPostProuver(){
        $db = config::getConnexion();

        try {
           $req = $db->query("
    SELECT * FROM posts WHERE status='approved' ");

            $posts =  $req->fetchAll();
           return $posts;
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
    
    


    public function modifyPost($post, $id) {
    $db = config::getConnexion();
     try {
        $req = $db->prepare('
    UPDATE posts
    SET author = :a, message = :m, time = :t , image = :im
    WHERE id = :id
    ');

        $req->execute([
            'a' => $post->getAuthor(),
            'm' => $post->getMessagePost(),
            't' => $post->getTime(),
            'im' => $post->getImage(),
            'id' => $id
        ]);
    } catch (Exception $e) {
        echo 'Erreur: '.$e->getMessage();
    }
}



    public function deletePost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM posts WHERE id = :id
');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
    public function deleteUserPost($id_User){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM posts WHERE id_user = :id
');

            $req->execute([
                'id' => $id_User
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
    
    public function ProuverPost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare("
           UPDATE posts 
           SET status='approved' WHERE id = :id
");

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function BlockPost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare("
           UPDATE posts 
           SET status='blocked' WHERE id = :id
");

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

}
