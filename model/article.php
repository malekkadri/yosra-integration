<?php
class Article {
    private $idArticle;
    private $titre;
    private $contenu;
    private $dateCreation;
    private $imagePath;
    private $idCategorie; // Foreign key
    private $status;
    private $authorId;

    public function __construct($titre, $contenu, $idCategorie, $imagePath = null, $dateCreation = null, $status = 'pending', $authorId = null) {
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->idCategorie = $idCategorie;
        $this->imagePath = $imagePath;
        $this->dateCreation = $dateCreation ?? date('Y-m-d H:i:s'); // default now
        $this->status = $status;
        $this->authorId = $authorId;
    }

    // GETTERS
    public function getIdArticle() {
        return $this->idArticle;
    }
    public function getTitre() {
        return $this->titre;
    }
    public function getContenu() {
        return $this->contenu;
    }
    public function getDateCreation() {
        return $this->dateCreation;
    }
    public function getIdCategorie() {
        return $this->idCategorie;
    }
    public function getImagePath() {
        return $this->imagePath;
    }
    public function getStatus() {
        return $this->status;
    }
    public function getAuthorId() {
        return $this->authorId;
    }

    // SETTERS
    public function setTitre($titre) {
        $this->titre = $titre;
    }
    public function setContenu($contenu) {
        $this->contenu = $contenu;
    }
    public function setDateCreation($dateCreation) {
        $this->dateCreation = $dateCreation;
    }
    public function setIdCategorie($idCategorie) {
        $this->idCategorie = $idCategorie;
    }
    public function setImagePath($imagePath) {
        $this->imagePath = $imagePath;
    }
    public function setStatus($status) {
        $this->status = $status;
    }
    public function setAuthorId($authorId) {
        $this->authorId = $authorId;
    }
}
?>
