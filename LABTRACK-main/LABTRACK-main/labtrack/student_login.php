
<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['student_id'])) {
    header('Location: student_dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $passwordInput = $_POST['password'] ?? '';

    if ($email === '' || $passwordInput === '') {
        $error = 'Please enter your email and password.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, student_number, name, email, password
                 FROM students
                 WHERE email = ?
                 LIMIT 1'
            );

            $stmt->execute([$email]);
            $student = $stmt->fetch();

            if (
                $student &&
                password_verify($passwordInput, $student['password'])
            ) {
                session_regenerate_id(true);

                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_number'] = $student['student_number'];
                $_SESSION['student_name'] = $student['name'];
                $_SESSION['student_email'] = $student['email'];

                header('Location: student_dashboard.php');
                exit;
            }

            $error = 'Invalid email address or password.';

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Unable to log in right now. Please check the database.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Login | LABTRACK</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="brand-mark">LT</div>

        <h1>LABTRACK</h1>
        <p class="muted">Computer Laboratory Management System</p>
        <h2>Student Login</h2>

        <?php if ($error !== ''): ?>
            <div class="alert error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="student_login.php">
            <label for="email">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                placeholder="Enter your email"
                value="<?= e($email) ?>"
                required
                autocomplete="username"
            >

            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            >

            <button class="btn primary full-width" type="submit">
                Sign In as Student
            </button>
        </form>

        <p class="login-note">
            New student?
            <a href="student_register.php">Create an account</a>
        </p>

        <p class="login-note">
            Administrator?
            <a href="login.php">Admin Login</a>
        </p>
    </div>
</body>
</html>
