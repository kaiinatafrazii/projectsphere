<?php
/**
 * ProjectSphere - Project Evaluation Engine (100-Mark Rubric)
 * Rubric Breakdown:
 *  - Innovation: 20
 *  - Functionality: 30
 *  - UI/Design: 20
 *  - Technology Usage: 15
 *  - Presentation/Documentation: 15
 *  Total = 100 Marks
 */
require_once __DIR__ . '/../../backend/core/db.php';
require_once __DIR__ . '/../../backend/core/auth.php';
require_once __DIR__ . '/../../backend/core/functions.php';

require_admin();

$adminId = $_SESSION['profile_id'];
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($projectId <= 0) {
    header('Location: ' . base_url('frontend/admin/projects.php'));
    exit;
}

// Fetch project details
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, s.full_name AS student_name, s.roll_no AS student_roll
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    WHERE p.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $projectId]);
$project = $stmt->fetch();

if (!$project) {
    $_SESSION['flash_error'] = 'Project not found.';
    header('Location: ' . base_url('frontend/admin/projects.php'));
    exit;
}

// Fetch existing evaluation if already evaluated
$evalStmt = $pdo->prepare("SELECT * FROM evaluations WHERE project_id = :pid LIMIT 1");
$evalStmt->execute([':pid' => $projectId]);
$existingEval = $evalStmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $innovation    = (float)($_POST['innovation_score'] ?? 0);
    $functionality = (float)($_POST['functionality_score'] ?? 0);
    $uiDesign      = (float)($_POST['ui_design_score'] ?? 0);
    $techUsage     = (float)($_POST['tech_usage_score'] ?? 0);
    $presentation  = (float)($_POST['presentation_score'] ?? 0);
    $feedback      = trim($_POST['feedback_text'] ?? '');
    $publish       = isset($_POST['is_published']) ? 1 : 0;

    // Validate bounds
    if ($innovation < 0 || $innovation > 20) {
        $errors[] = 'Innovation marks must be between 0 and 20.';
    }
    if ($functionality < 0 || $functionality > 30) {
        $errors[] = 'Functionality marks must be between 0 and 30.';
    }
    if ($uiDesign < 0 || $uiDesign > 20) {
        $errors[] = 'UI / Design marks must be between 0 and 20.';
    }
    if ($techUsage < 0 || $techUsage > 15) {
        $errors[] = 'Technology Usage marks must be between 0 and 15.';
    }
    if ($presentation < 0 || $presentation > 15) {
        $errors[] = 'Presentation & Documentation marks must be between 0 and 15.';
    }
    if (empty($feedback)) {
        $errors[] = 'Please provide constructive faculty feedback/comments for the student.';
    }

    $totalScore = round($innovation + $functionality + $uiDesign + $techUsage + $presentation, 1);

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            if ($existingEval) {
                // Update
                $upEval = $pdo->prepare("
                    UPDATE evaluations SET
                        admin_id = :aid,
                        innovation_score = :inno,
                        functionality_score = :func,
                        ui_design_score = :ui,
                        tech_usage_score = :tech,
                        presentation_score = :pres,
                        total_score = :tot,
                        feedback_text = :feedback,
                        is_published = :pub,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :eid
                ");
                $upEval->execute([
                    ':aid'      => $adminId,
                    ':inno'     => $innovation,
                    ':func'     => $functionality,
                    ':ui'       => $uiDesign,
                    ':tech'     => $techUsage,
                    ':pres'     => $presentation,
                    ':tot'      => $totalScore,
                    ':feedback' => $feedback,
                    ':pub'      => $publish,
                    ':eid'      => $existingEval['id']
                ]);
            } else {
                // Insert
                $inEval = $pdo->prepare("
                    INSERT INTO evaluations (
                        project_id, admin_id, innovation_score, functionality_score, 
                        ui_design_score, tech_usage_score, presentation_score, 
                        total_score, feedback_text, is_published
                    ) VALUES (
                        :pid, :aid, :inno, :func, :ui, :tech, :pres, :tot, :feedback, :pub
                    )
                ");
                $inEval->execute([
                    ':pid'      => $projectId,
                    ':aid'      => $adminId,
                    ':inno'     => $innovation,
                    ':func'     => $functionality,
                    ':ui'       => $uiDesign,
                    ':tech'     => $techUsage,
                    ':pres'     => $presentation,
                    ':tot'      => $totalScore,
                    ':feedback' => $feedback,
                    ':pub'      => $publish
                ]);
            }

            // Also record in feedback history log
            $fbLog = $pdo->prepare("INSERT INTO feedback (project_id, admin_id, comment) VALUES (:pid, :aid, :comm)");
            $fbLog->execute([
                ':pid'  => $projectId,
                ':aid'  => $adminId,
                ':comm' => "Evaluation marks awarded: {$totalScore}/100. " . $feedback
            ]);

            // If project was pending, auto-approve it upon scoring
            if ($project['status'] !== 'approved') {
                $pdo->prepare("UPDATE projects SET status = 'approved', rejection_reason = NULL WHERE id = :id")->execute([':id' => $projectId]);
            }

            $pdo->commit();

            // Trigger Automatic Platform Ranking Recalculation!
            recalculate_rankings($pdo);

            // Fetch newly assigned rank
            $rCheck = $pdo->prepare("SELECT overall_rank, category_rank FROM rankings WHERE project_id = :pid");
            $rCheck->execute([':pid' => $projectId]);
            $newRank = $rCheck->fetch();

            $rankMsg = $newRank ? " (Assigned Overall Rank #{$newRank['overall_rank']})" : "";
            $_SESSION['flash_success'] = "Project evaluated successfully! Total Score: {$totalScore}/100{$rankMsg}. Rankings updated.";

            header('Location: ' . base_url('frontend/admin/review-project.php?id=' . $projectId));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Evaluation failed: ' . $e->getMessage();
        }
    }
}

