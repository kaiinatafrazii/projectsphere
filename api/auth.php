<?php
/**
 * ProjectSphere - Authentication API Endpoint
 * Handles Login, Registration, Session verification, and Logout
 */
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';
$input  = get_json_input();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $emailOrUsername = trim($input['email'] ?? $input['username_or_email'] ?? $input['username'] ?? $_POST['email'] ?? $_POST['username_or_email'] ?? $_POST['username'] ?? '');
    $password = $input['password'] ?? $_POST['password'] ?? '';
    $role = $input['role'] ?? $_POST['role'] ?? 'student';

    if (empty($emailOrUsername) || empty($password)) {
        json_response(['success' => false, 'message' => 'Please provide email/username and password.'], 400);
    }

    $stmt = $pdo->prepare("
        SELECT u.*, 
               s.id AS student_id, s.full_name AS student_name, s.roll_no,
               a.id AS admin_id, a.full_name AS admin_name
        FROM users u
        LEFT JOIN students s ON u.id = s.user_id
        LEFT JOIN admins a ON u.id = a.user_id
        WHERE (u.email = :q1 OR u.username = :q2) AND u.role = :role AND u.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([':q1' => $emailOrUsername, ':q2' => $emailOrUsername, ':role' => $role]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] === 'admin') {
            $_SESSION['profile_id'] = $user['admin_id'];
            $_SESSION['full_name']  = $user['admin_name'] ?? 'Faculty Admin';
        } else {
            $_SESSION['profile_id'] = $user['student_id'];
            $_SESSION['full_name']  = $user['student_name'] ?? 'Student';
            $_SESSION['roll_no']    = $user['roll_no'] ?? '';
        }

        $userData = get_current_user_data($pdo);
        // Generate client token for convenience
        $token = base64_encode($user['id'] . ':' . sha1($user['password']));

        json_response([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $userData
        ]);
    } else {
        json_response(['success' => false, 'message' => 'Invalid credentials or incorrect role selection.'], 401);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    $fullName   = trim($input['name'] ?? $input['full_name'] ?? $_POST['name'] ?? $_POST['full_name'] ?? '');
    $rollNo     = strtoupper(trim($input['roll_number'] ?? $input['roll_no'] ?? $_POST['roll_number'] ?? $_POST['roll_no'] ?? ''));
    $email      = strtolower(trim($input['email'] ?? $_POST['email'] ?? ''));
    $username   = strtolower(trim($input['username'] ?? $_POST['username'] ?? ($email ? explode('@', $email)[0] : '')));
    $department = trim($input['department'] ?? $_POST['department'] ?? 'Computer Engineering');
    $semester   = trim($input['semester'] ?? $_POST['semester'] ?? '6th Semester');
    $phone      = trim($input['phone'] ?? $_POST['phone'] ?? '');
    $password   = $input['password'] ?? $_POST['password'] ?? '';

    // Validations
    if (empty($fullName)) json_response(['success' => false, 'message' => 'Full name is required.'], 400);
    if (empty($rollNo)) json_response(['success' => false, 'message' => 'Roll number is required.'], 400);
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['success' => false, 'message' => 'Valid email is required.'], 400);
    if (empty($username) || strlen($username) < 3) json_response(['success' => false, 'message' => 'Username must be at least 3 characters.'], 400);
    if (strlen($password) < 6) json_response(['success' => false, 'message' => 'Password must be at least 6 characters.'], 400);

    // Uniqueness checks
    $chk = $pdo->prepare("SELECT email, username FROM users WHERE email = :email OR username = :uname");
    $chk->execute([':email' => $email, ':uname' => $username]);
    if ($ex = $chk->fetch()) {
        if ($ex['email'] === $email) json_response(['success' => false, 'message' => 'Email already registered.'], 409);
        if ($ex['username'] === $username) json_response(['success' => false, 'message' => 'Username already taken.'], 409);
    }

    $chkRoll = $pdo->prepare("SELECT id FROM students WHERE roll_no = :roll");
    $chkRoll->execute([':roll' => $rollNo]);
    if ($chkRoll->fetch()) {
        json_response(['success' => false, 'message' => 'Roll number already registered.'], 409);
    }

    try {
        $pdo->beginTransaction();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $uIns = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (:uname, :email, :pass, 'student', 'active')");
        $uIns->execute([':uname' => $username, ':email' => $email, ':pass' => $hash]);
        $newUid = $pdo->lastInsertId();

        $sIns = $pdo->prepare("INSERT INTO students (user_id, roll_no, full_name, department, semester, phone) VALUES (:uid, :roll, :name, :dept, :sem, :phone)");
        $sIns->execute([':uid' => $newUid, ':roll' => $rollNo, ':name' => $fullName, ':dept' => $department, ':sem' => $semester, ':phone' => $phone]);
        $newSid = $pdo->lastInsertId();

        $pdo->commit();

        session_regenerate_id(true);
        $_SESSION['user_id']    = $newUid;
        $_SESSION['username']   = $username;
        $_SESSION['email']      = $email;
        $_SESSION['role']       = 'student';
        $_SESSION['profile_id'] = $newSid;
        $_SESSION['full_name']  = $fullName;
        $_SESSION['roll_no']    = $rollNo;

        $userData = get_current_user_data($pdo);
        $token = base64_encode($newUid . ':' . sha1($hash));

        json_response([
            'success' => true,
            'message' => 'Student registration successful!',
            'token'   => $token,
            'user'    => $userData
        ], 201);

    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Registration error: ' . $e->getMessage()], 500);
    }

} elseif ($action === 'me') {
    $user = get_current_user_data($pdo);
    json_response([
        'authenticated' => ($user !== null),
        'user'          => $user
    ]);

} elseif ($action === 'logout') {
    $_SESSION = [];
    if (session_id()) session_destroy();
    json_response(['success' => true, 'message' => 'Logged out successfully.']);

} else {
    json_response(['success' => false, 'message' => 'Unknown authentication action.'], 400);
}
