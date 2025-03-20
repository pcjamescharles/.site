<?php
class Database
{
    private $host = 'localhost';
    private $db_name = 'inventotrack';
    private $username = 'root';
    private $password = '';
    // private $host = 'localhost';
    // private $db_name = 'ciras';
    // private $username = 'root';
    // private $password = '';
    public $conn;

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>