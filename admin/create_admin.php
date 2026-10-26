<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Visit this file once in your browser (e.g. http://localhost/hostel-complaint-system/admin/create_admin.php)
 * to create the default admin account with a correctly generated password hash.
 * DELETE THIS FILE after you have created the admin account, for security.
 */
require_once '../config/db.php';

$message = '';
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullName = trim($_POST['full_name']);
    $password = $_POST['password'];

    if ($username && $fullName && strlen($password) >= 6) {
        $check = $pdo->prepare("SELECT admin_id FROM admin WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $message = "An admin with this username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO admin (username, full_name, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $fullName, $hash]);
            $done = true;
            $message = "Admin account created successfully! You can now log in and should delete this file.";
        }
    } else {
        $message = "Please fill all fields. Password must be at least 6 characters.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Admin Account</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <h2>Create Admin Account (One-Time Setup)</h2>
    <?php if ($message): ?>
        <div class="alert <?= $done ? 'alert-success' : 'alert-error' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$done): ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Password (min 6 chars)</label>
        <input type="password" name="password" required minlength="6">

        <button type="submit" class="btn btn-primary">Create Admin</button>
    </form>
    <?php else: ?>
        <a href="login.php" class="btn btn-primary">Go to Admin Login</a>
    <?php endif; ?>
</div>
</body>
</html>
