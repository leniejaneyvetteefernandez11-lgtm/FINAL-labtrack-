
<?php
require_once __DIR__ . '/config.php';

$message = '';

try {
    // Test student account
    $studentNumber = '2026-0001';
    $name = 'Juan Dela Cruz';
    $email = 'juan@student.com';
    $course = 'Information Technology';
    $yearLevel = '1st Year';

    // Generate a valid password hash
    $hashedPassword = password_hash(
        'Student123!',
        PASSWORD_DEFAULT
    );

    // Check whether this student already exists
    $stmt = $pdo->prepare(
        'SELECT id FROM students WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $student = $stmt->fetch();

    if ($student) {
        // Update the existing student's password
        $stmt = $pdo->prepare(
            'UPDATE students SET password = ? WHERE id = ?'
        );
        $stmt->execute([$hashedPassword, $student['id']]);
    } else {
        // Create the test student if it does not exist
        $stmt = $pdo->prepare(
            'INSERT INTO students
            (student_number, name, email, password, course, year_level)
            VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $studentNumber,
            $name,
            $email,
            $hashedPassword,
            $course,
            $yearLevel
        ]);
    }

    $message = 'SUCCESS! The student account is ready.';

} catch (PDOException $e) {
    error_log($e->getMessage());
    $message = 'Database error. Check the students table columns and database.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Account Setup</title>
</head>
<body>
    <h2><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></h2>
    <p>You can now try logging in to LABTRACK.</p>
    <p><a href="student_login.php">Go to Student Login</a></p>
</body>
</html>
