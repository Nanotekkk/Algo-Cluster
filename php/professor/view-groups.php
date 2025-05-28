<?php

// professor/view-groups.php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

// Récupérer les demandes traitées du professeur
$stmt = $pdo->prepare("
    SELECT d.*, 
           (SELECT COUNT(DISTINCT g.id_group) FROM `group` g WHERE g.id_demand = d.id_demand) as nb_groups
    FROM demand d
    WHERE d.id_user = ? AND d.istreated = 1
    ORDER BY d.date_finish DESC
");
$stmt->execute([$_SESSION['user_id']]);
$demands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si un ID de demande est spécifié, récupérer les groupes
$selected_demand = null;
$groups = [];

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_demand = intval($_GET['id']);
    
    // Vérifier que la demande appartient au professeur
    $stmt = $pdo->prepare("SELECT * FROM demand WHERE id_demand = ? AND id_user = ?");
    $stmt->execute([$id_demand, $_SESSION['user_id']]);
    $selected_demand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($selected_demand) {
        // Récupérer les groupes avec leurs membres
        $stmt = $pdo->prepare("
            SELECT g.*, 
                   GROUP_CONCAT(CONCAT(u.firstname, ' ', u.lastname) ORDER BY u.lastname SEPARATOR ', ') as members,
                   GROUP_CONCAT(u.email ORDER BY u.lastname SEPARATOR ', ') as emails
            FROM `group` g
            LEFT JOIN group_user gu ON g.id_group = gu.id_group_user
            LEFT JOIN user u ON gu.id_user = u.id_user
            WHERE g.id_demand = ?
            GROUP BY g.id_group
            ORDER BY g.group_name
        ");
        $stmt->execute([$id_demand]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir les groupes formés - Cluster Project</title>
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
        
        .demands-list {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .demand-card {
            border: 1px solid #ddd;
            padding: 1rem;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .demand-card:hover {
            border-color: #4CAF50;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .demand-card.active {
            border-color: #4CAF50;
            background-color: #f0f8ff;
        }
        
        .demand-info h3 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .demand-meta {
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .view-btn {
            background-color: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .view-btn:hover {
            background-color: #0056b3;
        }
        
        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .group-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s;
        }
        
        .group-card:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #dee2e6;
        }
        
        .group-name {
            font-size: 1.25rem;
            font-weight: bold;
            color: #333;
        }
        
        .group-size {
            background-color: #007bff;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }
        
        .members-list {
            list-style: none;
        }
        
        .members-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .members-list li:last-child {
            border-bottom: none;
        }
        
        .member-name {
            font-weight: 500;
            color: #495057;
        }
        
        .member-email {
            font-size: 0.875rem;
            color: #6c757d;
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
        <h1>Cluster Project - Groupes</h1>
        <div class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <div class="content-box">
            <h2>Vos demandes traitées</h2>
            <div class="demands-list">
                <?php foreach ($demands as $demand): ?>
                    <div class="demand-card <?= isset($selected_demand) && $selected_demand['id_demand'] == $demand['id_demand'] ? 'active' : '' ?>">
                        <div class="demand-info">
                            <h3><?= htmlspecialchars($demand['title']) ?></h3>
                            <p class="demand-meta">Date de fin : <?= htmlspecialchars($demand['date_finish']) ?></p>
                            <p class="demand-meta">Nombre de groupes : <?= htmlspecialchars($demand['nb_groups']) ?></p>
                        </div>
                        <a class="view-btn" href="?id=<?= $demand['id_demand'] ?>">Voir les groupes</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($selected_demand): ?>
            <div class="content-box">
                <h2>Groupes pour la demande : <?= htmlspecialchars($selected_demand['title']) ?></h2>
                <?php if (!empty($groups)): ?>
                    <div class="groups-grid">
                        <?php foreach ($groups as $group): ?>
                            <div class="group-card">
                                <div class="group-header">
                                    <span class="group-name"><?= htmlspecialchars($group['group_name']) ?></span>
                                    <span class="group-size"><?= htmlspecialchars($group['size']) ?> membres</span>
                                </div>
                                <ul class="members-list">
                                    <?php 
                                    $members = explode(', ', $group['members']);
                                    $emails = explode(', ', $group['emails']);
                                    foreach ($members as $index => $member): ?>
                                        <li>
                                            <span class="member-name"><?= htmlspecialchars($member) ?></span>
                                            <br>
                                            <span class="member-email"><?= htmlspecialchars($emails[$index]) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">Aucun groupe trouvé pour cette demande.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>