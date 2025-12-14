<?php
class Categorie {
    private $idCategorie;
    private $nomCategorie;
    private $description;

    public function __construct($nomCategorie, $description) {
        $this->nomCategorie = $nomCategorie;
        $this->description = $description;
    }

    // GETTERS
    public function getNomCategorie() {
        return $this->nomCategorie;
    }

    public function getDescription() {
        return $this->description;
    }

    // SETTERS
    public function setNomCategorie($nomCategorie) {
        $this->nomCategorie = $nomCategorie;
    }

    public function setDescription($description) {
        $this->description = $description;
    }
}
?>
