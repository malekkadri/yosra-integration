let currentGeneratedPassword = '';

// Afficher/masquer le mot de passe
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (!passwordInput || !toggleIcon) return;  // Sécurité
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.replace('fas fa-eye', 'fas fa-eye-slash');  // Utilisez classList pour plus de robustesse
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.replace('fas fa-eye-slash', 'fas fa-eye');
    }
}

// Afficher le générateur de mot de passe
function generateSecurePassword() {
    document.getElementById('passwordGeneratorCard').style.display = 'block';
}

// Générer un mot de passe avec IA
async function generateAIpassword(type = 'strong') {
    try {
        const length = document.getElementById('pwdLength').value;
        const includeUpper = document.getElementById('includeUpper').checked;
        const includeNumbers = document.getElementById('includeNumbers').checked;
        const includeSymbols = document.getElementById('includeSymbols').checked;
        
        // Envoyer la requête au contrôleur PHP
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'generate',
                length: length,
                includeUpper: includeUpper,
                includeNumbers: includeNumbers,
                includeSymbols: includeSymbols,
                type: type
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentGeneratedPassword = data.password;
            
            // Afficher le résultat
            document.getElementById('generatedPassword').textContent = currentGeneratedPassword;
            document.getElementById('strengthBadge').textContent = `Force : ${data.strength}`;
            document.getElementById('strengthBadge').className = `badge bg-${data.strength_color || 'primary'}`;
            document.getElementById('complexityBadge').textContent = `Complexité : ${data.complexity}`;
            document.getElementById('passwordHint').textContent = `💡 Conseil : ${data.hint}`;
            document.getElementById('aiResult').style.display = 'block';
            
            // Vérifier les fuites
            await checkPasswordLeak(currentGeneratedPassword);
            
            // Animer l'affichage
            document.getElementById('aiResult').className = 'alert alert-info animate__animated animate__fadeIn';
            
        } else {
            alert('Erreur : ' + (data.message || 'Impossible de générer le mot de passe'));
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        // Fallback côté client
        generateFallbackPassword();
    }
}

// Générer un mot de passe thématique
async function generateThemedPassword(theme) {
    try {
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'themed',
                theme: theme,
                length: 14
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentGeneratedPassword = data.password;
            document.getElementById('generatedPassword').textContent = currentGeneratedPassword;
            document.getElementById('strengthBadge').textContent = `Thème : ${theme}`;
            document.getElementById('aiResult').style.display = 'block';
            document.getElementById('passwordHint').textContent = `🎨 Motif ${theme} généré avec IA`;
        }
        
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Copier le mot de passe généré
function copyPassword() {
    if (!currentGeneratedPassword) return;
    
    navigator.clipboard.writeText(currentGeneratedPassword).then(() => {
        const copyBtn = document.querySelector('button[onclick="copyPassword()"]');
        const originalHTML = copyBtn.innerHTML;
        
        copyBtn.innerHTML = '<i class="fas fa-check"></i> Copié!';
        copyBtn.className = 'btn btn-sm btn-success';
        
        setTimeout(() => {
            copyBtn.innerHTML = originalHTML;
            copyBtn.className = 'btn btn-sm btn-outline-primary';
        }, 2000);
    }).catch(err => {
        alert('Erreur lors de la copie : ' + err);
    });
}

// Utiliser le mot de passe généré
function useGeneratedPassword() {
    if (!currentGeneratedPassword) return;
    
    document.getElementById('password').value = currentGeneratedPassword;
    document.getElementById('password').type = 'text';
    document.getElementById('toggleIcon').className = 'fas fa-eye-slash';
    
    // Vérifier la force
    checkPasswordStrength(currentGeneratedPassword);
    
    // Afficher un message de succès
    const useBtn = document.querySelector('button[onclick="useGeneratedPassword()"]');
    const originalHTML = useBtn.innerHTML;
    
    useBtn.innerHTML = '<i class="fas fa-check-double"></i> Utilisé!';
    useBtn.className = 'btn btn-sm btn-success';
    
    setTimeout(() => {
        useBtn.innerHTML = originalHTML;
        useBtn.className = 'btn btn-sm btn-success';
    }, 2000);
}

// Vérifier les fuites de mot de passe
async function checkPasswordLeak(password) {
    try {
        const response = await fetch('generate_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'checkLeak',
                password: password
            })
        });
        
        const data = await response.json();
        
        if (data.leaked) {
            document.getElementById('leakCheck').className = 'alert alert-danger';
        } else {
            document.getElementById('leakCheck').className = 'alert alert-success';
        }
        
        document.getElementById('leakMessage').textContent = data.message;
        document.getElementById('leakCheck').style.display = 'block';
        
    } catch (error) {
        console.error('Erreur vérification fuite:', error);
    }
}

