<?php
// professor/manage-demands.php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

// Récupérer toutes les demandes du professeur
$stmt = $pdo->prepare("
    SELECT d.*, 
           (SELECT COUNT(*) FROM answer_student WHERE id_demand = d.id_demand) as nb_responses,
           (SELECT COUNT(*) FROM answer_student WHERE id_demand = d.id_demand AND ignore_student = 0) as nb_active_responses
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
        }
        
        .btn {
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: all 0.3s;
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
            
            <a href="create-demand.php" class="add-btn">+ Créer un nouveau formulaire</a>
            
            <?php if(count($demands) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
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
                        ?>
                            <tr>
                                <td>#<?php echo $demand['id_demand']; ?></td>
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
                                <td><?php echo $demand['nb_active_responses']; ?> / <?php echo $demand['nb_responses']; ?></td>
                                <td class="status-<?php echo $status; ?>"><?php echo $status_text; ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="view-demand.php?id=<?php echo $demand['id_demand']; ?>" class="btn btn-view">Voir</a>
                                        <?php if($status !== 'closed'): ?>
                                            <a href="edit-demand.php?id=<?php echo $demand['id_demand']; ?>" class="btn btn-edit">Modifier</a>
                                        <?php endif; ?>
                                        <?php if($demand['istreated']): ?>
                                            <a href="view-groups.php?id=<?php echo $demand['id_demand']; ?>" class="btn btn-results">Groupes</a>
                                        <?php elseif($status === 'closed'): ?>
                                            <a href="generate-groups.php?id=<?php echo $demand['id_demand']; ?>" class="btn btn-results">Générer</a>
                                        <?php endif; ?>
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