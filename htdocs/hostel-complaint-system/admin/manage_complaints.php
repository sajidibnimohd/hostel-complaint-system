<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_admin_login();

$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT c.*, s.full_name, s.roll_no FROM complaints c JOIN students s ON c.student_id = s.student_id WHERE 1=1";
$params = [];

if ($statusFilter && in_array($statusFilter, ['Pending', 'In Progress', 'Resolved', 'Rejected'])) {
    $sql .= " AND c.status = ?";
    $params[] = $statusFilter;
}
if ($categoryFilter) {
    $sql .= " AND c.category = ?";
    $params[] = $categoryFilter;
}
if ($search) {
    $sql .= " AND (c.subject LIKE ? OR s.full_name LIKE ? OR s.roll_no LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}
$sql .= " ORDER BY FIELD(c.priority,'High','Medium','Low'), c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$pageTitle = "Manage Complaints";
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header"><h1>Manage Complaints</h1></div>

    <form method="GET" class="filters">
        <input type="text" name="search" placeholder="Search subject / student / roll no" value="<?= htmlspecialchars($search) ?>">
        <select name="status">
            <option value="">All Statuses</option>
            <?php foreach (['Pending', 'In Progress', 'Resolved', 'Rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach (['Electrical','Plumbing','Cleanliness','Internet/WiFi','Mess/Food','Furniture','Security','Noise','Other'] as $c): ?>
                <option value="<?= $c ?>" <?= $categoryFilter === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="manage_complaints.php" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <div class="card">
        <?php if (empty($complaints)): ?>
            <div class="empty-state">No complaints match these filters.</div>
        <?php else: ?>
        <table>
            <tr><th>ID</th><th>Student</th><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Filed</th><th></th></tr>
            <?php foreach ($complaints as $c): ?>
            <tr>
                <td>#<?= $c['complaint_id'] ?></td>
                <td><?= htmlspecialchars($c['full_name']) ?><br><span class="text-light" style="font-size:12px;"><?= htmlspecialchars($c['roll_no']) ?></span></td>
                <td><?= htmlspecialchars($c['subject']) ?></td>
                <td><?= htmlspecialchars($c['category']) ?></td>
                <td><span class="badge badge-<?= $c['priority'] ?>"><?= $c['priority'] ?></span></td>
                <td><span class="badge badge-<?= str_replace(' ', '', $c['status']) ?>"><?= $c['status'] ?></span></td>
                <td class="text-light"><?= time_ago($c['created_at']) ?></td>
                <td><a href="view_complaint.php?id=<?= $c['complaint_id'] ?>" class="btn btn-outline btn-sm">Manage</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
