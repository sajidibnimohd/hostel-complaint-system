<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_student_login();

$studentId = $_SESSION['student_id'];
$complaintId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM complaints WHERE complaint_id = ? AND student_id = ?");
$stmt->execute([$complaintId, $studentId]);
$complaint = $stmt->fetch();

if (!$complaint) {
    die("Complaint not found or you don't have access to it. <a href='my_complaints.php'>Go back</a>");
}

$logStmt = $pdo->prepare("SELECT * FROM complaint_logs WHERE complaint_id = ? ORDER BY changed_at ASC");
$logStmt->execute([$complaintId]);
$logs = $logStmt->fetchAll();

$pageTitle = "Complaint #$complaintId";
include '../includes/header.php';
?>

<div class="container" style="max-width:720px;">
    <div class="page-header">
        <h1>Complaint #<?= $complaintId ?></h1>
        <a href="my_complaints.php" class="btn btn-outline">← Back</a>
    </div>

    <div class="card">
        <div class="flex-between">
            <h2 style="margin:0;"><?= htmlspecialchars($complaint['subject']) ?></h2>
            <span class="badge badge-<?= str_replace(' ', '', $complaint['status']) ?>"><?= $complaint['status'] ?></span>
        </div>
        <p class="text-light" style="margin-top:6px;">
            <?= htmlspecialchars($complaint['category']) ?> &nbsp;•&nbsp;
            <span class="badge badge-<?= $complaint['priority'] ?>"><?= $complaint['priority'] ?> priority</span> &nbsp;•&nbsp;
            Room <?= htmlspecialchars($complaint['room_no']) ?>, <?= htmlspecialchars($complaint['hostel_block']) ?>
        </p>
        <hr style="border:none;border-top:1px solid var(--border); margin:16px 0;">
        <p><?= nl2br(htmlspecialchars($complaint['description'])) ?></p>

        <?php if ($complaint['admin_remarks']): ?>
            <div class="alert alert-info"><strong>Admin remarks:</strong> <?= htmlspecialchars($complaint['admin_remarks']) ?></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Status Timeline</h3>
        <ul class="timeline">
            <?php foreach ($logs as $log): ?>
            <li>
                <strong><?= htmlspecialchars($log['status']) ?></strong> — <?= htmlspecialchars($log['remarks']) ?>
                <div class="log-date"><?= htmlspecialchars($log['changed_by']) ?> • <?= date('d M Y, h:i A', strtotime($log['changed_at'])) ?></div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php $basePath = '..'; include '../includes/chatbot_widget.php'; ?>
<?php include '../includes/footer.php'; ?>
