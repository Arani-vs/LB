<?php
require_once 'includes/header.php';
require_once 'classes/User.php';

if(isset($_SESSION['user_id'])){
    header("Location: user_dashboard.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    $email = $_POST['email'];
    $password = $_POST['password'];
    $login = $user->login($email, $password);
    if ($login) {
        $status = isset($login['status']) ? $login['status'] : 1;
        $role = isset($login['role']) ? $login['role'] : 'Student';
        
        if ($status == 1) {
            $_SESSION['user_id'] = $login['id'];
            $_SESSION['user_name'] = $login['name'];
            $_SESSION['user_role'] = $role;
            header("Location: user_dashboard.php");
            exit;
        } else {
            $error = "Your account has been deactivated.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>

