<?php
session_start();

// Set timezone
date_default_timezone_set('Asia/Manila');

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'inventotrack';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user_id from session
$user_id = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$username = "Unknown"; // Default in case user_id is null

if ($user_id) {
    // Fetch username from database
    $query = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $username = $row['username'];
    }

    $stmt->close();
}

// Insert log entry into logs table
$action_type = 'LOGOUT';
$module = 'USER';
$description = "$username just logged out";

$query = "INSERT INTO logs (user_id, username, action_type, module, description, log_timestamp) 
          VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("issss", $user_id, $username, $action_type, $module, $description);
$stmt->execute();
$stmt->close();
$conn->close();

// Clear session and redirect to login page
$_SESSION = array();
session_destroy();
header("Location: index.php");
exit;
?>
