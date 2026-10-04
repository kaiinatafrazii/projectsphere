<?php
/**
 * ProjectSphere - Feedback REST API Endpoint
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $sql = "
        SELECT f.*, p.title AS project_title, p.id AS proj_id,
               s.full_name AS student_name, s.roll_no AS student_roll,
               a.full_name AS faculty_name, a.designation AS faculty_designation
        FROM feedback f
        INNER JOIN projects p ON f.project_id = p.id
        INNER JOIN students s ON p.student_id = s.id
        INNER JOIN admins a ON f.admin_id = a.id
    ";
    $params = [];
    if (!empty($search)) {
        $sql .= " WHERE (p.title LIKE :s1 OR s.full_name LIKE :s2 OR f.comment LIKE :s3)";
        $params[':s1'] = "%{$search}%";
        $params[':s2'] = "%{$search}%";
        $params[':s3'] = "%{$search}%";
    }
    $sql .= " ORDER BY f.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(['success' => true, 'feedbacks' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $admin = require_admin_api($pdo);
    $input = get_json_input();
    $projId = (int)($input['project_id'] ?? $_POST['project_id'] ?? 0);
    $comment = trim($input['comment'] ?? $_POST['comment'] ?? '');

    if ($projId <= 0 || empty($comment)) {
        json_response(['success' => false, 'message' => 'Project and feedback comment required.'], 400);
    }

    $ins = $pdo->prepare("INSERT INTO feedback (project_id, admin_id, comment) VALUES (:pid, :aid, :comm)");
    $ins->execute([':pid' => $projId, ':aid' => $admin['profile_id'], ':comm' => $comment]);

    json_response(['success' => true, 'message' => 'Feedback posted successfully.']);
}
