<?php
http_response_code(404);
$pageTitle = 'Page Not Found - ProjectSphere';
$pageDescription = 'The requested ProjectSphere page could not be found.';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container legal-page" aria-labelledby="not-found-title">
    <p class="text-uppercase small fw-bold text-primary">ProjectSphere / 404</p>
    <h1 id="not-found-title">This page is not here.</h1>
    <p>The address may be out of date, or the page may have moved.</p>
    <a class="btn btn-primary" href="<?= base_url() ?>">Return to ProjectSphere</a>
    <a class="btn btn-outline-primary ms-2" href="<?= base_url('browse.php') ?>">Browse projects</a>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>