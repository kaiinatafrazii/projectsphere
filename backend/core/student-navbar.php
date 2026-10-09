<?php
/**
 * ProjectSphere - Student Sidebar Navigation
 * Location: backend/core/student-navbar.php
 */
$currentScript = basename($_SERVER['PHP_SELF']);
?>
<div class="dashboard-sidebar">
    <div class="d-flex align-items-center gap-3 px-2 mb-4 pb-3 border-bottom">
        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
            <i class="bi bi-person-fill fs-4"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Student') ?></h6>
            <span class="badge bg-primary-subtle text-primary small">Student Portal</span>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?= base_url('frontend/student/dashboard.php') ?>" class="sidebar-link <?= ($currentScript == 'dashboard.php') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>Dashboard
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/student/submit-project.php') ?>" class="sidebar-link <?= ($currentScript == 'submit-project.php') ? 'active' : '' ?>">
                <i class="bi bi-cloud-arrow-up"></i>Submit Project
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/student/my-projects.php') ?>" class="sidebar-link <?= in_array($currentScript, ['my-projects.php', 'edit-project.php']) ? 'active' : '' ?>">
                <i class="bi bi-folder2-open"></i>My Projects
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/student/my-evaluations.php') ?>" class="sidebar-link <?= ($currentScript == 'my-evaluations.php') ? 'active' : '' ?>">
                <i class="bi bi-award"></i>My Evaluations &amp; Marks
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/browse.php') ?>" class="sidebar-link">
                <i class="bi bi-compass"></i>Browse Projects
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/student/profile.php') ?>" class="sidebar-link <?= ($currentScript == 'profile.php') ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i>My Profile
            </a>
        </li>
        <li class="mt-4 pt-3 border-top">
            <a href="<?= base_url('frontend/logout.php') ?>" class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-right"></i>Logout
            </a>
        </li>
    </ul>
</div>
