<?php
session_start();
$pageTitle = "Home | Hostel Complaint Management System";
include 'includes/header.php';
?>

<section class="hero">
    <h1>Hostel Complaint Management System</h1>
    <p>Report hostel issues quickly, track resolution status in real time, and get help from our AI assistant while filing your complaint.</p>
    <a href="student/register.php" class="btn btn-primary">Student Register</a>
    <a href="student/login.php" class="btn btn-outline" style="background:#fff;">Student Login</a>
    <a href="admin/login.php" class="btn btn-outline" style="background:#fff;">Admin Login</a>
</section>

<section class="features">
    <div class="feature-card">
        <div class="icon"></div>
        <h3>Easy Complaint Filing</h3>
        <p class="text-light">Submit hostel complaints with category, priority and room details in seconds.</p>
    </div>
    <div class="feature-card">
        <div class="icon"></div>
        <h3>AI Chat Assistant</h3>
        <p class="text-light">Describe your issue in plain words — the assistant auto-categorizes and fills the form.</p>
    </div>
    <div class="feature-card">
        <div class="icon"></div>
        <h3>Live Status Tracking</h3>
        <p class="text-light">Track every complaint from Pending to Resolved with a full status timeline.</p>
    </div>
    <div class="feature-card">
        <div class="icon"></div>
        <h3>Admin Dashboard</h3>
        <p class="text-light">Admins can filter, prioritize and resolve complaints from one clean dashboard.</p>
    </div>
</section>

<div class="container" style="text-align:center; margin-top: 60px;">
    <!-- <h2>How it works</h2> -->
    <!-- <p class="text-light">Register as a student → Log in → File a complaint (with AI assistant help) → Track status until it's resolved by hostel admin.</p> -->
</div>

<?php include 'includes/footer.php'; ?>
