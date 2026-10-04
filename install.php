<?php
/**
 * ProjectSphere - One-Click Database Setup & System Installer
 * Designed for seamless XAMPP localhost installation & diploma viva evaluation
 */

$host     = '127.0.0.1';
$user     = 'root';
$pass     = '';
$dbname   = 'projectsphere';
$sqlFile  = __DIR__ . '/database/projectsphere.sql';

// 1. Pre-flight Environment Checks
$requirements = [
    'php_version' => [
        'name'     => 'PHP Version (>= 7.4 required, 8.x recommended)',
        'current'  => PHP_VERSION,
        'status'   => version_compare(PHP_VERSION, '7.4.0', '>=')
    ],
    'pdo_mysql' => [
        'name'     => 'PDO MySQL Extension',
        'current'  => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
        'status'   => extension_loaded('pdo_mysql')
    ],
    'upload_dir' => [
        'name'     => 'Uploads Directory Writable',
        'current'  => is_writable(__DIR__ . '/uploads') ? 'Writable' : 'Not Writable (Check Permissions)',
        'status'   => is_writable(__DIR__ . '/uploads') || @mkdir(__DIR__ . '/uploads', 0777, true)
    ],
    'sql_schema' => [
        'name'     => 'Database Schema File Exists',
        'current'  => file_exists($sqlFile) ? 'Found (database/projectsphere.sql)' : 'Missing',
        'status'   => file_exists($sqlFile)
    ]
];

$allRequirementsPassed = !in_array(false, array_column($requirements, 'status'));

$statusMessages = [];
$installed = false;
$error = '';
$tableStats = [];

