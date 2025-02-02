<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Check if the user is authorized (Only Admins & Procurement Officers can update vendors)
checkUserRole(['admin', 'procurement_officer']);

// Generate CSRF Token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate vendor ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$id = (int)$_GET['id'];

// Fetch existing vendor details
$stmt = $conn->prepare("SELECT id, name, contact_info, services, payment_terms FROM vendors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$vendor = $result->fetch_assoc();

if (!$vendor) {
    die("Vendor not found.");
}

// Handle form submission (Update Vendor)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vendor'])) {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed! Please try again.");
    }

    // Get and sanitize inputs
    $name = trim($_POST['name']);
    $contact_info = trim($_POST['contact_info']);
    $services = trim($_POST['services']);
    $payment_terms = trim($_POST['payment_terms']);

    // Validate inputs
    if (!empty($name) && !empty($contact_info) && !empty($services) && !empty($payment_terms)) {
        // Update vendor in database
        $stmt = $conn->prepare("UPDATE vendors SET name = ?, contact_info = ?, services = ?, payment_terms = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $contact_info, $services, $payment_terms, $id);
        
        if ($stmt->execute()) {
            header("Location: vendors.php?update_success=1");
            exit();
        } else {
            $error = "Error updating vendor details.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vendor</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Vendor</h1>
        <a href="../admin/vendors.php">Back to Vendors</a>

        <?php if (isset($error)) : ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= $id ?>">

            <label for="name">Vendor Name:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($vendor['name']) ?>" required>

            <label for="contact_info">Contact Info:</label>
            <textarea name="contact_info" id="contact_info" required><?= htmlspecialchars($vendor['contact_info']) ?></textarea>

            <label for="services">Services:</label>
            <textarea name="services" id="services" required><?= htmlspecialchars($vendor['services']) ?></textarea>

            <label for="payment_terms">Payment Terms:</label>
            <textarea name="payment_terms" id="payment_terms" required><?= htmlspecialchars($vendor['payment_terms']) ?></textarea>

            <button type="submit" name="update_vendor">Update Vendor</button>
        </form>
    </div>
</body>
</html>
