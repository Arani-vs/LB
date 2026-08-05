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


<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="glass-card mt-5 mb-5">
            <h3 class="text-center mb-4">New User Registration</h3>
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" id="registerForm">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="Member">Member</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">Register</button>
            </form>
            <div class="mt-3 text-center">
                <span>Already have an account?</span> <a href="index.php">Login here</a>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
