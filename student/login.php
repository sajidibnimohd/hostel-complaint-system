<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (is_student_logged_in()) redirect('dashboard.php');

$error = '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['full_name'];
            redirect('dashboard.php');
        } else {
            $error = "Invalid email or password.";
        }
    }
}

$pageTitle = "Student Login";
include '../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Student Login</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <form method="POST" id="loginForm" data-idfield="email" novalidate>
            <label>Email</label>
            <input type="email" name="email">

            <label>Password</label>
            <input type="password" name="password">

            <button type="submit" class="btn btn-primary mt-20" style="width:100%;">Login</button>
        </form>
        <div class="switch-link">New here? <a href="register.php">Create an account</a></div>
        <div class="switch-link"><a href="../index.php">← Back to Home</a></div>
    </div>
</div>

<script src="../assets/js/validation.js"></script>
</body>
</html>
