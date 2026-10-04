<?php
/**
 * ProjectSphere - Home Page
 */
$pageTitle = 'ProjectSphere - Student Project Showcase & Evaluation Portal';
require_once __DIR__ . '/includes/header.php';

// Fetch Statistics
$statTotalProjects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$statApproved = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'approved'")->fetchColumn();
$statStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$statCategories = $pdo->query("SELECT COUNT(*) FROM project_categories")->fetchColumn();

// Fetch Top Ranked Projects (Approved with evaluations)
$topRankQuery = "
    SELECT p.*, c.name AS category_name, s.full_name AS student_name, 
           e.total_score, r.overall_rank
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    INNER JOIN evaluations e ON p.id = e.project_id
    INNER JOIN rankings r ON p.id = r.project_id
    WHERE p.status = 'approved' AND e.is_published = 1
    ORDER BY r.overall_rank ASC
    LIMIT 3
";
$topRanked = $pdo->query($topRankQuery)->fetchAll();

// Fetch Featured / Latest Approved Projects
$latestQuery = "
    SELECT p.*, c.name AS category_name, s.full_name AS student_name,
           e.total_score, r.overall_rank
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    INNER JOIN students s ON p.student_id = s.id
    LEFT JOIN evaluations e ON p.id = e.project_id AND e.is_published = 1
    LEFT JOIN rankings r ON p.id = r.project_id
    WHERE p.status = 'approved'
    ORDER BY p.submitted_at DESC
    LIMIT 6
";
$latestProjects = $pdo->query($latestQuery)->fetchAll();

// Fetch Categories with project counts
$catQuery = "
    SELECT c.*, COUNT(p.id) as project_count
    FROM project_categories c
    LEFT JOIN projects p ON c.id = p.category_id AND p.status = 'approved'
    GROUP BY c.id
    ORDER BY c.name ASC
";
$categories = $pdo->query($catQuery)->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-center text-lg-start">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold mb-3">
                    <i class="bi bi-mortarboard-fill me-1"></i> Academic Project Repository & Evaluation
                </span>
                <h1 class="display-4 fw-extrabold lh-tight mb-3">
                    Showcase, Evaluate & Inspire Through <span class="text-gradient">Student Innovation</span>
                </h1>
                <p class="lead text-secondary mb-4">
                    ProjectSphere is a centralized college portal where computer engineering students submit final-year capstone projects, faculty review and award marks, and students learn from approved peer innovations in a secure, code-protected environment.
                </p>
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                    <a href="<?= base_url('student/submit-project.php') ?>" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Submit Your Project
                    </a>
                    <a href="<?= base_url('browse.php') ?>" class="btn btn-outline-secondary btn-lg bg-white">
                        <i class="bi bi-search me-2"></i>Explore Projects
                    </a>
                </div>

                <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-4 mt-4 pt-2 text-secondary small">
                    <div><i class="bi bi-shield-check text-success me-1"></i>Protected Source Code</div>
                    <div><i class="bi bi-award-fill text-warning me-1"></i>100-Mark Rubric</div>
                    <div><i class="bi bi-graph-up-arrow text-primary me-1"></i>Live Rankings</div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="hero-stats-card text-center">
                            <i class="bi bi-folder-fill fs-2 text-primary mb-2 d-block"></i>
                            <div class="stat-number"><?= $statTotalProjects ?></div>
                            <div class="stat-label">Total Projects</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="hero-stats-card text-center">
                            <i class="bi bi-check-circle-fill fs-2 text-success mb-2 d-block"></i>
                            <div class="stat-number"><?= $statApproved ?></div>
                            <div class="stat-label">Approved Projects</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="hero-stats-card text-center">
                            <i class="bi bi-people-fill fs-2 text-info mb-2 d-block"></i>
                            <div class="stat-number"><?= $statStudents ?></div>
                            <div class="stat-label">Registered Students</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="hero-stats-card text-center">
                            <i class="bi bi-diagram-3-fill fs-2 text-warning mb-2 d-block"></i>
                            <div class="stat-number"><?= $statCategories ?></div>
                            <div class="stat-label">Categories</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Top Ranked Projects Showcase (Leaderboard Podium) -->
