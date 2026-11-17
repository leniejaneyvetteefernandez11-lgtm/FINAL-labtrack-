
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

require_login();

$pageTitle = 'Dashboard';

/* =========================================
   DASHBOARD STATISTICS
========================================= */

// Total equipment units
$totalItems = (int) $pdo->query(
    "SELECT COALESCE(SUM(quantity), 0) FROM equipment"
)->fetchColumn();

// Total equipment records
$equipmentCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM equipment"
)->fetchColumn();

// Available equipment units
$available = (int) $pdo->query(
    "SELECT COALESCE(SUM(available_quantity), 0)
     FROM equipment"
)->fetchColumn();

// Equipment under maintenance
$maintenance = (int) $pdo->query(
    "SELECT COALESCE(SUM(quantity), 0)
     FROM equipment
     WHERE status = 'Under Maintenance'"
)->fetchColumn();

// Damaged equipment
$damaged = (int) $pdo->query(
    "SELECT COALESCE(SUM(quantity), 0)
     FROM equipment
     WHERE status = 'Damaged'"
)->fetchColumn();

// Equipment currently in use
$inUse = (int) $pdo->query(
    "SELECT COALESCE(SUM(quantity), 0)
     FROM equipment
     WHERE status = 'In Use'"
)->fetchColumn();

// Recently added equipment
$recent = $pdo->query(
    "SELECT * FROM equipment
     ORDER BY id DESC
     LIMIT 6"
)->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
?>

<style>
/* =========================================
   LABTRACK DASHBOARD DESIGN
========================================= */

.dashboard-container {
    width: 100%;
    max-width: 1600px;
    margin: 0 auto;
    padding-bottom: 30px;
    color: #1e293b;
}

/* Page heading */

.page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    padding: 28px 30px;
    border-radius: 18px;
    background: linear-gradient(120deg, #173fa8, #2563eb, #0ea5e9);
    color: #ffffff;
    box-shadow: 0 8px 22px rgba(37, 99, 235, 0.20);
}

.page-heading h2 {
    margin: 0 0 8px;
    font-size: clamp(23px, 3vw, 32px);
    font-weight: 800;
    color: #ffffff;
}

.page-heading p {
    margin: 0;
    color: #e0f2fe;
    font-size: 14px;
}

/* Buttons */

.btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    padding: 11px 18px;
    border-radius: 10px;
    border: none;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
    white-space: nowrap;
}

.btn.primary {
    background: #ffffff;
    color: #1d4ed8;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.btn.primary:hover {
    background: #dbeafe;
    transform: translateY(-2px);
}

