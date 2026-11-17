
<?php
require_once __DIR__ . '/config.php';

function require_login() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function status_class($status) {
    $classes = [
        'Available' => 'available',
        'In Use' => 'in-use',
        'Under Maintenance' => 'maintenance',
        'Damaged' => 'damaged'
    ];

    return $classes[$status] ?? 'unknown';
}

function flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals(csrf_token(), $token)
    ) {
        http_response_code(403);
        exit('Invalid request token. Refresh the page and try again.');
    }
}
