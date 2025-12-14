<?php
session_start();

$redirect = 'login.php';
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $allowed_redirects = ['login.php', 'index.php', '../frontoffice/index.php'];
    if (in_array($_GET['redirect'], $allowed_redirects)) {
        $redirect = $_GET['redirect'];
    }
}

session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - SafeSpace Admin</title>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #ffffff; /* Fond blanc */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            text-align: center;
            padding: 40px;
            max-width: 500px;
            width: 90%;
        }
        
        .logo {
            max-width: 120px;
            margin-bottom: 30px;
        }
        
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4e73df; /* Bleu SafeSpace */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 30px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .message {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="assets/logo.png" alt="SafeSpace Logo" class="logo">
        <div class="status">Déconnexion en cours...</div>
        <div class="loader"></div>
        <div class="message">Patientez pendant la fermeture de votre session</div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Déconnexion réussie',
                html: 'Vous avez été déconnecté de <b>SafeSpace Admin</b> avec succès.',
                icon: 'success',
                iconColor: '#4e73df', // Bleu SafeSpace
                confirmButtonText: 'Continuer vers la connexion',
                confirmButtonColor: '#4e73df', // Bleu SafeSpace
                background: '#ffffff', // Fond blanc
                color: '#333333', // Texte noir
                backdrop: 'rgba(0,0,0,0.4)',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showClass: {
                    popup: 'animate__animated animate__fadeIn'
                }
            }).then((result) => {
                window.location.href = '<?php echo $redirect; ?>';
            });
            
            // Redirection automatique après 3 secondes
            setTimeout(() => {
                window.location.href = '<?php echo $redirect; ?>';
            }, 3000);
        });
    </script>
    
    <!-- Animation CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</body>
</html>
<?php exit(); ?>