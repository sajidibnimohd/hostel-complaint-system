
<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (is_student_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];

$old = [
    'full_name' => '',
    'roll_no' => '',
    'email' => '',
    'phone' => '',
    'hostel_block' => '',
    'room_no' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect form data using consistent field names
    $old = [
        'full_name' => sanitize($_POST['full_name'] ?? ''),
        'roll_no' => sanitize($_POST['roll_no'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'hostel_block' => sanitize($_POST['hostel_block'] ?? ''),
        'room_no' => sanitize($_POST['room_no'] ?? '')
    ];

    $postData = $old + [
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? ''
    ];

    // Validate registration details
    $errors = validate_registration($postData);

    // Ensure a valid hostel block is selected
    if (!in_array($old['hostel_block'], ['Block A', 'Block B', 'Block C', 'Block D'], true)) {
        $errors['hostel_block'] = 'Please select a valid hostel block.';
    }

    if (empty($errors)) {
        try {
            // Check for existing email or roll number
            $stmt = $pdo->prepare(
                "SELECT student_id FROM students WHERE email = ? OR roll_no = ?"
            );
            $stmt->execute([$old['email'], $old['roll_no']]);

            if ($stmt->fetch()) {
                $errors['email'] = 'An account with this email or roll number already exists.';
            } else {
                // Hash the password securely
                $hash = password_hash($postData['password'], PASSWORD_BCRYPT);

                // Save student registration
                $stmt = $pdo->prepare(
                    "INSERT INTO students
                    (full_name, roll_no, email, phone, hostel_block, room_no, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->execute([
                    $old['full_name'],
                    $old['roll_no'],
                    $old['email'],
                    $old['phone'],
                    $old['hostel_block'],
                    $old['room_no'],
                    $hash
                ]);

                $_SESSION['flash_success'] = 'Registration successful! Please log in.';
                redirect('login.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Student registration error: ' . $e->getMessage());
            $errors['general'] = 'Registration failed. Please check your details and try again.';
        }
    }
}

$pageTitle = 'Student Register';
include '../includes/header.php';
?>

<div class="container" style="max-width:520px;">
    <div class="card">

        <h2 style="text-align:center; color:var(--primary-dark);">
            Student Registration
        </h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                Please fix the errors below.
                <?php if (isset($errors['general'])): ?>
                    <p><?= htmlspecialchars($errors['general']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm" novalidate>

            <label>Full Name</label>
            <input
                type="text"
                name="full_name"
                value="<?= htmlspecialchars($old['full_name']) ?>"
                required
            >
            <?php if (isset($errors['full_name'])): ?>
                <div class="field-error show">
                    <?= htmlspecialchars($errors['full_name']) ?>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div>
                    <label>Roll Number</label>
                    <input
                        type="text"
                        name="roll_no"
                        value="<?= htmlspecialchars($old['roll_no']) ?>"
                        required
                    >
                    <?php if (isset($errors['roll_no'])): ?>
                        <div class="field-error show">
                            <?= htmlspecialchars($errors['roll_no']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label>Phone Number</label>
                    <input
                        type="text"
                        name="phone"
                        maxlength="10"
                        value="<?= htmlspecialchars($old['phone']) ?>"
                        required
                    >
                    <?php if (isset($errors['phone'])): ?>
                        <div class="field-error show">
                            <?= htmlspecialchars($errors['phone']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <label>Email</label>
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($old['email']) ?>"
                required
            >
            <?php if (isset($errors['email'])): ?>
                <div class="field-error show">
                    <?= htmlspecialchars($errors['email']) ?>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div>
                    <label>Hostel Name</label>
                    <select name="hostel_block" required>
                        <option value="">-- Select --</option>

                        <?php foreach (['A', 'B', 'C', 'D'] as $block): ?>
                            <option
                                value="<?= 'Block ' . $block ?>"
                                <?= $old['hostel_block'] === 'Block ' . $block ? 'selected' : '' ?>
                            >
                                <?= 'Block ' . $block ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <?php if (isset($errors['hostel_block'])): ?>
                        <div class="field-error show">
                            <?= htmlspecialchars($errors['hostel_block']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label>Room Number</label>
                    <input
                        type="text"
                        name="room_no"
                        value="<?= htmlspecialchars($old['room_no']) ?>"
                        required
                    >
                    <?php if (isset($errors['room_no'])): ?>
                        <div class="field-error show">
                            <?= htmlspecialchars($errors['room_no']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <label>Password</label>
            <input type="password" name="password" required>
            <?php if (isset($errors['password'])): ?>
                <div class="field-error show">
                    <?= htmlspecialchars($errors['password']) ?>
                </div>
            <?php endif; ?>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
            <?php if (isset($errors['confirm_password'])): ?>
                <div class="field-error show">
                    <?= htmlspecialchars($errors['confirm_password']) ?>
                </div>
            <?php endif; ?>

            <button
                type="submit"
                class="btn btn-primary mt-20"
                style="width:100%;"
            >
                Register
            </button>

        </form>

        <div class="switch-link">
            Already have an account?
            <a href="login.php">Login here</a>
        </div>

    </div>
</div>

<script src="../assets/js/validation.js"></script>

<?php include '../includes/footer.php'; ?>
