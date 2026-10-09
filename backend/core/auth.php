<?php
/**
 * ProjectSphere - Authentication, Session Security & CSRF Protection Engine
 */

if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    session_start();
}

/**
 * --------------------------------------------------------------------------
 * CSRF Protection Subsystem
 * --------------------------------------------------------------------------
 */

/**
 * Get or generate persistent CSRF token for the active session
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output HTML hidden input containing CSRF token
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate incoming CSRF token (supports $_POST, JSON body, or X-CSRF-TOKEN header)
 */
function validate_csrf(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? null;
        if (!$token && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
    }

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Enforce CSRF token on POST requests; aborts execution if invalid
 */
function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!validate_csrf()) {
            http_response_code(403);
            die('<div style="font-family:sans-serif;max-width:550px;margin:50px auto;padding:24px;border:1px solid #f87171;background:#fef2f2;color:#991b1b;border-radius:12px;text-align:center;">
                <h3 style="margin-top:0;">403 - Invalid Security Token (CSRF)</h3>
                <p>Your session security token has expired or could not be verified. Please refresh the page and try again.</p>
                <a href="javascript:history.back()" style="display:inline-block;padding:8px 16px;background:#dc2626;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;">Go Back &rarr;</a>
            </div>');
        }
    }
}

/**
 * --------------------------------------------------------------------------
 * Rate Limiting Subsystem (Brute-Force & Abuse Mitigation)
 * --------------------------------------------------------------------------
 */

/**
 * Check if an action is currently rate-limited
 */
function is_rate_limited(string $actionKey, int $maxAttempts = 5, int $decaySeconds = 300): bool {
    $now = time();
    $tracker = $_SESSION['rate_limits'][$actionKey] ?? null;

    if (!$tracker) {
        return false;
    }

    // Expired tracker
    if ($now - $tracker['first_attempt'] > $decaySeconds) {
        unset($_SESSION['rate_limits'][$actionKey]);
        return false;
    }

    return $tracker['attempts'] >= $maxAttempts;
}

/**
 * Record a failed attempt under a rate limit key
 */
function record_rate_limit_attempt(string $actionKey): void {
    $now = time();
    if (!isset($_SESSION['rate_limits'][$actionKey])) {
        $_SESSION['rate_limits'][$actionKey] = [
            'attempts'      => 1,
            'first_attempt' => $now
        ];
    } else {
        $_SESSION['rate_limits'][$actionKey]['attempts']++;
    }
}

/**
 * Clear rate limit tracker after successful execution
 */
function clear_rate_limit(string $actionKey): void {
    if (isset($_SESSION['rate_limits'][$actionKey])) {
        unset($_SESSION['rate_limits'][$actionKey]);
    }
}

/**
 * --------------------------------------------------------------------------
 * Authentication & Role Authorization
 * --------------------------------------------------------------------------
 */

/**
 * Check if any user is logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is a student
 */
function is_student(): bool {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

/**
 * Check if the logged-in user is an admin / faculty
 */
function is_admin(): bool {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Fetch current user basic info from session
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => (int)$_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['email'] ?? '',
        'role'     => $_SESSION['role'] ?? '',
        'name'     => $_SESSION['full_name'] ?? 'User',
        'sub_id'   => $_SESSION['profile_id'] ?? null // student_id or admin_id
    ];
}

/**
 * Protect student pages
 */
function require_student(): void {
    if (!is_student()) {
        $_SESSION['flash_error'] = 'Please log in with your Student account to access this page.';
        $url = function_exists('base_url') ? base_url('frontend/login.php?role=student') : '/frontend/login.php?role=student';
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Protect admin pages
 */
function require_admin(): void {
    if (!is_admin()) {
        $_SESSION['flash_error'] = 'Administrator or Faculty access required.';
        $url = function_exists('base_url') ? base_url('frontend/admin/login.php') : '/frontend/admin/login.php';
        header('Location: ' . $url);
        exit;
    }
}

/**
 * Protect generic authenticated pages
 */
function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please log in to continue.';
        $url = function_exists('base_url') ? base_url('frontend/login.php') : '/frontend/login.php';
        header('Location: ' . $url);
        exit;
    }
}
