<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_admin_login();

$complaintId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT c.*, s.full_name, s.roll_no, s.email, s.phone FROM complaints c
                        JOIN students s ON c.student_id = s.student_id
                        WHERE c.complaint_id = ?");
$stmt->execute([$complaintId]);
$complaint = $stmt->fetch();

if (!$complaint) {
    die("Complaint not found. <a href='manage_complaints.php'>Go back</a>");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = sanitize($_POST['status'] ?? '');
    $remarks = sanitize($_POST['remarks'] ?? '');
    $validStatuses = ['Pending', 'In Progress', 'Resolved', 'Rejected'];

    if (!in_array($newStatus, $validStatuses)) {
        $error = "Invalid status selected.";
    } elseif (empty($remarks)) {
        $error = "Please add a remark explaining this status update.";
    } else {
        $upd = $pdo->prepare("UPDATE complaints SET status = ?, admin_remarks = ? WHERE complaint_id = ?");
        $upd->execute([$newStatus, $remarks, $complaintId]);

        $log = $pdo->prepare("INSERT INTO complaint_logs (complaint_id, status, remarks, changed_by) VALUES (?, ?, ?, ?)");
        $log->execute([$complaintId, $newStatus, $remarks, $_SESSION['admin_name']]);

        redirect("view_complaint.php?id=$complaintId&updated=1");
    }
}

$logStmt = $pdo->prepare("SELECT * FROM complaint_logs WHERE complaint_id = ? ORDER BY changed_at ASC");
$logStmt->execute([$complaintId]);
$logs = $logStmt->fetchAll();

$pageTitle = "Manage Complaint #$complaintId";
include '../includes/header.php';
?>

<div class="container" style="max-width:760px;">
    <div class="page-header">
        <h1>Complaint #<?= $complaintId ?></h1>
        <a href="manage_complaints.php" class="btn btn-outline">← Back</a>
    </div>

    <?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Status updated successfully.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

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

        <div class="card" style="background:#f7f9fc; box-shadow:none; border:1px solid var(--border);">
            <strong>Filed by:</strong> <?= htmlspecialchars($complaint['full_name']) ?> (<?= htmlspecialchars($complaint['roll_no']) ?>)<br>
            <strong>Email:</strong> <?= htmlspecialchars($complaint['email']) ?> &nbsp;|&nbsp;
            <strong>Phone:</strong> <?= htmlspecialchars($complaint['phone']) ?>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Update Status</h3>
        <form method="POST">
            <label>Status</label>
            <select name="status" required>
                <?php foreach (['Pending', 'In Progress', 'Resolved', 'Rejected'] as $s): ?>
                    <option value="<?= $s ?>" <?= $complaint['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>

            <label>Remarks (required)</label>
            <textarea name="remarks" rows="3" placeholder="e.g. Electrician assigned, will fix by tomorrow" required></textarea>

            <button type="submit" class="btn btn-primary mt-20">Update Status</button>
        </form>
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

<?php include '../includes/footer.php'; ?>
