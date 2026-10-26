<?php
/**
 * Shared helper functions used across the app.
 */

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function is_student_logged_in() {
    return isset($_SESSION['student_id']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function require_student_login() {
    if (!is_student_logged_in()) {
        redirect('login.php');
    }
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        redirect('login.php');
    }
}

/* ---------------- Server-side validation helpers ---------------- */

function validate_registration($data) {
    $errors = [];

    if (empty($data['full_name']) || strlen(trim($data['full_name'])) < 3) {
        $errors['full_name'] = "Full name must be at least 3 characters.";
    }
    if (empty($data['roll_no']) || !preg_match('/^[A-Za-z0-9\-\/]{3,30}$/', $data['roll_no'])) {
        $errors['roll_no'] = "Enter a valid roll number.";
    }
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email address.";
    }
    if (empty($data['phone']) || !preg_match('/^[6-9][0-9]{9}$/', $data['phone'])) {
        $errors['phone'] = "Enter a valid 10-digit phone number.";
    }
    if (empty($data['hostel_block'])) {
        $errors['hostel_block'] = "Select a hostel block.";
    }
    if (empty($data['room_no']) || !preg_match('/^[A-Za-z0-9\-]{1,10}$/', $data['room_no'])) {
        $errors['room_no'] = "Enter a valid room number.";
    }
    if (empty($data['password']) || strlen($data['password']) < 6 ||
        !preg_match('/[A-Z]/', $data['password']) ||
        !preg_match('/[0-9]/', $data['password'])) {
        $errors['password'] = "Password must be 6+ chars with at least one uppercase letter and one digit.";
    }
    if ($data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    return $errors;
}

function validate_complaint($data) {
    $errors = [];

    $validCategories = ['Electrical','Plumbing','Cleanliness','Internet/WiFi','Mess/Food','Furniture','Security','Noise','Other'];
    if (empty($data['category']) || !in_array($data['category'], $validCategories)) {
        $errors['category'] = "Please select a valid category.";
    }
    if (empty($data['subject']) || strlen(trim($data['subject'])) < 5) {
        $errors['subject'] = "Subject must be at least 5 characters.";
    }
    if (empty($data['description']) || strlen(trim($data['description'])) < 15) {
        $errors['description'] = "Please describe the issue in at least 15 characters.";
    }
    $validPriorities = ['Low', 'Medium', 'High'];
    if (empty($data['priority']) || !in_array($data['priority'], $validPriorities)) {
        $errors['priority'] = "Please select a priority.";
    }
    if (empty($data['room_no'])) {
        $errors['room_no'] = "Room number is required.";
    }

    return $errors;
}

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return "just now";
    if ($diff < 3600) return floor($diff / 60) . " min ago";
    if ($diff < 86400) return floor($diff / 3600) . " hr ago";
    if ($diff < 604800) return floor($diff / 86400) . " day(s) ago";
    return date("d M Y", strtotime($datetime));
}
