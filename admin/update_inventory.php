<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Check if the user is authorized (Admins & Procurement Officers)
checkUserRole(['admin', 'procurement_officer']);

// Generate CSRF Token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$id = (int)$_GET['id'];

// Fetch existing inventory details
$stmt = $conn->prepare("SELECT * FROM inventory WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Inventory item not found.");
}

// Handle form submission (Update Inventory)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_inventory'])) {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed! Please try again.");
    }

    // Get and sanitize inputs
    $product_name = trim($_POST['product_name']);
    $sku = trim($_POST['sku']);
    $quantity = (int)$_POST['quantity'];
    $restock_level = (int)$_POST['restock_level'];

    // Validate inputs
    if (!empty($product_name) && !empty($sku) && $quantity >= 0 && $restock_level >= 0) {
        // Update inventory in database
        $stmt = $conn->prepare("UPDATE inventory SET product_name = ?, sku = ?, quantity = ?, restock_level = ? WHERE id = ?");
        $stmt->bind_param("ssiii", $product_name, $sku, $quantity, $restock_level, $id);
        
        if ($stmt->execute()) {
            header("Location: inventory.php?update_success=1");
            exit();
        } else {
            $error = "Error updating inventory item.";
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
    <title>Edit Inventory Item</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Inventory Item</h1>
        <a href="inventory.php">Back to Inventory</a>

        <?php if (isset($error)) : ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">

            <label for="product_name">Product Name:</label>
            <input type="text" name="product_name" id="product_name" value="<?= htmlspecialchars($item['product_name']) ?>" required>

            <label for="sku">SKU (Unique):</label>
            <input type="text" name="sku" id="sku" value="<?= htmlspecialchars($item['sku']) ?>" required>

            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" value="<?= (int)$item['quantity'] ?>" required>

            <label for="restock_level">Restock Level:</label>
            <input type="number" name="restock_level" id="restock_level" value="<?= (int)$item['restock_level'] ?>" required>

            <button type="submit" name="update_inventory">Update Inventory</button>
        </form>
    </div>
</body>
</html>
