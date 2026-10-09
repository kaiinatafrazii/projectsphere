<?php
/**
 * ProjectSphere - 404 Page Not Found
 */
http_response_code(404);
$pageTitle = 'Page Not Found - ProjectSphere';
$pageDescription = 'The requested ProjectSphere page could not be found.';
require_once __DIR__ . '/../backend/core/header.php';
?>
<section class="container legal-page py-5" aria-labelledby="not-found-title">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <p class="text-uppercase small fw-bold text-primary mb-2">Error 404</p>
            <h1 id="not-found-title" class="display-5 fw-bold mb-3">Page Not Found</h1>
            <p class="text-muted mb-4">The page or resource you are looking for may have been moved, renamed, or is temporarily unavailable.</p>
            <div class="d-flex justify-content-center gap-3">
                <a class="btn btn-primary" href="<?= base_url('frontend/') ?>"><i class="bi bi-house-door me-2"></i>Return to Portal</a>
                <a class="btn btn-outline-primary" href="<?= base_url('frontend/browse.php') ?>"><i class="bi bi-compass me-2"></i>Browse Projects</a>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../backend/core/footer.php'; ?>
