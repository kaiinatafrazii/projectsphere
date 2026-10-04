<?php
/**
 * ProjectSphere - Search Results Router
 * Aliases to browse.php for centralized search and filtering logic
 */
if (isset($_GET['q']) && !isset($_GET['search'])) {
    $_GET['search'] = $_GET['q'];
}
require_once __DIR__ . '/browse.php';