// Prefill values
$valInno = $_POST['innovation_score'] ?? $existingEval['innovation_score'] ?? 15;
$valFunc = $_POST['functionality_score'] ?? $existingEval['functionality_score'] ?? 25;
$valUi   = $_POST['ui_design_score'] ?? $existingEval['ui_design_score'] ?? 16;
$valTech = $_POST['tech_usage_score'] ?? $existingEval['tech_usage_score'] ?? 12;
$valPres = $_POST['presentation_score'] ?? $existingEval['presentation_score'] ?? 12;
$valFeed = $_POST['feedback_text'] ?? $existingEval['feedback_text'] ?? '';

$pageTitle = 'Evaluate Project: ' . htmlspecialchars($project['title']) . ' - Faculty Portal';
require_once __DIR__ . '/../../backend/core/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../../backend/core/admin-navbar.php'; ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-success-subtle text-success border px-2 py-1">100-Mark Rubric</span>
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($project['category_name']) ?></span>
                        </div>
                        <h2 class="h4 fw-bold mb-1">Evaluate: <?= htmlspecialchars($project['title']) ?></h2>
                        <p class="text-secondary small mb-0">
                            Student: <strong><?= htmlspecialchars($project['student_name']) ?></strong> (<?= htmlspecialchars($project['student_roll']) ?>)
                        </p>
                    </div>

                    <a href="<?= base_url('frontend/admin/review-project.php?id=' . $projectId) ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0">
                        <i class="bi bi-arrow-left me-1"></i>Back to Review
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Evaluation Form -->
                    <div class="col-lg-7">
                        <form method="POST" id="evaluationForm" action="<?= base_url('frontend/admin/evaluate.php?id=' . $projectId) ?>">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-sliders me-2 text-primary"></i>Evaluation Marks Input</h5>
                            
                            <!-- Metric 1: Innovation (20) -->
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold mb-0">1. Innovation & Uniqueness</label>
                                    <span class="badge bg-secondary">Max: 20 Marks</span>
                                </div>
                                <div class="small text-muted mb-2">Originality of idea, problem significance, and creativity.</div>
                                <input type="number" step="0.5" min="0" max="20" name="innovation_score" id="innovation_score" class="form-control" value="<?= htmlspecialchars($valInno) ?>" required>
                            </div>

                            <!-- Metric 2: Functionality (30) -->
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold mb-0">2. System Functionality & Execution</label>
                                    <span class="badge bg-secondary">Max: 30 Marks</span>
                                </div>
                                <div class="small text-muted mb-2">Working modules, error handling, completeness, and practical applicability.</div>
                                <input type="number" step="0.5" min="0" max="30" name="functionality_score" id="functionality_score" class="form-control" value="<?= htmlspecialchars($valFunc) ?>" required>
                            </div>

                            <!-- Metric 3: UI / Design (20) -->
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold mb-0">3. UI / UX & Responsive Design</label>
                                    <span class="badge bg-secondary">Max: 20 Marks</span>
                                </div>
                                <div class="small text-muted mb-2">Visual presentation, user friendliness, mobile layout, and clean aesthetics.</div>
                                <input type="number" step="0.5" min="0" max="20" name="ui_design_score" id="ui_design_score" class="form-control" value="<?= htmlspecialchars($valUi) ?>" required>
                            </div>

                            <!-- Metric 4: Technology Usage (15) -->
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold mb-0">4. Technology Stack & Code Quality</label>
                                    <span class="badge bg-secondary">Max: 15 Marks</span>
                                </div>
                                <div class="small text-muted mb-2">Appropriateness of languages, database schema design, and modular code.</div>
                                <input type="number" step="0.5" min="0" max="15" name="tech_usage_score" id="tech_usage_score" class="form-control" value="<?= htmlspecialchars($valTech) ?>" required>
                            </div>

                            <!-- Metric 5: Presentation & Documentation (15) -->
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold mb-0">5. Documentation & Presentation</label>
                                    <span class="badge bg-secondary">Max: 15 Marks</span>
                                </div>
                                <div class="small text-muted mb-2">Quality of project report PDF, objectives clarity, and demonstration.</div>
                                <input type="number" step="0.5" min="0" max="15" name="presentation_score" id="presentation_score" class="form-control" value="<?= htmlspecialchars($valPres) ?>" required>
                            </div>

                            <!-- Feedback Textarea -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Faculty Evaluator Feedback & Remarks <span class="text-danger">*</span></label>
                                <textarea name="feedback_text" class="form-control" rows="4" placeholder="Provide constructive criticism, praise strengths, and specify recommendations for the student..." required><?= htmlspecialchars($valFeed) ?></textarea>
                            </div>

                            <!-- Hidden total input for auto-sync -->
                            <input type="hidden" name="total_score" id="total_score" value="0">

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="<?= base_url('frontend/admin/review-project.php?id=' . $projectId) ?>" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                    <i class="bi bi-check-circle-fill me-2"></i>Save Evaluation & Update Rankings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Live Calculation Card & Project Context -->
                    <div class="col-lg-5">
                        <div class="card border shadow-sm p-4 sticky-top" style="top: 80px; border-radius: 14px;">
                            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-calculator me-2 text-primary"></i>Live Total Score</h5>
                            
                            <div class="text-center p-3 bg-light rounded-3 border mb-3">
                                <div class="small text-muted fw-semibold text-uppercase">Calculated Score</div>
                                <div class="display-4 fw-extrabold text-primary mb-2">
                                    <span id="totalScoreDisplay">0</span><span class="fs-4 text-muted">/100</span>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar bg-primary" id="scoreProgressBar" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <div class="p-3 bg-warning-subtle text-warning-emphasis rounded-3 border small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                <strong>Automatic Ranking:</strong> Saving this evaluation recalculates global and category ranks across all approved projects automatically.
                            </div>

                            <h6 class="fw-bold text-dark mb-2">Project Quick Summary:</h6>
                            <div class="small text-secondary mb-3">
                                <?= htmlspecialchars($project['short_description']) ?>
                            </div>

                            <?php if (!empty($project['private_source_code_file'])): ?>
                                <a href="<?= base_url($project['private_source_code_file']) ?>" class="btn btn-outline-dark btn-sm w-100 mb-2" download>
                                    <i class="bi bi-file-earmark-zip me-1"></i>Download Private Source Code
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($project['documentation_file'])): ?>
                                <a href="<?= base_url($project['documentation_file']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>View PDF Report
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../backend/core/footer.php'; ?>
