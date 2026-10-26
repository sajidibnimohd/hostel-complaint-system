<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_student_login();

$studentId = $_SESSION['student_id'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM complaints WHERE student_id = ? GROUP BY status");
$stmt->execute([$studentId]);
$statusCounts = ['Pending' => 0, 'In Progress' => 0, 'Resolved' => 0, 'Rejected' => 0];
foreach ($stmt->fetchAll() as $row) {
    $statusCounts[$row['status']] = (int)$row['cnt'];
}
$total = array_sum($statusCounts);

$stmt = $pdo->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$studentId]);
$recent = $stmt->fetchAll();

$pageTitle = "Student Dashboard";
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Welcome, <?= htmlspecialchars($student['full_name']) ?> 👋</h1>
        <a href="submit_complaint.php" class="btn btn-primary">+ New Complaint</a>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <strong>Roll No:</strong> <?= htmlspecialchars($student['roll_no']) ?> &nbsp;|&nbsp;
        <strong>Hostel:</strong> <?= htmlspecialchars($student['hostel_block']) ?>, Room <?= htmlspecialchars($student['room_no']) ?> &nbsp;|&nbsp;
        <strong>Email:</strong> <?= htmlspecialchars($student['email']) ?>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-value"><?= $total ?></div><div class="stat-label">Total Complaints</div></div>
        <div class="stat-card pending"><div class="stat-value"><?= $statusCounts['Pending'] ?></div><div class="stat-label">Pending</div></div>
        <div class="stat-card progress"><div class="stat-value"><?= $statusCounts['In Progress'] ?></div><div class="stat-label">In Progress</div></div>
        <div class="stat-card resolved"><div class="stat-value"><?= $statusCounts['Resolved'] ?></div><div class="stat-label">Resolved</div></div>
    </div>

    <div class="card">
        <div class="flex-between">
            <h3 style="margin-top:0;">Recent Complaints</h3>
            <a href="my_complaints.php">View all →</a>
        </div>
        <?php if (empty($recent)): ?>
            <div class="empty-state">You haven't filed any complaints yet. <a href="submit_complaint.php">File your first complaint</a>.</div>
        <?php else: ?>
        <table>
            <tr><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Filed</th></tr>
            <?php foreach ($recent as $c): ?>
            <tr>
                <td><a href="view_complaint.php?id=<?= $c['complaint_id'] ?>"><?= htmlspecialchars($c['subject']) ?></a></td>
                <td><?= htmlspecialchars($c['category']) ?></td>
                <td><span class="badge badge-<?= $c['priority'] ?>"><?= $c['priority'] ?></span></td>
                <td><span class="badge badge-<?= str_replace(' ', '', $c['status']) ?>"><?= $c['status'] ?></span></td>
                <td class="text-light"><?= time_ago($c['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php $basePath = '..'; include '../includes/chatbot_widget.php'; ?>
<?php include '../includes/footer.php'; ?>