// Handle Form Submission: Install or Reset Database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? '127.0.0.1');
    $user = trim($_POST['user'] ?? 'root');
    $pass = $_POST['pass'] ?? '';

    if (!$allRequirementsPassed) {
        $error = "Please resolve the system pre-flight checks before proceeding.";
    } elseif (!file_exists($sqlFile)) {
        $error = "SQL file not found at: <code>{$sqlFile}</code>";
    } else {
        try {
            // Step A: Connect to MySQL server
            $pdoInit = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $statusMessages[] = "Connected to MySQL server on {$host} as user '{$user}'.";

            // Step B: Create database if not exists
            $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $statusMessages[] = "Database `{$dbname}` created/verified successfully.";

            // Step C: Connect to target database
            $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Step D: Execute SQL Schema & Seed Data
            $sqlContent = file_get_contents($sqlFile);
            $pdo->exec($sqlContent);
            $statusMessages[] = "Executed all schema definitions, constraints, and seed data.";

            // Step E: Query and confirm table stats
            $tablesStmt = $pdo->query("SHOW TABLES");
            $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $t) {
                $c = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
                $tableStats[$t] = $c;
            }

            $statusMessages[] = "Database installation complete with " . count($tables) . " verified tables.";
            $installed = true;

        } catch (PDOException $e) {
            $error = "Database setup failed: " . $e->getMessage();
        }
    }
} else {
    // Check if database already exists on page load
    try {
        $testPdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
        ]);
        $chk = $testPdo->query("SHOW TABLES");
        if ($chk && $chk->rowCount() > 0) {
            $tables = $chk->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $t) {
                $c = $testPdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
                $tableStats[$t] = $c;
            }
            $statusMessages[] = "Database `{$dbname}` is active with " . count($tables) . " tables loaded.";
            $installed = true;
        }
    } catch (Exception $e) {
        // Not yet initialized
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup & Installer - ProjectSphere</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- ProjectSphere Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<!-- Navbar Banner -->
<nav class="navbar navbar-light bg-white border-bottom shadow-sm py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="assets/images/logo.svg" alt="ProjectSphere Logo" style="height: 36px;">
        </a>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                <i class="bi bi-gear-wide-connected me-1"></i>System Setup Wizard
            </span>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Home</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card border shadow-sm p-4 p-md-5" style="border-radius: 16px;">
                <div class="text-center mb-4">
                    <img src="assets/images/logo.svg" alt="ProjectSphere Logo" style="height: 48px;" class="mb-3">
                    <h2 class="h3 fw-bold mb-1">One-Click Database Setup & Installer</h2>
                    <p class="text-secondary small">
                        Initialize, import, or reset the MySQL database for the ProjectSphere academic portal
                    </p>
                </div>

                <!-- Pre-flight System Checks -->
                <div class="card bg-white border mb-4 rounded-3 p-3">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-shield-check text-primary me-2"></i>1. System Environment Checks
                    </h6>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($requirements as $req): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div>
                                    <span class="fw-semibold"><?= htmlspecialchars($req['name']) ?></span>
                                    <div class="text-muted small">Detected: <?= htmlspecialchars($req['current']) ?></div>
                                </div>
                                <?php if ($req['status']): ?>
                                    <span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>OK</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Failed</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Error Alert -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            <div>
                                <strong>Setup Error:</strong> <?= htmlspecialchars($error) ?>
                            </div>
                        </div>
                        <div class="small mt-2 text-dark">
                            <strong>How to solve:</strong> Open your <strong>XAMPP Control Panel</strong> and verify that the <strong>MySQL</strong> service shows as Running on Port 3306.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Installation Status Display -->
                <?php if ($installed): ?>
                    <div class="alert alert-success border-0 shadow-sm p-4 mb-4" style="border-radius: 12px;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; flex-shrink: 0;">
                                <i class="bi bi-check-lg fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-success">Database Connected & Populated!</h5>
                                <p class="small mb-0 text-secondary">The database <code>projectsphere</code> is ready with all tables, constraints, and demo records.</p>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Table Stats Grid -->
                        <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-table me-1 text-primary"></i>Installed Tables & Record Counts:</h6>
                        <div class="row g-2 mb-3 small">
                            <?php foreach ($tableStats as $tbl => $cnt): ?>
                                <div class="col-sm-4 col-6">
                                    <div class="p-2 bg-white border rounded text-secondary d-flex justify-content-between align-items-center">
                                        <span class="font-monospace fw-semibold"><?= htmlspecialchars($tbl) ?></span>
                                        <span class="badge bg-light text-dark border"><?= $cnt ?> rows</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Default Credentials Card -->
                    <div class="card bg-light border p-4 mb-4 rounded-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-key-fill text-warning me-2"></i>Default Login Credentials (Pre-loaded):</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered bg-white mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Role</th>
                                        <th>Email / Username</th>
                                        <th>Default Password</th>
                                        <th>Assigned Project / Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-danger-subtle text-danger">Faculty Evaluator</span></td>
                                        <td><code>admin@projectsphere.edu</code></td>
                                        <td><code>admin123</code></td>
                                        <td>Prof. Arvind Kulkarni (Admin Panel)</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-primary-subtle text-primary">Student</span></td>
                                        <td><code>rahul@college.edu</code></td>
                                        <td><code>student123</code></td>
                                        <td>Smart Library Management (Rank #1)</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-primary-subtle text-primary">Student</span></td>
                                        <td><code>priya@college.edu</code></td>
                                        <td><code>student123</code></td>
                                        <td>QR Attendance System (Rank #2)</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-primary-subtle text-primary">Student</span></td>
                                        <td><code>vikram@college.edu</code></td>
                                        <td><code>student123</code></td>
                                        <td>Network Packet Sniffer (Rank #3)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <form method="POST" action="install.php" onsubmit="return confirm('Re-installing will reset database tables to fresh seed data. Proceed?');">
                            <input type="hidden" name="host" value="<?= htmlspecialchars($host) ?>">
                            <input type="hidden" name="user" value="<?= htmlspecialchars($user) ?>">
                            <input type="hidden" name="pass" value="<?= htmlspecialchars($pass) ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-arrow-repeat me-1"></i>Reset / Re-import Database
                            </button>
                        </form>

                        <div class="d-flex gap-2">
                            <a href="login.php?role=student" class="btn btn-outline-primary">
                                <i class="bi bi-person me-1"></i>Student Login
                            </a>
                            <a href="admin/login.php" class="btn btn-outline-dark">
                                <i class="bi bi-shield-lock me-1"></i>Faculty Login
                            </a>
                            <a href="index.php" class="btn btn-primary shadow-sm">
                                <i class="bi bi-house-door-fill me-1"></i>Open ProjectSphere &rarr;
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Setup Form -->
                    <form method="POST" action="install.php">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-database-fill-gear me-2 text-primary"></i>2. MySQL Connection Configuration</h6>

                        <div class="mb-3">
                            <label class="form-label">Database Host</label>
                            <input type="text" name="host" class="form-control" value="127.0.0.1" required>
                            <div class="form-text small">Default for XAMPP: <code>127.0.0.1</code> or <code>localhost</code></div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">MySQL Username</label>
                                <input type="text" name="user" class="form-control" value="root" required>
                                <div class="form-text small">Default for XAMPP is <code>root</code>.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">MySQL Password</label>
                                <input type="password" name="pass" class="form-control" placeholder="Leave empty for XAMPP default">
                                <div class="form-text small">Default for XAMPP root is blank (no password).</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Target Database Name</label>
                            <input type="text" class="form-control bg-light" value="projectsphere" disabled>
                            <div class="form-text small">Database will be created automatically if it does not exist.</div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" <?= !$allRequirementsPassed ? 'disabled' : '' ?>>
                                <i class="bi bi-lightning-charge-fill me-2"></i>Install & Populate Database Now
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top text-center text-muted small">
                    ProjectSphere &bull; Diploma Final Year Project &bull; Fully compatible with XAMPP MySQL & Apache
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
