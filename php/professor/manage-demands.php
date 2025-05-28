<?php
// professor/manage-demands.php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

$message = '';
$error = '';

// Gérer la génération des groupes
if(isset($_POST['generate_groups']) && is_numeric($_POST['generate_groups'])) {
    $id_demand = intval($_POST['generate_groups']);
    
    // Vérifier que la demande appartient au professeur et n'est pas déjà traitée
    $stmt = $pdo->prepare("SELECT * FROM demand WHERE id_demand = ? AND id_user = ? AND istreated = 0");
    $stmt->execute([$id_demand, $_SESSION['user_id']]);
    $demand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($demand) {
        // Vérifier que tous les étudiants ont répondu
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_students,
                   COUNT(CASE WHEN as_answer = 1 THEN 1 END) as answered_students
            FROM answer_student 
            WHERE id_demand = ? AND ignore_student = 0
        ");
        $stmt->execute([$id_demand]);
        $response_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($response_stats['total_students'] > 0 && $response_stats['answered_students'] == $response_stats['total_students']) {
            // Tous les étudiants ont répondu, lancer le script Python
            $command = "python3 main.py " . escapeshellarg($id_demand) . " " . escapeshellarg($demand['group_size']) . " " . escapeshellarg($demand['vote_size']) . " 2>&1";
            
            // Exécuter le script Python
            $output = shell_exec($command);
            $return_code = 0;
            
            // Vérifier si le script s'est exécuté correctement
            if($return_code === 0) {
                // Marquer la demande comme traitée
                $stmt = $pdo->prepare("UPDATE demand SET istreated = 1 WHERE id_demand = ?");
                $stmt->execute([$id_demand]);
                
                $message = "Les groupes ont été générés avec succès !";
            } else {
                $error = "Erreur lors de la génération des groupes : " . $output;
            }
        } else {
            $error = "Tous les étudiants n'ont pas encore répondu. (" . $response_stats['answered_students'] . "/" . $response_stats['total_students'] . " réponses)";
        }
    } else {
        $error = "Demande introuvable ou déjà traitée.";
    }
}

