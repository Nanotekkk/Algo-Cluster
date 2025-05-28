<?php
// vote.php
session_start();

// Vérifier si l'utilisateur est connecté et est un étudiant
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Récupérer les demandes auxquelles l'étudiant peut participer
$stmt = $pdo->prepare("
    SELECT d.*, u.firstname as teacher_firstname, u.lastname as teacher_lastname
    FROM demand d
    JOIN answer_student a ON d.id_demand = a.id_demand
    JOIN user u ON d.id_user = u.id_user
    WHERE a.id_user = ? 
    AND a.ignore_student = 0 
    AND a.as_answer = 0
    AND d.date_start <= NOW() 
    AND d.date_finish >= NOW()
    ORDER BY d.date_finish ASC
");
$stmt->execute([$user_id]);
$available_demands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si un ID de demande est spécifié
$selected_demand = null;
$other_students = [];

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_demand = intval($_GET['id']);
    
    // Vérifier que l'étudiant peut participer à cette demande
    $stmt = $pdo->prepare("
        SELECT d.*, u.firstname as teacher_firstname, u.lastname as teacher_lastname
        FROM demand d
        JOIN answer_student a ON d.id_demand = a.id_demand
        JOIN user u ON d.id_user = u.id_user
        WHERE d.id_demand = ? 
        AND a.id_user = ? 
        AND a.ignore_student = 0 
        AND a.as_answer = 0
        AND d.date_start <= NOW() 
        AND d.date_finish >= NOW()
    ");
    $stmt->execute([$id_demand, $user_id]);
    $selected_demand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($selected_demand) {
        // Récupérer les autres étudiants de cette demande (sauf lui-même)
        $stmt = $pdo->prepare("
            SELECT u.id_user, u.firstname, u.lastname
            FROM user u
            JOIN answer_student a ON u.id_user = a.id_user
            WHERE a.id_demand = ? 
            AND a.ignore_student = 0 
            AND u.id_user != ?
            ORDER BY u.lastname, u.firstname
        ");
        $stmt->execute([$id_demand, $user_id]);
        $other_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_demand'])) {
    $id_demand = intval($_POST['id_demand']);
    $votes = $_POST['votes'] ?? [];
    
    // Calculer le total des points
    $total_points = 0;
    foreach($votes as $points) {
        $total_points += intval($points);
    }
    
    if($total_points != 100) {
        $error = "Le total des points doit être exactement de 100 (actuellement: $total_points)";
    } elseif(count($votes) == 0) {
        $error = "Vous devez attribuer des points à au moins un étudiant";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Récupérer l'id_answer de l'étudiant pour cette demande
            $stmt = $pdo->prepare("SELECT id_answer FROM answer_student WHERE id_demand = ? AND id_user = ?");
            $stmt->execute([$id_demand, $user_id]);
            $answer_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_answer = $answer_data['id_answer'];
            
            // Insérer les nouveaux votes
            $stmt = $pdo->prepare("INSERT INTO user_answer (id_user, id_user2, id_answer, Affinity) VALUES (?, ?, ?, ?)");
            
            foreach($votes as $id_user2 => $points) {
                $points = intval($points);
                if($points > 0) {
                    $stmt->execute([$user_id, $id_user2, $id_answer, $points]);
                }
            }
            
            // Marquer l'étudiant comme ayant répondu
            $stmt = $pdo->prepare("UPDATE answer_student SET as_answer = 1 WHERE id_demand = ? AND id_user = ?");
            $stmt->execute([$id_demand, $user_id]);
            
            $pdo->commit();
            $success = "Votre vote a été enregistré avec succès !";
            
            // Rediriger vers le dashboard après 2 secondes
            header("refresh:2;url=dashboard.php");
            
        } catch(PDOException $e) {
            $pdo->rollback();
            $error = "Erreur lors de l'enregistrement de votre vote";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de vote - Cluster Project</title>
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
        
        .content-box {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        h2 {
            margin-bottom: 1.5rem;
            color: #333;
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
        
        .demands-list {
            display: grid;
            gap: 1rem;
        }
        
        .demand-card {
            border: 1px solid #ddd;
            padding: 1.5rem;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .demand-card:hover {
            border-color: #4CAF50;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .demand-info h3 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .demand-meta {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .vote-btn {
            background-color: #4CAF50;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .vote-btn:hover {
            background-color: #45a049;
        }
        
        .demand-details {
            background-color: #e9ecef;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 2rem;
        }
        
        .students-list {
            margin-bottom: 2rem;
        }
        
        .student-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            background-color: #f8f9fa;
        }
        
        .student-name {
            font-weight: 500;
        }
        
        .points-input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        
        .points-input:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .total-display {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: bold;
        }
        
        .total-valid {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .total-invalid {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
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
        
        button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cluster Project - Formulaire de vote</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <?php if($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if(!$selected_demand): ?>
            <!-- Liste des demandes disponibles -->
            <div class="content-box">
                <h2>Formulaires de vote disponibles</h2>
                
                <?php if(count($available_demands) > 0): ?>
                    <div class="demands-list">
                        <?php foreach($available_demands as $demand): ?>
                            <div class="demand-card">
                                <div class="demand-info">
                                    <h3><?php echo htmlspecialchars($demand['demand_name']); ?></h3>
                                    <div class="demand-meta">
                                        Professeur: <?php echo htmlspecialchars($demand['teacher_firstname'] . ' ' . $demand['teacher_lastname']); ?><br>
                                        Fin du vote: <?php echo date('d/m/Y à H:i', strtotime($demand['date_finish'])); ?><br>
                                        Taille des groupes: <?php echo $demand['group_size']; ?> personnes
                                    </div>
                                    <a href="vote.php?id=<?php echo $demand['id_demand']; ?>" class="vote-btn">Participer au vote</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Aucun formulaire de vote n'est actuellement disponible.</p>
                        <p>Vérifiez votre dashboard régulièrement pour voir les nouvelles demandes.</p>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <!-- Formulaire de vote -->
            <div class="content-box">
                <h2>Formulaire de vote</h2>
                
                <div class="demand-details">
                    <h3><?php echo htmlspecialchars($selected_demand['demand_name']); ?></h3>
                    <p><strong>Professeur:</strong> <?php echo htmlspecialchars($selected_demand['teacher_firstname'] . ' ' . $selected_demand['teacher_lastname']); ?></p>
                    <p><strong>Date limite:</strong> <?php echo date('d/m/Y à H:i', strtotime($selected_demand['date_finish'])); ?></p>
                    <p><strong>Taille des groupes:</strong> <?php echo $selected_demand['group_size']; ?> personnes</p>
                </div>
                
                <div class="help-text">
                    Répartissez <strong>100 points au total</strong> entre les étudiants selon vos préférences de collaboration.
                    Plus vous donnez de points à quelqu'un, plus vous souhaitez travailler avec cette personne.
                </div>
                
                <?php if(count($other_students) > 0): ?>
                    <form method="POST" action="" id="voteForm">
                        <input type="hidden" name="id_demand" value="<?php echo $selected_demand['id_demand']; ?>">
                        
                        <div class="total-display" id="totalDisplay">
                            Total des points: <span id="totalPoints">0</span> / 100
                        </div>
                        
                        <div class="students-list">
                            <?php foreach($other_students as $student): ?>
                                <div class="student-item">
                                    <span class="student-name">
                                        <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>
                                    </span>
                                    <div>
                                        <input type="number" 
                                               name="votes[<?php echo $student['id_user']; ?>]" 
                                               class="points-input" 
                                               min="0" 
                                               max="100" 
                                               value="0"
                                               onchange="updateTotal()">
                                        <span> points</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="submit" id="submitBtn" disabled>Envoyer mon vote</button>
                    </form>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Aucun autre étudiant n'est disponible pour cette demande.</p>
                        <a href="vote.php" class="vote-btn">← Retour à la liste</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function updateTotal() {
            const inputs = document.querySelectorAll('.points-input');
            let total = 0;
            
            inputs.forEach(input => {
                total += parseInt(input.value) || 0;
            });
            
            document.getElementById('totalPoints').textContent = total;
            
            const totalDisplay = document.getElementById('totalDisplay');
            const submitBtn = document.getElementById('submitBtn');
            
            if (total === 100) {
                totalDisplay.className = 'total-display total-valid';
                submitBtn.disabled = false;
            } else {
                totalDisplay.className = 'total-display total-invalid';
                submitBtn.disabled = true;
            }
        }
        
        // Initialiser le calcul du total au chargement de la page
        document.addEventListener('DOMContentLoaded', updateTotal);
    </script>
</body>
</html>