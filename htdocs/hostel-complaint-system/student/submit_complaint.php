<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_student_login();

$studentId = $_SESSION['student_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

$errors = [];
$old = ['category' => '', 'subject' => '', 'description' => '', 'priority' => 'Low', 'room_no' => $student['room_no']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'category' => sanitize($_POST['category'] ?? ''),
        'subject' => sanitize($_POST['subject'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'priority' => sanitize($_POST['priority'] ?? ''),
        'room_no' => sanitize($_POST['room_no'] ?? ''),
    ];

    $errors = validate_complaint($old);

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO complaints (student_id, category, subject, description, priority, hostel_block, room_no)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$studentId, $old['category'], $old['subject'], $old['description'], $old['priority'], $student['hostel_block'], $old['room_no']]);
        $complaintId = $pdo->lastInsertId();

        $log = $pdo->prepare("INSERT INTO complaint_logs (complaint_id, status, remarks, changed_by) VALUES (?, 'Pending', 'Complaint filed by student.', ?)");
        $log->execute([$complaintId, $student['full_name']]);

        $_SESSION['flash_success'] = "Complaint submitted successfully! Tracking ID: #$complaintId";
        redirect('my_complaints.php');
    }
}

$pageTitle = "Submit Complaint";
include '../includes/header.php';
?>

<div class="container" style="max-width:640px;">
    <div class="page-header"><h1>File a New Complaint</h1></div>

    <div class="alert alert-info">💬 Tip: Click the chat icon in the bottom-right corner and describe your issue — the AI assistant will auto-categorize it and fill this form for you!</div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">Please fix the errors below.</div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" id="complaintForm" novalidate>
            <label>Category</label>
            <select name="category">
                <option value="">-- Select Category --</option>
                <?php foreach (['Electrical','Plumbing','Cleanliness','Internet/WiFi','Mess/Food','Furniture','Security','Noise','Other'] as $cat): ?>
                    <option value="<?= $cat ?>" <?= $old['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['category'])): ?><div class="field-error show"><?= $errors['category'] ?></div><?php endif; ?>

            <label>Subject</label>
            <input type="text" name="subject" placeholder="Short summary of the issue" value="<?= htmlspecialchars($old['subject']) ?>">
            <?php if (isset($errors['subject'])): ?><div class="field-error show"><?= $errors['subject'] ?></div><?php endif; ?>

            <label>Description</label>
            <textarea name="description" rows="5" placeholder="Describe the issue in detail..."><?= htmlspecialchars($old['description']) ?></textarea>
            <div class="text-light" id="descCounter" style="font-size:12px; margin-top:4px;">0 characters</div>
            <?php if (isset($errors['description'])): ?><div class="field-error show"><?= $errors['description'] ?></div><?php endif; ?>

            <div class="form-row">
                <div>
                    <label>Priority</label>
                    <select name="priority">
                        <?php foreach (['Low','Medium','High'] as $p): ?>
                            <option value="<?= $p ?>" <?= $old['priority'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['priority'])): ?><div class="field-error show"><?= $errors['priority'] ?></div><?php endif; ?>
                </div>
                <div>
                    <label>Room Number</label>
                    <input type="text" name="room_no" value="<?= htmlspecialchars($old['room_no']) ?>">
                    <?php if (isset($errors['room_no'])): ?><div class="field-error show"><?= $errors['room_no'] ?></div><?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-20" style="width:100%;">Submit Complaint</button>
        </form>
    </div>
</div>

<script src="../assets/js/validation.js"></script>
<?php $basePath = '..'; include '../includes/chatbot_widget.php'; ?>
<?php include '../includes/footer.php'; ?>
