<?php
/**
 * ProjectSphere - Admin Sidebar Navigation
 * Location: backend/core/admin-navbar.php
 */
$currentScript = basename($_SERVER['PHP_SELF']);

// Count pending projects for notification badge
$pendingBadge = 0;
if (isset($pdo)) {
    try {
        $pStmt = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'pending'");
        $pendingBadge = (int)$pStmt->fetchColumn();
    } catch (Exception $e) {}
}
?>
<div class="dashboard-sidebar">
    <div class="d-flex align-items-center gap-3 px-2 mb-4 pb-3 border-bottom">
        <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
            <i class="bi bi-shield-lock-fill fs-4"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Faculty Admin') ?></h6>
            <span class="badge bg-danger-subtle text-danger small">Evaluator &amp; Admin</span>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?= base_url('frontend/admin/dashboard.php') ?>" class="sidebar-link <?= ($currentScript == 'dashboard.php') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>Dashboard
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/admin/projects.php') ?>" class="sidebar-link <?= in_array($currentScript, ['projects.php', 'review-project.php', 'evaluate.php']) ? 'active' : '' ?>">
                <i class="bi bi-stack"></i>Manage Projects
                <?php if ($pendingBadge > 0): ?>
                    <span class="badge bg-warning text-dark ms-auto"><?= $pendingBadge ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/admin/rankings.php') ?>" class="sidebar-link <?= ($currentScript == 'rankings.php') ? 'active' : '' ?>">
                <i class="bi bi-trophy"></i>Rankings &amp; Scores
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/admin/categories.php') ?>" class="sidebar-link <?= ($currentScript == 'categories.php') ? 'active' : '' ?>">
                <i class="bi bi-tags"></i>Project Categories
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/admin/students.php') ?>" class="sidebar-link <?= ($currentScript == 'students.php') ? 'active' : '' ?>">
                <i class="bi bi-people"></i>Manage Students
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/admin/feedback.php') ?>" class="sidebar-link <?= ($currentScript == 'feedback.php') ? 'active' : '' ?>">
                <i class="bi bi-chat-left-dots"></i>Feedback Management
            </a>
        </li>
        <li>
            <a href="<?= base_url('frontend/admin/profile.php') ?>" class="sidebar-link <?= ($currentScript == 'profile.php') ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i>Faculty Profile
            </a>
        </li>
        <li class="mt-4 pt-3 border-top">
            <a href="<?= base_url('frontend/logout.php') ?>" class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-right"></i>Logout
            </a>
        </li>
    </ul>
</div>
