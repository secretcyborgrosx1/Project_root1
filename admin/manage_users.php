<?php
session_start();
//require '../includes/config.php';


require '../includes/db.php';
require '../includes/auth.php';
//require BASE_PATH . '/includes/db.php';
//require BASE_PATH . '/includes/auth.php';

//require '../public/config1.php';
//require BASE_PATH . '/public/dashboard.php';



// Check if the user is an Admin
checkUserRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    if (!empty($username) && !empty($_POST['password'])) {
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);
        if ($stmt->execute()) {
            echo "User added successfully.";
        } else {
            echo "Error adding user.";
        }
    }
}

// Fetch users
$result = $conn->query("SELECT id, username, role FROM users");
?>

<!DOCTYPE html>
<link rel="stylesheet" href="../assets/styles.css">
<script src="../assets/scripts.js" defer></script>
<html lang="en">
    <link rel="stylesheet" href="../assets/styles.css">
<script src="../assets/scripts.js" defer></script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <h1>Manage Users</h1>
    <a href="../public/dashboard.php">Back to Dashboard</a>

    <h2>Add User</h2>
    <form method="POST">
        <input type="text" name="username" required placeholder="Username">
        <input type="password" name="password" required placeholder="Password">
        <select name="role">
            <option value="admin">Admin</option>
            <option value="procurement_officer">Procurement Officer</option>
            <option value="department_head">Department Head</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit" name="add_user">Add User</button>
    </form>

    <h2>User List</h2>
    <table>
        <tr><th>ID</th><th>Username</th><th>Role</th><th>Action</th></tr>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td>
                    <a href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
                    <a href="update_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Update this request?')">Edit</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
