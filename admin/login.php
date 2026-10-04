<?php
/**
 * ProjectSphere - Dedicated Faculty & Admin Login Portal
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in as admin
if (is_admin()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUsername = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($emailOrUsername) || empty($password)) {
        $error = 'Please enter faculty credentials.';
    } else {
        $stmt = $pdo->prepare("
            SELECT u.*, a.id AS admin_id, a.full_name AS admin_name, a.designation
            FROM users u
            INNER JOIN admins a ON u.id = a.user_id
            WHERE (u.email = :q1 OR u.username = :q2) AND u.role = 'admin' AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([':q1' => $emailOrUsername, ':q2' => $emailOrUsername]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['role']       = 'admin';
            $_SESSION['profile_id'] = $user['admin_id'];
            $_SESSION['full_name']  = $user['admin_name'] ?? 'Faculty Evaluator';
            $_SESSION['flash_success'] = 'Welcome to Faculty Evaluation Portal, ' . $_SESSION['full_name'];
            header('Location: ' . base_url('admin/dashboard.php'));
            exit;
        } else {
            $error = 'Invalid faculty credentials. Access restricted to authorized department evaluators.';
        }
    }
}

$pageTitle = 'Faculty & Evaluator Portal - ProjectSphere';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card border shadow-sm p-4" style="border-radius: 16px;">
                <div class="text-center mb-4">
                    <a href="<?= base_url() ?>">
                        <img src="<?= base_url('assets/images/logo.svg') ?>" alt="ProjectSphere Logo" style="height: 38px;" class="mb-3">
                    </a>
                    <span class="badge bg-danger-subtle text-danger px-2.5 py-1 rounded-pill small fw-bold text-uppercase d-inline-block mb-2">
                        <i class="bi bi-shield-lock-fill me-1"></i> Faculty & Staff Only
                    </span>
                    <h4 class="fw-bold mb-1">Evaluator Portal</h4>
                    <p class="text-secondary small">Review capstone projects & award academic marks</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('admin/login.php') ?>">
                    <div class="mb-3">
                        <label class="form-label">Faculty Username / Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="username_or_email" class="form-control" placeholder="admin@projectsphere.edu" value="admin@projectsphere.edu" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" value="admin123" required>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-dark btn-lg shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Access Admin Panel
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-3 border-top text-center small text-secondary">
                    <a href="<?= base_url('login.php?role=student') ?>" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Switch to Student Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
