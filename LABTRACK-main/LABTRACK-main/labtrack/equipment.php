
<?php
require_once __DIR__ . '/auth.php';
require_login();

$pageTitle = 'Equipment Status';

$statuses = [
    'Available',
    'In Use',
    'Under Maintenance',
    'Damaged'
];

$icons = [
    'Available' => ['✓', 'available'],
    'In Use' => ['▣', 'in-use'],
    'Under Maintenance' => ['⚙', 'maintenance'],
    'Damaged' => ['!', 'damaged']
];

/* UPDATE EQUIPMENT STATUS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = filter_var(
        $_POST['id'] ?? '',
        FILTER_VALIDATE_INT
    );

    $status = $_POST['status'] ?? '';

    if (
        !$id || $id < 1 ||
        !in_array($status, $statuses, true)
    ) {
        flash_message(
            'Invalid equipment status request.',
            'error'
        );
    } else {
        $stmt = $pdo->prepare(
            'UPDATE equipment SET status = ? WHERE id = ?'
        );

        $stmt->execute([$status, $id]);

        flash_message(
            $stmt->rowCount()
                ? 'Equipment status updated successfully.'
                : 'No changes made, or equipment was not found.'
        );
    }

    header('Location: equipment.php');
    exit;
}

/* FILTER EQUIPMENT */
$filter = $_GET['status'] ?? '';

if ($filter !== '' && in_array($filter, $statuses, true)) {
    $stmt = $pdo->prepare(
        'SELECT * FROM equipment
         WHERE status = ?
         ORDER BY equipment_name'
    );

    $stmt->execute([$filter]);
    $items = $stmt->fetchAll();
} else {
    $items = $pdo->query(
        'SELECT * FROM equipment
         ORDER BY equipment_name'
    )->fetchAll();

    $filter = '';
}

/* STATUS STATISTICS */
$counts = [];

$stmt = $pdo->query(
    'SELECT status,
            COUNT(*) AS records,
            COALESCE(SUM(quantity), 0) AS units
     FROM equipment
     GROUP BY status'
);

foreach ($stmt->fetchAll() as $row) {
    $counts[$row['status']] = $row;
}

foreach ($statuses as $status) {
    if (!isset($counts[$status])) {
        $counts[$status] = [
            'records' => 0,
            'units' => 0
        ];
    }
}

require __DIR__ . '/includes/header.php';
?>

<style>
/* ==================================
   LABTRACK EQUIPMENT STATUS PAGE
================================== */

.equipment-page {
    color: #1e293b;
    width: 100%;
}

/* PAGE HEADING */

.equipment-page .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 26px;
}

.equipment-page .page-heading h2 {
    margin: 0 0 7px;
    font-size: 28px;
    font-weight: 800;
    color: #172554;
}

.equipment-page .page-heading p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.equipment-page .btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 7px;
    padding: 11px 16px;
    border: none;
    border-radius: 9px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s, opacity .2s;
}

.equipment-page .btn:hover {
    transform: translateY(-1px);
}

.equipment-page .btn.primary {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff;
    box-shadow: 0 4px 10px #2563eb2e;
}

.equipment-page .btn.secondary {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}

.equipment-page .btn.small-btn {
    padding: 9px 12px;
    font-size: 12px;
}

/* COLORFUL STATISTICS */

.equipment-page .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 26px;
}

.equipment-page .stat-card {
    position: relative;
    overflow: hidden;
    padding: 22px;
    min-height: 145px;
    border: 1px solid transparent;
    border-radius: 16px;
    transition: transform .2s, box-shadow .2s;
}

.equipment-page .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px #0f172a12;
}

.equipment-page .stat-card::after {
    content: "";
    position: absolute;
    width: 100px;
    height: 100px;
    right: -30px;
    bottom: -40px;
    border-radius: 50%;
    background: #ffffff55;
    pointer-events: none;
}

.equipment-page .stat-card.available {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    border-color: #bbf7d0;
    color: #166534;
}

