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



<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="glass-card mt-5">
            <h3 class="text-center mb-4">User Login</h3>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" id="userLoginForm">
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">Login</button>
            </form>
            <div class="mt-3 text-center">
                <span>Don't have an account?</span> <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
