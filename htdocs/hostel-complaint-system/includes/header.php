<?php
/**
 * Expects (optionally) $pageTitle to be set before including.
 * Also expects session already started by the including page.
 */
$pageTitle = $pageTitle ?? 'Hostel Complaint Management System';
$isStudent = isset($_SESSION['student_id']);
$isAdmin   = isset($_SESSION['admin_id']);

// Work out relative base path depending on folder depth
$basePath = (strpos($_SERVER['PHP_SELF'], '/student/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? '..' : '.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="brand"> Hostel Complaint System</div>
    <nav>
        <?php if ($isStudent): ?>
            <a href="<?= $basePath ?>/student/dashboard.php">Dashboard</a>
            <a href="<?= $basePath ?>/student/submit_complaint.php">New Complaint</a>
            <a href="<?= $basePath ?>/student/my_complaints.php">My Complaints</a>
            <a href="<?= $basePath ?>/student/logout.php">Logout</a>
            <span class="badge-role"><?= htmlspecialchars($_SESSION['student_name'] ?? 'Student') ?></span>
        <?php elseif ($isAdmin): ?>
            <a href="<?= $basePath ?>/admin/dashboard.php">Dashboard</a>
            <a href="<?= $basePath ?>/admin/manage_complaints.php">Complaints</a>
            <a href="<?= $basePath ?>/admin/manage_students.php">Students</a>
            <a href="<?= $basePath ?>/admin/logout.php">Logout</a>
            <span class="badge-role">Admin</span>
        <?php else: ?>
            <a href="<?= $basePath ?>/index.php">Home</a>
            <a href="<?= $basePath ?>/student/login.php">Student Login</a>
            <a href="<?= $basePath ?>/admin/login.php">Admin Login</a>
        <?php endif; ?>
    </nav>
</header>
