<?php
declare(strict_types=1);

if (!is_dir(__DIR__ . '/storage/sessions')) {
    mkdir(__DIR__ . '/storage/sessions', 0777, true);
}

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_save_path(__DIR__ . '/storage/sessions');
session_name('newn_session');
session_start();

date_default_timezone_set('America/Los_Angeles');

if (!extension_loaded('pdo_sqlite')) {
    http_response_code(500);
    echo 'NEWN requires the PDO SQLite extension. Please enable pdo_sqlite in your PHP installation.';
    exit;
}

require_once __DIR__ . '/lib/app.php';

newn_bootstrap();
newn_apply_security_headers();
