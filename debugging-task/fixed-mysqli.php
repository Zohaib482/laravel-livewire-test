<?php
/**
 * Part 6 - Fixed & Secure Version
 * Original buggy MySQLi code corrected with best practices
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'test');

// Create connection with error handling
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Get and validate ID
$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($id) || !is_numeric($id)) {
    echo "Invalid or missing ID parameter.";
    mysqli_close($conn);
    exit;
}

// Use Prepared Statement (Secure against SQL Injection)
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id);  // 'i' = integer
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo htmlspecialchars($row['name'] ?? 'No name found', ENT_QUOTES, 'UTF-8');
        }
    } else {
        echo "No user found with ID: " . htmlspecialchars($id);
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Query preparation failed.";
}

// Close connection
mysqli_close($conn);
?>
