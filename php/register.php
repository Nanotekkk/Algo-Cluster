<?php
// register.php
session_start();

// Si déjà connecté, rediriger vers le dashboard
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

require_once 'config/database.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $class = trim($_POST['class'] ?? '');
    $role = $_POST['role'] ?? 'student';
    
    // Validation
    if(empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        $error = "Tous les champs obligatoires doivent être remplis";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide";
    } elseif(strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } elseif($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id_user FROM user WHERE email = ?");
        $stmt->execute([$email]);
        
        if($stmt->fetch()) {
            $error = "Cette adresse email est déjà utilisée";
        } else {
            // Hasher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insérer le nouvel utilisateur
            try {
                $stmt = $pdo->prepare("INSERT INTO user (firstname, lastname, email, password, class, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$firstname, $lastname, $email, $hashedPassword, $class, $role]);
                
                $success = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
                
                // Optionnel : connecter automatiquement l'utilisateur
                // $_SESSION['user_id'] = $pdo->lastInsertId();
                // $_SESSION['firstname'] = $firstname;
                // $_SESSION['lastname'] = $lastname;
                // $_SESSION['role'] = $role;
                // header('Location: dashboard.php');
                // exit();
                
            } catch(PDOException $e) {
                $error = "Erreur lors de la création du compte";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Cluster Project</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }
        
        .register-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        input:focus,
        select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .login-link a {
            color: #4CAF50;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Inscription</h2>
        
        <?php if($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstname">Prénom *</label>
                    <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="lastname">Nom *</label>
                    <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe * (min. 6 caractères)</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="class">Classe</label>
                    <input type="text" id="class" name="class" value="<?php echo htmlspecialchars($_POST['class'] ?? ''); ?>" placeholder="ex: sdn">
                </div>
                
                <div class="form-group">
                    <label for="role">Rôle *</label>
                    <select id="role" name="role" required>
                        <option value="student" <?php echo ($_POST['role'] ?? 'student') == 'student' ? 'selected' : ''; ?>>Étudiant</option>
                        <option value="teacher" <?php echo ($_POST['role'] ?? '') == 'teacher' ? 'selected' : ''; ?>>Professeur</option>
                    </select>
                </div>
            </div>
            
            <button type="submit">S'inscrire</button>
        </form>
        
        <div class="login-link">
            Déjà un compte ? <a href="login.php">Se connecter</a>
        </div>
    </div>
</body>
</html>