.equipment-page .stat-card.in-use {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.equipment-page .stat-card.maintenance {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-color: #fde68a;
    color: #92400e;
}

.equipment-page .stat-card.damaged {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border-color: #fecaca;
    color: #991b1b;
}

.equipment-page .stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    font-size: 13px;
    font-weight: 700;
}

.equipment-page .stat-icon {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    font-size: 23px;
    font-weight: 800;
    background: #ffffffa8;
}

.equipment-page .stat-number {
    margin-top: 14px;
    font-size: 31px;
    line-height: 1.15;
    font-weight: 800;
    letter-spacing: -.6px;
}

.equipment-page .stat-note {
    margin-top: 7px;
    font-size: 12px;
    opacity: .85;
}

/* WHITE CONTENT PANELS */

.equipment-page .panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    margin-bottom: 24px;
    padding: 23px;
    box-shadow: 0 5px 18px #0f172a08;
}

.equipment-page .panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 19px;
}

.equipment-page .panel-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #1e293b;
}

.equipment-page .panel-description {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 13px;
}

/* FILTER FORM */

.equipment-page .search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    margin-bottom: 22px;
}

.equipment-page .search-form select {
    flex: 1;
    min-width: 200px;
}

.equipment-page select {
    width: 100%;
    padding: 10px 12px;
    background: #fff;
    color: #334155;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    font: inherit;
    font-size: 13px;
    outline: none;
}

.equipment-page select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px #3b82f61a;
}

/* TABLE */

.equipment-page .table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #edf2f7;
    border-radius: 12px;
}

.equipment-page table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}

.equipment-page thead {
    background: #f1f5ff;
}

.equipment-page th {
    padding: 15px 14px;
    color: #475569;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .45px;
    text-align: left;
    white-space: nowrap;
    border-bottom: 1px solid #e2e8f0;
}

.equipment-page td {
    padding: 15px 14px;
    border-bottom: 1px solid #edf2f7;
    font-size: 13px;
    vertical-align: middle;
}

.equipment-page tbody tr:last-child td {
    border-bottom: none;
}

.equipment-page tbody tr {
    transition: background .15s;
}

.equipment-page tbody tr:hover {
    background: #f8faff;
}

.equipment-page .asset-code {
    color: #2457d6;
    font-weight: 800;
    white-space: nowrap;
}

.equipment-page .equipment-name {
    color: #1e293b;
    font-weight: 700;
}

/* STATUS BADGES */

