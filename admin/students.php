<?php
/**
 * ProjectSphere - Manage Students (Admin)
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$searchTerm = trim($_GET['search'] ?? '');

$sql = "
    SELECT s.*, u.email, u.username, u.status AS user_status,
           COUNT(p.id) AS total_submissions,
           SUM(CASE WHEN p.status = 'approved' THEN 1 ELSE 0 END) AS approved_count
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    LEFT JOIN projects p ON s.id = p.student_id
";

$params = [];
if (!empty($searchTerm)) {
    $sql .= " WHERE (s.full_name LIKE :s1 OR s.roll_no LIKE :s2 OR u.email LIKE :s3)";
    $params[':s1'] = "%{$searchTerm}%";
    $params[':s2'] = "%{$searchTerm}%";
    $params[':s3'] = "%{$searchTerm}%";
}

$sql .= " GROUP BY s.id ORDER BY s.roll_no ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$pageTitle = 'Manage Students - Faculty Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../includes/admin-navbar.php'; ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-people-fill me-2 text-primary"></i>Registered Students</h2>
                        <p class="text-secondary small mb-0">View student rosters, enrollment roll numbers, and submission counts.</p>
                    </div>

                    <form method="GET" action="<?= base_url('admin/students.php') ?>" class="d-flex gap-2 mt-2 mt-md-0">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search student name or roll..." value="<?= htmlspecialchars($searchTerm) ?>" style="width: 220px;">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Department & Sem</th>
                                <th>Contact Email</th>
                                <th class="text-center">Projects</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $st): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border fw-bold"><?= htmlspecialchars($st['roll_no']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($st['full_name']) ?></div>
                                        <div class="text-muted small">@<?= htmlspecialchars($st['username']) ?></div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($st['department']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($st['semester']) ?></div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($st['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($st['email']) ?></a>
                                        <?php if (!empty($st['phone'])): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($st['phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary"><?= $st['total_submissions'] ?> Total</span>
                                        <?php if ($st['approved_count'] > 0): ?>
                                            <span class="badge bg-success-subtle text-success"><?= $st['approved_count'] ?> Approved</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url('admin/projects.php?search=' . urlencode($st['roll_no'])) ?>" class="btn btn-sm btn-outline-primary" title="View Submissions">
                                            <i class="bi bi-folder2-open me-1"></i>Projects
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
