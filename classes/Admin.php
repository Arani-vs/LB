<?php
require_once 'database.php';

class Admin {
    private $conn;
    private $table_name = "admins";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function login($email, $password) {
        $sql = "SELECT u.*, a.adminID FROM users u INNER JOIN " . $this->table_name . " a ON u.member_id = a.member_id WHERE u.email = ? AND u.role = 'Admin'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                return $admin;
            }
        }
        return false;
    }
}
?>
