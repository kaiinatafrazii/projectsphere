<?php
/**
 * ProjectSphere - Students REST API Endpoint
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$user = get_current_user_data($pdo);

if ($method === 'GET') {
    require_admin_api($pdo);
    $search = trim($_GET['search'] ?? '');

    $sql = "
        SELECT s.*, u.email, u.username, u.status AS user_status,
               COUNT(p.id) AS total_submissions,
               SUM(CASE WHEN p.status = 'approved' THEN 1 ELSE 0 END) AS approved_count
        FROM students s
        INNER JOIN users u ON s.user_id = u.id
        LEFT JOIN projects p ON s.id = p.student_id
    ";
    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE (s.full_name LIKE :s1 OR s.roll_no LIKE :s2 OR u.email LIKE :s3)";
        $params[':s1'] = "%{$search}%";
        $params[':s2'] = "%{$search}%";
        $params[':s3'] = "%{$search}%";
    }
    $sql .= " GROUP BY s.id ORDER BY s.roll_no ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(['success' => true, 'students' => $stmt->fetchAll()]);
}

if ($method === 'PUT') {
    if (!$user) json_response(['success' => false, 'message' => 'Unauthorized'], 401);
    $input = get_json_input();
    $action = $input['action'] ?? 'profile';

    if ($action === 'profile' && $user['role'] === 'student') {
        $name  = trim($input['full_name'] ?? '');
        $dept  = trim($input['department'] ?? '');
        $sem   = trim($input['semester'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $bio   = trim($input['bio'] ?? '');

        if (empty($name)) json_response(['success' => false, 'message' => 'Name cannot be empty.'], 400);

        $up = $pdo->prepare("UPDATE students SET full_name = :n, department = :d, semester = :s, phone = :p, bio = :b WHERE id = :id");
        $up->execute([':n' => $name, ':d' => $dept, ':s' => $sem, ':p' => $phone, ':b' => $bio, ':id' => $user['profile_id']]);

        json_response(['success' => true, 'message' => 'Profile updated successfully.']);
    } elseif ($action === 'password') {
        $curr = $input['current_password'] ?? '';
        $new  = $input['new_password'] ?? '';

        $pStmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $pStmt->execute([':id' => $user['id']]);
        $hash = $pStmt->fetchColumn();

        if (!password_verify($curr, $hash)) {
            json_response(['success' => false, 'message' => 'Current password is incorrect.'], 400);
        }
        if (strlen($new) < 6) {
            json_response(['success' => false, 'message' => 'New password must be at least 6 characters.'], 400);
        }

        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = :h WHERE id = :id")->execute([':h' => $newHash, ':id' => $user['id']]);
        json_response(['success' => true, 'message' => 'Password updated successfully.']);
    } else {
        json_response(['success' => false, 'message' => 'Invalid action.'], 400);
    }
}
