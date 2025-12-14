<?php
require_once '../../config.php';
include '../../model/Respond.php';
class RespondC{
    public function addRespond($respond){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
    INSERT INTO responds (id_post ,id_com ,author, message, time)
    VALUES (:ip ,:i ,:a, :m, :t)');

            $req->execute([
                'ip' => $respond->getId_Post(),
                'i' => $respond->getId_Com(),
                'a' => $respond->getAuthor(),
                'm' => $respond->getMessageRes(),
                't' => $respond->getTime()
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function listRespond($id_Com){
    $db = config::getConnexion();

    try {
        $req = $db->prepare('SELECT * FROM responds WHERE id_Com = :id_com');
        $req->execute([':id_com' => $id_Com]);
        
        $responds = $req->fetchAll();
        return $responds;
    } catch (Exception $e) {
        echo 'Erreur: '.$e->getMessage();
        return []; // Return empty array on error
    }
}

    public function modifyRespond($respond,$id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
    UPDATE responds
    SET id_post = :ip, id_com = :i, author = :a, message = :m, time = :t 
    WHERE id = :id
    ');

            $req->execute([
                'ip' => $respond->getId_Post(),
                'i' => $respond->getId_Com(),
                'a' => $respond->getAuthor(),
                'm' => $respond->getMessageRes(),
                't' => $respond->getTime(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }


    public function deleteRespond($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM responds WHERE id = :id
');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function deleteResCom($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM responds WHERE id_com = :id
');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function deleteResComPost($id){
        $db = config::getConnexion();

        try {
           $req = $db->prepare('
           DELETE FROM responds WHERE id_post = :id
');

            $req->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }


}