.equipment-page .badge {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 6px 11px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.equipment-page .badge.available {
    background: #dcfce7;
    color: #166534;
}

.equipment-page .badge.in-use {
    background: #dbeafe;
    color: #1d4ed8;
}

.equipment-page .badge.maintenance {
    background: #fef3c7;
    color: #92400e;
}

.equipment-page .badge.damaged {
    background: #fee2e2;
    color: #991b1b;
}

/* UPDATE STATUS FORM */

.equipment-page .action-group {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 245px;
}

.equipment-page .action-group select {
    min-width: 155px;
    padding: 9px 8px;
    font-size: 12px;
}

.equipment-page .empty-state {
    text-align: center;
    padding: 38px 20px;
    color: #64748b;
    font-size: 14px;
}

/* RESPONSIVE DESIGN */

@media (max-width: 1100px) {
    .equipment-page .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .equipment-page .page-heading h2 {
        font-size: 23px;
    }

    .equipment-page .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .equipment-page .stat-card {
        padding: 15px;
        min-height: 130px;
    }

    .equipment-page .stat-top {
        align-items: flex-start;
        font-size: 12px;
    }

    .equipment-page .stat-icon {
        width: 33px;
        height: 33px;
        font-size: 18px;
    }

    .equipment-page .stat-number {
        font-size: 25px;
    }

    .equipment-page .panel {
        padding: 15px;
    }

    .equipment-page .search-form {
        align-items: stretch;
        flex-direction: column;
    }

    .equipment-page .search-form select {
        min-width: 0;
    }

    .equipment-page .search-form .btn {
        width: 100%;
    }
}

@media print {
    .equipment-page .search-form,
    .equipment-page .page-heading .btn,
    .equipment-page .action-group {
        display: none !important;
    }

    .equipment-page .panel,
    .equipment-page .stat-card {
        box-shadow: none;
        break-inside: avoid;
    }

    .equipment-page .table-wrap {
        overflow: visible;
    }

    .equipment-page table {
        min-width: 0;
    }
}
</style>

<div class="equipment-page">

    <!-- PAGE HEADING -->
    <div class="page-heading">
        <div>
            <h2>Equipment Monitoring</h2>
            <p>
                Monitor condition and availability across your laboratories.
            </p>
        </div>

        <a class="btn secondary" href="inventory.php">
            Manage Inventory &rarr;
        </a>
    </div>

    <!-- COLORFUL STATUS CARDS -->
    <div class="stats-grid">

        <?php foreach ($statuses as $status): ?>
            <?php
            $cardClass = $icons[$status][1];
            $icon = $icons[$status][0];
            ?>

            <div class="stat-card <?= e($cardClass) ?>">
                <div class="stat-top">
                    <span><?= e($status) ?></span>

                    <span class="stat-icon">
                        <?= e($icon) ?>
                    </span>
                </div>

                <div class="stat-number">
                    <?= number_format(
                        (int)$counts[$status]['units']
                    ) ?>
                </div>

                <div class="stat-note">
                    <?= number_format(
                        (int)$counts[$status]['records']
                    ) ?>
                    inventory records
                </div>
            </div>

        <?php endforeach; ?>

    </div>

    <!-- FILTER PANEL -->
    <div class="panel">

        <div class="panel-header">
            <div>
                <h2>Filter Equipment</h2>
                <p class="panel-description">
                    Select a status to find specific equipment.
                </p>
            </div>
        </div>

        <form method="GET"
              action="equipment.php"
              class="search-form">

            <select name="status"
                    aria-label="Filter equipment status">

                <option value="">All Statuses</option>

                <?php foreach ($statuses as $status): ?>
                    <option value="<?= e($status) ?>"
                        <?= $filter === $status ? 'selected' : '' ?>>
                        <?= e($status) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <button class="btn primary" type="submit">
                Apply Filter
            </button>

            <a class="btn secondary" href="equipment.php">
                Reset
            </a>

        </form>

        <div class="panel-header">
            <div>
                <h2>Equipment List</h2>
                <p class="panel-description">
                    <?= number_format(count($items)) ?>
                    equipment records displayed
                </p>
            </div>

            <button class="btn secondary"
                    type="button"
                    onclick="window.print()">
                Print List
            </button>
        </div>

        <!-- EQUIPMENT TABLE -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Equipment Name</th>
                        <th>Location</th>
                        <th>Quantity</th>
                        <th>Current Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!$items): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            No equipment matches this status.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($items as $item): ?>
                    <?php
                    $currentStatus = $item['status'] ?? '';
                    $badgeClass = $icons[$currentStatus][1]
                        ?? 'available';
                    ?>

                    <tr>
                        <td class="asset-code">
                            <?= e($item['asset_code']) ?>
                        </td>

                        <td class="equipment-name">
                            <?= e($item['equipment_name']) ?>
                        </td>

                        <td>
                            <?= e($item['location']) ?>
                        </td>

                        <td>
                            <strong>
                                <?= number_format(
                                    (int)$item['quantity']
                                ) ?>
                            </strong>
                        </td>

                        <td>
                            <span class="badge <?= e($badgeClass) ?>">
                                <?= e($currentStatus) ?>
                            </span>
                        </td>

                        <td>
                            <form method="POST"
                                  action="equipment.php"
                                  class="action-group">

                                <input type="hidden"
                                       name="csrf_token"
                                       value="<?= e(csrf_token()) ?>">

                                <input type="hidden"
                                       name="id"
                                       value="<?= (int)$item['id'] ?>">

                                <select name="status"
                                        aria-label="New status for <?= e($item['asset_code']) ?>"
                                        required>

                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?= e($status) ?>"
                                            <?= $currentStatus === $status
                                                ? 'selected' : '' ?>>
                                            <?= e($status) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>

                                <button class="btn primary small-btn"
                                        type="submit">
                                    Update
                                </button>

                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
