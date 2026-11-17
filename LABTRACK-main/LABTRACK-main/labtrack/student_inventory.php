
<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit;
}

$stmt = $pdo->query(
    "SELECT asset_code, equipment_name, category,
            available_quantity, location, status
     FROM equipment
     WHERE status = 'Available'
       AND available_quantity > 0
     ORDER BY equipment_name"
);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Available Equipment | LABTRACK</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            background: #f4f7fc;
            color: #1e293b;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .student-inventory {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 22px;
        }
        .student-inventory .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 22px;
        }
        .student-inventory .top a {
            color: #2457d6;
            font-weight: 700;
            text-decoration: none;
        }
        .student-inventory .table-panel {
            background: white;
            padding: 22px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
        }
        .student-inventory table {
            width: 100%;
            min-width: 650px;
            border-collapse: collapse;
        }
        .student-inventory th,
        .student-inventory td {
            text-align: left;
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }
        .student-inventory th {
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            text-transform: uppercase;
        }
        .student-inventory .available-badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="student-inventory">
        <div class="top">
            <div>
                <h1>Available Equipment</h1>
                <p>Equipment currently marked as available.</p>
            </div>
            <a href="student_dashboard.php">&larr; Dashboard</a>
        </div>

        <div class="table-panel">
            <table>
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Equipment</th>
                        <th>Category</th>
                        <th>Available Qty.</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr>
                        <td colspan="6">No equipment is currently available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['asset_code']) ?></td>
                            <td><strong><?= e($item['equipment_name']) ?></strong></td>
                            <td><?= e($item['category'] ?? '') ?></td>
                            <td><?= number_format((int)$item['available_quantity']) ?></td>
                            <td><?= e($item['location'] ?? '') ?></td>
                            <td><span class="available-badge">Available</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
