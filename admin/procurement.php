<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';


// Check user role (Only Admins & Procurement Officers)
checkUserRole(['admin', 'procurement_officer']);

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Add procurement request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_procurement'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed!");
    }

    $item_name = trim($_POST['item_name']);
    $quantity = (int)$_POST['quantity'];
    $department = trim($_POST['department']);
    $priority = $_POST['priority'];

    if (!empty($item_name) && $quantity > 0) {
        $stmt = $conn->prepare("INSERT INTO procurement (item_name, quantity, department, priority, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("siss", $item_name, $quantity, $department, $priority);
        $stmt->execute();
    }
}

// Fetch procurement requests
$result = $conn->query("SELECT * FROM procurement");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Procurement</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <h1>Manage Procurement</h1>
    <a href="../public/dashboard.php">Back to Dashboard</a>

    <h2>Add Procurement Request</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="item_name" required placeholder="Item Name">
        <input type="number" name="quantity" required placeholder="Quantity">
        <input type="text" name="department" required placeholder="Department">
        <select name="priority">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
        <button type="submit" name="add_procurement">Add Request</button>
    </form>

    <h2>Procurement Requests</h2>
    <table>
        <tr><th>ID</th><th>Item Name</th><th>Quantity</th><th>Department</th><th>Priority</th><th>Status</th><th>Action</th></tr>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td><?= (int)$row['quantity'] ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td><?= htmlspecialchars($row['priority']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <a href="update_procurement.php?id=<?= $row['id'] ?>">Update</a> | 
                    <a href="delete_procurement.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this request?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
