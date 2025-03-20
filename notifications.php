<?php
session_start();
include_once 'db/function.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventotrack";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Fetch logs for recent activity
$sql1 = "SELECT logs.username, logs.action_type, logs.description, logs.log_timestamp 
        FROM logs 
        JOIN users ON logs.username = users.username OR logs.username = users.full_name
        WHERE logs.module = 'Inventory Management'
        ORDER BY logs.log_timestamp DESC"; 


$result1 = $conn->query($sql1);
if (!$result1) {
    die("Query failed: " . $conn->error);
}


// Function to format logs
function format_logs($result) {
    $logs = [];
    $current_time = new DateTime();

    while ($row = $result->fetch_assoc()) {
        $log_time = new DateTime($row['log_timestamp']);
        $time_diff = $current_time->diff($log_time);

        // Format time difference
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
    return $logs;
}

// Store logs in separate arrays
$logs1 = format_logs($result1);

// Close connection
$conn->close();
?>


<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">


</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content p-4">
        <h2>Notifications</h2>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Product Activity</h5>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <ul class="list-group">
                    <?php 
                    if (count($logs1) > 0) {
                        foreach ($logs1 as $log) {
                            echo "<li class='list-group-item'>
                                    <strong>{$log['username']}</strong> {$log['action_type']} - {$log['description']} 
                                    <span class='badge badge-secondary float-right'>{$log['time_display']}</span>
                                  </li>";
                        }
                    } else {
                        echo "<li class='list-group-item'>No recent activity</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>

        


    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#notificationsTable').DataTable();
        });
    </script>
</body>

</html>