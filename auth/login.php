<?php
/**
 * Page de connexion
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/session.php';

// Si déjà connecté, rediriger vers l'accueil
if (is_logged_in()) {
    header('Location: ' . get_base_url() . 'index.php');
    exit();
}

$error = '';
$identifiant = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    // Validation
    if (empty($identifiant) || empty($mot_de_passe)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Charger les utilisateurs
        $users = json_decode(file_get_contents(UTILISATEURS_FILE), true) ?? [];
        
        // Chercher l'utilisateur
        $user_found = null;
        foreach ($users as $user) {
            if ($user['identifiant'] === $identifiant && $user['actif']) {
                $user_found = $user;
                break;
            }
        }
        
        // Vérifier le mot de passe
        if ($user_found && password_verify($mot_de_passe, $user_found['mot_de_passe'])) {
            // Authentification réussie
            $_SESSION['user_id'] = $user_found['identifiant'];
            $_SESSION['user_role'] = $user_found['role'];
            $_SESSION['user_name'] = $user_found['nom_complet'];
            $_SESSION['login_time'] = time();
            
            header('Location: ' . get_base_url() . 'index.php');
            exit();
        } else {
            $error = 'Identifiant ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo get_base_url(); ?>assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .login-header p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .demo-credentials {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 13px;
            color: #555;
        }
        
        .demo-credentials strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🏪 Facturation</h1>
            <p><?php echo APP_NAME; ?></p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant" value="<?php echo htmlspecialchars($identifiant); ?>" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
        
        <div class="demo-credentials">
            <strong>Compte de démonstration :</strong><br>
            Identifiant: <code>admin</code><br>
            Mot de passe: <code>Admin@2026</code>
        </div>
    </div>
</body>
</html>
