<?php
/**
 * ProjectSphere - Student Profile Management
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_student();

$studentId = $_SESSION['profile_id'];
$userId    = $_SESSION['user_id'];

$errors = [];
$success = '';

// Fetch profile
$stmt = $pdo->prepare("
    SELECT s.*, u.username, u.email
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    WHERE s.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $studentId]);
$student = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $fullName   = trim($_POST['full_name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $semester   = trim($_POST['semester'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $bio        = trim($_POST['bio'] ?? '');

        if (empty($fullName)) {
            $errors[] = 'Full name cannot be blank.';
        }

        if (empty($errors)) {
            $upStmt = $pdo->prepare("
                UPDATE students SET 
                    full_name = :name, 
                    department = :dept, 
                    semester = :sem, 
                    phone = :phone, 
                    bio = :bio 
                WHERE id = :id
            ");
            $upStmt->execute([
                ':name'  => $fullName,
                ':dept'  => $department,
                ':sem'   => $semester,
                ':phone' => $phone,
                ':bio'   => $bio,
                ':id'    => $studentId
            ]);

            $_SESSION['full_name'] = $fullName;
            $success = 'Profile details updated successfully!';
            
            // Refresh
            $stmt->execute([':id' => $studentId]);
            $student = $stmt->fetch();
        }
    } elseif ($action === 'change_password') {
        $currPass = $_POST['current_password'] ?? '';
        $newPass  = $_POST['new_password'] ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        $pCheck = $pdo->prepare("SELECT password FROM users WHERE id = :uid");
        $pCheck->execute([':uid' => $userId]);
        $currHash = $pCheck->fetchColumn();

        if (!password_verify($currPass, $currHash)) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPass !== $confPass) {
            $errors[] = 'New password confirmation does not match.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $pUp = $pdo->prepare("UPDATE users SET password = :hash WHERE id = :uid");
            $pUp->execute([':hash' => $newHash, ':uid' => $userId]);
            $success = 'Password changed successfully!';
        }
    }
}

$pageTitle = 'Student Profile - ProjectSphere';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../includes/student-navbar.php'; ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-person-gear me-2 text-primary"></i>My Student Profile</h2>
                        <p class="text-secondary small mb-0">Manage your enrollment information and credentials.</p>
                    </div>
                    <span class="badge bg-light text-dark border p-2">Roll No: <?= htmlspecialchars($student['roll_no']) ?></span>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Profile Update Form -->
                    <div class="col-lg-7">
                        <form method="POST" action="<?= base_url('student/profile.php') ?>">
                            <input type="hidden" name="action" value="update_profile">

                            <h5 class="fw-bold text-dark mb-3">Academic & Personal Details</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($student['full_name']) ?>" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Roll / Enrollment No</label>
                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($student['roll_no']) ?>" disabled>
                                    <div class="form-text small">Roll number is fixed by college admin.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($student['username']) ?>" disabled>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($student['email']) ?>" disabled>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Department / Branch</label>
                                    <select name="department" class="form-select">
                                        <option value="Computer Engineering" <?= ($student['department'] === 'Computer Engineering') ? 'selected' : '' ?>>Computer Engineering</option>
                                        <option value="Information Technology" <?= ($student['department'] === 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                                        <option value="Electronics & Telecommunication" <?= ($student['department'] === 'Electronics & Telecommunication') ? 'selected' : '' ?>>Electronics & Telecommunication</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Semester</label>
                                    <select name="semester" class="form-select">
                                        <option value="6th Semester" <?= ($student['semester'] === '6th Semester') ? 'selected' : '' ?>>6th Semester</option>
                                        <option value="5th Semester" <?= ($student['semester'] === '5th Semester') ? 'selected' : '' ?>>5th Semester</option>
                                        <option value="Graduated" <?= ($student['semester'] === 'Graduated') ? 'selected' : '' ?>>Graduated</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone / WhatsApp</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">About Me / Bio</label>
                                <textarea name="bio" class="form-control" rows="3" placeholder="Brief technical bio or interests..."><?= htmlspecialchars($student['bio'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Save Profile
                            </button>
                        </form>
                    </div>

                    <!-- Password Change Form -->
                    <div class="col-lg-5">
                        <div class="p-4 bg-light rounded-3 border">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock me-2 text-primary"></i>Change Password</h5>
                            <form method="POST" action="<?= base_url('student/profile.php') ?>">
                                <input type="hidden" name="action" value="change_password">

                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-outline-dark w-100">
                                    <i class="bi bi-key me-1"></i>Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
