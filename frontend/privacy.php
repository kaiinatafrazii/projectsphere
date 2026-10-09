<?php
/**
 * ProjectSphere - Privacy Policy
 */
$pageTitle = 'Privacy Policy - ProjectSphere';
$pageDescription = 'Privacy policy for ProjectSphere student accounts, project submissions, evaluations, and session cookies.';
require_once __DIR__ . '/../backend/core/header.php';
?>
<article class="container legal-page">
    <p class="text-uppercase small fw-bold text-primary">ProjectSphere / Privacy</p>
    <h1>Privacy Policy</h1>
    <p>This policy explains how ProjectSphere collects, uses, and protects your information when you use our academic project showcase and evaluation portal.</p>

    <section>
        <h2>1. Information We Collect</h2>
        <p><strong>Account Information:</strong> When you register, we collect your full name, email address, college roll number, department, semester, and an optional phone number. Faculty accounts include a name and institutional email.</p>
        <p><strong>Project Submissions:</strong> Students submit project titles, descriptions, team member details, screenshots, documentation files, demo links, and optional private source-code archives for faculty evaluation.</p>
        <p><strong>Evaluation Data:</strong> Faculty evaluators award marks using a 100-point rubric (Innovation, Functionality, UI/UX, Technology, Documentation) and provide written feedback. Published evaluations and rankings may be visible to all users.</p>
    </section>

    <section>
        <h2>2. How We Use Your Information</h2>
        <p>Your information is used to provide sign-in authentication, project review workflows, faculty evaluation, approved project showcases, and published ranking leaderboards. Approved project details (title, summary, screenshots, team, and scores) are publicly visible. Private source-code archives are accessible <strong>only</strong> to authorized faculty evaluators.</p>
    </section>

    <section>
        <h2>3. Data Storage &amp; Security</h2>
        <p>All data is stored on the institution's MySQL database hosted locally. Passwords are hashed using PHP's <code>password_hash()</code> with the <code>PASSWORD_DEFAULT</code> algorithm. Session cookies are configured with <code>HttpOnly</code>, <code>SameSite=Lax</code>, and the <code>Secure</code> flag on HTTPS connections. CSRF tokens protect all form submissions.</p>
    </section>

    <section>
        <h2>4. Cookies</h2>
        <p>ProjectSphere uses a single session cookie (<code>PHPSESSID</code>) for authenticated requests. A local-storage key remembers your cookie notice dismissal. No analytics, advertising, or third-party tracking cookies are used.</p>
    </section>

    <section>
        <h2>5. Data Retention &amp; Deletion</h2>
        <p>Account data and project submissions are retained for the duration of the academic programme. Students may request account deletion by contacting the department administrator at <a href="mailto:support@projectsphere.edu">support@projectsphere.edu</a>. Upon graduation or programme completion, the institution may archive or remove data as per its records retention policy.</p>
    </section>

    <section>
        <h2>6. Third-Party Services</h2>
        <p>This portal loads Bootstrap CSS/JS and Bootstrap Icons from the jsDelivr CDN. No other third-party analytics, advertising, or tracking services are integrated.</p>
    </section>

    <section>
        <h2>7. Contact</h2>
        <p>For questions about this privacy policy or to exercise your data rights, contact the Department of Computer Engineering at <a href="mailto:support@projectsphere.edu">support@projectsphere.edu</a>.</p>
    </section>

    <p class="text-muted small mt-4"><em>Last updated: <?= date('F Y') ?></em></p>
    <p><a href="<?= base_url('frontend/terms.php') ?>">Read the Terms of Service &rarr;</a></p>
</article>
<?php require_once __DIR__ . '/../backend/core/footer.php'; ?>