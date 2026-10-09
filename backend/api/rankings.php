<?php
/**
 * ProjectSphere - Rankings REST API Endpoint
 */
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'recalculate') {
    require_admin_api($pdo);
    recalculate_rankings($pdo);
    json_response(['success' => true, 'message' => 'Rankings recalculated.']);
}

$categoryFilter = (int)($_GET['category'] ?? 0);

$sql = "
    SELECT r.*, p.title, p.thumbnail_image, p.technologies,
           c.name AS category_name,
           s.full_name AS student_name, s.roll_no AS student_roll,
           e.innovation_score, e.functionality_score, e.ui_design_score, 
           e.tech_usage_score, e.presentation_score, e.evaluated_at
    FROM rankings r
    INNER JOIN projects p ON r.project_id = p.id
    INNER JOIN project_categories c ON r.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    INNER JOIN evaluations e ON p.id = e.project_id
    WHERE p.status = 'approved'
";
$params = [];

if ($categoryFilter > 0) {
    $sql .= " AND r.category_id = :cat";
    $params[':cat'] = $categoryFilter;
    $sql .= " ORDER BY r.category_rank ASC, r.total_score DESC";
} else {
    $sql .= " ORDER BY r.overall_rank ASC, r.total_score DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
json_response(['success' => true, 'rankings' => $stmt->fetchAll()]);
