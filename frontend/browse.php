<?php
/**
 * ProjectSphere - Browse & Search Approved Projects
 */
$pageTitle = 'Browse student projects | ProjectSphere';
$pageDescription = 'Search approved student capstone projects by title, category, student, and technology.';
require_once __DIR__ . '/../backend/core/header.php';

// Get query filters
$searchTerm = trim($_GET['search'] ?? '');
$categoryFilter = !empty($_GET['category']) ? (int)$_GET['category'] : 0;
$techFilter = trim($_GET['tech'] ?? '');
$sortBy = trim($_GET['sort'] ?? 'latest'); // latest, rank, score, title

// Build base SQL for APPROVED projects only
$sql = "
    SELECT p.*, c.name AS category_name, c.icon AS category_icon, 
           s.full_name AS student_name, s.roll_no AS student_roll,
           e.total_score, r.overall_rank, r.category_rank
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    LEFT JOIN evaluations e ON p.id = e.project_id AND e.is_published = 1
    LEFT JOIN rankings r ON p.id = r.project_id
    WHERE p.status = 'approved'
";

$params = [];

if (!empty($searchTerm)) {
    $sql .= " AND (p.title LIKE :search1 OR p.short_description LIKE :search2 OR p.technologies LIKE :search3 OR s.full_name LIKE :search4)";
    $params[':search1'] = "%{$searchTerm}%";
    $params[':search2'] = "%{$searchTerm}%";
    $params[':search3'] = "%{$searchTerm}%";
    $params[':search4'] = "%{$searchTerm}%";
}

if ($categoryFilter > 0) {
    $sql .= " AND p.category_id = :cat_id";
    $params[':cat_id'] = $categoryFilter;
}

if (!empty($techFilter)) {
    $sql .= " AND p.technologies LIKE :tech";
    $params[':tech'] = "%{$techFilter}%";
}

// Order clause
switch ($sortBy) {
    case 'rank':
        $sql .= " ORDER BY (r.overall_rank IS NULL), r.overall_rank ASC, p.submitted_at DESC";
        break;
    case 'score':
        $sql .= " ORDER BY (e.total_score IS NULL), e.total_score DESC, p.submitted_at DESC";
        break;
    case 'title':
        $sql .= " ORDER BY p.title ASC";
        break;
    case 'latest':
    default:
        $sql .= " ORDER BY p.submitted_at DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Fetch all categories for filter dropdown
$allCategories = $pdo->query("SELECT id, name FROM project_categories ORDER BY name ASC")->fetchAll();

// Popular technologies for quick filter pills
$popularTechs = ['PHP', 'MySQL', 'Python', 'Android', 'Java', 'IoT', 'Bootstrap', 'JavaScript'];
?>

<div class="container-xl py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Explore Projects</li>
        </ol>
    </nav>

    <!-- Header Banner -->
    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="h3 fw-bold mb-1">Explore Approved Capstone Projects</h1>
                <p class="text-secondary small mb-0">Browse through accredited diploma computer engineering projects evaluated by faculty.</p>
            </div>
            <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-primary-subtle text-primary p-2 px-3 rounded-pill fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> <?= count($projects) ?> Approved Projects Found
                </span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="card border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="<?= base_url('frontend/browse.php') ?>" class="row g-3">
            <div class="col-lg-4 col-md-6">
                <label class="form-label small text-muted">Search Keywords</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by title, student, or topic..." value="<?= htmlspecialchars($searchTerm) ?>">
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label small text-muted">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($allCategories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($categoryFilter == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label small text-muted">Sort By</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="latest" <?= ($sortBy === 'latest') ? 'selected' : '' ?>>Latest Submissions</option>
                    <option value="rank" <?= ($sortBy === 'rank') ? 'selected' : '' ?>>Rank Leaderboard (Highest First)</option>
                    <option value="score" <?= ($sortBy === 'score') ? 'selected' : '' ?>>Score (100 - 0)</option>
                    <option value="title" <?= ($sortBy === 'title') ? 'selected' : '' ?>>Project Title (A - Z)</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <?php if (!empty($searchTerm) || $categoryFilter > 0 || !empty($techFilter) || $sortBy !== 'latest'): ?>
                    <a href="<?= base_url('frontend/browse.php') ?>" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Quick Tech Filter Pills -->
        <div class="mt-3 pt-2 border-top d-flex flex-wrap align-items-center gap-1">
            <span class="small text-muted me-2">Quick Tech:</span>
            <?php foreach ($popularTechs as $pt): ?>
                <a href="<?= base_url('frontend/browse.php?tech=' . urlencode($pt) . ($categoryFilter ? '&category='.$categoryFilter : '')) ?>" 
                   class="badge text-decoration-none <?= ($techFilter === $pt) ? 'bg-primary text-white' : 'bg-light text-dark border' ?> px-2 py-1">
                    <?= htmlspecialchars($pt) ?>
                </a>
            <?php endforeach; ?>
            <?php if (!empty($techFilter)): ?>
                <a href="<?= base_url('frontend/browse.php') ?>" class="badge bg-danger-subtle text-danger text-decoration-none px-2 py-1 ms-1">
                    <i class="bi bi-x"></i> Clear Tech
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Projects Grid -->
    <?php if (empty($projects)): ?>
        <div class="text-center py-5 bg-white rounded-3 border">
            <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
            <h4 class="fw-bold">No projects matched your criteria</h4>
            <p class="text-secondary small">Try changing search keywords, selecting another category, or resetting filters.</p>
            <a href="<?= base_url('frontend/browse.php') ?>" class="btn btn-outline-primary btn-sm">Reset All Filters</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($projects as $proj): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="project-card">
                        <div class="project-card-img-wrapper">
                            <img src="<?= base_url($proj['thumbnail_image']) ?>" alt="<?= htmlspecialchars($proj['title']) ?>" class="project-card-img">
                            <div class="project-card-badges">
                                <?php if (!empty($proj['overall_rank'])): ?>
                                    <?= get_rank_badge((int)$proj['overall_rank']) ?>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">Approved</span>
                                <?php endif; ?>

                                <?php if (!empty($proj['total_score'])): ?>
                                    <span class="badge-score">
                                        <i class="bi bi-star-fill text-warning me-1"></i><?= format_score($proj['total_score']) ?>/100
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="project-card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="category-pill"><?= htmlspecialchars($proj['category_name']) ?></span>
                                <span class="text-muted small"><i class="bi bi-person me-1"></i><?= htmlspecialchars($proj['student_name']) ?></span>
                            </div>

                            <h3 class="project-card-title">
                                <a href="<?= base_url('frontend/project-details.php?id=' . $proj['id']) ?>"><?= htmlspecialchars($proj['title']) ?></a>
                            </h3>

                            <p class="project-card-desc"><?= htmlspecialchars($proj['short_description']) ?></p>

                            <div class="mb-3">
                                <?php 
                                $tList = array_map('trim', explode(',', $proj['technologies']));
                                foreach (array_slice($tList, 0, 3) as $t): 
                                ?>
                                    <span class="tech-tag"><?= htmlspecialchars($t) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="project-card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= date('M d, Y', strtotime($proj['submitted_at'])) ?></small>
                                <a href="<?= base_url('frontend/project-details.php?id=' . $proj['id']) ?>" class="btn btn-sm btn-primary">
                                    View Project <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../backend/core/footer.php'; ?>
