<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookie settings
    $cookie_params = [
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    // Only use 'secure' if HTTPS is on, otherwise InfinityFree HTTP breaks login
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off'){
        $cookie_params['secure'] = true;
    }
    session_set_cookie_params($cookie_params);
    session_start();
}

// Session timeout (4 hours of inactivity)
$timeout = 4 * 60 * 60;
if(isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout){
    $_SESSION = [];
    session_destroy();
    session_start();
    $_SESSION['flash'] = ['type'=>'info','msg'=>'Session expired. Please log in again.'];
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}
// Refresh login_time on activity
if(isset($_SESSION['user_id'])){ $_SESSION['login_time'] = time(); }

function is_logged_in(): bool  { return isset($_SESSION['user_id'], $_SESSION['role']); }
function current_user_id(): int { return (int)($_SESSION['user_id'] ?? 0); }
function current_role(): string { return $_SESSION['role'] ?? ''; }
function current_name(): string { return $_SESSION['name'] ?? 'User'; }
function current_roll(): string { return $_SESSION['roll'] ?? ''; }

function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['flash'] = ['type'=>'error','msg'=>'Please log in to continue.'];
        header('Location: ' . BASE_URL . '/public/login.php');
        exit;
    }
}

function guard(string|array $roles): void {
    require_login();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array(current_role(), $allowed)) {
        http_response_code(403);
        die("<!DOCTYPE html><html><head><title>Access Denied</title><style>body{font-family:system-ui;background:#f0ece8;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}.box{background:white;padding:40px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.1);text-align:center;max-width:400px;}h1{color:#9b1c3c;font-family:'Libre Baskerville',serif;}p{color:#666;font-size:14px;line-height:1.6;}a{color:#9b1c3c;text-decoration:none;font-weight:700;}</style></head><body><div class='box'><h1>403 — Access Denied</h1><p>You don't have permission to access this page.</p><a href='" . BASE_URL . "/public/dashboard.php'>Back to Dashboard</a></div></body></html>");
    }
}

function logout(): void {
    $_SESSION = [];
    if(ini_get("session.use_cookies")){
        $params = session_get_cookie_params();
        setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secure'],$params['httponly']);
    }
    session_destroy();
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}
