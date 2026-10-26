<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
require_admin_login();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$id]);
    redirect('manage_students.php?deleted=1');
}

$search = trim($_GET['search'] ?? '');
$sql = "SELECT s.*, (SELECT COUNT(*) FROM complaints c WHERE c.student_id = s.student_id) as complaint_count
        FROM students s WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (s.full_name LIKE ? OR s.roll_no LIKE ? OR s.email LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$pageTitle = "Manage Students";
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header"><h1>Registered Students</h1></div>

    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Student record deleted.</div><?php endif; ?>

    <form method="GET" class="filters">
        <input type="text" name="search" placeholder="Search name / roll no / email" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
    </form>

    <div class="card">
        <?php if (empty($students)): ?>
            <div class="empty-state">No students found.</div>
        <?php else: ?>
        <table>
            <tr><th>Name</th><th>Roll No</th><th>Email</th><th>Phone</th><th>Hostel</th><th>Complaints</th><th></th></tr>
            <?php foreach ($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['full_name']) ?></td>
                <td><?= htmlspecialchars($s['roll_no']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><?= htmlspecialchars($s['hostel_block']) ?> - <?= htmlspecialchars($s['room_no']) ?></td>
                <td><?= $s['complaint_count'] ?></td>
                <td>
                    <a href="manage_students.php?delete=<?= $s['student_id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this student and all their complaints? This cannot be undone.');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
