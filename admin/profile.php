<?php
/**
 * ProjectSphere - Faculty / Admin Profile
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$adminId = $_SESSION['profile_id'];
$userId  = $_SESSION['user_id'];

$errors = [];
$success = '';

$stmt = $pdo->prepare("
    SELECT a.*, u.username, u.email
    FROM admins a
    INNER JOIN users u ON a.user_id = u.id
    WHERE a.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $adminId]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName    = trim($_POST['full_name'] ?? '');
        $department  = trim($_POST['department'] ?? '');
        $designation = trim($_POST['designation'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');

        if (empty($fullName)) {
            $errors[] = 'Full name cannot be blank.';
        }

        if (empty($errors)) {
            $up = $pdo->prepare("UPDATE admins SET full_name = :name, department = :dept, designation = :desig, phone = :phone WHERE id = :id");
            $up->execute([':name' => $fullName, ':dept' => $department, ':desig' => $designation, ':phone' => $phone, ':id' => $adminId]);
            $_SESSION['full_name'] = $fullName;
            $success = 'Faculty profile updated successfully!';
            
            $stmt->execute([':id' => $adminId]);
            $admin = $stmt->fetch();
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
            $success = 'Password updated successfully!';
        }
    }
}

$pageTitle = 'Faculty Profile - ProjectSphere';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>Faculty Evaluator Profile</h2>
                        <p class="text-secondary small mb-0">Manage evaluator credentials and faculty contact information.</p>
                    </div>
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
                    <div class="col-lg-7">
                        <form method="POST" action="<?= base_url('admin/profile.php') ?>">
                            <input type="hidden" name="action" value="update_profile">

                            <h5 class="fw-bold text-dark mb-3">Faculty Credentials</h5>

                            <div class="mb-3">
                                <label class="form-label">Full Name & Title</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($admin['department']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Academic Designation</label>
                                    <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($admin['designation']) ?>" required>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Faculty Email</label>
                                    <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($admin['email']) ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Contact</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($admin['phone']) ?>">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Save Faculty Profile
                            </button>
                        </form>
                    </div>

                    <div class="col-lg-5">
                        <div class="p-4 bg-light rounded-3 border">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock me-2 text-danger"></i>Change Admin Password</h5>
                            <form method="POST" action="<?= base_url('admin/profile.php') ?>">
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

                                <button type="submit" class="btn btn-dark w-100">
                                    <i class="bi bi-key me-1"></i>Update Admin Password
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
