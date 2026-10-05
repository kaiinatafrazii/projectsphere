<?php
/**
 * ProjectSphere - Home Page
 */
$pageTitle = 'ProjectSphere | Student project showcase';
$pageDescription = 'Explore approved student capstone projects, faculty evaluations, and academic rankings on ProjectSphere.';
require_once __DIR__ . '/includes/header.php';

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

<section class="hero-section" id="home">
    <div class="container home-hero-layout">
        <div class="home-hero-copy">
            <p class="home-eyebrow">ProjectSphere / Student work, in focus</p>
            <h1>Good work deserves a clear place to be seen.</h1>
            <p class="home-hero-intro">A college project portal for students to share capstones, faculty to review them consistently, and peers to learn from approved work.</p>
            <div class="home-hero-actions">
                <a href="<?= base_url('browse.php') ?>" class="btn btn-primary btn-lg">Explore student projects</a>
                <a href="<?= base_url(is_student() ? 'student/submit-project.php' : 'login.php') ?>" class="home-secondary-link">Submit a project <span aria-hidden="true">&#8599;</span></a>
            </div>
            <p class="home-hero-note">Approved project showcases. Private source code stays restricted to faculty.</p>
        </div>

        <div class="rubric-preview" aria-label="Faculty evaluation rubric framework, totaling 100 points">
            <div class="rubric-preview-header">
                <div><p class="preview-kicker">Faculty evaluation</p><h2>One shared rubric</h2></div>
                <span class="rubric-total">100 <small>points</small></span>
            </div>
            <div class="rubric-rows">
                <div class="rubric-row"><span>Innovation</span><strong>20</strong><span class="rubric-line"><i style="width:66.67%"></i></span></div>
                <div class="rubric-row"><span>Functionality</span><strong>30</strong><span class="rubric-line"><i style="width:100%"></i></span></div>
                <div class="rubric-row"><span>UI &amp; experience</span><strong>20</strong><span class="rubric-line"><i style="width:66.67%"></i></span></div>
                <div class="rubric-row"><span>Technology use</span><strong>15</strong><span class="rubric-line"><i style="width:50%"></i></span></div>
                <div class="rubric-row"><span>Docs &amp; presentation</span><strong>15</strong><span class="rubric-line"><i style="width:50%"></i></span></div>
            </div>
            <p class="rubric-caption">The published faculty scoring framework</p>
        </div>
    </div>
</section>

<section class="home-content-section" id="about">
    <div class="container home-content-layout">
        <p class="home-eyebrow">About ProjectSphere</p>
        <div>
            <h2>A shared place for student capstones and faculty review.</h2>
            <p>Students submit their academic projects and supporting materials. Faculty review each submission using a common scoring rubric. Approved projects become a reference point for the wider student community.</p>
        </div>
    </div>
</section>

<section class="home-content-section home-features-section" id="features">
    <div class="container">
        <div class="home-section-heading">
            <p class="home-eyebrow">Features</p>
            <h2>Everything follows the project lifecycle.</h2>
        </div>
        <div class="home-feature-list">
            <article><span>01</span><div><h3>Project submissions</h3><p>Keep project details, screenshots, documentation, and team information together for review.</p></div></article>
            <article><span>02</span><div><h3>Consistent faculty evaluation</h3><p>Assess innovation, functionality, interface, technology, and documentation and presentation across a 100-point rubric.</p></div></article>
            <article><span>03</span><div><h3>Approved project showcase</h3><p>Browse published student work and rankings while source-code archives remain restricted to faculty.</p></div></article>
        </div>
    </div>
</section>

<section class="workflow-band" id="how-it-works" aria-label="Project review workflow">
    <div class="container workflow-layout">
        <div><span>01</span><p>Students submit a capstone and its documentation.</p></div>
        <div><span>02</span><p>Faculty assess it against the shared rubric.</p></div>
        <div><span>03</span><p>Approved work joins the public showcase.</p></div>
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
<section class="py-5" id="contact" style="background: #e8eee9; color: #222821;">
    <div class="container text-center py-4">
        <p class="home-eyebrow">Contact</p>
        <h2 class="h2 fw-bold mb-3">Need help with portal access?</h2>
        <p class="text-secondary mb-4 mx-auto" style="max-width: 650px;">
            For account or project-review questions, contact your department project coordinator. Sign in to track or manage your submission.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= base_url('login.php') ?>" class="btn btn-primary btn-lg px-4">Sign in to ProjectSphere</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
