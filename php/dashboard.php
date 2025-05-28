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