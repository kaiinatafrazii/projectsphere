<?php
/**
 * ProjectSphere - Database Connection
 * Configured for XAMPP default settings (root with no password)
 */

$host     = '127.0.0.1';
$dbname   = 'projectsphere';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;border:1px solid #f87171;background:#fef2f2;border-radius:12px;color:#991b1b;">
        <h2 style="margin-top:0;">Database Connection Error</h2>
        <p>Could not connect to MySQL database <strong>' . htmlspecialchars($dbname) . '</strong>.</p>
        <p style="font-size:14px;color:#4b5563;">Error details: ' . htmlspecialchars($e->getMessage()) . '</p>
        <hr style="border:none;border-top:1px solid #fecaca;margin:15px 0;">
        <p style="font-size:13px;margin-bottom:15px;"><strong>How to fix:</strong><br>
        1. Ensure MySQL is running in your <strong>XAMPP Control Panel</strong>.<br>
        2. Click the button below to automatically create and populate the database:</p>
        <a href="install.php" style="display:inline-block;padding:10px 18px;background:#dc2626;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:14px;">Run One-Click Database Installer &rarr;</a>
    </div>');
}
