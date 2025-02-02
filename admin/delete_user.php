<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

checkUserRole(['admin']);

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
}

header("Location: manage_users.php");
exit();
?>
