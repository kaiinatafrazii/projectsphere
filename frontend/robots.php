<?php
header('Content-Type: text/plain; charset=UTF-8');

$requestHost = preg_replace('/[^A-Za-z0-9.:-]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
$requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$siteRoot = $requestScheme . $requestHost . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($siteRoot === $requestScheme . $requestHost) {
    $siteRoot .= '';
}
?>
User-agent: *
Allow: /
Disallow: /frontend/admin/
Disallow: /frontend/student/
Disallow: /backend/
Sitemap: <?= htmlspecialchars($siteRoot, ENT_QUOTES, 'UTF-8') ?>/sitemap.php
