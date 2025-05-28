<?php
// professor/create-demand.php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_start = $_POST['date_start'] ?? '';
    $date_finish = $_POST['date_finish'] ?? '';
    $group_size = intval($_POST['group_size'] ?? 0);
    $vote_size = intval($_POST['vote_size'] ?? 0);
    $ispublic = isset($_POST['ispublic']) ? 1 : 0;
    
    // Validation
    if(empty($date_start) || empty($date_finish)) {
        $error = "Les dates de début et de fin sont obligatoires";
    } elseif(strtotime($date_start) >= strtotime($date_finish)) {
        $error = "La date de début doit être antérieure à la date de fin";
    } elseif(strtotime($date_start) < time()) {
        $error = "La date de début ne peut pas être dans le passé";
    } elseif($group_size < 2) {
        $error = "La taille du groupe doit être d'au moins 2 personnes";
    } elseif($vote_size < 1) {
        $error = "Le nombre de votes doit être d'au moins 1";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO demand (id_user, date_start, date_finish, ispublic, group_size, vote_size, istreated) VALUES (?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$_SESSION['user_id'], $date_start, $date_finish, $ispublic, $group_size, $vote_size]);
            
            $success = "Formulaire créé avec succès!";
            
            // Rediriger après succès
            header('Location: manage-demands.php');
            exit();
        } catch(PDOException $e) {
            $error = "Erreur lors de la création du formulaire";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un formulaire</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        
        .header {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 1.5rem;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: #555;
        }
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h2 {
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
        
        input[type="datetime-local"],
        input[type="number"],
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
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        input[type="checkbox"] {
            width: auto;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 0.75rem 2rem;
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
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cluster Project - Créer un formulaire</h1>
        <div class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
            <a href="../logout.php">Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <div class="form-container">
            <h2>Nouveau formulaire de création de groupes</h2>
            
            <?php if($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_start">Date et heure de début *</label>
                        <input type="datetime-local" id="date_start" name="date_start" 
                               value="<?php echo htmlspecialchars($_POST['date_start'] ?? ''); ?>" required>
                        <p class="help-text">Date à partir de laquelle les étudiants peuvent voter</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_finish">Date et heure de fin *</label>
                        <input type="datetime-local" id="date_finish" name="date_finish" 
                               value="<?php echo htmlspecialchars($_POST['date_finish'] ?? ''); ?>" required>
                        <p class="help-text">Date limite pour voter</p>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="group_size">Taille des groupes *</label>
                        <input type="number" id="group_size" name="group_size" min="2" max="10" 
                               value="<?php echo htmlspecialchars($_POST['group_size'] ?? '3'); ?>" required>
                        <p class="help-text">Nombre de personnes par groupe (min: 2)</p>
                    </div>
                    
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="ispublic" name="ispublic" 
                               <?php echo isset($_POST['ispublic']) ? 'checked' : ''; ?>>
                        <label for="ispublic">Formulaire public</label>
                    </div>
                    <p class="help-text">Si coché, tous les étudiants peuvent participer. Sinon, vous devrez inviter manuellement les étudiants.</p>
                </div>
                
                <button type="submit">Créer le formulaire</button>
            </form>
        </div>
    </div>
</body>
</html>