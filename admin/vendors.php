<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Check user role
checkUserRole(['admin', 'procurement_officer']);

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Add a new vendor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_vendor'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed!");
    }

    $name = trim($_POST['name']);
    $contact_info = trim($_POST['contact_info']);
    $services = trim($_POST['services']);
    $payment_terms = trim($_POST['payment_terms']);

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO vendors (name, contact_info, services, payment_terms) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $contact_info, $services, $payment_terms);
        $stmt->execute();
    }
}

// Fetch vendors
$result = $conn->query("SELECT * FROM vendors");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vendors</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <h1>Manage Vendors</h1>
    <a href="../public/dashboard.php">Back to Dashboard</a>

    <h2>Add Vendor</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="name" required placeholder="Vendor Name">
        <textarea name="contact_info" required placeholder="Contact Info"></textarea>
        <textarea name="services" required placeholder="Services Provided"></textarea>
        <textarea name="payment_terms" required placeholder="Payment Terms"></textarea>
        <button type="submit" name="add_vendor">Add Vendor</button>
    </form>

    <h2>Vendor List</h2>
    <table>
        <tr><th>ID</th><th>Name</th><th>Contact</th><th>Services</th><th>Payment Terms</th><th>Actions</th></tr>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['contact_info']) ?></td>
                <td><?= htmlspecialchars($row['services']) ?></td>
                <td><?= htmlspecialchars($row['payment_terms']) ?></td>
                
                <td>
                    <a href="update_vendors.php?id=<?= $row['id'] ?>">Update</a> | 
                    <a href="delete_vendors.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this request?')">Delete</a>
                </td>

            </tr>
            
        <?php endwhile; ?>
    </table>
</body>
</html>
