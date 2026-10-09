<?php
/**
 * ProjectSphere - Student Registration
 */
require_once __DIR__ . '/../backend/core/db.php';
require_once __DIR__ . '/../backend/core/auth.php';
require_once __DIR__ . '/../backend/core/functions.php';

// Redirect if already logged in
if (is_student()) {
    header('Location: ' . base_url('frontend/student/dashboard.php'));
    exit;
} elseif (is_admin()) {
    header('Location: ' . base_url('frontend/admin/dashboard.php'));
    exit;
}

$errors = [];
$formData = [
    'full_name'  => '',
    'roll_no'    => '',
    'email'      => '',
    'username'   => '',
    'department' => '',
    'semester'   => '',
    'phone'      => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security check failed (CSRF token mismatch). Please refresh and try again.';
    }
    $formData['full_name']  = trim($_POST['full_name'] ?? '');
    $formData['roll_no']    = strtoupper(trim($_POST['roll_no'] ?? ''));
    $formData['email']      = strtolower(trim($_POST['email'] ?? ''));
    $formData['username']   = strtolower(trim($_POST['username'] ?? ''));
    $formData['department'] = trim($_POST['department'] ?? '');
    $formData['semester']   = trim($_POST['semester'] ?? '');
    $formData['phone']      = trim($_POST['phone'] ?? '');
    $password               = $_POST['password'] ?? '';
    $confirmPassword        = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($formData['full_name'])) {
        $errors[] = 'Full name is required.';
    }
    if (empty($formData['roll_no'])) {
        $errors[] = 'College Roll Number / Enrollment Number is required.';
    }
    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid college or personal email address is required.';
    }
    if (empty($formData['username']) || !preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $formData['username'])) {
        $errors[] = 'Username must be 3-30 characters (letters, numbers, underscore, dot, or hyphen).';
    }
    if (empty($formData['department'])) {
        $errors[] = 'Please select a department or branch.';
    }
    if (empty($formData['semester'])) {
        $errors[] = 'Please select a semester or year.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    // Check if email, username, or roll_no already exists
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT email, username FROM users WHERE email = :email OR username = :uname");
        $checkStmt->execute([':email' => $formData['email'], ':uname' => $formData['username']]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            if ($existing['email'] === $formData['email']) {
                $errors[] = 'An account with this email address already exists.';
            }
            if ($existing['username'] === $formData['username']) {
                $errors[] = 'This username is already taken. Please choose another.';
            }
        }

        $checkRoll = $pdo->prepare("SELECT id FROM students WHERE roll_no = :roll_no");
        $checkRoll->execute([':roll_no' => $formData['roll_no']]);
        if ($checkRoll->fetch()) {
            $errors[] = 'A student with this Roll Number is already registered.';
        }
    }

    // Insert user and student profile in transaction
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // 1. Insert into users
            $userInsert = $pdo->prepare("
                INSERT INTO users (username, email, password, role, status)
                VALUES (:uname, :email, :pass, 'student', 'active')
            ");
            $userInsert->execute([
                ':uname' => $formData['username'],
                ':email' => $formData['email'],
                ':pass'  => $hashedPassword
            ]);
            $newUserId = $pdo->lastInsertId();

            // 2. Insert into students
            $studentInsert = $pdo->prepare("
                INSERT INTO students (user_id, roll_no, full_name, department, semester, phone)
                VALUES (:uid, :roll, :name, :dept, :sem, :phone)
            ");
            $studentInsert->execute([
                ':uid'   => $newUserId,
                ':roll'  => $formData['roll_no'],
                ':name'  => $formData['full_name'],
                ':dept'  => $formData['department'],
                ':sem'   => $formData['semester'],
                ':phone' => $formData['phone']
            ]);
            $newStudentId = $pdo->lastInsertId();

            $pdo->commit();

            // Auto-login registered student
            session_regenerate_id(true);
            $_SESSION['user_id']    = $newUserId;
            $_SESSION['username']   = $formData['username'];
            $_SESSION['email']      = $formData['email'];
            $_SESSION['role']       = 'student';
            $_SESSION['profile_id'] = $newStudentId;
            $_SESSION['full_name']  = $formData['full_name'];
            $_SESSION['roll_no']    = $formData['roll_no'];
            $_SESSION['flash_success'] = 'Account registered successfully! Welcome to ProjectSphere.';

            header('Location: ' . base_url('frontend/student/dashboard.php'));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Student Registration - ProjectSphere Academic Portal';
$pageDescription = 'Create your student account to submit capstone projects and receive official faculty evaluations.';
$hideMainNavigation = true;
require_once __DIR__ . '/../backend/core/header.php';
?>

<div class="auth-page-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <a class="auth-home-link" href="<?= base_url() ?>">
                    <i class="bi bi-arrow-left"></i> Back to Catalog
                </a>

                <div class="card auth-card p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <a href="<?= base_url() ?>" class="d-inline-block mb-3">
                            <img src="<?= base_url('frontend/assets/images/logo.svg') ?>" alt="ProjectSphere Logo" style="height: 40px;">
                        </a>
                        <span class="auth-header-badge bg-primary-subtle text-primary">
                            <i class="bi bi-mortarboard-fill"></i> New Student Account
                        </span>
                        <h2 class="h4 fw-bold text-dark mb-1">Create Student Profile</h2>
                        <p class="text-secondary small mb-0">Register your enrollment details to submit diploma projects and track evaluations</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                                <strong>Please fix the following:</strong>
                            </div>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="registerForm" novalidate>
                        <?= csrf_field() ?>
                        
                        <!-- 1. Academic Information -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3 border-bottom pb-2">
                            <i class="bi bi-award-fill text-primary me-1"></i> 1. Academic Credentials
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="full_name">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="full_name" id="full_name" class="form-control" placeholder="e.g. Rahul Sharma" value="<?= htmlspecialchars($formData['full_name']) ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="roll_no">College Roll / Enrollment No <span class="text-danger">*</span></label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <input type="text" name="roll_no" id="roll_no" class="form-control" placeholder="e.g. DCS-2023-45" value="<?= htmlspecialchars($formData['roll_no']) ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="department">Department / Branch <span class="text-danger">*</span></label>
                                <select name="department" id="department" class="form-select" required>
                                    <option value="" disabled <?= ($formData['department'] === '') ? 'selected' : '' ?>>-- Choose Department --</option>
                                    <option value="Computer Engineering" <?= ($formData['department'] === 'Computer Engineering') ? 'selected' : '' ?>>Computer Engineering</option>
                                    <option value="Information Technology" <?= ($formData['department'] === 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                                    <option value="Electronics & Telecommunication" <?= ($formData['department'] === 'Electronics & Telecommunication') ? 'selected' : '' ?>>Electronics & Telecommunication</option>
                                    <option value="Electrical Engineering" <?= ($formData['department'] === 'Electrical Engineering') ? 'selected' : '' ?>>Electrical Engineering</option>
                                    <option value="Mechanical Engineering" <?= ($formData['department'] === 'Mechanical Engineering') ? 'selected' : '' ?>>Mechanical Engineering</option>
                                    <option value="Civil Engineering" <?= ($formData['department'] === 'Civil Engineering') ? 'selected' : '' ?>>Civil Engineering</option>
                                    <option value="Artificial Intelligence & Data Science" <?= ($formData['department'] === 'Artificial Intelligence & Data Science') ? 'selected' : '' ?>>Artificial Intelligence & Data Science</option>
                                    <option value="Computer Technology" <?= ($formData['department'] === 'Computer Technology') ? 'selected' : '' ?>>Computer Technology</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="semester">Semester / Year <span class="text-danger">*</span></label>
                                <select name="semester" id="semester" class="form-select" required>
                                    <option value="" disabled <?= ($formData['semester'] === '') ? 'selected' : '' ?>>-- Choose Semester --</option>
                                    <option value="1st Semester" <?= ($formData['semester'] === '1st Semester') ? 'selected' : '' ?>>1st Semester</option>
                                    <option value="2nd Semester" <?= ($formData['semester'] === '2nd Semester') ? 'selected' : '' ?>>2nd Semester</option>
                                    <option value="3rd Semester" <?= ($formData['semester'] === '3rd Semester') ? 'selected' : '' ?>>3rd Semester</option>
                                    <option value="4th Semester" <?= ($formData['semester'] === '4th Semester') ? 'selected' : '' ?>>4th Semester</option>
                                    <option value="5th Semester" <?= ($formData['semester'] === '5th Semester') ? 'selected' : '' ?>>5th Semester</option>
                                    <option value="6th Semester (Final Year)" <?= ($formData['semester'] === '6th Semester (Final Year)') ? 'selected' : '' ?>>6th Semester (Final Year)</option>
                                    <option value="7th Semester" <?= ($formData['semester'] === '7th Semester') ? 'selected' : '' ?>>7th Semester</option>
                                    <option value="8th Semester (Final Year)" <?= ($formData['semester'] === '8th Semester (Final Year)') ? 'selected' : '' ?>>8th Semester (Final Year)</option>
                                    <option value="Graduated" <?= ($formData['semester'] === 'Graduated') ? 'selected' : '' ?>>Graduated / Alumni</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2. Account Security -->
                        <h6 class="text-uppercase text-muted fw-bold small mb-3 border-bottom pb-2">
                            <i class="bi bi-shield-lock-fill text-primary me-1"></i> 2. Portal Access Credentials
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="email">College or Personal Email <span class="text-danger">*</span></label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="student@college.edu" value="<?= htmlspecialchars($formData['email']) ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="username">Portal Username <span class="text-danger">*</span></label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-at"></i></span>
                                    <input type="text" name="username" id="username" class="form-control" placeholder="e.g. rahul_sharma" value="<?= htmlspecialchars($formData['username']) ?>" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="phone">Contact Phone / WhatsApp <span class="text-muted small">(Optional)</span></label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="phone" id="phone" class="form-control" placeholder="+91 98765 43210" value="<?= htmlspecialchars($formData['phone']) ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="regPassword">Create Password <span class="text-danger">*</span></label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="regPassword" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                                    <button class="btn btn-outline-secondary password-toggle-btn" type="button" data-target="regPassword" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="regConfirmPassword">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control" placeholder="Re-enter password" required minlength="6">
                                    <button class="btn btn-outline-secondary password-toggle-btn" type="button" data-target="regConfirmPassword" aria-label="Toggle confirm password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordMatchHint" class="password-match-badge text-muted"></div>
                            </div>
                        </div>

                        <div class="form-check mt-3 mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required checked>
                            <label class="form-check-label small text-secondary" for="terms">
                                I confirm all submitted capstone projects represent original academic work and agree to abide by the institution's submission code.
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg auth-submit-btn">
                                <i class="bi bi-person-check-fill me-1"></i> Register Student Account
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 border-top text-center small text-secondary">
                        Already have an account? <a href="<?= base_url('frontend/login.php?role=student') ?>" class="text-primary fw-semibold ms-1">Sign in here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pwd = document.getElementById('regPassword');
    const cpwd = document.getElementById('regConfirmPassword');
    const hint = document.getElementById('passwordMatchHint');

    function checkMatch() {
        if (!pwd.value || !cpwd.value) {
            hint.innerHTML = '';
            return;
        }
        if (pwd.value === cpwd.value) {
            hint.className = 'password-match-badge text-success';
            hint.innerHTML = '<i class="bi bi-check-circle-fill"></i> Passwords match';
        } else {
            hint.className = 'password-match-badge text-danger';
            hint.innerHTML = '<i class="bi bi-x-circle-fill"></i> Passwords do not match';
        }
    }

    if (pwd && cpwd && hint) {
        pwd.addEventListener('input', checkMatch);
        cpwd.addEventListener('input', checkMatch);
    }
});
</script>

<?php require_once __DIR__ . '/../backend/core/footer.php'; ?>
