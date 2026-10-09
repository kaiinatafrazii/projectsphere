<?php
/**
 * ProjectSphere - Project Rankings & Leaderboard (Admin)
 */
require_once __DIR__ . '/../../backend/core/db.php';
require_once __DIR__ . '/../../backend/core/auth.php';
require_once __DIR__ . '/../../backend/core/functions.php';

require_admin();

// Manual Sync / Recalculate Trigger
if (isset($_POST['recalculate'])) {
    recalculate_rankings($pdo);
    $_SESSION['flash_success'] = 'Project rankings and category standings have been recalculated successfully.';
    header('Location: ' . base_url('frontend/admin/rankings.php'));
    exit;
}

$categoryFilter = (int)($_GET['category'] ?? 0);

// Query rankings
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
    $sql .= " AND r.category_id = :cat_id";
    $params[':cat_id'] = $categoryFilter;
    $sql .= " ORDER BY r.category_rank ASC, r.total_score DESC";
} else {
    $sql .= " ORDER BY r.overall_rank ASC, r.total_score DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rankings = $stmt->fetchAll();

$categories = $pdo->query("SELECT id, name FROM project_categories ORDER BY name ASC")->fetchAll();

$pageTitle = 'Project Rankings & Leaderboard - Faculty Admin';
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
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-trophy-fill text-warning me-2"></i>Official Project Leaderboard</h2>
                        <p class="text-secondary small mb-0">Automated rank allocation based on evaluation scores and faculty rubric.</p>
                    </div>

                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <form method="POST" action="<?= base_url('frontend/admin/rankings.php') ?>">
                            <input type="hidden" name="recalculate" value="1">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-repeat me-1"></i>Sync & Recalculate
                            </button>
                        </form>
                        <button onclick="window.print()" class="btn btn-light btn-sm border">
                            <i class="bi bi-printer me-1"></i>Print Sheet
                        </button>
                    </div>
                </div>

                <!-- Filter by Category -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <form method="GET" action="<?= base_url('frontend/admin/rankings.php') ?>" class="d-flex align-items-center gap-2">
                        <label class="small text-muted fw-bold">Filter By:</label>
                        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 240px;">
                            <option value="0">Overall Platform Rankings</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($categoryFilter == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <div class="small text-muted">
                        Showing <strong><?= count($rankings) ?></strong> evaluated projects
                    </div>
                </div>

                <!-- Leaderboard Table -->
                <?php if (empty($rankings)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-trophy fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold">No evaluated projects found</h5>
                        <p class="text-secondary small">Projects must be approved and evaluated to appear in the official rankings.</p>
                        <a href="<?= base_url('frontend/admin/projects.php?status=pending') ?>" class="btn btn-primary btn-sm">Go to Evaluation Queue</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 100px;">Rank</th>
                                    <th>Project Title</th>
                                    <th>Lead Student</th>
                                    <th>Category</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rankings as $rk): 
                                    $displayRank = ($categoryFilter > 0) ? $rk['category_rank'] : $rk['overall_rank'];
                                ?>
                                    <tr class="<?= ($displayRank === 1) ? 'table-warning-subtle' : '' ?>">
                                        <td>
                                            <?= get_rank_badge((int)$displayRank) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= base_url($rk['thumbnail_image']) ?>" class="rounded-2" style="width: 50px; height: 35px; object-fit: cover;" alt="Thumbnail">
                                                <div>
                                                    <a href="<?= base_url('frontend/project-details.php?id=' . $rk['project_id']) ?>" class="fw-bold text-dark text-decoration-none" target="_blank">
                                                        <?= htmlspecialchars($rk['title']) ?>
                                                    </a>
                                                    <div class="small text-muted">
                                                        Eval Date: <?= date('d M Y', strtotime($rk['evaluated_at'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($rk['student_name']) ?></strong>
                                            <div class="text-muted small"><?= htmlspecialchars($rk['student_roll']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($rk['category_name']) ?></span>
                                            <?php if ($categoryFilter === 0 && !empty($rk['category_rank'])): ?>
                                                <div class="text-muted small">Cat #<?= $rk['category_rank'] ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="h4 fw-bold text-success mb-0"><?= format_score($rk['total_score']) ?></div>
                                            <div class="small text-muted">/100</div>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= base_url('frontend/admin/evaluate.php?id=' . $rk['project_id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit Evaluation">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="<?= base_url('frontend/admin/review-project.php?id=' . $rk['project_id']) ?>" class="btn btn-sm btn-outline-secondary" title="Review">
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

<?php require_once __DIR__ . '/../../backend/core/footer.php'; ?>
