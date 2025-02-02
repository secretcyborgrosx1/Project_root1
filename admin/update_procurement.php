<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Check if the user is authorized
checkUserRole(['admin', 'procurement_officer']);

// Generate CSRF Token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate ID in URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$id = (int)$_GET['id'];

// Fetch current procurement details
$stmt = $conn->prepare("SELECT * FROM procurement WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$procurement = $result->fetch_assoc();

if (!$procurement) {
    die("Procurement request not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed! Please try again.");
    }

    // Get the new status
    $status = trim($_POST['status']);

    if (!empty($status)) {
        $stmt = $conn->prepare("UPDATE procurement SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            header("Location: manage_procurement.php?update_success=1");
            exit();
        } else {
            $error = "Error updating status.";
        }
    } else {
        $error = "Invalid input.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Procurement Status</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <h1>Update Procurement Status</h1>
        <a href="manage_procurement.php">Back to Procurement List</a>

        <?php if (isset($error)) : ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">

            <label>Item Name:</label>
            <input type="text" value="<?= htmlspecialchars($procurement['item_name']) ?>" disabled>

            <label>Current Status:</label>
            <input type="text" value="<?= htmlspecialchars($procurement['status']) ?>" disabled>

            <label>Update Status:</label>
            <select name="status">
                <option value="pending" <?= $procurement['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $procurement['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="completed" <?= $procurement['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>

            <button type="submit" name="update_status">Update Status</button>
        </form>
    </div>
</body>
</html>
