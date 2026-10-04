<?php
/**
 * ProjectSphere - Authentication & Session Management Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        'id'       => $_SESSION['user_id'],
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
function require_student() {
    if (!is_student()) {
        $_SESSION['flash_error'] = 'Please log in with your Student account to access this page.';
        header('Location: ' . base_url('login.php?role=student'));
        exit;
    }
}

/**
 * Protect admin pages
 */
function require_admin() {
    if (!is_admin()) {
        $_SESSION['flash_error'] = 'Administrator or Faculty access required.';
        header('Location: ' . base_url('admin/login.php'));
        exit;
    }
}

/**
 * Protect generic authenticated pages
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please log in to continue.';
        header('Location: ' . base_url('login.php'));
        exit;
    }
}
