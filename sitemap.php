<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');

$requestHost = preg_replace('/[^A-Za-z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
$requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$siteRoot = $requestScheme . $requestHost . base_url();
$publicUrls = ['', 'browse.php', 'privacy.php', 'terms.php'];
$projects = $pdo->query("SELECT id FROM projects WHERE status = 'approved' ORDER BY submitted_at DESC")->fetchAll();

foreach ($projects as $project) {
    $publicUrls[] = 'project-details.php?id=' . (int)$project['id'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($publicUrls as $path): ?>
    <url><loc><?= htmlspecialchars($siteRoot . $path, ENT_XML1, 'UTF-8') ?></loc></url>
<?php endforeach; ?>
</urlset>