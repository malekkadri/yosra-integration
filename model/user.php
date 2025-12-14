<?php
// Prevent redeclaration issues when the file is included multiple times
if (!class_exists('User')) {
class User {
    private $id;
    private $nom;
    private $email;
    private $password;
    private $role;
    private $status;
    private $profile_picture;
    private $date_naissance;
    private $telephone;
    private $adresse;
    private $bio;
    private $specialite;
    private $created_at;
    private $updated_at;

    public function __construct($nom = "", $email = "", $password = "", $role = "membre", $status = "en attente") {
        $this->nom = $nom;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->status = $status;
        $this->profile_picture = 'assets/images/default-avatar.png';
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getStatus() { return $this->status; }
    public function getProfilePicture() { return $this->profile_picture; }
    public function getDateNaissance() { return $this->date_naissance; }
    public function getTelephone() { return $this->telephone; }
    public function getAdresse() { return $this->adresse; }
    public function getBio() { return $this->bio; }
    public function getSpecialite() { return $this->specialite; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setId($id) { $this->id = $id; return $this; }
    public function setNom($nom) { $this->nom = $nom; return $this; }
    public function setEmail($email) { $this->email = $email; return $this; }
    public function setPassword($password) { $this->password = $password; return $this; }
    public function setRole($role) { $this->role = $role; return $this; }
    public function setStatus($status) { $this->status = $status; return $this; }
    public function setProfilePicture($profile_picture) { $this->profile_picture = $profile_picture; return $this; }
    public function setDateNaissance($date_naissance) { $this->date_naissance = $date_naissance; return $this; }
    public function setTelephone($telephone) { $this->telephone = $telephone; return $this; }
    public function setAdresse($adresse) { $this->adresse = $adresse; return $this; }
    public function setBio($bio) { $this->bio = $bio; return $this; }
    public function setSpecialite($specialite) { $this->specialite = $specialite; return $this; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; return $this; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; return $this; }
}
}
?>