<?php
/**
 * ProjectSphere - REST API Configuration & Common Helpers
 */

// Enable error reporting during development
error_reporting(E_ALL);
ini_set('display_errors', '0');

header("Content-Type: application/json; charset=UTF-8");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-cache, no-store, must-revalidate");

// Handle OPTIONS preflight request immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    session_start();
}

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/functions.php';

/**
 * Standard JSON Response Output
 */
function json_response($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Read Raw JSON Request Body
 */
function get_json_input(): array {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Fetch Current Authenticated User from the server-side session
 */
function get_current_user_data(PDO $pdo): ?array {
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.email, u.role, u.status,
               s.id AS student_id, s.roll_no, s.full_name AS student_name, s.department, s.semester, s.phone, s.bio, s.avatar,
               a.id AS admin_id, a.full_name AS admin_name, a.designation
        FROM users u
        LEFT JOIN students s ON u.id = s.user_id
        LEFT JOIN admins a ON u.id = a.user_id
        WHERE u.id = :id AND u.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return null;
    }

    return [
        'id'          => (int)$user['id'],
        'username'    => $user['username'],
        'email'       => $user['email'],
        'role'        => $user['role'],
        'name'        => ($user['role'] === 'admin') ? ($user['admin_name'] ?? 'Faculty') : ($user['student_name'] ?? 'Student'),
        'profile_id'  => ($user['role'] === 'admin') ? $user['admin_id'] : $user['student_id'],
        'roll_no'     => $user['roll_no'] ?? null,
        'department'  => $user['department'] ?? null,
        'semester'    => $user['semester'] ?? null,
        'phone'       => $user['phone'] ?? null,
        'bio'         => $user['bio'] ?? null,
        'avatar'      => $user['avatar'] ?? null,
        'designation' => $user['designation'] ?? null
    ];
}

/**
 * Require Student Authentication
 */
function require_student_api(PDO $pdo): array {
    $user = get_current_user_data($pdo);
    if (!$user || $user['role'] !== 'student') {
        json_response([
            'success' => false,
            'message' => 'Unauthorized. Please login with a student account.'
        ], 401);
    }
    return $user;
}

/**
 * Require Faculty/Admin Authentication
 */
function require_admin_api(PDO $pdo): array {
    $user = get_current_user_data($pdo);
    if (!$user || $user['role'] !== 'admin') {
        json_response([
            'success' => false,
            'message' => 'Unauthorized. Faculty admin privileges required.'
        ], 403);
    }
    return $user;
}
