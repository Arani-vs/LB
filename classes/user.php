<?php
require_once 'database.php';

class User {
    private $conn;
    private $table_name = "users";

    
    private $name;
    private $email;
    private $password;
    private $mobile;
    private $address;
    private $role;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setMobile($mobile) { $this->mobile = $mobile; }
    public function setAddress($address) { $this->address = $address; }
    public function setRole($role) { $this->role = $role; }

    public function isEmailExists($email) {
        $sql = "SELECT member_id FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function register() {
        $status = 1;
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO " . $this->table_name . " (name, email, password, mobile, address, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ssssssi", $this->name, $this->email, $hashed_password, $this->mobile, $this->address, $this->role, $status);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
    
    public function getUserDetails($member_id) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE member_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function getAllUsers() {
        $sql = "SELECT * FROM " . $this->table_name;
        return $this->conn->query($sql);
    }
    
    public function toggleUserStatus($member_id, $status) {
        $sql = "UPDATE " . $this->table_name . " SET status = ? WHERE member_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $status, $member_id);
        return $stmt->execute();
    }
}
?>
