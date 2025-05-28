<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

$error = '';
$success = '';

// Récupérer la liste des élèves
$students = [];
try {
    $stmt = $pdo->query("SELECT id_user, firstname, lastname FROM user WHERE role = 'student'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des élèves";
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $demand_name = trim($_POST['demand_name'] ?? '');
    $date_start = $_POST['date_start'] ?? '';
    $date_finish = $_POST['date_finish'] ?? '';
    $group_size = intval($_POST['group_size'] ?? 0);

    // Validation
    if(empty($demand_name)) {
        $error = "Le nom du formulaire est obligatoire";
    } elseif(empty($date_start) || empty($date_finish)) {
        $error = "Les dates de début et de fin sont obligatoires";
    } elseif(strtotime($date_start) >= strtotime($date_finish)) {
        $error = "La date de fin doit être après la date de début";
    } elseif(strtotime($date_start) < time()) {
        $error = "La date de début ne peut pas être dans le passé";  
    } elseif(!isset($_POST['students']) || !is_array($_POST['students']) || count($_POST['students']) == 0) {
        $error = "Veuillez sélectionner au moins un élève";
    } elseif($group_size < 2 || $group_size > 10) {
        $error = "La taille du groupe doit être comprise entre 2 et 10";
    } elseif(count($_POST['students']) % $group_size != 0) {
        $error = "Le nombre d'élèves sélectionnés doit être un multiple de la taille du groupe";
    }
     else {
        try {
            $stmt = $pdo->prepare("INSERT INTO demand (id_user, demand_name, date_start, date_finish, group_size) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $demand_name, $date_start, $date_finish, $group_size]);
            $demand_id = $pdo->lastInsertId();

            // Associer les élèves à la demande dans answer_student
            $stmt = $pdo->prepare("INSERT INTO answer_student (id_demand, id_user) VALUES (?, ?)");
            foreach ($_POST['students'] as $student_id) {
                $stmt->execute([$demand_id, $student_id]);
            }

            $success = "Formulaire créé avec succès!";
            // exit();
        } catch(PDOException $e) {
            $error = "Erreur lors de la création du formulaire: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un formulaire - Cluster Project</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .header { background-color: #333; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.5rem; }
        .header a { color: white; text-decoration: none; padding: 0.5rem 1rem; background-color: #555; border-radius: 4px; }
        .header a:hover { background-color: #666; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 2rem; }
        .form-container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 2rem; color: #333; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; font-weight: bold; }
        input[type="text"], input[type="datetime-local"], input[type="number"], select { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        input[type="checkbox"] { margin-right: 0.5rem; }
        .checkbox-label { display: flex; align-items: center; }
        input:focus, select:focus { outline: none; border-color: #4CAF50; }
        button { width: 100%; padding: 0.75rem; background-color: #4CAF50; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #45a049; }
        .error { background-color: #f8d7da; color: #721c24; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .success { background-color: #d4edda; color: #155724; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
        .help-text { font-size: 0.875rem; color: #666; margin-top: 0.25rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
        .students-list { max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 10px; background: #fafafa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cluster Project - Créer un formulaire</h1>
        <a href="../dashboard.php">← Retour au dashboard</a>
    </div>
    <div class="container">
        <div class="form-container">
            <h2>Nouveau formulaire de formation de groupes</h2>
            <?php if($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="demand_name">Nom du formulaire *</label>
                    <input type="text" id="demand_name" name="demand_name" 
                           value="<?php echo htmlspecialchars($_POST['demand_name'] ?? ''); ?>" 
                           placeholder="Ex: Formation des groupes - Projet Web" required>
                    <div class="help-text">Donnez un nom descriptif à votre formulaire</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_start">Date et heure de début *</label>
                        <input type="datetime-local" id="date_start" name="date_start" 
                               value="<?php echo htmlspecialchars($_POST['date_start'] ?? ''); ?>" 
                               min="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        <div class="help-text">Date à partir de laquelle les étudiants peuvent voter</div>
                    </div>
                    <div class="form-group">
                        <label for="date_finish">Date et heure de fin *</label>
                        <input type="datetime-local" id="date_finish" name="date_finish" 
                               value="<?php echo htmlspecialchars($_POST['date_finish'] ?? ''); ?>" required>
                        <div class="help-text">Date limite pour voter</div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="group_size">Taille des groupes * (2-10)</label>
                        <input type="number" id="group_size" name="group_size" 
                               value="<?php echo htmlspecialchars($_POST['group_size'] ?? '3'); ?>" 
                               min="2" max="10" required>
                        <div class="help-text">Nombre d'étudiants par groupe</div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Choisir les élèves participants *</label>
                    <div class="students-list">
                        <?php foreach($students as $student): ?>
                            <div>
                                <input type="checkbox" name="students[]" value="<?php echo $student['id_user']; ?>"
                                    <?php echo (isset($_POST['students']) && in_array($student['id_user'], $_POST['students'])) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="help-text">Seuls les élèves sélectionnés verront ce formulaire dans leur dashboard.</p>
                </div>
                <button type="submit">Créer le formulaire</button>
            </form>
        </div>
    </div>
</body>
</html>