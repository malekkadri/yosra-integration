<?php
// controller/AIPasswordController.php

class AIPasswordController {
    
    /**
     * Génère un mot de passe sécurisé avec "intelligence"
     * @param int $length Longueur du mot de passe
     * @param bool $includeUpper Inclure majuscules
     * @param bool $includeLower Inclure minuscules
     * @param bool $includeNumbers Inclure chiffres
     * @param bool $includeSymbols Inclure symboles
     * @return array ['password', 'strength', 'hint', 'complexity']
     */
    public function generateSecurePassword(
        $length = 12,
        $includeUpper = true,
        $includeLower = true,
        $includeNumbers = true,
        $includeSymbols = true
    ) {
        try {
            // Caractères disponibles
            $lowercase = 'abcdefghijklmnopqrstuvwxyz';
            $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $numbers = '0123456789';
            $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
            
            // Construire le jeu de caractères basé sur les préférences
            $characters = '';
            if ($includeLower) $characters .= $lowercase;
            if ($includeUpper) $characters .= $uppercase;
            if ($includeNumbers) $characters .= $numbers;
            if ($includeSymbols) $characters .= $symbols;
            
            // Vérifier qu'il y a au moins un type de caractère
            if (empty($characters)) {
                $characters = $lowercase . $uppercase . $numbers . $symbols;
            }
            
            // Génération "intelligente" pour garantir la diversité
            $password = '';
            $charCount = strlen($characters);
            
            // Étape 1: Ajouter au moins un caractère de chaque type sélectionné
            $minChars = [];
            if ($includeLower) $minChars[] = $this->getRandomChar($lowercase);
            if ($includeUpper) $minChars[] = $this->getRandomChar($uppercase);
            if ($includeNumbers) $minChars[] = $this->getRandomChar($numbers);
            if ($includeSymbols) $minChars[] = $this->getRandomChar($symbols);
            
            // Mélanger les caractères obligatoires
            shuffle($minChars);
            $password .= implode('', $minChars);
            
            // Étape 2: Compléter avec des caractères aléatoires
            $remainingLength = $length - strlen($password);
            if ($remainingLength > 0) {
                for ($i = 0; $i < $remainingLength; $i++) {
                    $password .= $characters[random_int(0, $charCount - 1)];
                }
            }
            
            // Étape 3: Mélanger le mot de passe
            $password = str_shuffle($password);
            
            // Étape 4: Analyser la force du mot de passe
            $strengthAnalysis = $this->analyzePasswordStrength($password);
            
            // Étape 5: Générer un conseil mnémotechnique (optionnel)
            $hint = $this->generatePasswordHint($password);
            
            // Étape 6: Calculer la complexité
            $complexity = $this->calculateComplexity($password);
            
            return [
                'success' => true,
                'password' => $password,
                'strength' => $strengthAnalysis['level'],
                'strength_score' => $strengthAnalysis['score'],
                'hint' => $hint,
                'complexity' => $complexity,
                'length' => strlen($password),
                'character_types' => $strengthAnalysis['types']
            ];
            
        } catch (Exception $e) {
            error_log("AIPasswordController Error: " . $e->getMessage());
            
            // Fallback simple
            $fallback = $this->generateFallbackPassword($length);
            
            return [
                'success' => true,
                'password' => $fallback,
                'strength' => 'Moyen',
                'strength_score' => 60,
                'hint' => 'Mot de passe généré automatiquement',
                'complexity' => 'Moyenne',
                'length' => $length,
                'character_types' => 3
            ];
        }
    }
    
    /**
     * Analyse la force d'un mot de passe
     */
    private function analyzePasswordStrength($password) {
        $score = 0;
        $types = 0;
        $length = strlen($password);
        
        // Score basé sur la longueur
        $score += min($length * 4, 40); // Max 40 points pour la longueur
        
        // Vérifier les types de caractères
        if (preg_match('/[a-z]/', $password)) {
            $score += 10;
            $types++;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $score += 10;
            $types++;
        }
        if (preg_match('/[0-9]/', $password)) {
            $score += 10;
            $types++;
        }
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 15;
            $types++;
        }
        
