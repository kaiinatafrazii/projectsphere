<?php
/**
 * ProjectSphere - Edit Submitted Project (Allowed only while status is Pending)
 */
require_once __DIR__ . '/../../backend/core/db.php';
require_once __DIR__ . '/../../backend/core/auth.php';
require_once __DIR__ . '/../../backend/core/functions.php';

require_student();

$studentId = $_SESSION['profile_id'];
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($projectId <= 0) {
    header('Location: ' . base_url('frontend/student/my-projects.php'));
    exit;
}

// Fetch project
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id AND student_id = :sid LIMIT 1");
$stmt->execute([':id' => $projectId, ':sid' => $studentId]);
$project = $stmt->fetch();

if (!$project) {
    $_SESSION['flash_error'] = 'Project not found or unauthorized access.';
    header('Location: ' . base_url('frontend/student/my-projects.php'));
    exit;
}

if ($project['status'] !== 'pending') {
    $_SESSION['flash_error'] = 'This project has already been reviewed/evaluated by faculty and can no longer be edited.';
    header('Location: ' . base_url('frontend/student/my-projects.php'));
    exit;
}

// Categories
$categories = $pdo->query("SELECT id, name FROM project_categories ORDER BY name ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security check failed (CSRF token mismatch). Please refresh and try again.';
    }
    $title       = trim($_POST['title'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $shortDesc   = trim($_POST['short_description'] ?? '');
    $problem     = trim($_POST['problem_statement'] ?? '');
    $objectives  = trim($_POST['objectives'] ?? '');
    $features    = trim($_POST['features'] ?? '');
    $technologies= trim($_POST['technologies'] ?? '');
    $demoUrl     = trim($_POST['demo_url'] ?? '');
    $privateRepo = trim($_POST['private_repo'] ?? '');

    if (empty($title)) $errors[] = 'Project title is required.';
    if (empty($categoryId)) $errors[] = 'Please select a category.';
    if (empty($shortDesc)) $errors[] = 'Short description is required.';
    if (empty($problem)) $errors[] = 'Problem statement is required.';
    if (empty($objectives)) $errors[] = 'Objectives are required.';
    if (empty($features)) $errors[] = 'Features description is required.';
    if (empty($technologies)) $errors[] = 'Technologies list is required.';

    $thumbnailPath = $project['thumbnail_image'];
    if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uRes = handle_file_upload($_FILES['thumbnail_image'], __DIR__ . '/../../backend/uploads/project-images/', ['jpg', 'jpeg', 'png', 'webp', 'svg'], 5);
        if ($uRes['success']) {
            $thumbnailPath = 'uploads/project-images/' . $uRes['filename'];
        } else {
            $errors[] = 'Thumbnail upload: ' . $uRes['error'];
        }
    }

    $docPath = $project['documentation_file'];
    if (isset($_FILES['documentation_file']) && $_FILES['documentation_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $dRes = handle_file_upload($_FILES['documentation_file'], __DIR__ . '/../../backend/uploads/documents/', ['pdf', 'doc', 'docx'], 15);
        if ($dRes['success']) {
            $docPath = 'uploads/documents/' . $dRes['filename'];
        } else {
            $errors[] = 'Documentation PDF upload: ' . $dRes['error'];
        }
    }

    $privateCodePath = $project['private_source_code_file'];
    if (isset($_FILES['private_source_code']) && $_FILES['private_source_code']['error'] !== UPLOAD_ERR_NO_FILE) {
        $cRes = handle_file_upload($_FILES['private_source_code'], __DIR__ . '/../../backend/uploads/private-code/', ['zip', 'rar', '7z', 'tar', 'gz'], 50);
        if ($cRes['success']) {
            $privateCodePath = 'uploads/private-code/' . $cRes['filename'];
        } else {
            $errors[] = 'Private code archive: ' . $cRes['error'];
        }
    }

    if (empty($errors)) {
        $updateStmt = $pdo->prepare("
            UPDATE projects SET 
                category_id = :cat_id,
                title = :title,
                short_description = :short_desc,
                problem_statement = :prob,
                objectives = :obj,
                features = :feat,
                technologies = :tech,
                demo_url = :demo,
                thumbnail_image = :thumb,
                documentation_file = :doc,
                private_source_code_file = :code,
                private_source_repo = :repo
            WHERE id = :id AND student_id = :sid
        ");
        $updateStmt->execute([
            ':cat_id'     => $categoryId,
            ':title'      => $title,
            ':short_desc' => $shortDesc,
            ':prob'       => $problem,
            ':obj'        => $objectives,
            ':feat'       => $features,
            ':tech'       => $technologies,
            ':demo'       => $demoUrl,
            ':thumb'      => $thumbnailPath,
            ':doc'        => $docPath,
            ':code'       => $privateCodePath,
            ':repo'       => $privateRepo,
            ':id'         => $projectId,
            ':sid'        => $studentId
        ]);

        $_SESSION['flash_success'] = 'Project updated successfully!';
        header('Location: ' . base_url('frontend/student/my-projects.php'));
        exit;
    }
}

$pageTitle = 'Edit Project - ProjectSphere';
require_once __DIR__ . '/../../backend/core/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../../backend/core/student-navbar.php'; ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Pending Project</h2>
                        <p class="text-secondary small mb-0">Modify submission details prior to faculty committee review.</p>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis p-2">Pending Review</span>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('frontend/student/edit-project.php?id=' . $projectId) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Project Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? $project['title']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? $project['category_id']) == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Short Description</label>
                            <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($_POST['short_description'] ?? $project['short_description']) ?>" maxlength="300" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Technologies Used</label>
                            <input type="text" name="technologies" class="form-control" value="<?= htmlspecialchars($_POST['technologies'] ?? $project['technologies']) ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Problem Statement</label>
                            <textarea name="problem_statement" class="form-control" rows="4" required><?= htmlspecialchars($_POST['problem_statement'] ?? $project['problem_statement']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Objectives</label>
                            <textarea name="objectives" class="form-control" rows="4" required><?= htmlspecialchars($_POST['objectives'] ?? $project['objectives']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Key Features & Modules</label>
                            <textarea name="features" class="form-control" rows="5" required><?= htmlspecialchars($_POST['features'] ?? $project['features']) ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Update Banner Screenshot (Optional)</label>
                            <input type="file" name="thumbnail_image" class="form-control" accept="image/*">
                            <div class="form-text small">Current: <code><?= htmlspecialchars($project['thumbnail_image']) ?></code></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Update Documentation PDF (Optional)</label>
                            <input type="file" name="documentation_file" class="form-control" accept=".pdf,.doc,.docx">
                            <?php if ($project['documentation_file']): ?>
                                <div class="form-text small">Current: <code><?= htmlspecialchars($project['documentation_file']) ?></code></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Demo Video / Live URL</label>
                            <input type="url" name="demo_url" class="form-control" value="<?= htmlspecialchars($_POST['demo_url'] ?? $project['demo_url']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Private Git Repo (Faculty Review Only)</label>
                            <input type="url" name="private_repo" class="form-control" value="<?= htmlspecialchars($_POST['private_repo'] ?? $project['private_source_repo']) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Update Private Source Code Archive (.ZIP)</label>
                            <input type="file" name="private_source_code" class="form-control" accept=".zip,.rar,.tar,.gz,.7z">
                            <?php if ($project['private_source_code_file']): ?>
                                <div class="form-text small text-success"><i class="bi bi-shield-check"></i> Private code archive already attached. Upload a new file only to replace it.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="<?= base_url('frontend/student/my-projects.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../backend/core/footer.php'; ?>
