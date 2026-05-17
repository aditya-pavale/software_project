<?php
// Root entry point — redirects to login
require_once __DIR__ . '/includes/config.php';
header('Location: ' . BASE_URL . '/public/login.php');
exit;
