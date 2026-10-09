<?php
/**
 * ProjectSphere - Faculty Feedback Management
 */
require_once __DIR__ . '/../../backend/core/db.php';
require_once __DIR__ . '/../../backend/core/auth.php';
require_once __DIR__ . '/../../backend/core/functions.php';

require_admin();

$adminId = $_SESSION['profile_id'];

// Handle new feedback post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_feedback') {
    if (!validate_csrf()) {
        $_SESSION['flash_error'] = 'Security check failed (CSRF). Please refresh and try again.';
        header('Location: ' . base_url('frontend/admin/feedback.php'));
        exit;
    }
    $projId = (int)($_POST['project_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($projId > 0 && !empty($comment)) {
        $fbStmt = $pdo->prepare("INSERT INTO feedback (project_id, admin_id, comment) VALUES (:pid, :aid, :comm)");
        $fbStmt->execute([
            ':pid'  => $projId,
            ':aid'  => $adminId,
            ':comm' => $comment
        ]);
        $_SESSION['flash_success'] = 'Faculty feedback remark added successfully.';
    }
    header('Location: ' . base_url('frontend/admin/feedback.php'));
    exit;
}

// Fetch feedback list
$searchTerm = trim($_GET['search'] ?? '');
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
if (!empty($searchTerm)) {
    $sql .= " WHERE (p.title LIKE :s1 OR s.full_name LIKE :s2 OR f.comment LIKE :s3)";
    $params[':s1'] = "%{$searchTerm}%";
    $params[':s2'] = "%{$searchTerm}%";
    $params[':s3'] = "%{$searchTerm}%";
}

$sql .= " ORDER BY f.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll();

// All projects for quick remark modal dropdown
$allProjects = $pdo->query("SELECT id, title FROM projects ORDER BY title ASC")->fetchAll();

$pageTitle = 'Feedback Management - Faculty Admin';
require_once __DIR__ . '/../../backend/core/header.php';
?>

<div class="container-xl py-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../../backend/core/admin-navbar.php'; ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-chat-left-dots-fill me-2 text-primary"></i>Feedback & Remarks Log</h2>
                        <p class="text-secondary small mb-0">Track official feedback comments provided to students during evaluation rounds.</p>
                    </div>

                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#newFeedbackModal">
                            <i class="bi bi-plus-circle me-1"></i>Add Feedback Remark
                        </button>
                    </div>
                </div>

                <!-- Search Filter -->
                <form method="GET" action="<?= base_url('frontend/admin/feedback.php') ?>" class="row g-2 mb-4">
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search by project, student, or remarks..." value="<?= htmlspecialchars($searchTerm) ?>">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </form>

                <!-- Feedback Timeline Table -->
                <?php if (empty($feedbacks)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-square-quote fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold">No feedback entries found</h5>
                        <p class="text-secondary small">Feedback remarks are automatically logged upon evaluation or can be posted manually.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Project & Student</th>
                                    <th>Faculty Feedback Comment</th>
                                    <th>Evaluator</th>
                                    <th>Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedbacks as $fb): ?>
                                    <tr>
                                        <td style="max-width: 220px;">
                                            <a href="<?= base_url('frontend/admin/review-project.php?id=' . $fb['proj_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                                <?= htmlspecialchars($fb['project_title']) ?>
                                            </a>
                                            <div class="text-muted small">
                                                Student: <?= htmlspecialchars($fb['student_name']) ?> (<?= htmlspecialchars($fb['student_roll']) ?>)
                                            </div>
                                        </td>
                                        <td class="text-secondary" style="max-width: 380px;">
                                            <span class="fst-italic">"<?= htmlspecialchars($fb['comment']) ?>"</span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($fb['faculty_name']) ?></strong>
                                            <div class="text-muted small"><?= htmlspecialchars($fb['faculty_designation']) ?></div>
                                        </td>
                                        <td class="text-muted"><?= date('d M Y, h:i A', strtotime($fb['created_at'])) ?></td>
                                        <td class="text-end">
                                            <a href="<?= base_url('frontend/admin/review-project.php?id=' . $fb['proj_id']) ?>" class="btn btn-sm btn-outline-secondary" title="View Project">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Feedback Modal -->
<div class="modal fade" id="newFeedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= base_url('frontend/admin/feedback.php') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="post_feedback">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Post Faculty Remark</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select" required>
                            <option value="">-- Choose Project --</option>
                            <?php foreach ($allProjects as $ap): ?>
                                <option value="<?= $ap['id'] ?>"><?= htmlspecialchars($ap['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Faculty Feedback / Guidance Remark <span class="text-danger">*</span></label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Enter constructive remarks, viva advice, or improvements..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Post Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../backend/core/footer.php'; ?>