.btn.secondary {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

.btn.secondary:hover {
    background: #dbeafe;
}

.small-btn {
    padding: 9px 14px;
    font-size: 13px;
}

/* =========================================
   COLORFUL STATISTIC BOXES
========================================= */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.stat-card {
    position: relative;
    overflow: hidden;
    min-width: 0;
    padding: 24px;
    border: 1px solid transparent;
    border-radius: 18px;
    box-shadow: 0 5px 16px rgba(15, 23, 42, 0.06);
    transition: transform 0.25s ease,
                box-shadow 0.25s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(15, 23, 42, 0.12);
}

.stat-card::after {
    content: "";
    position: absolute;
    width: 105px;
    height: 105px;
    right: -25px;
    bottom: -35px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.07;
    pointer-events: none;
}

.stat-top {
    position: relative;
    z-index: 1;
    margin-bottom: 12px;
    font-size: 14px;
    font-weight: 700;
}

.stat-number {
    position: relative;
    z-index: 1;
    margin-bottom: 9px;
    font-size: clamp(28px, 3vw, 38px);
    font-weight: 800;
    line-height: 1.2;
}

.stat-note {
    position: relative;
    z-index: 1;
    font-size: 13px;
    color: #475569;
}

/* 1. Blue - Total Equipment */

.stat-card:nth-child(1) {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-color: #60a5fa;
    color: #1d4ed8;
}

/* 2. Green - Available */

.stat-card:nth-child(2) {
    background: linear-gradient(135deg, #f0fdf4, #d1fae5);
    border-color: #4ade80;
    color: #047857;
}

/* 3. Orange - In Use */

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    border-color: #fb923c;
    color: #c2410c;
}

/* 4. Purple - Under Maintenance */

.stat-card:nth-child(4) {
    background: linear-gradient(135deg, #faf5ff, #ede9fe);
    border-color: #c084fc;
    color: #6d28d9;
}

/* 5. Red - Damaged */

.stat-card:nth-child(5) {
    background: linear-gradient(135deg, #fff1f2, #ffe4e6);
    border-color: #fb7185;
    color: #be123c;
}

/* 6. Cyan - Equipment Records */

.stat-card:nth-child(6) {
    background: linear-gradient(135deg, #ecfeff, #cffafe);
    border-color: #22d3ee;
    color: #0e7490;
}

/* =========================================
   RECENT EQUIPMENT TABLE
========================================= */

.panel {
    background: #ffffff;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 5px 20px rgba(30, 64, 175, 0.07);
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
}

.panel-header h2 {
    margin: 0;
    color: #173f8a;
    font-size: 21px;
    font-weight: 800;
}

.table-wrap {
    width: 100%;
    overflow-x: auto;
    border-radius: 10px;
}

table {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
    background: #ffffff;
}

thead {
    background: #eff6ff;
}

th {
    padding: 15px 12px;
    color: #1e40af;
    text-align: left;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
    border-bottom: 1px solid #dbeafe;
}

td {
    padding: 14px 12px;
    color: #334155;
    font-size: 13px;
    border-bottom: 1px solid #e5e7eb;
}

tbody tr {
    transition: background 0.2s ease;
}

tbody tr:hover {
    background: #f8fbff;
}

tbody tr:last-child td {
    border-bottom: none;
}

.equipment-name {
    color: #0f172a;
    font-weight: 700;
}

.asset-code {
    color: #2563eb;
    font-weight: 700;
    white-space: nowrap;
}

.empty-state {
    padding: 35px 15px;
    text-align: center;
    color: #64748b;
}

/* =========================================
   STATUS BADGES
========================================= */

.badge {
    display: inline-block;
    padding: 6px 11px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.badge.available {
    color: #047857;
    background: #d1fae5;
}

.badge.in-use {
    color: #1d4ed8;
    background: #dbeafe;
}

.badge.under-maintenance {
    color: #6d28d9;
    background: #ede9fe;
}

.badge.damaged {
    color: #be123c;
    background: #ffe4e6;
}

.badge.default-status {
    color: #475569;
    background: #e2e8f0;
}

/* =========================================
   RESPONSIVE DESIGN
========================================= */

@media (max-width: 1100px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 650px) {
    .dashboard-container {
        padding: 0 0 20px;
    }

    .page-heading {
        align-items: flex-start;
        flex-direction: column;
        padding: 22px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .stat-card {
        padding: 22px;
    }

    .panel {
        padding: 15px;
    }

    .panel-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .panel-header h2 {
        font-size: 18px;
    }
}
</style>

<div class="dashboard-container">

    <!-- PAGE HEADING -->
    <div class="page-heading">
        <div>
            <h2>Welcome to LABTRACK!</h2>
            <p>Computer Laboratory Equipment Management System</p>
        </div>

        <a href="inventory.php" class="btn primary">
            <span aria-hidden="true">+</span>
            Add Equipment
        </a>
    </div>

    <!-- COLORFUL STATISTICS -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-top">Total Equipment</div>
            <div class="stat-number">
                <?= number_format($totalItems) ?>
            </div>
            <div class="stat-note">
                Total registered units
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">Available</div>
            <div class="stat-number">
                <?= number_format($available) ?>
            </div>
            <div class="stat-note">
                Available quantity
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">In Use</div>
            <div class="stat-number">
                <?= number_format($inUse) ?>
            </div>
            <div class="stat-note">
                Units currently in use
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">Under Maintenance</div>
            <div class="stat-number">
                <?= number_format($maintenance) ?>
            </div>
            <div class="stat-note">
                Units requiring maintenance
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">Damaged Equipment</div>
            <div class="stat-number">
                <?= number_format($damaged) ?>
            </div>
            <div class="stat-note">
                Damaged units
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">Equipment Records</div>
            <div class="stat-number">
                <?= number_format($equipmentCount) ?>
            </div>
            <div class="stat-note">
                Registered equipment types
            </div>
        </div>

    </div>

    <!-- RECENTLY ADDED EQUIPMENT -->
    <div class="panel">

        <div class="panel-header">
            <h2>Recently Added Equipment</h2>

            <a href="inventory.php" class="btn secondary small-btn">
                View Inventory
            </a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Equipment Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (empty($recent)): ?>

                    <tr>
                        <td colspan="6" class="empty-state">
                            No equipment records found.
                            Add your first equipment in the Inventory page.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($recent as $item): ?>

                        <?php
                        $status = (string) ($item['status'] ?? '');
                        $statusKey = strtolower(trim($status));

                        $statusClass = 'default-status';

                        if ($statusKey === 'available') {
                            $statusClass = 'available';
                        } elseif ($statusKey === 'in use') {
                            $statusClass = 'in-use';
                        } elseif ($statusKey === 'under maintenance') {
                            $statusClass = 'under-maintenance';
                        } elseif ($statusKey === 'damaged') {
                            $statusClass = 'damaged';
                        }
                        ?>

                        <tr>
                            <td class="asset-code">
                                <?= e($item['asset_code'] ?? '') ?>
                            </td>

                            <td class="equipment-name">
                                <?= e($item['equipment_name'] ?? '') ?>
                            </td>

                            <td>
                                <?= e($item['category'] ?? '') ?>
                            </td>

                            <td>
                                <?= number_format(
                                    (int) ($item['quantity'] ?? 0)
                                ) ?>
                            </td>

                            <td>
                                <?= e($item['location'] ?? '') ?>
                            </td>

                            <td>
                                <span class="badge <?= e($statusClass) ?>">
                                    <?= e($status) ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
