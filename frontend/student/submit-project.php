<?php
/**
 * ProjectSphere - Project Submission Form
 */
require_once __DIR__ . '/../../backend/core/db.php';
require_once __DIR__ . '/../../backend/core/auth.php';
require_once __DIR__ . '/../../backend/core/functions.php';

require_student();

$studentId = $_SESSION['profile_id'];

// Fetch categories
$categories = $pdo->query("SELECT id, name FROM project_categories ORDER BY name ASC")->fetchAll();

$errors = [];
$formData = [
    'title'             => '',
    'category_id'       => '',
    'short_description' => '',
    'problem_statement' => '',
    'objectives'        => '',
    'features'          => '',
    'technologies'      => '',
    'demo_url'          => '',
    'private_repo'      => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Security check failed (CSRF token mismatch). Please refresh and try again.';
    }
    $formData['title']             = trim($_POST['title'] ?? '');
    $formData['category_id']       = (int)($_POST['category_id'] ?? 0);
    $formData['short_description'] = trim($_POST['short_description'] ?? '');
    $formData['problem_statement'] = trim($_POST['problem_statement'] ?? '');
    $formData['objectives']        = trim($_POST['objectives'] ?? '');
    $formData['features']          = trim($_POST['features'] ?? '');
    $formData['technologies']      = trim($_POST['technologies'] ?? '');
    $formData['demo_url']          = trim($_POST['demo_url'] ?? '');
    $formData['private_repo']      = trim($_POST['private_repo'] ?? '');

    // Validation
    if (empty($formData['title'])) $errors[] = 'Project title is required.';
    if (empty($formData['category_id'])) $errors[] = 'Please select a valid project category.';
    if (empty($formData['short_description'])) $errors[] = 'Short description is required.';
    if (empty($formData['problem_statement'])) $errors[] = 'Problem statement is required.';
    if (empty($formData['objectives'])) $errors[] = 'Project objectives are required.';
    if (empty($formData['features'])) $errors[] = 'Features and modules description is required.';
    if (empty($formData['technologies'])) $errors[] = 'Please list the technologies used (e.g. PHP, MySQL, Bootstrap).';

    // Handle Primary Thumbnail Screenshot
    $thumbnailPath = 'assets/images/project-default.svg';
    if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = handle_file_upload(
            $_FILES['thumbnail_image'], 
            __DIR__ . '/../../backend/uploads/project-images/', 
            ['jpg', 'jpeg', 'png', 'webp', 'svg'],
            5
        );
        if ($uploadResult['success']) {
            $thumbnailPath = 'uploads/project-images/' . $uploadResult['filename'];
        } else {
            $errors[] = 'Thumbnail upload: ' . $uploadResult['error'];
        }
    }

    // Handle Project Documentation PDF
    $docFilePath = null;
    if (isset($_FILES['documentation_file']) && $_FILES['documentation_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $docUpload = handle_file_upload(
            $_FILES['documentation_file'], 
            __DIR__ . '/../../backend/uploads/documents/', 
            ['pdf', 'docx', 'doc'], 
            15
        );
        if ($docUpload['success']) {
            $docFilePath = 'uploads/documents/' . $docUpload['filename'];
        } else {
            $errors[] = 'Documentation PDF upload: ' . $docUpload['error'];
        }
    }

    // Handle Private Source Code ZIP (Teacher review only)
    $privateCodePath = null;
    if (isset($_FILES['private_source_code']) && $_FILES['private_source_code']['error'] !== UPLOAD_ERR_NO_FILE) {
        $codeUpload = handle_file_upload(
            $_FILES['private_source_code'], 
            __DIR__ . '/../../backend/uploads/private-code/', 
            ['zip', 'rar', '7z', 'tar', 'gz'], 
            50
        );
        if ($codeUpload['success']) {
            $privateCodePath = 'uploads/private-code/' . $codeUpload['filename'];
        } else {
            $errors[] = 'Private code archive upload: ' . $codeUpload['error'];
        }
    }

    // If no validation errors, proceed to insert
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $slug = slugify($formData['title']) . '-' . rand(100, 999);

            // 1. Insert Project
            $stmt = $pdo->prepare("
                INSERT INTO projects (
                    student_id, category_id, title, slug, short_description, 
                    problem_statement, objectives, features, technologies, 
                    demo_url, thumbnail_image, documentation_file, 
                    private_source_code_file, private_source_repo, status
                ) VALUES (
                    :student_id, :cat_id, :title, :slug, :short_desc,
                    :prob, :obj, :feat, :tech,
                    :demo, :thumb, :doc,
                    :code, :repo, 'pending'
                )
            ");
            $stmt->execute([
                ':student_id' => $studentId,
                ':cat_id'     => $formData['category_id'],
                ':title'      => $formData['title'],
                ':slug'       => $slug,
                ':short_desc' => $formData['short_description'],
                ':prob'       => $formData['problem_statement'],
                ':obj'        => $formData['objectives'],
                ':feat'       => $formData['features'],
                ':tech'       => $formData['technologies'],
                ':demo'       => $formData['demo_url'],
                ':thumb'      => $thumbnailPath,
                ':doc'        => $docFilePath,
                ':code'       => $privateCodePath,
                ':repo'       => $formData['private_repo']
            ]);
            $newProjectId = $pdo->lastInsertId();

            // 2. Insert Additional Team Members
            $memberNames = $_POST['member_names'] ?? [];
            $memberRolls = $_POST['member_rolls'] ?? [];
            $memberRoles = $_POST['member_roles'] ?? [];

            if (!empty($memberNames)) {
                $teamStmt = $pdo->prepare("
                    INSERT INTO project_team_members (project_id, member_name, roll_no, role)
                    VALUES (:pid, :name, :roll, :role)
                ");
                for ($i = 0; $i < count($memberNames); $i++) {
                    $mName = trim($memberNames[$i] ?? '');
                    $mRoll = trim($memberRolls[$i] ?? '');
                    $mRole = trim($memberRoles[$i] ?? 'Member');
                    if (!empty($mName) && !empty($mRoll)) {
                        $teamStmt->execute([
                            ':pid'  => $newProjectId,
                            ':name' => $mName,
                            ':roll' => $mRoll,
                            ':role' => $mRole
                        ]);
                    }
                }
            }

            // 3. Handle Optional Additional Screenshots
            if (isset($_FILES['additional_screenshots'])) {
                $files = $_FILES['additional_screenshots'];
                $imgStmt = $pdo->prepare("INSERT INTO project_images (project_id, image_path, caption) VALUES (:pid, :path, :caption)");

                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $singleFile = [
                            'name'     => $files['name'][$i],
                            'type'     => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error'    => $files['error'][$i],
                            'size'     => $files['size'][$i]
                        ];
                        $addUpload = handle_file_upload($singleFile, __DIR__ . '/../../backend/uploads/project-images/', ['jpg', 'jpeg', 'png', 'webp', 'svg'], 5);
                        if ($addUpload['success']) {
                            $imgStmt->execute([
                                ':pid'     => $newProjectId,
                                ':path'    => 'uploads/project-images/' . $addUpload['filename'],
                                ':caption' => 'Screenshot ' . ($i + 1)
                            ]);
                        }
                    }
                }
            }

            $pdo->commit();

            $_SESSION['flash_success'] = 'Project submitted successfully! It is now pending faculty evaluation.';
            header('Location: ' . base_url('frontend/student/my-projects.php'));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Submission failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Submit Project - ProjectSphere';
