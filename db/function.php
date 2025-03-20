<?php
include_once 'db.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class DBFunctions
{
    private $conn;
    private $api_token = '821|fIAkc64uhsb7YWnwMNYRsKJMKvDy0sDqAL32CJIB'; // Your API token

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAllByTableName($table_name)
    {
        // Sanitize the table name to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
            throw new InvalidArgumentException('Invalid table name');
        }

        $query = "SELECT * FROM `$table_name`"; // Use backticks to handle reserved words or special characters
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . $this->conn->errorInfo()[2]);
        }

        $success = $stmt->execute();

        if (!$success) {
            throw new RuntimeException('Failed to execute query: ' . $stmt->errorInfo()[2]);
        }

        // Fetch results if needed, for example:
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Optionally return the results or handle them as needed
        return $results;
    }
    function sendOtpEmail($to, $otp)
    {
        $mail = new PHPMailer(true); // Create a new PHPMailer instance
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'support@sweetsofsuburban.com';
            $mail->Password = 'Sweetsofurban_123';
            $mail->SMTPSecure = 'ssl'; // Use 'tls' if required
            $mail->Port = 465;

            $mail->setFrom('support@sweetsofsuburban.com', 'Sweets of Urban');
            $mail->addAddress($to);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Your OTP code is: $otp";

            $mail->send();
            return ['status' => 'success', 'message' => 'OTP has been sent to your email.'];
        } catch (Exception $e) {
            // Return the exception message as part of the response
            return ['status' => 'error', 'message' => 'Mail Error: ' . $e->getMessage()];
        }
    }
    // Insert function
    public function insert($table, $data)
    {
        try {
            // Prepare the column names and placeholders for the query
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));

            // Create the SQL insert query
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->conn->prepare($sql);

            // Bind the parameters to the SQL query
            foreach ($data as $key => &$val) {
                $stmt->bindParam(':' . $key, $val);
            }

            // Execute the SQL query
            $stmt->execute();
            return true; // Return true if the execution was successful
        } catch (PDOException $e) {
            // Handle the exception by echoing the error message
            echo "Error: " . $e->getMessage();
            return false; // Return false if an error occurred
        }
    }


    // Update function
    public function update($table, $data, $conditions)
    {
        $set_clause = implode(", ", array_map(function ($key) {
            return "$key = :$key";
        }, array_keys($data)));

        $condition_clause = implode(" AND ", array_map(function ($key) {
            return "$key = :$key";
        }, array_keys($conditions)));

        $sql = "UPDATE $table SET $set_clause WHERE $condition_clause";
        $stmt = $this->conn->prepare($sql);

        foreach (array_merge($data, $conditions) as $key => &$val) {
            $stmt->bindParam(':' . $key, $val);
        }

        return $stmt->execute();
    }

    // Delete function
    public function delete($table, $conditions)
    {
        $condition_clause = implode(" AND ", array_map(function ($key) {
            return "$key = :$key";
        }, array_keys($conditions)));

        $sql = "DELETE FROM $table WHERE $condition_clause";
        $stmt = $this->conn->prepare($sql);

        foreach ($conditions as $key => &$val) {
            $stmt->bindParam(':' . $key, $val);
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to execute query: ' . implode(' ', $stmt->errorInfo()));
        }

        return $stmt->rowCount(); // Return number of affected rows for confirmation
    }


    public function select($table, $columns = "*", $conditions = [])
    {
        $sql = "SELECT $columns FROM $table";

        if (!empty($conditions)) {
            $condition_clause = implode(" AND ", array_map(function ($key) {
                return "$key = :$key";
            }, array_keys($conditions)));

            $sql .= " WHERE $condition_clause";
        }

        $stmt = $this->conn->prepare($sql);

        if (!empty($conditions)) {
            foreach ($conditions as $key => &$val) {
                $stmt->bindParam(':' . $key, $val);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function select2($table, $columns = "*", $conditions = [], $customCondition = "")
    {
        // Start building the SQL query
        $sql = "SELECT $columns FROM $table";

        // If there are conditions, add them as key-value pairs
        if (!empty($conditions)) {
            $condition_clause = implode(" AND ", array_map(function ($key) {
                return "$key = :$key";
            }, array_keys($conditions)));

            $sql .= " WHERE $condition_clause";
        }

        // Append custom conditions if provided
        if ($customCondition) {
            // If there are already conditions, add 'AND' to chain the custom condition
            $sql .= empty($conditions) ? " WHERE $customCondition" : " AND $customCondition";
        }

        // Prepare the statement
        $stmt = $this->conn->prepare($sql);

        // Bind parameters for the conditions
        if (!empty($conditions)) {
            foreach ($conditions as $key => &$val) {
                $stmt->bindParam(':' . $key, $val);
            }
        }

        // Execute and return the result
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectUniqueOrders($table, $columns = "*")
    {
        $sql = "SELECT $columns
                FROM $table
                GROUP BY order_id";

        // Prepare and execute the query
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        // Return the fetched result as an associative array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUniqueIctype($table_name)
    {
        // Sanitize the table name to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
            throw new InvalidArgumentException('Invalid table name');
        }

        // Construct the SQL query to count unique ictype values for incidents
        $sql = "SELECT COUNT(DISTINCT ictype) AS unique_ictype_count, MAX(ictype) AS highest_ictype 
                FROM `$table_name` 
                WHERE type = 'incident'";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . implode(' ', $this->conn->errorInfo()));
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to execute query: ' . implode(' ', $stmt->errorInfo()));
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new RuntimeException('Failed to fetch result: ' . implode(' ', $stmt->errorInfo()));
        }

        return [
            'unique_ictype_count' => $result['unique_ictype_count'] ?? 0,
            'highest_ictype' => $result['highest_ictype'] ?? null,
        ];
    }


    public function count($table_name, $condition = null)
    {
        // Sanitize the table name to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
            throw new InvalidArgumentException('Invalid table name');
        }

        // Construct the SQL query
        $sql = "SELECT COUNT(*) AS count FROM `$table_name`";

        // Add condition if provided
        if ($condition) {
            $sql .= " WHERE $condition";
        }

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . implode(' ', $this->conn->errorInfo()));
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to execute query: ' . implode(' ', $stmt->errorInfo()));
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new RuntimeException('Failed to fetch result: ' . implode(' ', $stmt->errorInfo()));
        }

        return $result['count'] ?? 0;
    }


    public function sum($table_name, $column_name, $condition = null)
    {
        // Sanitize the table name and column name to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name) || !preg_match('/^[a-zA-Z0-9_]+$/', $column_name)) {
            throw new InvalidArgumentException('Invalid table name or column name');
        }

        // Construct the SQL query
        $sql = "SELECT SUM($column_name) AS total FROM `$table_name`";

        // Add condition if provided
        if ($condition) {
            $sql .= " WHERE $condition";
        }

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . implode(' ', $this->conn->errorInfo()));
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to execute query: ' . implode(' ', $stmt->errorInfo()));
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new RuntimeException('Failed to fetch result: ' . implode(' ', $stmt->errorInfo()));
        }

        return $result['total'] ?? 0;
    }


    public function getSMSBalance()
    {
        $url = 'https://app.philsms.com/api/v3/balance';
        $headers = [
            "Authorization: Bearer {$this->api_token}",
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
            return 'Error decoding JSON: ' . json_last_error_msg();
        }

        if (isset($result['status']) && $result['status'] === 'success') {
            $balance = $result['data']['remaining_balance'] ?? 'N/A';
            $expiration = $result['data']['expired_on'] ?? 'N/A';

            return "{$balance}";
        } else {
            return 'Error retrieving balance: ' . ($result['message'] ?? 'Unknown error');
        }
    }


    public function sendSMS($mobileNumber, $message)
    {
        $url = 'https://app.philsms.com/api/v3/sms/send';

        $data = [
            'recipient' => $mobileNumber,
            'sender_id' => 'PhilSMS',
            'type' => 'plain',
            'message' => $message
        ];

        $headers = [
            "Authorization: Bearer {$this->api_token}",
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 'error', 'message' => $error];
        }

        return json_decode($response, true);
    }

}