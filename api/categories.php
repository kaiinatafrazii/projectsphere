<?php
/**
 * ProjectSphere - Categories REST API Endpoint
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) AS project_count
        FROM project_categories c
        LEFT JOIN projects p ON c.id = p.category_id AND p.status = 'approved'
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    json_response(['success' => true, 'categories' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    require_admin_api($pdo);
    $input = get_json_input();
    $name = trim($input['name'] ?? $_POST['name'] ?? '');
    $desc = trim($input['description'] ?? $_POST['description'] ?? '');
    $icon = trim($input['icon'] ?? $_POST['icon'] ?? 'bi-folder');

    if (empty($name)) json_response(['success' => false, 'message' => 'Category name is required.'], 400);

    $slug = slugify($name);
    try {
        $ins = $pdo->prepare("INSERT INTO project_categories (name, slug, description, icon) VALUES (:name, :slug, :desc, :icon)");
        $ins->execute([':name' => $name, ':slug' => $slug, ':desc' => $desc, ':icon' => $icon]);
        json_response(['success' => true, 'message' => 'Category added successfully.', 'id' => $pdo->lastInsertId()], 201);
    } catch (Exception $e) {
        json_response(['success' => false, 'message' => 'Category already exists or invalid data.'], 409);
    }
}

if ($method === 'PUT') {
    require_admin_api($pdo);
    $input = get_json_input();
    $id   = (int)($_GET['id'] ?? $input['id'] ?? 0);
    $name = trim($input['name'] ?? '');
    $desc = trim($input['description'] ?? '');
    $icon = trim($input['icon'] ?? 'bi-folder');

    if ($id <= 0 || empty($name)) json_response(['success' => false, 'message' => 'Valid ID and name required.'], 400);

    $slug = slugify($name);
    $up = $pdo->prepare("UPDATE project_categories SET name = :name, slug = :slug, description = :desc, icon = :icon WHERE id = :id");
    $up->execute([':name' => $name, ':slug' => $slug, ':desc' => $desc, ':icon' => $icon, ':id' => $id]);
    json_response(['success' => true, 'message' => 'Category updated successfully.']);
}

if ($method === 'DELETE') {
    require_admin_api($pdo);
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) json_response(['success' => false, 'message' => 'Invalid category ID.'], 400);

    $chk = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE category_id = :id");
    $chk->execute([':id' => $id]);
    if ($chk->fetchColumn() > 0) {
        json_response(['success' => false, 'message' => 'Cannot delete category: projects are linked to it.'], 400);
    }

    $pdo->prepare("DELETE FROM project_categories WHERE id = :id")->execute([':id' => $id]);
    json_response(['success' => true, 'message' => 'Category deleted.']);
}
