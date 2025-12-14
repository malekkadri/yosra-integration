<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/categorie.php';

class CategorieC {
    public function addCategorie(Categorie $categorie) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('INSERT INTO categories (nom_categorie, description) VALUES (:nom_categorie, :description)');
            $query->execute([
                'nom_categorie' => $categorie->getNomCategorie(),
                'description' => $categorie->getDescription(),
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function listCategories() {
        $db = config::getConnexion();
        try {
            $stmt = $db->query('SELECT * FROM categories ORDER BY nom_categorie');
            return $stmt->fetchAll();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function getCategorie(int $id) {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare('SELECT * FROM categories WHERE id_categorie = :id');
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function updateCategorie(int $id, Categorie $categorie) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('UPDATE categories SET nom_categorie = :nom_categorie, description = :description WHERE id_categorie = :id');
            $query->execute([
                'nom_categorie' => $categorie->getNomCategorie(),
                'description' => $categorie->getDescription(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }

    public function deleteCategorie(int $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM categories WHERE id_categorie = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            echo 'Erreur: '.$e->getMessage();
        }
    }
}
?>
