<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../config.php';

class UserController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // AJOUTER UN UTILISATEUR
    public function addUser($user) {
        try {
            // Hasher le mot de passe avant insertion
            $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);
            
            $req = $this->pdo->prepare('INSERT INTO users (nom, email, password, role, status, profile_picture, created_at) 
                                       VALUES (:n, :e, :p, :r, :s, :pic, :ca)');
            
            $result = $req->execute([
                'n' => $user->getNom(),
                'e' => $user->getEmail(),
                'p' => $hashedPassword,
                'r' => $user->getRole(),
                's' => $user->getStatus(),
                'pic' => $user->getProfilePicture(),
                'ca' => $user->getCreatedAt()
            ]);
            
            // Récupérer l'ID inséré
            if ($result) {
                $user->setId($this->pdo->lastInsertId());
            }
            
            return $result;
        } catch(Exception $e) {
            error_log("UserController::addUser() - Erreur: " . $e->getMessage());
            return false;
        }
    }

    // LISTER LES UTILISATEURS (retourne des objets User)
    public function listUsers() {
        try {
            $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $users = [];
            foreach ($results as $row) {
                $user = new User();
                $user->setId($row['id'])
                     ->setNom($row['nom'])
                     ->setEmail($row['email'])
                     ->setPassword('') // Ne pas exposer le hash
                     ->setRole($row['role'])
                     ->setStatus($row['status'])
                     ->setProfilePicture($row['profile_picture'] ?? '')
                     ->setDateNaissance($row['date_naissance'] ?? null)
                     ->setTelephone($row['telephone'] ?? null)
                     ->setAdresse($row['adresse'] ?? null)
                     ->setBio($row['bio'] ?? null)
                     ->setSpecialite($row['specialite'] ?? null)
                     ->setCreatedAt($row['created_at'])
                     ->setUpdatedAt($row['updated_at'] ?? null);
                
                $users[] = $user;
            }
            
            return $users;
        } catch(Exception $e) {
            error_log("UserController::listUsers() - Erreur: " . $e->getMessage());
            return [];
        }
    }

    // LISTER LES UTILISATEURS EN TABLEAU
    public function listUsersAsArray() {
        try {
            $stmt = $this->pdo->query('SELECT id, nom, email, role, status, profile_picture, created_at FROM users ORDER BY created_at DESC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("UserController::listUsersAsArray() - Erreur: " . $e->getMessage());
            return [];
        }
    }

    // SUPPRIMER UN UTILISATEUR
    public function deleteUser($id) {
        try {
            $req = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
            return $req->execute(['id' => $id]);
        } catch(Exception $e) {
            error_log("UserController::deleteUser() - Erreur: " . $e->getMessage());
            return false;
        }
    }

    // METTRE À JOUR UN UTILISATEUR
    public function updateUser($id, $user) {
        try {
            $sql = 'UPDATE users SET 
                    nom = :n, 
                    email = :e, 
                    role = :r, 
                    status = :s,
                    profile_picture = :pic, 
                    date_naissance = :dn, 
                    telephone = :tel, 
                    adresse = :addr, 
                    bio = :bio, 
                    specialite = :spec,
                    updated_at = NOW()
                    WHERE id = :id';
            
            $params = [
                'id' => $id,
                'n' => $user->getNom(),
                'e' => $user->getEmail(),
                'r' => $user->getRole(),
                's' => $user->getStatus(),
                'pic' => $user->getProfilePicture(),
                'dn' => $user->getDateNaissance(),
                'tel' => $user->getTelephone(),
                'addr' => $user->getAdresse(),
                'bio' => $user->getBio(),
                'spec' => $user->getSpecialite()
            ];
            
            // Si un nouveau mot de passe est fourni
            if (!empty($user->getPassword())) {
                $sql = 'UPDATE users SET 
                        nom = :n, 
                        email = :e, 
                        password = :p,
                        role = :r, 
                        status = :s,
                        profile_picture = :pic, 
                        date_naissance = :dn, 
                        telephone = :tel, 
                        adresse = :addr, 
                        bio = :bio, 
                        specialite = :spec,
                        updated_at = NOW()
                        WHERE id = :id';
                
                $params['p'] = password_hash($user->getPassword(), PASSWORD_DEFAULT);
            }
            
            $req = $this->pdo->prepare($sql);
            return $req->execute($params);
            
        } catch(Exception $e) {
            error_log("UserController::updateUser() - Erreur: " . $e->getMessage());
            return false;
        }
    }

    // RÉCUPÉRER UN UTILISATEUR PAR ID
    public function getUser($id) {
        try {
            $req = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
            $req->execute(['id' => $id]);
            $data = $req->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) return null;
            
            $user = new User();
            $user->setId($data['id'])
                 ->setNom($data['nom'])
                 ->setEmail($data['email'])
                 ->setPassword('') // Ne pas exposer le hash
                 ->setRole($data['role'])
                 ->setStatus($data['status'])
                 ->setProfilePicture($data['profile_picture'] ?? '')
                 ->setDateNaissance($data['date_naissance'] ?? null)
                 ->setTelephone($data['telephone'] ?? null)
                 ->setAdresse($data['adresse'] ?? null)
                 ->setBio($data['bio'] ?? null)
                 ->setSpecialite($data['specialite'] ?? null)
                 ->setCreatedAt($data['created_at'])
                 ->setUpdatedAt($data['updated_at'] ?? null);
            
            return $user;
        } catch(Exception $e) {
            error_log("UserController::getUser() - Erreur: " . $e->getMessage());
            return null;
        }
    }

    // ALIAS POUR getUser
    public function getUserById($id) {
        return $this->getUser($id);
    }

    // RÉCUPÉRER UN UTILISATEUR PAR EMAIL (retourne tableau pour login)
    public function getUserByEmail($email) {
        try {
            $req = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $req->execute(['email' => $email]);
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
            error_log("UserController::getUserByEmail() - Erreur: " . $e->getMessage());
            return null;
        }
    }

    // METTRE À JOUR LE STATUT
    public function updateUserStatus($id, $status) {
        try {
            $req = $this->pdo->prepare("UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id");
            return $req->execute([
                'status' => $status,
                'id' => $id
            ]);
        } catch(Exception $e) {
            error_log("UserController::updateUserStatus() - Erreur: " . $e->getMessage());
            return false;
        }
    }

    // APPROUVER UN UTILISATEUR
    public function approveUser($id) {
        return $this->updateUserStatus($id, 'actif');
    }

    // BLOQUER UN UTILISATEUR
    public function blockUser($id) {
        return $this->updateUserStatus($id, 'suspendu');
    }

    // VÉRIFIER UN MOT DE PASSE
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // RÉCUPÉRER LA CONNEXION PDO
    public function getConnection() {
        return $this->pdo;
    }

    // METTRE À JOUR LE MOT DE PASSE
    public function updateUserPassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $req = $this->pdo->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id');
            return $req->execute([
                'password' => $hashedPassword,
                'id' => $userId
            ]);
        } catch(Exception $e) {
            error_log("UserController::updateUserPassword() - Erreur: " . $e->getMessage());
            return false;
        }
    }

    // COMPTER LES UTILISATEURS
    public function countUsers() {
        try {
            $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM users');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch(Exception $e) {
            error_log("UserController::countUsers() - Erreur: " . $e->getMessage());
            return 0;
        }
    }
}
?>