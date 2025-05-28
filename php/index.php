<?php
// index.php
session_start();

// Si l'utilisateur est connecté, rediriger vers le dashboard
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
} else {
    // Sinon, rediriger vers la page de connexion
    header('Location: login.php');
}
exit();
?>