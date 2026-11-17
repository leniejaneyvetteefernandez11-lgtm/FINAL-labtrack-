
<?php
require_once __DIR__ . '/config.php';

$email = 'admin@labtrack.local';
$plainPassword = 'Admin@12345';

$stmt = $pdo->prepare(
    'SELECT id FROM admins WHERE email = ?'
);
$stmt->execute([$email]);

if ($stmt->fetch()) {
    exit(
        'The admin account already exists. ' .
        'Delete setup_admin.php and go to login.php.'
    );
}

$hash = password_hash(
    $plainPassword,
    PASSWORD_DEFAULT
);

$stmt = $pdo->prepare(
    'INSERT INTO admins (name, email, password)
     VALUES (?, ?, ?)'
);

$stmt->execute([
    'LABTRACK Administrator',
    $email,
    $hash
]);

echo 'Administrator created successfully.<br>';
echo 'Email: admin@labtrack.local<br>';
echo 'Password: Admin@12345<br>';
echo 'Delete setup_admin.php now, then open login.php.';
