<?php
// dashboard.php
session_start();

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$role = $_SESSION['role'];

// Récupérer les informations supplémentaires selon le rôle
if($role == 'student') {
    // Récupérer les demandes auxquelles l'étudiant a répondu
    $stmt = $pdo->prepare("
        SELECT d.*, u.firstname as teacher_firstname, u.lastname as teacher_lastname
        FROM answer_student a
        JOIN demand d ON a.id_demand = d.id_demand
        JOIN user u ON d.id_user = u.id_user
        WHERE a.id_user = ?
    ");
    $stmt->execute([$user_id]);
    $student_demands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les votes de l'étudiant
    $stmt = $pdo->prepare("
        SELECT ua.*, u1.firstname, u1.lastname
        FROM user_answer ua
        JOIN user u1 ON ua.id_user2 = u1.id_user
        WHERE ua.id_user = ?
        ORDER BY ua.Affinity
    ");
    $stmt->execute([$user_id]);
    $student_votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} elseif($role == 'teacher') {
    // Récupérer les demandes créées par le professeur
    $stmt = $pdo->prepare("
        SELECT d.*, 
               (SELECT COUNT(*) FROM answer_student WHERE id_demand = d.id_demand) as nb_responses
        FROM demand d
        WHERE d.id_user = ?
    ");
    $stmt->execute([$user_id]);
    $teacher_demands = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cluster Project</title>
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
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logout-btn {
            background-color: #f44336;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #da190b;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .menu {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .menu h2 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .menu-item {
            background-color: #4CAF50;
            color: white;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .menu-item:hover {
            background-color: #45a049;
        }
        
        .info-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-section h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-closed {
            color: #dc3545;
            font-weight: bold;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .role-student {
            background-color: #007bff;
            color: white;
        }
        
        .role-teacher {
            background-color: #28a745;
            color: white;
        }
        
        .demand-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }
        
        .demand-card:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .demand-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .demand-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #333;
        }
        
        .demand-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .vote-btn {
            background-color: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        
        .vote-btn:hover {
            background-color: #0056b3;
        }
        
        .vote-btn:disabled,
        .vote-btn.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cluster Project - Dashboard</h1>
        <div class="user-info">
            <span>Bienvenue, <?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></span>
            <span class="role-badge role-<?php echo $role; ?>"><?php echo $role == 'student' ? 'Étudiant' : 'Professeur'; ?></span>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <?php if($role == 'student'): ?>
            <!-- Menu Étudiant -->
            <div class="menu">
                <h2>Menu Étudiant</h2>
                <div class="menu-grid">
                    <a href="vote.php" class="menu-item">Voter pour un groupe</a>
                    <a href="my-groups.php" class="menu-item">Mes groupes</a>                
                </div>
            </div>
            
            <!-- Formulaires disponibles pour voter -->
            <div class="info-section">
                <h3>Formulaires disponibles</h3>
                <?php
                // Récupérer les demandes disponibles pour l'étudiant
                $current_time = time();
                $stmt = $pdo->prepare("
                    SELECT d.*, u.firstname as teacher_firstname, u.lastname as teacher_lastname,
                           CASE 
                               WHEN ? < UNIX_TIMESTAMP(d.date_start) THEN 'pending'
                               WHEN ? >= UNIX_TIMESTAMP(d.date_start) AND ? <= UNIX_TIMESTAMP(d.date_finish) THEN 'active'
                               ELSE 'closed'
                           END as status
                    FROM demand d
                    JOIN user u ON d.id_user = u.id_user
                    JOIN answer_student a ON d.id_demand = a.id_demand
                    WHERE a.id_user = ? AND a.ignore_student = 0
                    ORDER BY d.date_start DESC
                ");
                $stmt->execute([$current_time, $current_time, $current_time, $user_id]);
                $available_demands = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if(count($available_demands) > 0): 
                    foreach($available_demands as $demand): ?>
                        <div class="demand-card">
                            <div class="demand-header">
                                <div class="demand-title"><?php echo htmlspecialchars($demand['demand_name']); ?></div>
                                <div class="demand-status status-<?php echo $demand['status']; ?>">
                                    <?php 
                                    switch($demand['status']) {
                                        case 'pending': echo 'À venir'; break;
                                        case 'active': echo 'En cours'; break;
                                        case 'closed': echo 'Terminé'; break;
                                    }
                                    ?>
                                </div>
                            </div>
                            <p><strong>Professeur:</strong> <?php echo htmlspecialchars($demand['teacher_firstname'] . ' ' . $demand['teacher_lastname']); ?></p>
                            <p><strong>Période:</strong> Du <?php echo date('d/m/Y H:i', strtotime($demand['date_start'])); ?> au <?php echo date('d/m/Y H:i', strtotime($demand['date_finish'])); ?></p>
                            <p><strong>Taille des groupes:</strong> <?php echo $demand['group_size']; ?> personnes</p>
                            
                            <?php if($demand['status'] == 'active'): ?>
                                <a href="./student/vote.php?demand=<?php echo $demand['id_demand']; ?>" class="vote-btn">Commencer à voter</a>
                            <?php elseif($demand['status'] == 'pending'): ?>
                                <span class="vote-btn disabled">Vote pas encore ouvert</span>
                            <?php else: ?>
                                <span class="vote-btn disabled">Vote terminé</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; 
                else: ?>
                    <p>Aucun formulaire disponible pour le moment.</p>
                <?php endif; ?>
            </div>
            
            <!-- Demandes en cours -->
            <div class="info-section">
                <h3>Mes participations aux demandes</h3>
                <?php if(count($student_demands) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Professeur</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>Taille groupe</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($student_demands as $demand): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($demand['teacher_firstname'] . ' ' . $demand['teacher_lastname']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($demand['date_start'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($demand['date_finish'])); ?></td>
                                    <td><?php echo $demand['group_size']; ?> personnes</td>
                                    <td class="<?php echo strtotime($demand['date_finish']) > time() ? 'status-active' : 'status-closed'; ?>">
                                        <?php echo strtotime($demand['date_finish']) > time() ? 'En cours' : 'Terminé'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Vous n'avez participé à aucune demande pour le moment.</p>
                <?php endif; ?>
            </div>
            
            <!-- Mes votes -->
            <div class="info-section">
                <h3>Mes derniers votes</h3>
                <?php if(count($student_votes) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Affinité</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($student_votes as $vote): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vote['firstname'] . ' ' . $vote['lastname']); ?></td>
                                    <td><?php echo $vote['Affinity']; ?>/5</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Vous n'avez pas encore voté.</p>
                <?php endif; ?>
            </div>
            
        <?php elseif($role == 'teacher'): ?>
            <!-- Menu Professeur -->
            <div class="menu">
                <h2>Menu Professeur</h2>
                <div class="menu-grid">
                    <a href="professor/create-demand.php" class="menu-item">Créer un formulaire</a>
                    <a href="professor/manage-demands.php" class="menu-item">Gérer mes formulaire</a>
                    <a href="professor/view-groups.php" class="menu-item">Voir les groupes formés</a>
                </div>
            </div>
            
            <!-- Demandes créées -->
            <div class="info-section">
                <h3>Mes demandes de formation de groupes</h3>
                <?php if(count($teacher_demands) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>Taille groupe</th>
                                <th>Nb votes/étudiant</th>
                                <th>Réponses</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($teacher_demands as $demand): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($demand['date_start'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($demand['date_finish'])); ?></td>
                                    <td><?php echo $demand['group_size']; ?> personnes</td>
                                    <td><?php echo $demand['vote_size']; ?> votes</td>
                                    <td><?php echo $demand['nb_responses']; ?> étudiants</td>
                                    <td class="<?php echo strtotime($demand['date_finish']) > time() ? 'status-active' : 'status-closed'; ?>">
                                        <?php echo strtotime($demand['date_finish']) > time() ? 'En cours' : 'Terminé'; ?>
                                    </td>
                                    <td>
                                        <a href="view-demand.php?id=<?php echo $demand['id_demand']; ?>">Voir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Vous n'avez créé aucune demande pour le moment.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>