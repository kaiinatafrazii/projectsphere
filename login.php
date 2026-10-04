<?php
/**
 * ProjectSphere - User Login (Student & Faculty)
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (is_admin()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
} elseif (is_student()) {
    header('Location: ' . base_url('student/dashboard.php'));
    exit;
}

$error = '';
$selectedRole = $_GET['role'] ?? 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUsername = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if (empty($emailOrUsername) || empty($password)) {
        $error = 'Please enter both your username/email and password.';
    } else {
        // Query user
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   s.id AS student_id, s.full_name AS student_name, s.roll_no,
                   a.id AS admin_id, a.full_name AS admin_name
            FROM users u
            LEFT JOIN students s ON u.id = s.user_id
            LEFT JOIN admins a ON u.id = a.user_id
            WHERE (u.email = :query1 OR u.username = :query2) AND u.role = :role AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([
            ':query1' => $emailOrUsername,
            ':query2' => $emailOrUsername,
            ':role'   => $role
        ]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session for security
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                $_SESSION['profile_id'] = $user['admin_id'];
                $_SESSION['full_name']  = $user['admin_name'] ?? 'Faculty Evaluator';
                $_SESSION['flash_success'] = 'Welcome back, ' . $_SESSION['full_name'] . '!';
                header('Location: ' . base_url('admin/dashboard.php'));
                exit;
            } else {
                $_SESSION['profile_id'] = $user['student_id'];
                $_SESSION['full_name']  = $user['student_name'] ?? 'Student';
                $_SESSION['roll_no']    = $user['roll_no'] ?? '';
                $_SESSION['flash_success'] = 'Welcome back, ' . $_SESSION['full_name'] . '!';
                header('Location: ' . base_url('student/dashboard.php'));
                exit;
            }
        } else {
            $error = 'Invalid credentials or incorrect role selection. Please check and try again.';
        }
    }
}

$pageTitle = 'Login - ProjectSphere Portal';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border shadow-sm p-4 p-md-5" style="border-radius: 16px;">
                <div class="text-center mb-4">
                    <img src="<?= base_url('assets/images/logo.svg') ?>" alt="ProjectSphere Logo" style="height: 42px;" class="mb-3">
                    <h3 class="h4 fw-bold">Welcome Back</h3>
                    <p class="text-secondary small">Access the Project Showcase & Evaluation System</p>
                </div>

                <!-- Role Selection Tabs -->
                <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3" id="loginRoleTabs">
                    <li class="nav-item">
                        <button class="nav-link fw-semibold <?= ($selectedRole === 'student') ? 'active' : '' ?>" id="studentTabBtn" type="button" onclick="setRole('student')">
                            <i class="bi bi-person me-1"></i>Student
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold <?= ($selectedRole === 'admin') ? 'active' : '' ?>" id="adminTabBtn" type="button" onclick="setRole('admin')">
                            <i class="bi bi-shield-check me-1"></i>Teacher / Admin
                        </button>
                    </li>
                </ul>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('login.php') ?>">
                    <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($selectedRole) ?>">

                    <div class="mb-3">
                        <label for="username_or_email" class="form-label" id="usernameLabel">
                            <?= ($selectedRole === 'admin') ? 'Faculty Email or Username' : 'Student Email or Username' ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username_or_email" name="username_or_email" 
                                   placeholder="<?= ($selectedRole === 'admin') ? 'e.g. admin@projectsphere.edu' : 'e.g. rahul@college.edu or rahul_sharma' ?>" 
                                   value="<?= htmlspecialchars($_POST['username_or_email'] ?? '') ?>" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label">Password</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-3 border-top text-center small text-secondary">
                    <span id="registerPrompt">Don't have a student account?</span>
                    <a href="<?= base_url('register.php') ?>" class="text-primary fw-semibold ms-1" id="registerLink">Register here</a>
                </div>

                <!-- Demo Credentials Helper Callout -->
                <div class="mt-4 p-3 bg-light rounded-3 small border">
                    <div class="fw-bold mb-1 text-dark"><i class="bi bi-info-circle me-1 text-primary"></i>Demo Credentials:</div>
                    <div class="text-muted">
                        <strong>Student:</strong> <code>rahul@college.edu</code> | Pass: <code>student123</code><br>
                        <strong>Teacher/Admin:</strong> <code>admin@projectsphere.edu</code> | Pass: <code>admin123</code>
                    </div>
                    <div class="mt-2 pt-2 border-top text-center">
                        <a href="<?= base_url('install.php') ?>" class="text-secondary text-decoration-none">
                            <i class="bi bi-gear-fill me-1 text-primary"></i>Database Setup & Installer (install.php) &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setRole(role) {
    document.getElementById('roleInput').value = role;
    const sBtn = document.getElementById('studentTabBtn');
    const aBtn = document.getElementById('adminTabBtn');
    const uLabel = document.getElementById('usernameLabel');
    const uInput = document.getElementById('username_or_email');
    const rPrompt = document.getElementById('registerPrompt');
    const rLink = document.getElementById('registerLink');

    if (role === 'admin') {
        sBtn.classList.remove('active');
        aBtn.classList.add('active');
        uLabel.textContent = 'Faculty Email or Username';
        uInput.placeholder = 'e.g. admin@projectsphere.edu';
        rPrompt.textContent = 'Need faculty access?';
        rLink.textContent = 'Contact Department HOD';
        rLink.href = 'mailto:hod.cs@college.edu';
    } else {
        aBtn.classList.remove('active');
        sBtn.classList.add('active');
        uLabel.textContent = 'Student Email or Username';
        uInput.placeholder = 'e.g. rahul@college.edu or rahul_sharma';
        rPrompt.textContent = "Don't have a student account?";
        rLink.textContent = 'Register here';
        rLink.href = '<?= base_url("register.php") ?>';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
