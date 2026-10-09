<?php
/**
 * ProjectSphere - Manage Projects (Admin)
 */
require_once __DIR__ . '/../../backend/core/db.php';
require_once __DIR__ . '/../../backend/core/auth.php';
require_once __DIR__ . '/../../backend/core/functions.php';

require_admin();

// Handle Status Changes (Approve / Reject) or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $_SESSION['flash_error'] = 'Security check failed (CSRF). Please refresh and try again.';
        header('Location: ' . base_url('frontend/admin/projects.php'));
        exit;
    }
    $action = $_POST['action'] ?? '';
    $projId = (int)($_POST['project_id'] ?? 0);

    if ($projId > 0) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE projects SET status = 'approved', rejection_reason = NULL WHERE id = :id")->execute([':id' => $projId]);
            recalculate_rankings($pdo);
            $_SESSION['flash_success'] = 'Project marked as Approved.';
        } elseif ($action === 'reject') {
            $reason = trim($_POST['rejection_reason'] ?? 'Does not meet academic submission standards.');
            $pdo->prepare("UPDATE projects SET status = 'rejected', rejection_reason = :reason WHERE id = :id")->execute([':reason' => $reason, ':id' => $projId]);
            recalculate_rankings($pdo);
            $_SESSION['flash_info'] = 'Project marked as Rejected with feedback remarks.';
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM projects WHERE id = :id")->execute([':id' => $projId]);
            recalculate_rankings($pdo);
            $_SESSION['flash_success'] = 'Project permanently deleted.';
        }
    }
    header('Location: ' . base_url('frontend/admin/projects.php?' . http_build_query($_GET)));
    exit;
}

// Filters
$statusFilter   = trim($_GET['status'] ?? '');
$categoryFilter = (int)($_GET['category'] ?? 0);
$searchTerm     = trim($_GET['search'] ?? '');

$sql = "
    SELECT p.*, c.name AS category_name, s.full_name AS student_name, s.roll_no AS student_roll,
           e.total_score, r.overall_rank
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    LEFT JOIN evaluations e ON p.id = e.project_id
    LEFT JOIN rankings r ON p.id = r.project_id
    WHERE 1=1
";
$params = [];

if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
    $sql .= " AND p.status = :status";
    $params[':status'] = $statusFilter;
}

if ($categoryFilter > 0) {
    $sql .= " AND p.category_id = :cat_id";
    $params[':cat_id'] = $categoryFilter;
}

if (!empty($searchTerm)) {
    $sql .= " AND (p.title LIKE :s1 OR s.full_name LIKE :s2 OR s.roll_no LIKE :s3)";
    $params[':s1'] = "%{$searchTerm}%";
    $params[':s2'] = "%{$searchTerm}%";
    $params[':s3'] = "%{$searchTerm}%";
}

$sql .= " ORDER BY p.submitted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$categories = $pdo->query("SELECT id, name FROM project_categories ORDER BY name ASC")->fetchAll();

$pageTitle = 'Manage Projects - Faculty Admin';
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
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-stack me-2 text-primary"></i>Manage Student Submissions</h2>
                        <p class="text-secondary small mb-0">Review capstones, change statuses, assign marks, and remove inappropriate submissions.</p>
                    </div>
                    <span class="badge bg-light text-dark border p-2"><?= count($projects) ?> Submissions Found</span>
                </div>

                <!-- Search and Filters Form -->
                <form method="GET" action="<?= base_url('frontend/admin/projects.php') ?>" class="row g-2 mb-4 bg-light p-3 rounded-3 border">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title, student, roll no..." value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending Review</option>
                            <option value="approved" <?= ($statusFilter === 'approved') ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= ($statusFilter === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($categoryFilter == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                        <a href="<?= base_url('frontend/admin/projects.php') ?>" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>

                <!-- Projects Table -->
                <?php if (empty($projects)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
                        <p class="text-secondary">No projects found matching the filter criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Thumbnail</th>
                                    <th>Title & Student</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Score / Rank</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $p): ?>
                                    <tr>
                                        <td style="width: 70px;">
                                            <img src="<?= base_url($p['thumbnail_image']) ?>" class="rounded-2" style="width: 60px; height: 42px; object-fit: cover;" alt="Thumb">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <a href="<?= base_url('frontend/admin/review-project.php?id=' . $p['id']) ?>" class="text-dark text-decoration-none">
                                                    <?= htmlspecialchars($p['title']) ?>
                                                </a>
                                            </div>
                                            <small class="text-muted">
                                                By: <strong><?= htmlspecialchars($p['student_name']) ?></strong> (<?= htmlspecialchars($p['student_roll']) ?>)
                                            </small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name']) ?></span></td>
                                        <td><?= get_status_badge($p['status']) ?></td>
                                        <td>
                                            <?php if ($p['status'] === 'approved' && !empty($p['total_score'])): ?>
                                                <span class="badge bg-success-subtle text-success fw-bold">
                                                    <?= format_score($p['total_score']) ?>/100
                                                </span>
                                                <?php if (!empty($p['overall_rank'])): ?>
                                                    <span class="badge bg-dark ms-1">#<?= $p['overall_rank'] ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('frontend/admin/review-project.php?id=' . $p['id']) ?>" class="btn btn-outline-secondary" title="Review Full Submission">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </a>
                                                <a href="<?= base_url('frontend/admin/evaluate.php?id=' . $p['id']) ?>" class="btn btn-outline-primary" title="Award / Edit Marks">
                                                    <i class="bi bi-award"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $p['id'] ?>" title="Delete Project">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?= $p['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold text-danger"><i class="bi bi-trash me-1"></i>Delete Project</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body small">
                                                    Permanently delete <strong><?= htmlspecialchars($p['title']) ?></strong>? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="POST" action="<?= base_url('frontend/admin/projects.php') ?>">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="project_id" value="<?= $p['id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">Confirm Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../backend/core/footer.php'; ?>
