<?php
/**
 * ProjectSphere - Teacher / Faculty Admin Dashboard
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

// Dashboard Statistics
$stats = [
    'students'   => (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'projects'   => (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'pending'    => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'pending'")->fetchColumn(),
    'approved'   => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'approved'")->fetchColumn(),
    'rejected'   => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'rejected'")->fetchColumn(),
    'categories' => (int)$pdo->query("SELECT COUNT(*) FROM project_categories")->fetchColumn(),
];

// Pending Reviews Queue (Needs attention)
$pendingQueue = $pdo->query("
    SELECT p.*, c.name AS category_name, s.full_name AS student_name, s.roll_no AS student_roll
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    WHERE p.status = 'pending'
    ORDER BY p.submitted_at ASC
    LIMIT 5
")->fetchAll();

// Top Ranked Projects
$topProjects = $pdo->query("
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

// Recent Project Submissions (All statuses)
$recentSubmissions = $pdo->query("
    SELECT p.*, c.name AS category_name, s.full_name AS student_name,
           e.total_score
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    LEFT JOIN evaluations e ON p.id = e.project_id
    ORDER BY p.submitted_at DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Faculty Admin Dashboard - ProjectSphere';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>
        </div>

        <!-- Main Dashboard View -->
        <div class="col-lg-9 col-md-8">
            <!-- Welcome Header -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; border-radius: 16px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-danger text-white mb-2">Faculty Evaluation Portal</span>
                        <h2 class="h3 fw-bold text-white mb-1">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h2>
                        <p class="text-light opacity-75 small mb-0">Academic Project Review, Evaluation & Ranking Control Center</p>
                    </div>
                    <div class="mt-3 mt-md-0 d-flex gap-2">
                        <a href="<?= base_url('admin/projects.php?status=pending') ?>" class="btn btn-warning btn-sm fw-semibold">
                            <i class="bi bi-clock-history me-1"></i>Review Queue (<?= $stats['pending'] ?>)
                        </a>
                        <a href="<?= base_url('admin/rankings.php') ?>" class="btn btn-outline-light btn-sm fw-semibold">
                            <i class="bi bi-trophy me-1"></i>View Rankings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dashboard 6-Metric Cards Grid -->
            <div class="row g-3 mb-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="content-card text-center p-3 h-100">
                        <i class="bi bi-people-fill fs-3 text-primary mb-1 d-block"></i>
                        <div class="h4 fw-bold text-dark mb-0"><?= $stats['students'] ?></div>
                        <div class="small text-muted fw-semibold">Students</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="content-card text-center p-3 h-100">
                        <i class="bi bi-stack fs-3 text-secondary mb-1 d-block"></i>
                        <div class="h4 fw-bold text-dark mb-0"><?= $stats['projects'] ?></div>
                        <div class="small text-muted fw-semibold">All Projects</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="content-card text-center p-3 h-100 border-warning-subtle">
                        <i class="bi bi-hourglass-split fs-3 text-warning mb-1 d-block"></i>
                        <div class="h4 fw-bold text-warning mb-0"><?= $stats['pending'] ?></div>
                        <div class="small text-muted fw-semibold">Pending</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="content-card text-center p-3 h-100 border-success-subtle">
                        <i class="bi bi-check-circle-fill fs-3 text-success mb-1 d-block"></i>
                        <div class="h4 fw-bold text-success mb-0"><?= $stats['approved'] ?></div>
                        <div class="small text-muted fw-semibold">Approved</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="content-card text-center p-3 h-100 border-danger-subtle">
                        <i class="bi bi-x-circle-fill fs-3 text-danger mb-1 d-block"></i>
                        <div class="h4 fw-bold text-danger mb-0"><?= $stats['rejected'] ?></div>
                        <div class="small text-muted fw-semibold">Rejected</div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="content-card text-center p-3 h-100">
                        <i class="bi bi-tags-fill fs-3 text-info mb-1 d-block"></i>
                        <div class="h4 fw-bold text-dark mb-0"><?= $stats['categories'] ?></div>
                        <div class="small text-muted fw-semibold">Categories</div>
                    </div>
                </div>
            </div>

            <!-- Two Columns: Pending Reviews & Top Rankings -->
            <div class="row g-4 mb-4">
                <!-- Pending Reviews Queue -->
                <div class="col-lg-7">
                    <div class="content-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-exclamation-circle text-warning me-2"></i>Pending Reviews Queue
                            </h5>
                            <a href="<?= base_url('admin/projects.php?status=pending') ?>" class="btn btn-sm btn-link text-decoration-none">
                                View All (<?= $stats['pending'] ?>) &rarr;
                            </a>
                        </div>

                        <?php if (empty($pendingQueue)): ?>
                            <div class="text-center py-4 bg-light rounded-3">
                                <i class="bi bi-check2-all fs-2 text-success mb-2 d-block"></i>
                                <p class="text-muted small mb-0">All submitted projects have been reviewed and evaluated!</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Project</th>
                                            <th>Author</th>
                                            <th>Category</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingQueue as $p): ?>
                                            <tr>
                                                <td class="fw-bold text-dark"><?= htmlspecialchars($p['title']) ?></td>
                                                <td><?= htmlspecialchars($p['student_name']) ?> <span class="text-muted">(<?= htmlspecialchars($p['student_roll']) ?>)</span></td>
                                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name']) ?></span></td>
                                                <td class="text-end">
                                                    <a href="<?= base_url('admin/review-project.php?id=' . $p['id']) ?>" class="btn btn-xs btn-primary btn-sm">
                                                        Review &rarr;
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

                <!-- Top Ranked Projects -->
                <div class="col-lg-5">
                    <div class="content-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Top Ranked</h5>
                            <a href="<?= base_url('admin/rankings.php') ?>" class="btn btn-sm btn-link text-decoration-none">Full List &rarr;</a>
                        </div>

                        <?php if (empty($topProjects)): ?>
                            <div class="text-center py-4 bg-light rounded-3">
                                <p class="text-muted small mb-0">No projects evaluated yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush border rounded-3 small">
                                <?php foreach ($topProjects as $tp): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <?= get_rank_badge((int)$tp['overall_rank']) ?>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars(substr($tp['title'], 0, 24)) ?><?= (strlen($tp['title']) > 24) ? '...' : '' ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($tp['student_name']) ?></small>
                                            </div>
                                        </div>
                                        <span class="badge bg-success-subtle text-success fw-bold">
                                            <?= format_score($tp['total_score']) ?>/100
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Submissions Overview Table -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Latest Platform Activity</h5>
                    <a href="<?= base_url('admin/projects.php') ?>" class="btn btn-sm btn-link text-decoration-none">All Projects &rarr;</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Project Title</th>
                                <th>Student</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Date</th>
                                <th class="text-end">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSubmissions as $r): ?>
                                <tr>
                                    <td class="fw-bold text-dark">
                                        <a href="<?= base_url('admin/review-project.php?id=' . $r['id']) ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($r['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($r['student_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['category_name']) ?></span></td>
                                    <td><?= get_status_badge($r['status']) ?></td>
                                    <td>
                                        <?= !empty($r['total_score']) ? '<span class="badge bg-success-subtle text-success fw-bold">' . format_score($r['total_score']) . '/100</span>' : '<span class="text-muted">&mdash;</span>' ?>
                                    </td>
                                    <td class="text-muted"><?= date('d M Y', strtotime($r['submitted_at'])) ?></td>
                                    <td class="text-end">
                                        <a href="<?= base_url('admin/review-project.php?id=' . $r['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Review">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                        <a href="<?= base_url('admin/evaluate.php?id=' . $r['id']) ?>" class="btn btn-sm btn-outline-primary" title="Evaluate">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
