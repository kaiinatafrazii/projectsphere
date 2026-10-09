<?php
/**
 * ProjectSphere - Evaluation REST API Endpoint
 * Handles 100-mark rubric evaluation and automatic ranking trigger
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $projectId = (int)($_GET['project_id'] ?? 0);
    if ($projectId <= 0) json_response(['success' => false, 'message' => 'Project ID required.'], 400);

    $stmt = $pdo->prepare("
        SELECT e.*, a.full_name AS evaluator_name, a.designation AS evaluator_designation
        FROM evaluations e
        INNER JOIN admins a ON e.admin_id = a.id
        WHERE e.project_id = :pid
        LIMIT 1
    ");
    $stmt->execute([':pid' => $projectId]);
    $eval = $stmt->fetch();

    json_response(['success' => true, 'evaluation' => $eval]);
}

if ($method === 'POST') {
    $admin = require_admin_api($pdo);
    $input = get_json_input();

    $projectId     = (int)($input['project_id'] ?? $_POST['project_id'] ?? 0);
    $innovation    = (float)($input['innovation_score'] ?? $_POST['innovation_score'] ?? 0);
    $functionality = (float)($input['functionality_score'] ?? $_POST['functionality_score'] ?? 0);
    $uiDesign      = (float)($input['ui_design_score'] ?? $input['ui_ux_score'] ?? $_POST['ui_design_score'] ?? $_POST['ui_ux_score'] ?? 0);
    $techUsage     = (float)($input['tech_usage_score'] ?? $input['tech_stack_score'] ?? $_POST['tech_usage_score'] ?? $_POST['tech_stack_score'] ?? 0);
    $presentation  = (float)($input['presentation_score'] ?? $_POST['presentation_score'] ?? 0);
    $feedback      = trim($input['feedback_text'] ?? $input['feedback'] ?? $_POST['feedback_text'] ?? $_POST['feedback'] ?? '');

    if ($projectId <= 0) json_response(['success' => false, 'message' => 'Invalid project.'], 400);
    if ($innovation < 0 || $innovation > 20) json_response(['success' => false, 'message' => 'Innovation marks must be between 0 and 20.'], 400);
    if ($functionality < 0 || $functionality > 30) json_response(['success' => false, 'message' => 'Functionality marks must be between 0 and 30.'], 400);
    if ($uiDesign < 0 || $uiDesign > 20) json_response(['success' => false, 'message' => 'UI / Design marks must be between 0 and 20.'], 400);
    if ($techUsage < 0 || $techUsage > 15) json_response(['success' => false, 'message' => 'Technology Usage marks must be between 0 and 15.'], 400);
    if ($presentation < 0 || $presentation > 15) json_response(['success' => false, 'message' => 'Presentation marks must be between 0 and 15.'], 400);
    if (empty($feedback)) json_response(['success' => false, 'message' => 'Feedback remarks are required.'], 400);

    $totalScore = round($innovation + $functionality + $uiDesign + $techUsage + $presentation, 1);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO evaluations (
                project_id, admin_id, innovation_score, functionality_score, 
                ui_design_score, tech_usage_score, presentation_score, 
                total_score, feedback_text, is_published
            ) VALUES (
                :pid, :aid, :inno, :func, :ui, :tech, :pres, :tot, :feedback, 1
            )
            ON DUPLICATE KEY UPDATE
                admin_id = VALUES(admin_id),
                innovation_score = VALUES(innovation_score),
                functionality_score = VALUES(functionality_score),
                ui_design_score = VALUES(ui_design_score),
                tech_usage_score = VALUES(tech_usage_score),
                presentation_score = VALUES(presentation_score),
                total_score = VALUES(total_score),
                feedback_text = VALUES(feedback_text),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([
            ':pid'      => $projectId,
            ':aid'      => $admin['profile_id'],
            ':inno'     => $innovation,
            ':func'     => $functionality,
            ':ui'       => $uiDesign,
            ':tech'     => $techUsage,
            ':pres'     => $presentation,
            ':tot'      => $totalScore,
            ':feedback' => $feedback
        ]);

        // Auto approve project
        $pdo->prepare("UPDATE projects SET status = 'approved', rejection_reason = NULL WHERE id = :id")->execute([':id' => $projectId]);

        // Log in feedback table
        $fbLog = $pdo->prepare("INSERT INTO feedback (project_id, admin_id, comment) VALUES (:pid, :aid, :comm)");
        $fbLog->execute([':pid' => $projectId, ':aid' => $admin['profile_id'], ':comm' => "Evaluation marks awarded: {$totalScore}/100. " . $feedback]);

        $pdo->commit();

        // Recalculate platform rankings
        recalculate_rankings($pdo);

        // Fetch new rank
        $rStmt = $pdo->prepare("SELECT overall_rank, category_rank FROM rankings WHERE project_id = :pid");
        $rStmt->execute([':pid' => $projectId]);
        $rankData = $rStmt->fetch();

        json_response([
            'success'       => true,
            'message'       => 'Evaluation saved successfully!',
            'total_score'   => $totalScore,
            'overall_rank'  => $rankData['overall_rank'] ?? null,
            'category_rank' => $rankData['category_rank'] ?? null
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['success' => false, 'message' => 'Evaluation failed: ' . $e->getMessage()], 500);
    }
}
