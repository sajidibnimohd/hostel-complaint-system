<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_admin_login();

$stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM complaints GROUP BY status");
$statusCounts = ['Pending' => 0, 'In Progress' => 0, 'Resolved' => 0, 'Rejected' => 0];
foreach ($stmt->fetchAll() as $row) {
    $statusCounts[$row['status']] = (int)$row['cnt'];
}
$total = array_sum($statusCounts);

$catStmt = $pdo->query("SELECT category, COUNT(*) as cnt FROM complaints GROUP BY category ORDER BY cnt DESC");
$categoryCounts = $catStmt->fetchAll();

$totalStudents = $pdo->query("SELECT COUNT(*) as cnt FROM students")->fetch()['cnt'];

$highPriorityStmt = $pdo->query("SELECT c.*, s.full_name, s.roll_no FROM complaints c
                                  JOIN students s ON c.student_id = s.student_id
                                  WHERE c.priority = 'High' AND c.status NOT IN ('Resolved','Rejected')
                                  ORDER BY c.created_at DESC LIMIT 5");
$highPriority = $highPriorityStmt->fetchAll();

$pageTitle = "Admin Dashboard";
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Admin Dashboard</h1>
        <a href="manage_complaints.php" class="btn btn-primary">Manage Complaints</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-value"><?= $total ?></div><div class="stat-label">Total Complaints</div></div>
        <div class="stat-card pending"><div class="stat-value"><?= $statusCounts['Pending'] ?></div><div class="stat-label">Pending</div></div>
        <div class="stat-card progress"><div class="stat-value"><?= $statusCounts['In Progress'] ?></div><div class="stat-label">In Progress</div></div>
        <div class="stat-card resolved"><div class="stat-value"><?= $statusCounts['Resolved'] ?></div><div class="stat-label">Resolved</div></div>
        <div class="stat-card"><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">Registered Students</div></div>
    </div>

    <div class="form-row">
        <div class="card">
            <h3 style="margin-top:0;">Complaints by Category</h3>
            <?php if (empty($categoryCounts)): ?>
                <p class="text-light">No data yet.</p>
            <?php else: ?>
                <?php foreach ($categoryCounts as $row):
                    $pct = $total > 0 ? round(($row['cnt'] / $total) * 100) : 0; ?>
                    <div style="margin-bottom:10px;">
                        <div class="flex-between" style="font-size:13px;">
                            <span><?= htmlspecialchars($row['category']) ?></span>
                            <span class="text-light"><?= $row['cnt'] ?></span>
                        </div>
                        <div style="background:#eef1f6;border-radius:6px;height:8px;overflow:hidden;">
                            <div style="width:<?= $pct ?>%;background:var(--primary);height:100%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">🔴 Urgent — High Priority Open</h3>
            <?php if (empty($highPriority)): ?>
                <p class="text-light">No urgent open complaints. 🎉</p>
            <?php else: ?>
                <?php foreach ($highPriority as $c): ?>
                    <div style="padding:8px 0; border-bottom:1px solid var(--border);">
                        <a href="view_complaint.php?id=<?= $c['complaint_id'] ?>"><?= htmlspecialchars($c['subject']) ?></a>
                        <div class="text-light" style="font-size:12px;"><?= htmlspecialchars($c['full_name']) ?> (<?= htmlspecialchars($c['roll_no']) ?>) • <?= time_ago($c['created_at']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
