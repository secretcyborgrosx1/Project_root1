<?php
//session_start();
require '../includes/config.php';
require BASE_PATH . '/includes/db.php';

/**
 * Secure user login function
 */

 
function loginUser($username, $password) {
    global $conn;

    // Prepare statement to fetch user details
    $stmt = $conn->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $passwordHash, $role);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $role;
            return true;
        }
    }
    return false;
}

/**
 * Logout function
 */
function logoutUser() {
    session_destroy();
    header("Location: index.php");
    exit();
}

/**
 * Role-based access control function
 */
function checkUserRole($allowedRoles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        header("Location: dashboard.php");
        exit();
    }
}

/**
 * CSRF Token Generator
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token Validation
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