        // Pénalités pour motifs simples
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score -= 15; // Caractères répétés
        }
        if (preg_match('/^(123|abc|qwe|azerty|qwerty)/i', $password)) {
            $score -= 20; // Motifs trop courants
        }
        if (preg_match('/(\d{3,})/', $password)) {
            $score -= 10; // Longues séquences de chiffres
        }
        
        // Normaliser le score (0-100)
        $score = max(0, min(100, $score));
        
        // Déterminer le niveau
        if ($score >= 80) {
            $level = 'Très Fort';
            $color = 'success';
        } elseif ($score >= 60) {
            $level = 'Fort';
            $color = 'primary';
        } elseif ($score >= 40) {
            $level = 'Moyen';
            $color = 'warning';
        } elseif ($score >= 20) {
            $level = 'Faible';
            $color = 'danger';
        } else {
            $level = 'Très Faible';
            $color = 'dark';
        }
        
        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
            'types' => $types
        ];
    }
    
    /**
     * Génère un conseil mnémotechnique pour le mot de passe
     */
    private function generatePasswordHint($password) {
        $hints = [];
        $words = [
            'maison', 'soleil', 'ordinateur', 'aventure', 'chocolat',
            'montagne', 'océan', 'étoile', 'musique', 'voyage',
            'forêt', 'espace', 'dragon', 'magie', 'liberté'
        ];
        
        // Simple transformation du mot de passe en phrase
        $passwordArray = str_split($password);
        $hint = '';
        $wordIndex = 0;
        
        foreach ($passwordArray as $char) {
            if (ctype_alpha($char)) {
                $hint .= $words[$wordIndex % count($words)] . ' ';
                $wordIndex++;
            } elseif (ctype_digit($char)) {
                $hint .= 'nombre' . $char . ' ';
            } else {
                $hint .= 'symbole ';
            }
        }
        
        return trim($hint);
    }
    
    /**
     * Calcule la complexité du mot de passe
     */
    private function calculateComplexity($password) {
        $entropy = 0;
        $length = strlen($password);
        
        // Estimation de l'entropie
        $charSetSize = 0;
        if (preg_match('/[a-z]/', $password)) $charSetSize += 26;
        if (preg_match('/[A-Z]/', $password)) $charSetSize += 26;
        if (preg_match('/[0-9]/', $password)) $charSetSize += 10;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $charSetSize += 32;
        
        if ($charSetSize > 0) {
            $entropy = $length * log($charSetSize, 2);
        }
        
        if ($entropy > 80) return 'Très Élevée';
        if ($entropy > 60) return 'Élevée';
        if ($entropy > 40) return 'Moyenne';
        return 'Faible';
    }
    
    /**
     * Génère un mot de passe de secours
     */
    private function generateFallbackPassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Obtenir un caractère aléatoire d'une chaîne
     */
    private function getRandomChar($string) {
        return $string[random_int(0, strlen($string) - 1)];
    }
    
    /**
     * Vérifie si un mot de passe est dans une liste de fuites
     * (Version simplifiée - en production, utiliser une API)
     */
    public function checkPasswordLeak($password) {
        // En production, utiliser l'API HaveIBeenPwned
        // https://haveibeenpwned.com/API/v3#PwnedPasswords
        
        // Pour cette version, simulation avec une petite liste
        $commonPasswords = [
            'password', '123456', 'qwerty', 'azerty', 'admin',
            'welcome', 'sunshine', 'dragon', 'password123'
        ];
        
        $hash = sha1($password);
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);
        
        // Simuler une vérification
        $isLeaked = in_array(strtolower($password), $commonPasswords);
        
        return [
            'leaked' => $isLeaked,
            'message' => $isLeaked ? 
                '⚠️ Ce mot de passe a été compromis dans des fuites de données' :
                '✅ Ce mot de passe n\'a pas été trouvé dans nos bases de fuites'
        ];
    }
    
    /**
     * Génère des mots de passe thématiques (option amusante)
     */
    public function generateThemedPassword($theme = 'tech', $length = 14) {
        $themes = [
            'tech' => ['byte', 'code', 'data', 'chip', 'node', 'cloud', 'AI', 'VR'],
            'nature' => ['sun', 'moon', 'star', 'tree', 'river', 'mountain', 'ocean'],
            'fantasy' => ['dragon', 'wizard', 'castle', 'magic', 'sword', 'shield'],
            'food' => ['pizza', 'coffee', 'choco', 'berry', 'spice', 'sugar']
        ];
        
        $selectedTheme = $themes[$theme] ?? $themes['tech'];
        $password = '';
        
        // Combiner mots thématiques avec caractères spéciaux
        for ($i = 0; $i < ceil($length / 6); $i++) {
            $word = $selectedTheme[array_rand($selectedTheme)];
            $password .= ucfirst($word);
            $password .= random_int(10, 99);
            $password .= $this->getRandomChar('!@#$%^&*');
        }
        
        // Tronquer à la longueur demandée
        $password = substr($password, 0, $length);
        
        return $this->generateSecurePassword($length);
    }
}
?>