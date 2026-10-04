<?php
/**
 * ProjectSphere - Manage Project Categories (Admin)
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$errors = [];
$success = '';

// Handle Category CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-folder');

        if (empty($name)) {
            $errors[] = 'Category name is required.';
        } else {
            $slug = slugify($name);
            try {
                $stmt = $pdo->prepare("INSERT INTO project_categories (name, slug, description, icon) VALUES (:name, :slug, :desc, :icon)");
                $stmt->execute([':name' => $name, ':slug' => $slug, ':desc' => $description, ':icon' => $icon]);
                $_SESSION['flash_success'] = 'Category added successfully!';
                header('Location: ' . base_url('admin/categories.php'));
                exit;
            } catch (Exception $e) {
                $errors[] = 'Failed to create category. Ensure the name is unique.';
            }
        }
    } elseif ($action === 'edit') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-folder');

        if ($catId > 0 && !empty($name)) {
            $slug = slugify($name);
            try {
                $stmt = $pdo->prepare("UPDATE project_categories SET name = :name, slug = :slug, description = :desc, icon = :icon WHERE id = :id");
                $stmt->execute([':name' => $name, ':slug' => $slug, ':desc' => $description, ':icon' => $icon, ':id' => $catId]);
                $_SESSION['flash_success'] = 'Category updated successfully!';
                header('Location: ' . base_url('admin/categories.php'));
                exit;
            } catch (Exception $e) {
                $errors[] = 'Failed to update category: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $catId = (int)($_POST['category_id'] ?? 0);
        if ($catId > 0) {
            // Check if projects exist under this category
            $pCount = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE category_id = :id");
            $pCount->execute([':id' => $catId]);
            if ($pCount->fetchColumn() > 0) {
                $_SESSION['flash_error'] = 'Cannot delete category: projects are currently linked to it. Reassign or delete those projects first.';
            } else {
                $pdo->prepare("DELETE FROM project_categories WHERE id = :id")->execute([':id' => $catId]);
                $_SESSION['flash_success'] = 'Category deleted successfully.';
            }
            header('Location: ' . base_url('admin/categories.php'));
            exit;
        }
    }
}

// Fetch categories with project count
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS project_count
    FROM project_categories c
    LEFT JOIN projects p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll();

$pageTitle = 'Manage Categories - Faculty Admin';
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
                        <h2 class="h4 fw-bold mb-1"><i class="bi bi-tags-fill me-2 text-primary"></i>Manage Project Categories</h2>
                        <p class="text-secondary small mb-0">Organize project domains and taxonomies for student submissions.</p>
                    </div>

                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle-fill me-1"></i>Add New Category
                    </button>
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

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Icon</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Total Projects</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td>
                                        <div class="rounded-3 bg-light text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi <?= htmlspecialchars($cat['icon']) ?> fs-5"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-dark"><?= htmlspecialchars($cat['name']) ?></strong>
                                        <div class="text-muted small">slug: <code><?= htmlspecialchars($cat['slug']) ?></code></div>
                                    </td>
                                    <td class="text-secondary" style="max-width: 300px;"><?= htmlspecialchars($cat['description']) ?></td>
                                    <td>
                                        <span class="badge bg-light text-secondary border px-2.5 py-1.5"><?= $cat['project_count'] ?> Projects</span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $cat['id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($cat['project_count'] == 0): ?>
                                            <form method="POST" action="<?= base_url('admin/categories.php') ?>" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light border text-muted" disabled title="Cannot delete: has linked projects">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?= $cat['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="<?= base_url('admin/categories.php') ?>">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Category</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Category Name</label>
                                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['name']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Icon Class (Bootstrap Icons)</label>
                                                        <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($cat['icon']) ?>">
                                                        <div class="form-text small">e.g. bi-globe, bi-phone, bi-cpu, bi-shield-check</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($cat['description']) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= base_url('admin/categories.php') ?>">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Artificial Intelligence & Robotics" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon Class (Bootstrap Icons)</label>
                        <input type="text" name="icon" class="form-control" placeholder="e.g. bi-robot or bi-motherboard" value="bi-folder">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief scope of projects under this domain..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
