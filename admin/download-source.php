<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$projectId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$projectId) {
    http_response_code(404);
    exit('File not found.');
}

$stmt = $pdo->prepare('SELECT private_source_code_file FROM projects WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $projectId]);
$project = $stmt->fetch();

$storageRoot = realpath(__DIR__ . '/../uploads/private-code');
$filename = basename(str_replace('\\', '/', $project['private_source_code_file'] ?? ''));
$filePath = $storageRoot ? realpath($storageRoot . DIRECTORY_SEPARATOR . $filename) : false;

if (!$storageRoot || !$filePath || !is_file($filePath) || strpos($filePath, $storageRoot . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(404);
    exit('File not found.');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
readfile($filePath);
exit;