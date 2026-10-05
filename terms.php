<?php
$pageTitle = 'Terms of Service Draft - ProjectSphere';
$pageDescription = 'Draft terms for students, faculty, and visitors using the ProjectSphere academic portal.';
require_once __DIR__ . '/includes/header.php';
?>
<article class="container legal-page">
    <p class="text-uppercase small fw-bold text-primary">ProjectSphere / Terms</p>
    <h1>Terms of service</h1>
    <p>This draft outlines the portal's intended academic use. It is not a final agreement.</p>
    <div class="legal-draft"><strong>Draft for institutional review</strong><p>Confirm ownership, acceptable-use rules, intellectual-property terms, appeals, and applicable requirements before publication.</p></div>

    <section>
        <h2>Who the portal serves</h2>
        <p>ProjectSphere is built for student project submissions, faculty evaluation, and browsing approved academic work. Access to student and faculty workspaces is controlled by account role.</p>
    </section>
    <section>
        <h2>Submissions and review</h2>
        <p>Students provide project descriptions and supporting files for academic review. Faculty use the published five-part scoring rubric and can provide feedback. Approved project details may appear in the public showcase; private source-code files are intended for authorized faculty review only.</p>
    </section>
    <section>
        <h2>Details requiring approval</h2>
        <p>The repository does not define intellectual-property ownership, acceptable-use enforcement, appeal procedures, service availability, liability terms, or governing law. The institution must decide and approve these terms before the portal is used as a binding service.</p>
    </section>
    <p><a href="<?= base_url('privacy.php') ?>">Read the privacy policy draft</a></p>
</article>
<?php require_once __DIR__ . '/includes/footer.php'; ?>