<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

checkUserRole(['admin', 'procurement_officer']);

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: inventory.php");
exit();
?>
