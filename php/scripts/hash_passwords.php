<?php
// scripts/hash_passwords.php
// Script à exécuter une seule fois pour hasher tous les mots de passe existants

require_once '../config/database.php';

echo "Début du hashage des mots de passe...\n\n";

try {
    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT id_user, password FROM user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = 0;
    
    foreach($users as $user) {
        // Vérifier si le mot de passe est déjà hashé (commence par $2y$)
        if(substr($user['password'], 0, 4) !== '$2y$') {
            // Hasher le mot de passe
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            
            // Mettre à jour dans la base de données
            $updateStmt = $pdo->prepare("UPDATE user SET password = ? WHERE id_user = ?");
            $updateStmt->execute([$hashedPassword, $user['id_user']]);
            
            $count++;
            echo "Mot de passe hashé pour l'utilisateur ID: " . $user['id_user'] . "\n";
        }
    }
    
    echo "\n$count mot(s) de passe hashé(s) avec succès!\n";
    
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>