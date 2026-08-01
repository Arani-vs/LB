<?php
require_once 'Database.php';

class Admin {
    private $conn;
    private $table_name = "admins";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE email = ? AND password = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            return $admin;
        }
        return false;
    }
}
?>
