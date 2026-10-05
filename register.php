<?php
/**
 * ProjectSphere - Student Registration
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (is_student()) {
    header('Location: ' . base_url('student/dashboard.php'));
    exit;
} elseif (is_admin()) {
    header('Location: ' . base_url('admin/dashboard.php'));
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
    if (empty($formData['username']) || !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $formData['username'])) {
        $errors[] = 'Username must be 3-30 characters (letters, numbers, underscores only).';
    }
    if (!in_array($formData['department'], ['Computer Engineering', 'Information Technology', 'Electronics & Telecommunication', 'Electrical Engineering'], true)) {
        $errors[] = 'Please select a department or branch.';
    }
    if (!in_array($formData['semester'], ['6th Semester (Final Year)', '5th Semester', '4th Semester', 'Graduated'], true)) {
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

            header('Location: ' . base_url('student/dashboard.php'));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Student Registration - ProjectSphere';
$hideMainNavigation = true;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <a class="auth-home-link" href="<?= base_url() ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Home</a>
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border shadow-sm p-4 p-md-5" style="border-radius: 16px;">
                <div class="text-center mb-4">
                    <img src="<?= base_url('assets/images/logo.svg') ?>" alt="ProjectSphere Logo" style="height: 42px;" class="mb-3">
                    <h3 class="h4 fw-bold">Student Registration</h3>
                    <p class="text-secondary small">Create your student account to submit and track your diploma project</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following:</h6>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('register.php') ?>" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="e.g. Rahul Sharma" value="<?= htmlspecialchars($formData['full_name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">College Roll / Enrollment No <span class="text-danger">*</span></label>
                            <input type="text" name="roll_no" class="form-control" placeholder="e.g. DCS-2023-45" value="<?= htmlspecialchars($formData['roll_no']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="student@college.edu" value="<?= htmlspecialchars($formData['email']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Choose Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="e.g. rahul_sharma" value="<?= htmlspecialchars($formData['username']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Department / Branch <span class="text-danger">*</span></label>
                            <select name="department" class="form-select" required>
                                <option value="" disabled <?= ($formData['department'] === '') ? 'selected' : '' ?>>Select department / branch</option>
                                <option value="Computer Engineering" <?= ($formData['department'] === 'Computer Engineering') ? 'selected' : '' ?>>Computer Engineering</option>
                                <option value="Information Technology" <?= ($formData['department'] === 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                                <option value="Electronics & Telecommunication" <?= ($formData['department'] === 'Electronics & Telecommunication') ? 'selected' : '' ?>>Electronics & Telecommunication</option>
                                <option value="Electrical Engineering" <?= ($formData['department'] === 'Electrical Engineering') ? 'selected' : '' ?>>Electrical Engineering</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Semester / Year <span class="text-danger">*</span></label>
                            <select name="semester" class="form-select" required>
                                <option value="" disabled <?= ($formData['semester'] === '') ? 'selected' : '' ?>>Select semester / year</option>
                                <option value="6th Semester (Final Year)" <?= ($formData['semester'] === '6th Semester (Final Year)') ? 'selected' : '' ?>>6th Semester (Final Year)</option>
                                <option value="5th Semester" <?= ($formData['semester'] === '5th Semester') ? 'selected' : '' ?>>5th Semester</option>
                                <option value="4th Semester" <?= ($formData['semester'] === '4th Semester') ? 'selected' : '' ?>>4th Semester</option>
                                <option value="Graduated" <?= ($formData['semester'] === 'Graduated') ? 'selected' : '' ?>>Graduated / Alumni</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Contact Phone / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?= htmlspecialchars($formData['phone']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="terms" required checked>
                        <label class="form-check-label small text-secondary" for="terms">
                            I agree to abide by the academic code of conduct and confirm all submitted projects represent genuine student work.
                        </label>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-person-check-fill me-2"></i>Complete Student Registration
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-3 border-top text-center small text-secondary">
                    Already registered? <a href="<?= base_url('login.php?role=student') ?>" class="text-primary fw-semibold">Sign in here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
