<?php
/**
 * ProjectSphere - Student Dashboard
 */
require_once __DIR__ . '/../../backend/core/db.php';
require_once __DIR__ . '/../../backend/core/auth.php';
require_once __DIR__ . '/../../backend/core/functions.php';

require_student();

$studentId = $_SESSION['profile_id'];

// Fetch Student Profile Data
$sStmt = $pdo->prepare("
    SELECT s.*, u.email, u.username
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    WHERE s.id = :id
    LIMIT 1
");
$sStmt->execute([':id' => $studentId]);
$student = $sStmt->fetch();

// Fetch Submission Counts
$cStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_projects,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count
    FROM projects
    WHERE student_id = :sid
");
$cStmt->execute([':sid' => $studentId]);
$counts = $cStmt->fetch();

$totalProjects = $counts['total_projects'] ?? 0;
$approvedCount = $counts['approved_count'] ?? 0;
$pendingCount  = $counts['pending_count'] ?? 0;
$rejectedCount = $counts['rejected_count'] ?? 0;

// Fetch Recent Projects Submitted by this Student
$recentStmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name,
           e.total_score, r.overall_rank
    FROM projects p
    INNER JOIN project_categories c ON p.category_id = c.id
    LEFT JOIN evaluations e ON p.id = e.project_id
    LEFT JOIN rankings r ON p.id = r.project_id
    WHERE p.student_id = :sid
    ORDER BY p.submitted_at DESC
    LIMIT 5
");
$recentStmt->execute([':sid' => $studentId]);
$recentProjects = $recentStmt->fetchAll();

$pageTitle = 'Student Dashboard - ProjectSphere';
require_once __DIR__ . '/../../backend/core/header.php';
?>

<div class="container-xl py-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../../backend/core/student-navbar.php'; ?>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-9 col-md-8">
            <!-- Welcome Header Card -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%); border-radius: 16px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary text-white mb-2">Student Portal</span>
                        <h2 class="h3 fw-bold text-dark mb-1">Welcome, <?= htmlspecialchars($student['full_name']) ?>!</h2>
                        <p class="text-secondary small mb-0">
                            <strong>Roll No:</strong> <?= htmlspecialchars($student['roll_no']) ?> &bull; 
                            <strong>Branch:</strong> <?= htmlspecialchars($student['department']) ?> &bull; 
                            <?= htmlspecialchars($student['semester']) ?>
                        </p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= base_url('frontend/student/submit-project.php') ?>" class="btn btn-primary shadow-sm">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i>Submit New Project
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="content-card text-center h-100 p-3">
                        <i class="bi bi-folder-fill fs-2 text-primary mb-1 d-block"></i>
                        <div class="h3 fw-bold text-dark mb-0"><?= $totalProjects ?></div>
                        <div class="small text-muted text-uppercase fw-semibold">Submitted</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="content-card text-center h-100 p-3">
                        <i class="bi bi-check-circle-fill fs-2 text-success mb-1 d-block"></i>
                        <div class="h3 fw-bold text-success mb-0"><?= $approvedCount ?></div>
                        <div class="small text-muted text-uppercase fw-semibold">Approved</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="content-card text-center h-100 p-3">
                        <i class="bi bi-hourglass-split fs-2 text-warning mb-1 d-block"></i>
                        <div class="h3 fw-bold text-warning mb-0"><?= $pendingCount ?></div>
                        <div class="small text-muted text-uppercase fw-semibold">Pending Review</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="content-card text-center h-100 p-3">
                        <i class="bi bi-x-circle-fill fs-2 text-danger mb-1 d-block"></i>
                        <div class="h3 fw-bold text-danger mb-0"><?= $rejectedCount ?></div>
                        <div class="small text-muted text-uppercase fw-semibold">Rejected</div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="content-card mb-4 p-3">
                <div class="row g-2">
                    <div class="col-md-3 col-6">
                        <a href="<?= base_url('frontend/student/submit-project.php') ?>" class="btn btn-outline-primary w-100 py-2 small fw-semibold">
                            <i class="bi bi-plus-circle me-1"></i>Submit Project
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= base_url('frontend/student/my-projects.php') ?>" class="btn btn-outline-secondary w-100 py-2 small fw-semibold">
                            <i class="bi bi-journal-text me-1"></i>My Projects
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= base_url('frontend/browse.php') ?>" class="btn btn-outline-secondary w-100 py-2 small fw-semibold">
                            <i class="bi bi-compass me-1"></i>Browse Projects
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="<?= base_url('frontend/student/profile.php') ?>" class="btn btn-outline-secondary w-100 py-2 small fw-semibold">
                            <i class="bi bi-person-gear me-1"></i>My Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- My Recent Submissions -->
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>My Recent Submissions</h5>
                    <a href="<?= base_url('frontend/student/my-projects.php') ?>" class="btn btn-sm btn-link text-decoration-none">View All &rarr;</a>
                </div>

                <?php if (empty($recentProjects)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <h6 class="fw-bold text-secondary">You haven't submitted any projects yet</h6>
                        <p class="small text-muted mb-3">Submit your diploma capstone project to receive faculty evaluation and marks.</p>
                        <a href="<?= base_url('frontend/student/submit-project.php') ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i>Submit Your First Project
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Project Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Marks / Rank</th>
                                    <th>Submitted On</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentProjects as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <a href="<?= base_url('frontend/project-details.php?id=' . $p['id']) ?>" class="text-decoration-none text-dark">
                                                    <?= htmlspecialchars($p['title']) ?>
                                                </a>
                                            </div>
                                            <small class="text-muted"><?= htmlspecialchars(substr($p['short_description'], 0, 50)) ?>...</small>
                                        </td>
                                        <td><span class="category-pill small"><?= htmlspecialchars($p['category_name']) ?></span></td>
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
                                                <span class="text-muted small">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted"><?= date('d M Y', strtotime($p['submitted_at'])) ?></td>
                                        <td class="text-end">
                                            <a href="<?= base_url('frontend/project-details.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($p['status'] === 'pending'): ?>
                                                <a href="<?= base_url('frontend/student/edit-project.php?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
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
