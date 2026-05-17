<?php
// ─────────────────────────────────────────────────────────────
//  MentorBridge — Configuration
//  Auto-detects the base path so the project works on any host
//  (InfinityFree, localhost, cPanel shared hosting, etc.)
// ─────────────────────────────────────────────────────────────

// ── Database ─────────────────────────────────────────────────
// Update these with your InfinityFree database credentials
define('DB_HOST', 'sql200.infinityfree.com');   // InfinityFree MySQL host (check your panel)
define('DB_PORT', '3306');
define('DB_USER', 'your_db_username');           // e.g. if123456_mentorbridge
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');               // e.g. if123456_mentorbridge

// ── Base URL (auto-detected) ──────────────────────────────────
// Figures out the subfolder the project lives in automatically.
// e.g. if deployed at http://example.epizy.com/mentorbridge/
// BASE_URL will be "/mentorbridge"
// Works equally for root-level deploys (BASE_URL = "")
if (!defined('BASE_URL')) {
    $scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
    $docRoot    = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));

    // Walk up from current file to find project root (one level above /public or /actions)
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));

    // Relative subfolder from document root to project root
    $subFolder = '';
    if (strpos($projectRoot, $docRoot) === 0) {
        $subFolder = substr($projectRoot, strlen($docRoot));
    }

    define('BASE_URL', rtrim($subFolder, '/'));
}
