<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Check if the user is authorized (Only Admins can update users)
checkUserRole(['admin']);

// Generate CSRF Token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate user ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$id = (int)$_GET['id'];

// Fetch existing user details
$stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Handle form submission (Update User)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed! Please try again.");
    }

    // Get and sanitize inputs
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);

    // Validate inputs
    if (!empty($username) && in_array($role, ['admin', 'procurement_officer', 'department_head'])) {
        // Update user in database
        $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $role, $id);
        
        if ($stmt->execute()) {
            header("Location: manage_users.php?update_success=1");
            exit();
        } else {
            $error = "Error updating user details.";
        }
    } else {
        $error = "Invalid input values.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit User</h1>
        <a href="manage_users.php">Back to Users</a>

        <?php if (isset($error)) : ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">

            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label for="role">Role:</label>
            <select name="role" id="role">
                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="procurement_officer" <?= $user['role'] == 'procurement_officer' ? 'selected' : '' ?>>Procurement Officer</option>
                <option value="department_head" <?= $user['role'] == 'department_head' ? 'selected' : '' ?>>Department Head</option>
            </select>

            <button type="submit" name="update_user">Update User</button>
        </form>
    </div>
</body>
</html>