<?php if (!empty($topRanked)): ?>
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1 rounded-pill fw-bold text-uppercase small">
                    <i class="bi bi-trophy-fill me-1"></i> Academic Excellence
                </span>
                <h2 class="h3 fw-bold mt-2 mb-1">Top Ranked Projects</h2>
                <p class="text-secondary small mb-0">Highest scoring submissions evaluated by the departmental faculty committee.</p>
            </div>
            <a href="<?= base_url('browse.php?sort=rank') ?>" class="btn btn-outline-primary btn-sm mt-3 mt-md-0 fw-semibold">
                View Full Leaderboard &rarr;
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($topRanked as $top): ?>
                <div class="col-md-4">
                    <div class="project-card">
                        <div class="project-card-img-wrapper">
                            <img src="<?= base_url($top['thumbnail_image']) ?>" alt="<?= htmlspecialchars($top['title']) ?>" class="project-card-img">
                            <div class="project-card-badges">
                                <?= get_rank_badge((int)$top['overall_rank']) ?>
                                <span class="badge-score">
                                    <i class="bi bi-star-fill text-warning me-1"></i><?= format_score($top['total_score']) ?>/100
                                </span>
                            </div>
                        </div>
                        <div class="project-card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="category-pill"><?= htmlspecialchars($top['category_name']) ?></span>
                                <span class="text-muted small"><i class="bi bi-person me-1"></i><?= htmlspecialchars($top['student_name']) ?></span>
                            </div>
                            <h3 class="project-card-title">
                                <a href="<?= base_url('project-details.php?id=' . $top['id']) ?>"><?= htmlspecialchars($top['title']) ?></a>
                            </h3>
                            <p class="project-card-desc"><?= htmlspecialchars($top['short_description']) ?></p>
                            
                            <div class="mb-3">
                                <?php 
                                $techs = array_map('trim', explode(',', $top['technologies']));
                                foreach (array_slice($techs, 0, 3) as $tech): 
                                ?>
                                    <span class="tech-tag"><?= htmlspecialchars($tech) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="project-card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= date('M Y', strtotime($top['submitted_at'])) ?></small>
                                <a href="<?= base_url('project-details.php?id=' . $top['id']) ?>" class="btn btn-sm btn-primary">
                                    View Project <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured / Latest Projects Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary px-2.5 py-1 rounded-pill fw-bold text-uppercase small">
                    <i class="bi bi-stars me-1"></i> Fresh Submissions
                </span>
                <h2 class="h3 fw-bold mt-2 mb-1">Approved Projects Showcase</h2>
                <p class="text-secondary small mb-0">Browse through innovative student engineering capstones.</p>
            </div>
            <a href="<?= base_url('browse.php') ?>" class="btn btn-outline-secondary btn-sm mt-3 mt-md-0 fw-semibold bg-white">
                Explore All Projects &rarr;
            </a>
        </div>

        <?php if (empty($latestProjects)): ?>
            <div class="text-center py-5 bg-white rounded-3 border">
                <i class="bi bi-folder-x fs-1 text-muted"></i>
                <p class="text-secondary mt-2">No projects have been approved yet. Be the first to submit!</p>
                <a href="<?= base_url('student/submit-project.php') ?>" class="btn btn-primary btn-sm">Submit Project</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($latestProjects as $proj): ?>
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
                                            <?= format_score($proj['total_score']) ?>/100
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
                                    <a href="<?= base_url('project-details.php?id=' . $proj['id']) ?>"><?= htmlspecialchars($proj['title']) ?></a>
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
                                    <a href="<?= base_url('project-details.php?id=' . $proj['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Browse by Category Section -->
<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="text-center max-w-xl mx-auto mb-5">
            <span class="badge bg-info-subtle text-info-emphasis px-2.5 py-1 rounded-pill fw-bold text-uppercase small">
                <i class="bi bi-grid-fill me-1"></i> Taxonomy
            </span>
            <h2 class="h3 fw-bold mt-2">Explore Projects by Discipline</h2>
            <p class="text-secondary small">Filter computer science projects across domains and technical specializations.</p>
        </div>

        <div class="row g-3">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-4 col-md-6">
                    <a href="<?= base_url('browse.php?category=' . $cat['id']) ?>" class="text-decoration-none">
                        <div class="card border h-100 shadow-sm transition-all p-3 hover-shadow" style="border-radius: 12px;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                    <i class="bi <?= htmlspecialchars($cat['icon']) ?> fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="h6 fw-bold mb-1 text-dark"><?= htmlspecialchars($cat['name']) ?></h5>
                                    <p class="small text-muted mb-0"><?= htmlspecialchars($cat['description']) ?></p>
                                </div>
                                <div>
                                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1.5">
                                        <?= $cat['project_count'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #fff;">
    <div class="container text-center py-4">
        <h2 class="display-6 fw-bold text-white mb-3">Ready to Submit Your Capstone Project?</h2>
        <p class="lead text-light opacity-75 mb-4 mx-auto" style="max-width: 650px;">
            Join your fellow students on ProjectSphere. Upload your documentation, screenshots, and system features for faculty review and institutional ranking.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= base_url('student/submit-project.php') ?>" class="btn btn-primary btn-lg px-4 shadow">
                <i class="bi bi-cloud-arrow-up-fill me-2"></i>Submit Project Now
            </a>
            <a href="<?= base_url('register.php') ?>" class="btn btn-outline-light btn-lg px-4">
                <i class="bi bi-person-plus me-2"></i>Create Student Account
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
