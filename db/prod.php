<?php
class Database
{
    private $host = 'localhost';
    private $db_name = 'u870747372_sweetsofurban';
    private $username = 'u870747372_sweetsofurban';
    private $password = 'xXW=eL[0';
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