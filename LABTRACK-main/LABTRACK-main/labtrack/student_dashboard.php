
<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit;
}

$stmt = $pdo->query(
    "SELECT COUNT(*) FROM equipment
     WHERE status = 'Available' AND available_quantity > 0"
);
$availableTypes = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM equipment
     WHERE status = 'Available' AND available_quantity > 0"
);
$stmt->execute();
$availableEquipment = (int) $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard | LABTRACK</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            margin: 0;
            background: #f4f7fc;
            color: #1e293b;
            font-family: Arial, sans-serif;
        }
        .student-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            padding: 18px 5%;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }
        .student-nav strong {
            color: #2457d6;
            font-size: 22px;
        }
        .student-nav a {
            color: #2457d6;
            font-weight: 700;
            text-decoration: none;
        }
        .student-content {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 22px;
        }
        .welcome {
            margin-bottom: 25px;
        }
        .welcome h1 {
            margin-bottom: 8px;
            font-size: 28px;
        }
        .welcome p {
            color: #64748b;
        }
        .student-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }
        .student-stat {
            padding: 25px;
            border-radius: 16px;
        }
        .student-stat.blue {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
        }
        .student-stat.green {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534;
        }
        .student-stat h2 {
            font-size: 32px;
            margin: 12px 0 5px;
        }
        .student-panel {
            padding: 25px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }
        .student-panel a {
            display: inline-block;
            margin-top: 10px;
            padding: 12px 18px;
            border-radius: 9px;
            background: #2457d6;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <nav class="student-nav">
        <strong>LABTRACK</strong>
        <div>
            Welcome, <?= e($_SESSION['student_name'] ?? 'Student') ?>
            &nbsp; | &nbsp;
            <a href="student_logout.php">Log Out</a>
        </div>
    </nav>

    <main class="student-content">
        <section class="welcome">
            <h1>Student Dashboard</h1>
            <p>View laboratory equipment and check availability.</p>
            <p>
                Student Number:
                <strong><?= e($_SESSION['student_number'] ?? '') ?></strong>
            </p>
        </section>

        <section class="student-stats">
            <div class="student-stat blue">
                Available Equipment Types
                <h2><?= number_format($availableTypes) ?></h2>
                <span>Equipment types marked available</span>
            </div>

            <div class="student-stat green">
                Account Status
                <h2>Active</h2>
                <span>You are signed in successfully.</span>
            </div>
        </section>

        <section class="student-panel">
            <h2>Laboratory Equipment</h2>
            <p>
                Check which equipment is currently available before
                contacting laboratory staff to borrow it.
            </p>
            <a href="student_inventory.php">View Available Equipment</a>
        </section>
    </main>
</body>
</html>
