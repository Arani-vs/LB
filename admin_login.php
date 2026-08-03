<?php
require_once 'includes/header.php';
require_once 'classes/Admin.php';

if(isset($_SESSION['admin_id'])){
    header("Location: admin_dashboard.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin = new Admin();
    $email = $_POST['email'];
    $password = $_POST['password'];
    $login = $admin->login($email, $password);
    if ($login) {
        $_SESSION['admin_id'] = $login['id'];
        $_SESSION['admin_name'] = $login['name'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="glass-card mt-5">
            <h3 class="text-center mb-4">Admin Login</h3>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" id="adminLoginForm">
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">Login as Admin</button>
            </form>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
