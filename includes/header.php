<?php
/**
 * ProjectSphere - Global Header
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

// Fetch categories for navbar dropdown
try {
    $catNavStmt = $pdo->query("SELECT id, name, slug FROM project_categories ORDER BY name ASC LIMIT 8");
    $navbarCategories = $catNavStmt->fetchAll();
} catch (Exception $e) {
    $navbarCategories = [];
}

$pageTitle = $pageTitle ?? 'ProjectSphere - Student Project Showcase & Evaluation Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ProjectSphere is an academic portal for submitting, evaluating, ranking, and showcasing student diploma/degree projects.">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom ProjectSphere CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<!-- Main Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-projectsphere sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url() ?>">
            <img src="<?= base_url('assets/images/logo.svg') ?>" alt="ProjectSphere Logo">
        </a>
        
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>" href="<?= base_url() ?>">
                        <i class="bi bi-house-door me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'browse.php') ? 'active' : '' ?>" href="<?= base_url('browse.php') ?>">
                        <i class="bi bi-compass me-1"></i>Explore Projects
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
                    <ul class="dropdown-menu shadow border-0 py-2">
                        <?php foreach ($navbarCategories as $nCat): ?>
                            <li>
                                <a class="dropdown-item py-1.5" href="<?= base_url('browse.php?category=' . $nCat['id']) ?>">
                                    <?= htmlspecialchars($nCat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-primary fw-semibold" href="<?= base_url('browse.php') ?>">
                                Browse All Categories &rarr;
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('browse.php?sort=rank') ?>">
                        <i class="bi bi-trophy me-1 text-warning"></i>Leaderboard
                    </a>
                </li>
            </ul>

            <!-- Search Quick Bar -->
            <form class="d-flex me-3" action="<?= base_url('browse.php') ?>" method="GET">
                <div class="input-group input-group-sm">
                    <input class="form-control" type="search" name="search" placeholder="Search projects..." aria-label="Search" style="width: 180px;">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <!-- Authentication User Actions -->
            <div class="d-flex align-items-center gap-2">
                <?php if (is_admin()): ?>
                    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-speedometer2 me-1"></i>Admin Panel
                    </a>
                    <a href="<?= base_url('logout.php') ?>" class="btn btn-sm btn-light border text-danger fw-medium" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                <?php elseif (is_student()): ?>
                    <a href="<?= base_url('student/submit-project.php') ?>" class="btn btn-sm btn-primary fw-semibold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i>Submit Project
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1 text-primary"></i><?= htmlspecialchars($_SESSION['full_name'] ?? 'Student') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                            <li><h6 class="dropdown-header">Student Portal</h6></li>
                            <li><a class="dropdown-item" href="<?= base_url('student/dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('student/my-projects.php') ?>"><i class="bi bi-folder2-open me-2"></i>My Projects</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('student/my-evaluations.php') ?>"><i class="bi bi-award me-2"></i>My Evaluations</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('student/profile.php') ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= base_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= base_url('login.php') ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                    <a href="<?= base_url('register.php') ?>" class="btn btn-sm btn-primary fw-semibold">
                        <i class="bi bi-person-plus-fill me-1"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

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
