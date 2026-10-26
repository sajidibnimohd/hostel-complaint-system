<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_student_login();

$studentId = $_SESSION['student_id'];
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT * FROM complaints WHERE student_id = ?";
$params = [$studentId];
if ($statusFilter && in_array($statusFilter, ['Pending', 'In Progress', 'Resolved', 'Rejected'])) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$pageTitle = "My Complaints";
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>My Complaints</h1>
        <a href="submit_complaint.php" class="btn btn-primary">+ New Complaint</a>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="filters">
        <select onchange="window.location.href='?status='+this.value">
            <option value="">All Statuses</option>
            <?php foreach (['Pending', 'In Progress', 'Resolved', 'Rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="card">
        <?php if (empty($complaints)): ?>
            <div class="empty-state">No complaints found. <a href="submit_complaint.php">File one now</a>.</div>
        <?php else: ?>
        <table>
            <tr><th>ID</th><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Filed</th><th></th></tr>
            <?php foreach ($complaints as $c): ?>
            <tr>
                <td>#<?= $c['complaint_id'] ?></td>
                <td><?= htmlspecialchars($c['subject']) ?></td>
                <td><?= htmlspecialchars($c['category']) ?></td>
                <td><span class="badge badge-<?= $c['priority'] ?>"><?= $c['priority'] ?></span></td>
                <td><span class="badge badge-<?= str_replace(' ', '', $c['status']) ?>"><?= $c['status'] ?></span></td>
                <td class="text-light"><?= time_ago($c['created_at']) ?></td>
                <td><a href="view_complaint.php?id=<?= $c['complaint_id'] ?>" class="btn btn-outline btn-sm">View</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php $basePath = '..'; include '../includes/chatbot_widget.php'; ?>
<?php include '../includes/footer.php'; ?>
