<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (is_admin_logged_in()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            redirect('dashboard.php');
        } else {
            $error = "Invalid username or password.";
        }
    }
}

$pageTitle = "Admin Login";
include '../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Admin Login</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" id="loginForm" data-idfield="username" novalidate>
            <label>Username</label>
            <input type="text" name="username">

            <label>Password</label>
            <input type="password" name="password">

            <button type="submit" class="btn btn-primary mt-20" style="width:100%;">Login</button>
        </form>
        <div class="switch-link"><a href="../index.php">← Back to Home</a></div>
        <div class="switch-link text-light" style="font-size:12px;">First time? Run <code>admin/create_admin.php</code> once to set up your account.</div>
    </div>
</div>

<script src="../assets/js/validation.js"></script>
</body>
</html>