// Fallback côté client si le serveur ne répond pas
function generateFallbackPassword() {
    const length = document.getElementById('pwdLength').value;
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    
    for (let i = 0; i < length; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    currentGeneratedPassword = password;
    document.getElementById('generatedPassword').textContent = password;
    document.getElementById('strengthBadge').textContent = 'Force : Forte';
    document.getElementById('complexityBadge').textContent = 'Complexité : Élevée';
    document.getElementById('passwordHint').textContent = '💡 Généré localement (fallback)';
    document.getElementById('aiResult').style.display = 'block';
}

// Vérifier la force du mot de passe en temps réel
function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('passwordStrength');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const feedback = document.getElementById('passwordFeedback');
    
    if (password.length === 0) {
        strengthDiv.style.display = 'none';
        return;
    }
    
    strengthDiv.style.display = 'block';
    
    let score = 0;
    let messages = [];
    
    // Longueur
    if (password.length >= 12) score += 40;
    else if (password.length >= 8) score += 25;
    else if (password.length >= 6) score += 10;
    else messages.push('Trop court (minimum 6 caractères)');
    
    // Complexité
    if (/[a-z]/.test(password)) score += 10;
    else messages.push('Ajoutez des minuscules');
    
    if (/[A-Z]/.test(password)) score += 10;
    else messages.push('Ajoutez des majuscules');
    
    if (/[0-9]/.test(password)) score += 10;
    else messages.push('Ajoutez des chiffres');
    
    if (/[^a-zA-Z0-9]/.test(password)) score += 15;
    else messages.push('Ajoutez des symboles (!@#$...)');
    
    // Pénalités
    if (/(.)\1{2,}/.test(password)) {
        score -= 10;
        messages.push('Évitez les répétitions');
    }
    
    if (/^(123|abc|qwe|azerty)/i.test(password)) {
        score -= 20;
        messages.push('Évitez les séquences courantes');
    }
    
    score = Math.max(0, Math.min(100, score));
    
    // Mettre à jour l'affichage
    strengthBar.style.width = score + '%';
    
    if (score >= 80) {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Très Fort';
        strengthText.className = 'text-success';
    } else if (score >= 60) {
        strengthBar.className = 'progress-bar bg-primary';
        strengthText.textContent = 'Fort';
        strengthText.className = 'text-primary';
    } else if (score >= 40) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Moyen';
        strengthText.className = 'text-warning';
    } else if (score >= 20) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Faible';
        strengthText.className = 'text-danger';
    } else {
        strengthBar.className = 'progress-bar bg-dark';
        strengthText.textContent = 'Très Faible';
        strengthText.className = 'text-dark';
    }
    
    // Afficher les conseils
    if (messages.length > 0 && score < 80) {
        feedback.textContent = '💡 Conseil : ' + messages[0];
        feedback.className = 'form-text text-warning';
    } else {
        feedback.textContent = '✅ Excellent mot de passe !';
        feedback.className = 'form-text text-success';
    }
}