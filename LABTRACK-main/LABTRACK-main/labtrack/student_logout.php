<?php
require_once __DIR__ . '/config.php';

unset(
    $_SESSION['student_id'],
    $_SESSION['student_number'],
    $_SESSION['student_name'],
    $_SESSION['student_email']
);

header('Location: student_login.php');
exit;