// Récupérer toutes les demandes du professeur avec les statistiques de réponses
$stmt = $pdo->prepare("
    SELECT d.*, 
           (SELECT COUNT(*) FROM answer_student WHERE id_demand = d.id_demand) as nb_responses,
           (SELECT COUNT(*) FROM answer_student WHERE id_demand = d.id_demand AND ignore_student = 0) as nb_active_responses,
           (SELECT COUNT(*) FROM answer_student WHERE id_demand = d.id_demand AND ignore_student = 0 AND as_answer = 1) as nb_answered
    FROM demand d
    WHERE d.id_user = ?
    ORDER BY d.date_start DESC
");
$stmt->execute([$_SESSION['user_id']]);
$demands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gérer la suppression
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_demand = intval($_GET['delete']);
    
    // Vérifier que la demande appartient bien au professeur
    $stmt = $pdo->prepare("SELECT id_demand FROM demand WHERE id_demand = ? AND id_user = ?");
    $stmt->execute([$id_demand, $_SESSION['user_id']]);
    
    if($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM demand WHERE id_demand = ?");
        $stmt->execute([$id_demand]);
        header('Location: manage-demands.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer mes formulaires - Cluster Project</title>
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
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .demands-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h2 {
            margin-bottom: 2rem;
            color: #333;
        }
        
        .add-btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 2rem;
            transition: background-color 0.3s;
        }
        
        .add-btn:hover {
            background-color: #45a049;
        }
        
        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-closed {
            color: #dc3545;
            font-weight: bold;
        }
        
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
        }
        
        .btn-view {
            background-color: #007bff;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #0056b3;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #bd2130;
        }
        
        .btn-results {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-results:hover {
            background-color: #117a8b;
        }
        
        .btn-generate {
            background-color: #28a745;
            color: white;
        }
        
        .btn-generate:hover {
            background-color: #218838;
        }
        
        .btn-generate:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-state img {
            width: 100px;
            opacity: 0.5;
            margin-bottom: 1rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .badge-public {
            background-color: #28a745;
            color: white;
        }
        
        .badge-private {
            background-color: #6c757d;
            color: white;
        }
        
        .response-stats {
            font-size: 0.875rem;
        }
        
        .response-complete {
            color: #28a745;
            font-weight: bold;
        }
        
        .response-incomplete {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cluster Project - Gérer mes formulaires</h1>
        <div class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
            <a href="../logout.php">Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <div class="demands-container">
            <h2>Mes formulaires de création de groupes</h2>
            
            <?php if($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <a href="create-demand.php" class="add-btn">+ Créer un nouveau formulaire</a>
            
            <?php if(count($demands) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Période</th>
                            <th>Taille groupe</th>
                            <th>Votes/étudiant</th>
                            <th>Type</th>
                            <th>Réponses</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($demands as $demand): 
                            $now = time();
                            $start = strtotime($demand['date_start']);
                            $finish = strtotime($demand['date_finish']);
                            
                            if($now < $start) {
                                $status = 'pending';
                                $status_text = 'À venir';
                            } elseif($now >= $start && $now <= $finish) {
                                $status = 'active';
                                $status_text = 'En cours';
                            } else {
                                $status = 'closed';
                                $status_text = 'Terminé';
                            }
                            
                            $all_answered = ($demand['nb_active_responses'] > 0 && $demand['nb_answered'] == $demand['nb_active_responses']);
                        ?>
                            <tr>
                                <td>#<?php echo $demand['id_demand']; ?></td>
                                <td><?php echo htmlspecialchars($demand['demand_name']); ?></td>
                                <td>
                                    Du <?php echo date('d/m/Y H:i', $start); ?><br>
                                    Au <?php echo date('d/m/Y H:i', $finish); ?>
                                </td>
                                <td><?php echo $demand['group_size']; ?> personnes</td>
                                <td><?php echo $demand['vote_size']; ?> votes</td>
                                <td>
                                    <span class="badge badge-<?php echo $demand['ispublic'] ? 'public' : 'private'; ?>">
                                        <?php echo $demand['ispublic'] ? 'Public' : 'Privé'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="response-stats <?php echo $all_answered ? 'response-complete' : 'response-incomplete'; ?>">
                                        <?php echo $demand['nb_answered']; ?> / <?php echo $demand['nb_active_responses']; ?>
                                        <?php if($all_answered): ?>
                                            <br><small>✓ Complet</small>
                                        <?php else: ?>
                                            <br><small>En attente</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="status-<?php echo $status; ?>"><?php echo $status_text; ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="view-demand.php?id=<?php echo $demand['id_demand']; ?>" class="btn btn-view">Voir</a>
                                        <?php if($status !== 'closed'): ?>
                                            <a href="edit-demand.php?id=<?php echo $demand['id_demand']; ?>" class="btn btn-edit">Modifier</a>
                                        <?php endif; ?>
                                        
                                        <?php if($demand['istreated']): ?>
                                            <a href="view-groups.php?id=<?php echo $demand['id_demand']; ?>" class="btn btn-results">Groupes</a>
                                        <?php elseif($status === 'closed' && $all_answered): ?>
                                            <form method="POST" style="display: inline;">
                                                <button type="submit" name="generate_groups" value="<?php echo $demand['id_demand']; ?>" 
                                                        class="btn btn-generate" 
                                                        onclick="return confirm('Êtes-vous sûr de vouloir générer les groupes ? Cette action est irréversible.');">
                                                    Générer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                         <button class="btn btn-generate">
                                            Générer
                                        </button>
                                        
                                        <a href="manage-demands.php?delete=<?php echo $demand['id_demand']; ?>" 
                                           class="btn btn-delete" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce formulaire ?');">Supprimer</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>Vous n'avez créé aucun formulaire pour le moment.</p>
                    <p>Cliquez sur le bouton ci-dessus pour créer votre premier formulaire.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>