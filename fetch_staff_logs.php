<?php
include_once 'db/function.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventotrack";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([])); // Return empty JSON if connection fails
}

date_default_timezone_set('Asia/Manila');

// Get username from request
$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if ($username === '') {
    echo json_encode([]);
    exit;
}

// Fetch logs **only for the selected staff**
$sql = "SELECT logs.username, logs.action_type, logs.description, logs.log_timestamp 
        FROM logs 
        JOIN users ON logs.username = users.username OR logs.username = users.full_name
        WHERE users.username = ? OR users.full_name = ?
        ORDER BY logs.log_timestamp DESC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $username);
$stmt->execute();

$result = $stmt->get_result();

$logs = [];
$current_time = new DateTime();

while ($row = $result->fetch_assoc()) {
    $log_time = new DateTime($row['log_timestamp']);
    $time_diff = $current_time->diff($log_time);

    if ($time_diff->days > 0) {
        $time_display = $time_diff->days . " days ago";
    } elseif ($time_diff->h > 0) {
        $time_display = $time_diff->h . " hours ago";
    } elseif ($time_diff->i > 0) {
        $time_display = $time_diff->i . " minutes ago";
    } else {
        $time_display = "Just now";
    }

    $logs[] = [
        "username" => $row['username'],
        "action_type" => $row['action_type'],
        "description" => $row['description'],
        "time_display" => $time_display
    ];
}

$stmt->close();
$conn->close();

// Return logs as JSON
echo json_encode($logs);
?>
