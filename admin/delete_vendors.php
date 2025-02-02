<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

checkUserRole(['admin', 'procurement_officer']);

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
}

header("Location: vendors.php");
exit();
?>
