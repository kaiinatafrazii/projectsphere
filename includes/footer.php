</main>

<aside class="cookie-notice" id="cookieNotice" aria-label="Cookie information" hidden>
    <div>
        <strong>Essential cookies</strong>
        <p>ProjectSphere uses session cookies for sign-in and portal requests. No analytics or advertising cookies are configured. <a href="<?= base_url('privacy.php') ?>">Privacy policy draft</a></p>
    </div>
    <button class="btn btn-primary btn-sm" type="button" id="dismissCookieNotice">Got it</button>
</aside>

<?php if ($isHomePage): ?>
<!-- Footer -->
<footer class="footer-projectsphere">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="<?= base_url('assets/images/logo.svg') ?>" alt="ProjectSphere Logo" style="height: 32px; filter: brightness(0) invert(1);">
                </div>
                <p class="small text-secondary mb-3">
                    A centralized, secure college portal designed for Diploma & Degree Computer Engineering students to submit, showcase, and evaluate academic capstone projects.
                </p>
                <div class="badge bg-dark border border-secondary text-secondary p-2">
                    <i class="bi bi-shield-lock-fill me-1 text-primary"></i>Protected Source Code Policy
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h5>Navigation</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= base_url() ?>">Home Portal</a></li>
                    <li class="mb-2"><a href="<?= base_url('browse.php') ?>">Browse Projects</a></li>
                    <li class="mb-2"><a href="<?= base_url('browse.php?sort=rank') ?>">Leaderboard</a></li>
                    <li class="mb-2"><a href="<?= base_url('student/submit-project.php') ?>">Submit Project</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h5>Portals & Roles</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= base_url('login.php?role=student') ?>"><i class="bi bi-person me-1"></i>Student Login</a></li>
                    <li class="mb-2"><a href="<?= base_url('register.php') ?>"><i class="bi bi-person-plus me-1"></i>Student Registration</a></li>
                    <li class="mb-2"><a href="<?= base_url('admin/login.php') ?>"><i class="bi bi-shield-check me-1"></i>Faculty / Admin Portal</a></li>
                    <li class="mb-2"><a href="<?= base_url('admin/dashboard.php') ?>"><i class="bi bi-clipboard-data me-1"></i>Evaluator Dashboard</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Academic Details</h5>
                <p class="small text-secondary mb-2">
                    <strong>Department:</strong> Computer Engineering<br>
                    <strong>Academic Year:</strong> 2026-2027<br>
                    <strong>Evaluation Criteria:</strong> 100 Marks System (Innovation, Functionality, UI/UX, Tech Stack & Docs)
                </p>
                <div class="text-secondary small mt-3">
                    <i class="bi bi-check2-circle text-success me-1"></i>PHP 8.2 &bull; MySQL &bull; Bootstrap 5
                </div>
                <div class="small mt-3">
                    <a class="d-block mb-2" href="<?= base_url('privacy.php') ?>">Privacy policy draft</a>
                    <a class="d-block" href="<?= base_url('terms.php') ?>">Terms of service draft</a>
                </div>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="row align-items-center small">
            <div class="col-md-6 text-center text-md-start text-secondary">
                &copy; <?= date('Y') ?> <strong>ProjectSphere</strong> &bull; Student Project Showcase & Evaluation Portal.
            </div>
            <div class="col-md-6 text-center text-md-end text-secondary mt-2 mt-md-0">
                Diploma Computer Science Final Year Project
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- ProjectSphere Custom JS -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
