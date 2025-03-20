<?php
class DBFunctions {
    private $pdo;

    public function __construct() {
        $host = "localhost";
        $dbname = "inventotrack"; // Change this to your database name
        $username = "root"; // Change if necessary
        $password = ""; // Change if necessary

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    // Insert a new record
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    // Update a record
    public function update($table, $data, $conditions) {
        $setValues = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
        $whereConditions = implode(" AND ", array_map(fn($key) => "$key = :where_$key", array_keys($conditions)));

        $sql = "UPDATE $table SET $setValues WHERE $whereConditions";
        $stmt = $this->pdo->prepare($sql);

        foreach ($conditions as $key => $value) {
            $data["where_$key"] = $value;
        }

        return $stmt->execute($data);
    }

    // Delete a record
    public function delete($table, $conditions) {
        $whereConditions = implode(" AND ", array_map(fn($key) => "$key = :$key", array_keys($conditions)));
        $sql = "DELETE FROM $table WHERE $whereConditions";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($conditions);
    }

    // Select records (Filtered by Department)
    public function select($table, $columns = "*", $conditions = []) {
        $sql = "SELECT $columns FROM $table";
        if (!empty($conditions)) {
            $whereConditions = implode(" AND ", array_map(fn($key) => "$key = :$key", array_keys($conditions)));
            $sql .= " WHERE $whereConditions";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Initialize the database functions
$function = new DBFunctions();
?>
