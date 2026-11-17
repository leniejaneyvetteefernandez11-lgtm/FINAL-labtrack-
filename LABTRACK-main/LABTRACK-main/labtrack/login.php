
<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, name, email, password
         FROM admins WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (
        $admin &&
        password_verify($password, $admin['password'])
    ) {
        session_regenerate_id(true);

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid email address or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Admin Login | LABTRACK</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="brand-mark">LT</div>
        <h1>LABTRACK</h1>
        <p class="muted">Computer Laboratory Management System</p>
        <h2>Administrator Login</h2>

        <?php if ($error): ?>
            <div class="alert error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label>Email Address</label>
            <input type="email" name="email"
                   placeholder="Enter your email"
                   required autocomplete="username">

            <label>Password</label>
            <input type="password" name="password"
                   placeholder="Enter your password"
                   required autocomplete="current-password">

            <button class="btn primary full-width"
                    type="submit">
                Sign In to LABTRACK
            </button>
        </form>

        <p class="login-note">
            Authorized laboratory administrators only.
        </p>
    </div>
</body>
</html>
