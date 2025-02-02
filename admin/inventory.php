<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Check if the user is allowed (Admins & Procurement Officers)
checkUserRole(['admin', 'procurement_officer']);

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission (Create Inventory Item)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_inventory'])) {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed! Please try again.");
    }

    // Get and sanitize inputs
    $product_name = trim($_POST['product_name']);
    $sku = trim($_POST['sku']);
    $quantity = (int)$_POST['quantity'];
    $restock_level = (int)$_POST['restock_level'];

    if (!empty($product_name) && !empty($sku) && $quantity >= 0 && $restock_level >= 0) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO inventory (product_name, sku, quantity, restock_level) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $product_name, $sku, $quantity, $restock_level);
        $stmt->execute();
    }
}

// Fetch inventory items
$result = $conn->query("SELECT * FROM inventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <h1>Manage Inventory</h1>
    <a href="../public/dashboard.php">Back to Dashboard</a>

    <h2>Add Inventory Item</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="product_name" required placeholder="Product Name">
        <input type="text" name="sku" required placeholder="SKU (Unique)">
        <input type="number" name="quantity" required placeholder="Quantity">
        <input type="number" name="restock_level" required placeholder="Restock Level">
        <button type="submit" name="add_inventory">Add Item</button>
    </form>

    <h2>Inventory Items</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>SKU</th>
            <th>Quantity</th>
            <th>Restock Level</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['sku']) ?></td>
                <td><?= (int)$row['quantity'] ?></td>
                <td><?= (int)$row['restock_level'] ?></td>
                <td>
                    <a href="update_inventory.php?id=<?= $row['id'] ?>">Edit</a> |
                    <a href="delete_inventory.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
