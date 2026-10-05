<?php
$pageTitle = 'Privacy Policy Draft - ProjectSphere';
$pageDescription = 'Draft privacy information for ProjectSphere student accounts, submissions, evaluations, and session cookies.';
require_once __DIR__ . '/includes/header.php';
?>
<article class="container legal-page">
    <p class="text-uppercase small fw-bold text-primary">ProjectSphere / Privacy</p>
    <h1>Privacy policy</h1>
    <p>This draft describes the information the current portal handles. It is not a final institutional privacy notice.</p>
    <div class="legal-draft"><strong>Draft for institutional review</strong><p>Confirm the institution owner, contact details, retention periods, and applicable requirements before publishing this page.</p></div>

    <section>
        <h2>Information in the portal</h2>
        <p>Student accounts may include a name, email address, roll number, department, semester, and optional phone number. Project submissions can include descriptions, team details, screenshots, documentation, demo links, and a private source-code archive. Faculty accounts create evaluations and feedback.</p>
    </section>
    <section>
        <h2>How it is used</h2>
        <p>The application uses account and submission information to provide sign-in, project review, feedback, approved project showcases, and published rankings. Approved project information and selected evaluation details may be visible to visitors. Source-code archives are intended for authorized faculty review and are not part of the public showcase.</p>
    </section>
    <section>
        <h2>Storage and cookies</h2>
        <p>The PHP application uses a session cookie for signed-in requests. The browser stores a local preference to remember dismissal of the cookie notice. No analytics or advertising service is configured in this application.</p>
    </section>
    <section>
        <h2>Retention and contact</h2>
        <p>The repository does not specify retention periods, a data-request process, or an institutional privacy contact. The institution operating this portal must supply and approve those details before publication. Do not treat this draft as a promise about deletion or retention.</p>
    </section>
    <p><a href="<?= base_url('terms.php') ?>">Read the terms of service draft</a></p>
</article>
<?php require_once __DIR__ . '/includes/footer.php'; ?>