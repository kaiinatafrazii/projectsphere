<?php
/**
 * ProjectSphere - Global Header
 * Location: backend/core/header.php
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Security Headers
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

// Fetch categories for navbar dropdown
try {
    $catNavStmt = $pdo->query("SELECT id, name, slug FROM project_categories ORDER BY name ASC LIMIT 8");
    $navbarCategories = $catNavStmt->fetchAll();
} catch (Exception $e) {
    $navbarCategories = [];
}

$pageTitle = $pageTitle ?? 'ProjectSphere - Student Project Showcase & Evaluation Portal';
$isHomePage = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php';
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$pageDescription = $pageDescription ?? match ($currentPage) {
    'index.php'           => 'Explore approved student capstone projects, faculty evaluations, and academic rankings on ProjectSphere.',
    'browse.php'          => 'Search approved student capstone projects by title, category, student, and technology.',
    'project-details.php' => 'Explore a student capstone project, its documentation, and published faculty evaluation.',
    'login.php'           => 'Sign in to the ProjectSphere student project and faculty evaluation portal.',
    'register.php'        => 'Create a student account to submit capstone projects and receive faculty feedback.',
    default               => 'ProjectSphere supports student project submissions, faculty evaluation, and approved academic showcases.'
};
$requestHost   = preg_replace('/[^A-Za-z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
$requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$socialPreview = $requestScheme . $requestHost . base_url('frontend/assets/images/social-preview.svg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($requestScheme . $requestHost . ($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($socialPreview, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image:alt" content="ProjectSphere student project showcase and faculty evaluation rubric">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($socialPreview, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('frontend/assets/images/projectsphere-mark.svg') ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom ProjectSphere CSS -->
    <?php
    $cssFile = __DIR__ . '/../../frontend/assets/css/style.css';
    $cssVer  = file_exists($cssFile) ? filemtime($cssFile) : time();
    ?>
    <link rel="stylesheet" href="<?= base_url('frontend/assets/css/style.css?v=' . $cssVer) ?>">
</head>
<body>

<!-- Main Navigation Bar -->
<?php if (empty($hideMainNavigation)): ?>
<nav class="navbar navbar-expand-lg navbar-projectsphere sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('frontend/') ?>">
            <img src="<?= base_url('frontend/assets/images/logo.svg') ?>" alt="ProjectSphere Logo">
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <?php if ($isHomePage): ?>
                <li class="nav-item">
                    <a class="nav-link active" href="#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#about">About</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How it works</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('frontend/') ?>"><i class="bi bi-house-door me-1"></i>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'browse.php') ? 'active' : '' ?>" href="<?= base_url('frontend/browse.php') ?>"><i class="bi bi-compass me-1"></i>Explore Projects</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
                    <ul class="dropdown-menu shadow border-0 py-2">
                        <?php foreach ($navbarCategories as $nCat): ?>
                            <li>
                                <a class="dropdown-item py-1.5" href="<?= base_url('frontend/browse.php?category=' . $nCat['id']) ?>">
                                    <?= htmlspecialchars($nCat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-primary fw-semibold" href="<?= base_url('frontend/browse.php') ?>">
                                Browse All Categories &rarr;
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('frontend/browse.php?sort=rank') ?>">
                        <i class="bi bi-trophy me-1 text-warning"></i>Leaderboard
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Search Quick Bar -->
            <?php if (!$isHomePage): ?>
            <form class="d-flex align-items-center me-3" action="<?= base_url('frontend/browse.php') ?>" method="GET">
                <div class="input-group input-group-sm flex-nowrap" style="width: 210px;">
                    <input class="form-control" type="search" name="search" placeholder="Search projects..." aria-label="Search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit" title="Search"><i class="bi bi-search"></i></button>
                </div>
            </form>
            <?php endif; ?>

            <!-- Authentication User Actions -->
            <div class="d-flex align-items-center gap-2">
                <?php if (is_admin()): ?>
                    <a href="<?= base_url('frontend/admin/dashboard.php') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-speedometer2 me-1"></i>Admin Panel
                    </a>
                    <a href="<?= base_url('frontend/logout.php') ?>" class="btn btn-sm btn-light border text-danger fw-medium" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                <?php elseif (is_student()): ?>
                    <a href="<?= base_url('frontend/student/submit-project.php') ?>" class="btn btn-sm btn-primary fw-semibold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i>Submit Project
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1 text-primary"></i><?= htmlspecialchars($_SESSION['full_name'] ?? 'Student') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                            <li><h6 class="dropdown-header">Student Portal</h6></li>
                            <li><a class="dropdown-item" href="<?= base_url('frontend/student/dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('frontend/student/my-projects.php') ?>"><i class="bi bi-folder2-open me-2"></i>My Projects</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('frontend/student/my-evaluations.php') ?>"><i class="bi bi-award me-2"></i>My Evaluations</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('frontend/student/profile.php') ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= base_url('frontend/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= base_url('frontend/login.php') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                    <a href="<?= base_url('frontend/register.php') ?>" class="btn btn-sm btn-primary fw-semibold">
                        <i class="bi bi-person-plus-fill me-1"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Flash Alerts Notification System -->
<?php if (isset($_SESSION['flash_success']) || isset($_SESSION['flash_error']) || isset($_SESSION['flash_info'])): ?>
    <div class="container mt-3">
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_info'])): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($_SESSION['flash_info']) ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_info']); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<main>
