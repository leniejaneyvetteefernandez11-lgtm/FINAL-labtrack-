
<?php
require_once __DIR__ . '/../auth.php';
require_login();

$pageTitle = $pageTitle ?? 'Dashboard';

$flash = $_SESSION['flash_message'] ?? '';
$flashType = $_SESSION['flash_type'] ?? 'success';

unset(
    $_SESSION['flash_message'],
    $_SESSION['flash_type']
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | LABTRACK</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app-layout">

    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">

        <header class="topbar">
            <div>
                <div class="breadcrumb">
                    LABTRACK / <?= e($pageTitle) ?>
                </div>
                <h1><?= e($pageTitle) ?></h1>
            </div>

            <div class="topbar-user">
                <div class="user-circle">A</div>
                <div>
                    <strong>
                        <?= e($_SESSION['admin_name'] ?? 'Administrator') ?>
                    </strong>
                    <small>Administrator</small>
                </div>
            </div>
        </header>

        <div class="page-body">

            <?php if ($flash !== ''): ?>
                <div class="alert <?= e($flashType) ?>">
                    <?= e($flash) ?>
                </div>
            <?php endif; ?>
