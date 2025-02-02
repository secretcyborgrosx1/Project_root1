<?php
session_start();
require '../includes/config.php';
require BASE_PATH . '/includes/db.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user role
$user_role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/styles.css"> <!-- Link to custom styles -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        h1 {
            text-align: center;
        }
        .nav {
            text-align: center;
            margin-bottom: 20px;
        }
        .nav a {
            text-decoration: none;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
        }
        .nav a:hover {
            background: #0056b3;
        }
        .logout {
            float: right;
            background: red;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['role']); ?>!</h1>

        <div class="nav">
            <a href="../admin/manage_procurement.php">Manage Procurement</a>
            <a href="../admin/vendors.php">Manage Vendors</a>
            <a href="../admin/inventory.php">Manage Inventory</a>
            <?php if ($user_role == 'admin') : ?>
                <a href="../admin/manage_users.php">Manage Users</a>
            <?php endif; ?>
            <a href="logout.php" class="logout">Logout</a>
        </div>

        <h2>Recent Procurement Orders</h2>

        <table>
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Department</th>
                <th>Status</th>
            </tr>
            <?php
            // Fetch procurement records based on user role
            if ($user_role == 'admin' || $user_role == 'procurement_officer') {
                $stmt = $conn->prepare("SELECT item_name, quantity, department, status FROM procurement ORDER BY id DESC LIMIT 10");
            } else {
                $stmt = $conn->prepare("SELECT item_name, quantity, department, status FROM procurement WHERE department = ? ORDER BY id DESC LIMIT 10");
                $stmt->bind_param("s", $_SESSION['department']);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['item_name']) . "</td>
                        <td>" . (int)$row['quantity'] . "</td>
                        <td>" . htmlspecialchars($row['department']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                      </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
