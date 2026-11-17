
<?php
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['student_id'])) {
    header('Location: student_dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNumber = trim($_POST['student_number'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $yearLevel = trim($_POST['year_level'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (
        $studentNumber === '' ||
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $course === '' ||
        $yearLevel === '' ||
        strlen($password) < 8
    ) {
        $error = 'Complete all fields and use a valid email and a password of at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO students
                (student_number, name, email, password, course, year_level)
                VALUES (?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $studentNumber,
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $course,
                $yearLevel
            ]);

            $success = 'Registration successful! You can now log in.';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'That student number or email is already registered.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration | LABTRACK</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .student-card {
            width: min(460px, 92%);
            margin: 35px auto;
            padding: 30px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 35px #0f172a15;
        }
        .student-card h1 {
            color: #2457d6;
            text-align: center;
        }
        .student-card .muted {
            text-align: center;
            color: #64748b;
            margin-bottom: 25px;
        }
        .student-card label {
            display: block;
            margin: 13px 0 6px;
            font-weight: 600;
        }
        .student-card input,
        .student-card select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .student-card .btn {
            width: 100%;
            margin-top: 20px;
        }
        .student-card .notice {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .student-card .error {
            background: #fee2e2;
            color: #991b1b;
        }
        .student-card .success {
            background: #dcfce7;
            color: #166534;
        }
        .student-card .links {
            margin-top: 18px;
            text-align: center;
        }
    </style>
</head>
<body class="login-page">
    <div class="student-card">
        <h1>LABTRACK</h1>
        <p class="muted">Student Account Registration</p>

        <?php if ($error): ?>
            <div class="notice error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="notice success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="student_register.php">
            <label for="student_number">Student Number</label>
            <input id="student_number" name="student_number"
                   maxlength="50" required
                   value="<?= e($_POST['student_number'] ?? '') ?>">

            <label for="name">Full Name</label>
            <input id="name" name="name" maxlength="150"
                   required value="<?= e($_POST['name'] ?? '') ?>">

            <label for="email">Email Address</label>
            <input id="email" type="email" name="email"
                   maxlength="150" required
                   value="<?= e($_POST['email'] ?? '') ?>">

            <label for="course">Course / Program</label>
            <input id="course" name="course" maxlength="100"
                   placeholder="e.g. BS Information Technology"
                   required value="<?= e($_POST['course'] ?? '') ?>">

            <label for="year_level">Year Level</label>
            <select id="year_level" name="year_level" required>
                <option value="">Select year level</option>
                <?php foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'] as $year): ?>
                    <option value="<?= e($year) ?>"
                        <?= ($_POST['year_level'] ?? '') === $year ? 'selected' : '' ?>>
                        <?= e($year) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="password">Password</label>
            <input id="password" type="password" name="password"
                   minlength="8" required autocomplete="new-password">

            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" type="password"
                   name="confirm_password" minlength="8"
                   required autocomplete="new-password">

            <button class="btn primary" type="submit">
                Create Student Account
            </button>
        </form>

        <div class="links">
            Already registered?
            <a href="student_login.php">Student Login</a>
        </div>
    </div>
</body>
</html>
