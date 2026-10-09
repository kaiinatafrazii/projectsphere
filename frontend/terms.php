<?php
/**
 * ProjectSphere - Terms of Service
 */
$pageTitle = 'Terms of Service - ProjectSphere';
$pageDescription = 'Terms of service for students, faculty, and visitors using the ProjectSphere academic project showcase and evaluation portal.';
require_once __DIR__ . '/../backend/core/header.php';
?>
<article class="container legal-page">
    <p class="text-uppercase small fw-bold text-primary">ProjectSphere / Terms</p>
    <h1>Terms of Service</h1>
    <p>By accessing or using the ProjectSphere portal, you agree to these terms. If you do not agree, you must discontinue use immediately.</p>

    <section>
        <h2>1. Purpose &amp; Scope</h2>
        <p>ProjectSphere is an academic portal operated by the Department of Computer Engineering for Diploma and Degree students to submit capstone projects, receive faculty evaluations, and participate in published project rankings. It is intended exclusively for educational and institutional use.</p>
    </section>

    <section>
        <h2>2. User Accounts</h2>
        <p><strong>Students</strong> may register using their college credentials (name, roll number, email). Each student account may submit projects, view evaluations, and manage their profile.</p>
        <p><strong>Faculty / Admin</strong> accounts are provisioned by the department and have access to project review, evaluation scoring, category management, and student administration.</p>
        <p>You are responsible for maintaining the confidentiality of your login credentials. Sharing accounts or impersonating another user is strictly prohibited.</p>
    </section>

    <section>
        <h2>3. Acceptable Use</h2>
        <p>All submitted projects must represent genuine student work produced as part of the academic curriculum. Submitting plagiarised, purchased, or fabricated projects violates academic integrity policies and may result in account suspension, project removal, and disciplinary referral.</p>
        <p>Users must not attempt to exploit security vulnerabilities, inject malicious content, or disrupt the portal's availability.</p>
    </section>

    <section>
        <h2>4. Intellectual Property</h2>
        <p>Students retain ownership of their project work, including source code and documentation. By submitting a project, you grant the institution a non-exclusive licence to display approved project details (title, summary, screenshots, evaluation scores) on the public showcase. Private source-code archives remain confidential and are accessible only to authorised faculty evaluators.</p>
    </section>

    <section>
        <h2>5. Evaluation &amp; Rankings</h2>
        <p>Faculty evaluations use a standardised 100-mark rubric. Published scores and rankings are calculated automatically based on evaluation data. The institution reserves the right to modify evaluation criteria, recalculate rankings, or remove projects that violate these terms.</p>
    </section>

    <section>
        <h2>6. Limitation of Liability</h2>
        <p>ProjectSphere is provided &ldquo;as is&rdquo; for academic purposes. The institution is not liable for data loss, service interruption, or any damages arising from portal usage. Regular backups are recommended for all submitted materials.</p>
    </section>

    <section>
        <h2>7. Modifications</h2>
        <p>The institution reserves the right to update these terms at any time. Continued use of the portal after changes constitutes acceptance of the revised terms.</p>
    </section>

    <section>
        <h2>8. Contact</h2>
        <p>For questions regarding these terms, contact the Department of Computer Engineering at <a href="mailto:support@projectsphere.edu">support@projectsphere.edu</a>.</p>
    </section>

    <p class="text-muted small mt-4"><em>Last updated: <?= date('F Y') ?></em></p>
    <p><a href="<?= base_url('frontend/privacy.php') ?>">Read the Privacy Policy &rarr;</a></p>
</article>
<?php require_once __DIR__ . '/../backend/core/footer.php'; ?>