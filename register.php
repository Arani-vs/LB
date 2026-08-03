<?php
require_once 'includes/header.php';
require_once 'classes/User.php';

if(isset($_SESSION['user_id'])){
    header("Location: user_dashboard.php");
    exit;
}

$success = "";
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    $user->setName($_POST['name']);
    $user->setEmail($_POST['email']);
    $user->setPassword($_POST['password']);
    $user->setMobile($_POST['mobile']);
    $user->setAddress($_POST['address']);
    $user->setRole($_POST['role']);

    if ($user->register()) {
        $success = "Registration successful! You can now login.";
    } else {
        $error = "Registration failed. Please try again.";
    }
}
?>