require_once __DIR__ . '/../../backend/core/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 mb-4">
            <?php require_once __DIR__ . '/../../backend/core/student-navbar.php'; ?>
        </div>

        <!-- Submission Form Area -->
        <div class="col-lg-9 col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-cloud-arrow-up-fill me-2 text-primary"></i>Submit Academic Capstone Project</h2>
                        <p class="text-secondary small mb-0">Fill in all details carefully. You can edit this project until faculty evaluates it.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary p-2">Diploma Evaluation</span>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Submission Incomplete:</h6>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('frontend/student/submit-project.php') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <!-- Section 1: Basic Info -->
                    <h5 class="fw-bold text-dark mt-2 mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>1. Basic Project Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Project Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Smart Library Management System" value="<?= htmlspecialchars($formData['title']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Project Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($formData['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Short Summary / Tagline <span class="text-danger">*</span></label>
                            <input type="text" name="short_description" class="form-control" placeholder="Brief 1-2 sentence elevator pitch of the project" value="<?= htmlspecialchars($formData['short_description']) ?>" maxlength="300" required>
                            <div class="form-text">Max 300 characters. Displayed on public project cards.</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Technologies Used <span class="text-danger">*</span></label>
                            <input type="text" name="technologies" class="form-control" placeholder="e.g. PHP 8.2, MySQL, HTML5, CSS3, Bootstrap 5, JavaScript" value="<?= htmlspecialchars($formData['technologies']) ?>" required>
                            <div class="form-text">Separate technologies with commas.</div>
                        </div>
                    </div>

                    <!-- Section 2: In-Depth Documentation Content -->
                    <h5 class="fw-bold text-dark mt-4 mb-3"><i class="bi bi-file-text me-2 text-primary"></i>2. Project Report Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">Problem Statement <span class="text-danger">*</span></label>
                            <textarea name="problem_statement" class="form-control" rows="4" placeholder="What existing real-world problem or inefficiency does this project solve?" required><?= htmlspecialchars($formData['problem_statement']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Project Objectives <span class="text-danger">*</span></label>
                            <textarea name="objectives" class="form-control" rows="4" placeholder="List the specific goals and objectives achieved by the project..." required><?= htmlspecialchars($formData['objectives']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Key Features & Modules <span class="text-danger">*</span></label>
                            <textarea name="features" class="form-control" rows="5" placeholder="Bullet points detailing the core modules (e.g. Admin Module, Student Module, Reports, Calculation Engine)..." required><?= htmlspecialchars($formData['features']) ?></textarea>
                        </div>
                    </div>

                    <!-- Section 3: Team Members -->
                    <h5 class="fw-bold text-dark mt-4 mb-3"><i class="bi bi-people me-2 text-primary"></i>3. Project Team Members</h5>
                    <div class="p-3 bg-light rounded-3 mb-4 border">
                        <div class="small text-muted mb-3">
                            <i class="bi bi-info-circle-fill text-primary me-1"></i>
                            <strong>Project Lead:</strong> <?= htmlspecialchars($_SESSION['full_name'] ?? 'You') ?> (<?= htmlspecialchars($_SESSION['roll_no'] ?? '') ?>) is registered as the primary submitter. Add other team members below:
                        </div>

                        <div id="teamMembersContainer">
                            <!-- Dynamically added member rows will append here -->
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="addTeamMemberBtn">
                            <i class="bi bi-person-plus-fill me-1"></i>+ Add Team Member
                        </button>
                    </div>

                    <!-- Section 4: Media & Uploads -->
                    <h5 class="fw-bold text-dark mt-4 mb-3"><i class="bi bi-image me-2 text-primary"></i>4. Media, Documentation & Demonstration</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Primary Screenshot / Banner Image</label>
                            <input type="file" name="thumbnail_image" id="thumbnail_image" class="form-control" accept="image/*">
                            <div class="form-text">JPG, PNG, WebP (Max 5MB). Used on project cards.</div>
                            <img id="imagePreview" src="#" alt="Preview" class="img-thumbnail mt-2" style="display:none; max-height: 150px;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Project Documentation / Synopsis (PDF)</label>
                            <input type="file" name="documentation_file" class="form-control" accept=".pdf,.doc,.docx">
                            <div class="form-text">PDF format recommended (Max 15MB).</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Demo Video URL or Live Hosted Link</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-play-btn"></i></span>
                                <input type="url" name="demo_url" class="form-control" placeholder="https://youtube.com/watch?v=... or https://demo.com" value="<?= htmlspecialchars($formData['demo_url']) ?>">
                            </div>
                            <div class="form-text">Optional link to YouTube video or working website.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Additional Screenshots (Optional)</label>
                            <input type="file" name="additional_screenshots[]" class="form-control" accept="image/*" multiple>
                            <div class="form-text">Hold Ctrl to select multiple screenshots for the gallery.</div>
                        </div>
                    </div>

                    <!-- Section 5: Private Code Submission (Faculty Review Only) -->
                    <h5 class="fw-bold text-dark mt-4 mb-3"><i class="bi bi-shield-lock me-2 text-warning"></i>5. Private Source Code (Faculty Evaluation Only)</h5>
                    <div class="p-3 rounded-3 mb-4 border bg-warning-subtle text-dark">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-check fs-4 text-warning-emphasis"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Strict Confidentiality Assurance</h6>
                                <p class="small text-muted mb-2">
                                    In accordance with college examination rules, source code submitted here is <strong>strictly private</strong>. It is accessible solely to authorized faculty evaluators and <strong>never visible or downloadable by other students or the general public</strong>.
                                </p>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Source Code Archive (.ZIP)</label>
                                <input type="file" name="private_source_code" class="form-control bg-white" accept=".zip,.rar,.tar,.gz,.7z">
                                <div class="form-text">Upload project ZIP containing clean source code (Max 50MB).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Private Git Repository (Optional)</label>
                                <input type="url" name="private_repo" class="form-control bg-white" placeholder="https://github.com/your-username/private-repo" value="<?= htmlspecialchars($formData['private_repo']) ?>">
                                <div class="form-text">Provide invite or access notes if applicable.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                        <a href="<?= base_url('frontend/student/dashboard.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-send-check-fill me-2"></i>Submit Project for Evaluation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../backend/core/footer.php'; ?>
