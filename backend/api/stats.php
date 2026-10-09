<?php
/**
 * ProjectSphere - Platform Statistics API Endpoint
 */
require_once __DIR__ . '/config.php';

$type = $_GET['type'] ?? 'public';
$user = get_current_user_data($pdo);

if ($type === 'student') {
    if (!$user || $user['role'] !== 'student') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    $sid = $user['profile_id'];
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total_projects,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count
        FROM projects
        WHERE student_id = :sid
    ");
    $stmt->execute([':sid' => $sid]);
    $counts = $stmt->fetch();

    json_response(['success' => true, 'stats' => $counts]);
}

if ($type === 'admin') {
    if (!$user || $user['role'] !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $stats = [
        'students'   => (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
        'projects'   => (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
        'pending'    => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'pending'")->fetchColumn(),
        'approved'   => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'approved'")->fetchColumn(),
        'rejected'   => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'rejected'")->fetchColumn(),
        'categories' => (int)$pdo->query("SELECT COUNT(*) FROM project_categories")->fetchColumn(),
    ];

    $pendingQueue = $pdo->query("
        SELECT p.id, p.title, p.submitted_at, c.name AS category_name, s.full_name AS student_name, s.roll_no AS student_roll
        FROM projects p
        INNER JOIN project_categories c ON p.category_id = c.id
        INNER JOIN students s ON p.student_id = s.id
        WHERE p.status = 'pending'
        ORDER BY p.submitted_at ASC
        LIMIT 5
    ")->fetchAll();

    $topRanked = $pdo->query("
        SELECT p.id, p.title, c.name AS category_name, s.full_name AS student_name,
               e.total_score, r.overall_rank
        FROM projects p
        INNER JOIN project_categories c ON p.category_id = c.id
        INNER JOIN students s ON p.student_id = s.id
        INNER JOIN evaluations e ON p.id = e.project_id
        INNER JOIN rankings r ON p.id = r.project_id
        WHERE p.status = 'approved'
        ORDER BY r.overall_rank ASC
        LIMIT 5
    ")->fetchAll();

    json_response([
        'success'       => true,
        'metrics'       => $stats,
        'pending_queue' => $pendingQueue,
        'top_ranked'    => $topRanked
    ]);
}

// Default: Public Homepage Stats
$publicStats = [
    'total_projects'    => (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'approved_projects' => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'approved'")->fetchColumn(),
    'students'          => (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'categories'        => (int)$pdo->query("SELECT COUNT(*) FROM project_categories")->fetchColumn(),
];

// Top 3 for hero
$topPodium = $pdo->query("
    SELECT p.id, p.title, p.short_description, p.technologies, p.thumbnail_image,
           c.name AS category_name, s.full_name AS student_name,
           e.total_score, r.overall_rank
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    INNER JOIN evaluations e ON p.id = e.project_id
    INNER JOIN rankings r ON p.id = r.project_id
    WHERE p.status = 'approved' AND e.is_published = 1
    ORDER BY r.overall_rank ASC
    LIMIT 3
")->fetchAll();

json_response([
    'success'    => true,
    'stats'      => $publicStats,
    'top_podium' => $topPodium
]);
