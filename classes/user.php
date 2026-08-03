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
        $sql = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function register() {
       
        $status = 1;
        $sql = "INSERT INTO " . $this->table_name . " (name, email, password, mobile, address, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ssssssi", $this->name, $this->email, $this->password, $this->mobile, $this->address, $this->role, $status);
        if($stmt->execute()){
            $user_id = $this->conn->insert_id;
            $child_table = "";
            if ($this->role == 'Admin') {
                $child_table = 'admins';
            } elseif ($this->role == 'Student' || strtolower($this->role) == 'user') {
                $child_table = 'students';
            } elseif ($this->role == 'Staff') {
                $child_table = 'staff';
            }
            
            if ($child_table != "") {
                $child_sql = "INSERT INTO " . $child_table . " (user_id) VALUES (?)";
                $child_stmt = $this->conn->prepare($child_sql);
                if ($child_stmt) {
                    $child_stmt->bind_param("i", $user_id);
                    $child_stmt->execute();
                }
            }
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
    
    public function getUserDetails($id) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function getAllUsers() {
        $sql = "SELECT u.*, a.admin_id, s.student_id, st.staff_id 
                FROM " . $this->table_name . " u
                LEFT JOIN admins a ON u.id = a.user_id
                LEFT JOIN students s ON u.id = s.user_id
                LEFT JOIN staff st ON u.id = st.user_id";
        return $this->conn->query($sql);
    }
    
    public function toggleUserStatus($id, $status) {
        $sql = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $status, $id);
        return $stmt->execute();
    }
}
?>
