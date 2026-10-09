<?php
/**
 * ProjectSphere - Student Evaluations & Feedback Center
 */
require_once __DIR__ . '/../../backend/core/db.php';
require_once __DIR__ . '/../../backend/core/auth.php';
require_once __DIR__ . '/../../backend/core/functions.php';

require_student();

$studentId = $_SESSION['profile_id'];

// Fetch evaluated projects
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name,
           e.innovation_score, e.functionality_score, e.ui_design_score, 
           e.tech_usage_score, e.presentation_score, e.total_score, 
           e.feedback_text, e.is_published, e.evaluated_at,
           r.overall_rank, r.category_rank,
           a.full_name AS evaluator_name, a.designation AS evaluator_designation
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    LEFT JOIN evaluations e ON p.id = e.project_id
    LEFT JOIN rankings r ON p.id = r.project_id
    LEFT JOIN admins a ON e.admin_id = a.id
    WHERE p.student_id = :sid
    ORDER BY (e.total_score IS NOT NULL) DESC, e.total_score DESC, p.submitted_at DESC
");
$stmt->execute([':sid' => $studentId]);
$projects = $stmt->fetchAll();

$pageTitle = 'My Evaluations & Faculty Feedback - ProjectSphere';
require_once __DIR__ . '/../../backend/core/header.php';
?>

<div class="container-xl py-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../../backend/core/student-navbar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-award-fill text-warning me-2"></i>My Evaluations & Faculty Feedback</h2>
                        <p class="text-secondary small mb-0">Detailed scoring breakdown across the 100-mark academic rubric and official faculty feedback.</p>
                    </div>
                    <a href="<?= base_url('frontend/student/my-projects.php') ?>" class="btn btn-outline-secondary btn-sm mt-2 mt-md-0">
                        <i class="bi bi-folder2-open me-1"></i>All Submissions
                    </a>
                </div>

                <?php if (empty($projects)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clipboard-x fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold">No submissions found</h5>
                        <p class="text-secondary small mb-3">Submit your academic capstone project to receive official faculty evaluation.</p>
                        <a href="<?= base_url('frontend/student/submit-project.php') ?>" class="btn btn-primary btn-sm">Submit Project</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-4">
                        <?php foreach ($projects as $p): ?>
                            <div class="card border shadow-sm p-4" style="border-radius: 14px;">
                                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3 border-bottom pb-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="category-pill small"><?= htmlspecialchars($p['category_name']) ?></span>
                                            <?= get_status_badge($p['status']) ?>
                                            <?php if ($p['status'] === 'approved' && !empty($p['overall_rank'])): ?>
                                                <?= get_rank_badge((int)$p['overall_rank']) ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                                    Category Rank #<?= $p['category_rank'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="h5 fw-bold mb-1">
                                            <a href="<?= base_url('frontend/project-details.php?id=' . $p['id']) ?>" class="text-dark text-decoration-none">
                                                <?= htmlspecialchars($p['title']) ?>
                                            </a>
                                        </h4>
                                        <div class="small text-muted">Submitted on <?= date('d M Y', strtotime($p['submitted_at'])) ?></div>
                                    </div>

                                    <?php if (!empty($p['total_score'])): ?>
                                        <div class="text-end">
                                            <div class="small text-muted fw-bold text-uppercase">Total Marks</div>
                                            <div class="display-6 fw-extrabold text-success mb-0">
                                                <?= format_score($p['total_score']) ?><span class="fs-5 text-muted">/100</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-end">
                                            <span class="badge bg-warning-subtle text-warning-emphasis p-2">
                                                <i class="bi bi-clock me-1"></i>Pending Review
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($p['total_score'])): ?>
                                    <!-- 5-Criteria Rubric Breakdown -->
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-grid-3x3-gap-fill me-1 text-primary"></i>100-Mark Rubric Breakdown:</h6>
                                    <div class="row g-2 mb-4">
                                        <div class="col-md-2 col-4">
                                            <div class="eval-metric-box p-2">
                                                <div class="eval-metric-score fs-4"><?= format_score($p['innovation_score']) ?></div>
                                                <div class="eval-metric-max small">Max: 20</div>
                                                <div class="eval-metric-title small fw-semibold">Innovation</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-4">
                                            <div class="eval-metric-box p-2">
                                                <div class="eval-metric-score fs-4"><?= format_score($p['functionality_score']) ?></div>
                                                <div class="eval-metric-max small">Max: 30</div>
                                                <div class="eval-metric-title small fw-semibold">Functionality</div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 col-4">
                                            <div class="eval-metric-box p-2">
                                                <div class="eval-metric-score fs-4"><?= format_score($p['ui_design_score']) ?></div>
                                                <div class="eval-metric-max small">Max: 20</div>
                                                <div class="eval-metric-title small fw-semibold">UI / UX</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="eval-metric-box p-2">
                                                <div class="eval-metric-score fs-4"><?= format_score($p['tech_usage_score']) ?></div>
                                                <div class="eval-metric-max small">Max: 15</div>
                                                <div class="eval-metric-title small fw-semibold">Technology Stack</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="eval-metric-box p-2">
                                                <div class="eval-metric-score fs-4"><?= format_score($p['presentation_score']) ?></div>
                                                <div class="eval-metric-max small">Max: 15</div>
                                                <div class="eval-metric-title small fw-semibold">Documentation</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Faculty Remarks Box -->
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-chat-quote-fill me-2 text-primary"></i>Faculty Comments:</h6>
                                        <p class="text-secondary small fst-italic mb-2">"<?= htmlspecialchars($p['feedback_text']) ?>"</p>
                                        <div class="text-end small text-muted">
                                            Evaluated by: <strong><?= htmlspecialchars($p['evaluator_name'] ?? 'Faculty Board') ?></strong> &bull; <?= htmlspecialchars($p['evaluator_designation'] ?? 'Evaluator') ?> (<?= date('d M Y', strtotime($p['evaluated_at'])) ?>)
                                        </div>
                                    </div>
                                <?php elseif ($p['status'] === 'rejected'): ?>
                                    <div class="alert alert-danger mb-0">
                                        <h6 class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-2"></i>Submission Rejected</h6>
                                        <p class="small mb-0"><strong>Faculty Reason:</strong> <?= htmlspecialchars($p['rejection_reason'] ?? 'Submission does not meet academic criteria.') ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3 bg-light rounded-3 text-center text-muted small">
                                        <i class="bi bi-hourglass-split me-1 text-warning"></i>
                                        Your project is currently awaiting faculty review. Scores and rankings will appear here as soon as evaluation is completed.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../backend/core/footer.php'; ?>
