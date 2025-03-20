<?php

// Include the Database class
include_once 'db.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
// Create a new Database object
$database = new Database();
$conn = $database->connect();  // Get the PDO connection

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: Could not connect to the database.");
}

// Handle fetch operation
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'fetch' && isset($_GET['table'])) {
        $table = $_GET['table'];

        // Use a prepared statement to fetch all data from the specified table
        $sql = "SELECT * FROM " . $table;
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch the result as an associative array

        echo json_encode($data);  // Return data as JSON
    }
}

// Handle insert operation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'insert' && isset($_POST['table']) && isset($_POST['data'])) {
        $table = $_POST['table'];
        $data = $_POST['data'];  // Data is assumed to be an associative array

        // Prepare the columns and values for the insert query
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));  // Named placeholders for security

        // Prepare the insert SQL query with named placeholders
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);

        // Bind the parameters to the prepared statement
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Record inserted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error inserting record"]);
        }
    }
}
