
<?php
require_once __DIR__ . '/auth.php';
require_login();

$pageTitle = 'Reports';

/* =========================================
   REPORT STATISTICS
========================================= */

$totalRecords = (int) $pdo->query(
    'SELECT COUNT(*) FROM equipment'
)->fetchColumn();

$totalUnits = (int) $pdo->query(
    'SELECT COALESCE(SUM(quantity), 0) FROM equipment'
)->fetchColumn();

$totalAvailable = (int) $pdo->query(
    'SELECT COALESCE(SUM(available_quantity), 0) FROM equipment'
)->fetchColumn();

$totalValue = (float) $pdo->query(
    'SELECT COALESCE(SUM(quantity * unit_price), 0)
     FROM equipment'
)->fetchColumn();

/* =========================================
   LOW STOCK REPORT
========================================= */

$lowStock = $pdo->query(
    'SELECT asset_code, equipment_name, quantity, location
     FROM equipment
     WHERE quantity <= 5
     ORDER BY quantity ASC, equipment_name ASC'
)->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   CONDITION REPORT
========================================= */

$conditionReport = $pdo->query(
    "SELECT status,
            COUNT(*) AS records,
            COALESCE(SUM(quantity), 0) AS units,
            COALESCE(SUM(quantity * unit_price), 0) AS value_total
     FROM equipment
     GROUP BY status"
)->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   CATEGORY REPORT
========================================= */

$categoryReport = $pdo->query(
    'SELECT category,
            COUNT(*) AS records,
            COALESCE(SUM(quantity), 0) AS units,
            COALESCE(SUM(quantity * unit_price), 0) AS value_total
     FROM equipment
     GROUP BY category
     ORDER BY category'
)->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   COMPLETE INVENTORY
========================================= */

$allItems = $pdo->query(
    'SELECT * FROM equipment
     ORDER BY category, equipment_name'
)->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   REPORT CALCULATIONS
========================================= */

$maxUnits = 1;

foreach ($conditionReport as $row) {
    $maxUnits = max($maxUnits, (int) $row['units']);
}

require __DIR__ . '/includes/header.php';
?>

<style>
/* =========================================
   LABTRACK REPORTS DESIGN
========================================= */

.reports-container {
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
    box-shadow: 0 8px 22px rgba(37, 99, 235, .20);
    color: #fff;
}

.page-heading h2 {
    margin: 0 0 8px;
    color: #fff;
    font-size: clamp(23px, 3vw, 32px);
    font-weight: 800;
}

.page-heading p {
    margin: 0;
    color: #dbeafe;
    font-size: 14px;
}

/* Buttons */

.btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    padding: 11px 17px;
    border: 1px solid transparent;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: .2s ease;
}

.btn.primary {
    color: #fff;
    background: #2563eb;
    border-color: #2563eb;
}

.btn.primary:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
}

.page-heading .btn.primary {
    color: #1d4ed8;
    background: #fff;
    border-color: #fff;
}

.page-heading .btn.primary:hover {
    background: #dbeafe;
}

.btn.secondary {
    color: #1d4ed8;
    background: #eff6ff;
    border-color: #bfdbfe;
}

.btn.secondary:hover {
    background: #dbeafe;
}

.btn.danger {
    color: #fff;
    background: #e11d48;
    border-color: #e11d48;
}

.btn.danger:hover {
    background: #be123c;
}

.small-btn {
    padding: 7px 11px;
    font-size: 12px;
}

/* =========================================
   COLORFUL STAT CARDS
========================================= */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 26px;
}

.stat-card {
    position: relative;
    overflow: hidden;
    min-width: 0;
    padding: 23px;
    border: 1px solid transparent;
    border-radius: 18px;
    box-shadow: 0 5px 16px rgba(15, 23, 42, .06);
    transition: transform .25s ease, box-shadow .25s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(15, 23, 42, .12);
}

.stat-card::after {
    content: "";
    position: absolute;
    right: -25px;
    bottom: -35px;
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: currentColor;
    opacity: .07;
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
    font-size: clamp(27px, 2.5vw, 36px);
    line-height: 1.2;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.stat-note {
    position: relative;
    z-index: 1;
    color: #475569;
    font-size: 12px;
}

/* Blue: records */

.stats-grid .stat-card:nth-child(1) {
    color: #1d4ed8;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-color: #93c5fd;
}

/* Green: units */

.stats-grid .stat-card:nth-child(2) {
    color: #047857;
    background: linear-gradient(135deg, #f0fdf4, #d1fae5);
    border-color: #86efac;
}

/* Orange: available */

.stats-grid .stat-card:nth-child(3) {
    color: #c2410c;
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    border-color: #fdba74;
}

/* Purple: value */

.stats-grid .stat-card:nth-child(4) {
    color: #6d28d9;
    background: linear-gradient(135deg, #faf5ff, #ede9fe);
    border-color: #c4b5fd;
}

/* =========================================
   REPORT PANELS
========================================= */

.report-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
    margin-bottom: 24px;
    align-items: stretch;
}

.panel {
    min-width: 0;
    margin-bottom: 24px;
    padding: 24px;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 5px 20px rgba(30, 64, 175, .06);
}

.report-grid .panel {
    margin-bottom: 0;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.panel-header h2 {
    margin: 0;
    color: #173f8a;
    font-size: 19px;
    font-weight: 800;
}

.muted {
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
}

.report-description {
    margin: -8px 0 18px;
    color: #64748b;
    font-size: 13px;
}

/* =========================================
   STATUS REPORT PROGRESS BARS
========================================= */

.report-line {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 15px;
    padding: 16px 14px;
    margin-bottom: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #f8fafc;
    transition: background .2s ease, transform .2s ease;
}

.report-line:last-child {
    margin-bottom: 0;
}

.report-line:hover {
    background: #eff6ff;
    transform: translateX(2px);
}

.report-line > div:first-child {
    min-width: 0;
}

.report-line strong {
    color: #1e293b;
    font-size: 13px;
}

.report-line .muted {
    margin-top: 5px;
}

.bar-track {
    height: 9px;
    overflow: hidden;
    margin-top: 12px;
    border-radius: 20px;
    background: #e2e8f0;
}

.bar-fill {
    height: 100%;
    min-width: 0;
    border-radius: 20px;
    background: linear-gradient(90deg, #2563eb, #38bdf8);
    transition: width .4s ease;
}

/* Different bar colors by report order */

.report-line:nth-child(2) .bar-fill {
    background: linear-gradient(90deg, #2563eb, #60a5fa);
}

.report-line:nth-child(3) .bar-fill {
    background: linear-gradient(90deg, #16a34a, #4ade80);
}

.report-line:nth-child(4) .bar-fill {
    background: linear-gradient(90deg, #ea580c, #fb923c);
}

.report-line:nth-child(5) .bar-fill {
    background: linear-gradient(90deg, #9333ea, #c084fc);
}

.report-line:nth-child(6) .bar-fill {
    background: linear-gradient(90deg, #e11d48, #fb7185);
}

/* Category report cards */

.category-line {
    background: linear-gradient(135deg, #f8fbff, #eff6ff);
    border-color: #dbeafe;
}

.category-value {
    color: #1d4ed8 !important;
    font-weight: 800;
    white-space: nowrap;
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

.badge.maintenance,
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
   TABLES
========================================= */

.table-wrap {
    width: 100%;
    overflow-x: auto;
    border-radius: 12px;
}

table {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
    background: #fff;
}

thead {
    background: #eff6ff;
}

th {
    padding: 14px 12px;
    color: #1e40af;
    font-size: 12px;
    font-weight: 800;
    text-align: left;
    white-space: nowrap;
    border-bottom: 1px solid #dbeafe;
}

td {
    padding: 13px 12px;
    color: #334155;
    font-size: 12px;
    border-bottom: 1px solid #e5e7eb;
}

tbody tr {
    transition: background .2s ease;
}

tbody tr:nth-child(even) {
    background: #fafcff;
}

tbody tr:hover {
    background: #eff6ff;
}

tbody tr:last-child td {
    border-bottom: none;
}

.asset-code {
    color: #2563eb;
    font-weight: 800;
    white-space: nowrap;
}

.equipment-name {
    color: #0f172a;
    font-weight: 700;
}

.empty-state {
    padding: 30px 15px;
    text-align: center;
    color: #64748b;
}

/* Low-stock warning */

.low-stock-heading {
    color: #9a3412;
}

.low-stock-panel {
    border-top: 4px solid #f97316;
}

.low-stock-count {
    color: #9a3412;
    background: #ffedd5;
    border: 1px solid #fdba74;
}

.low-stock-quantity {
    color: #be123c;
    background: #ffe4e6;
}

/* Inventory report panel */

.complete-report-panel {
    border-top: 4px solid #2563eb;
}

/* =========================================
   PRINT / SAVE PDF
========================================= */

@media print {
    @page {
        size: landscape;
        margin: 12mm;
    }

    body {
        background: #fff !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .no-print,
    .sidebar,
    .navbar,
    .topbar,
    .main-header,
    .site-header {
        display: none !important;
    }

    .reports-container {
        max-width: none;
        padding: 0;
    }

    .page-heading {
        color: #fff !important;
        background: #1d4ed8 !important;
        box-shadow: none;
    }

    .stat-card,
    .panel,
    .report-line {
        break-inside: avoid;
        box-shadow: none;
    }

    .panel {
        margin-bottom: 15px;
    }

    .report-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .table-wrap {
        overflow: visible;
    }

    table {
        min-width: 0;
        font-size: 10px;
    }

    th, td {
        padding: 7px 5px;
        font-size: 9px;
    }

    .stats-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    a {
        text-decoration: none;
    }
}

/* =========================================
   RESPONSIVE LAYOUT
========================================= */

@media (max-width: 1100px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .page-heading {
        align-items: flex-start;
        flex-direction: column;
        padding: 22px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .stat-card {
        padding: 21px;
    }

    .panel {
        padding: 16px;
    }

    .panel-header {
        align-items: flex-start;
    }

    .report-line {
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        padding: 12px 10px;
    }

    .report-line strong {
        font-size: 12px;
    }
}
</style>

<div class="reports-container">

    <!-- PAGE HEADING -->
    <div class="page-heading">
        <div>
            <h2>Laboratory Reports</h2>
            <p>Inventory summaries and equipment condition analysis.</p>
        </div>

        <button class="btn primary no-print"
                type="button"
                onclick="window.print()">
            Print / Save PDF
        </button>
    </div>

    <!-- COLORFUL SUMMARY CARDS -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-top">Inventory Records</div>
            <div class="stat-number">
                <?= number_format($totalRecords) ?>
            </div>
            <div class="stat-note">
                Registered equipment types
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">Total Units</div>
            <div class="stat-number">
                <?= number_format($totalUnits) ?>
            </div>
            <div class="stat-note">
                All registered equipment units
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">Available Units</div>
            <div class="stat-number">
                <?= number_format($totalAvailable) ?>
            </div>
            <div class="stat-note">
                Recorded available quantities
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">Inventory Value</div>
            <div class="stat-number" style="font-size: clamp(22px, 2vw, 30px);">
                ₱<?= number_format($totalValue, 2) ?>
            </div>
            <div class="stat-note">
                Quantity × recorded unit price
            </div>
        </div>

    </div>

    <!-- STATUS AND CATEGORY REPORTS -->
    <div class="report-grid">

        <!-- STATUS REPORT -->
        <div class="panel">
            <div class="panel-header">
                <h2>Equipment by Status</h2>
            </div>

            <p class="report-description">
                Distribution of equipment units by recorded status.
            </p>

            <?php if (!$conditionReport): ?>
                <div class="empty-state">
                    No report data available yet.
                </div>
            <?php endif; ?>

            <?php foreach ($conditionReport as $index => $row): ?>
                <?php
                $units = (int) $row['units'];
                $percentage = min(100, ($units / $maxUnits) * 100);

                $statusText = (string) $row['status'];
                $statusKey = strtolower(trim($statusText));
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

                <div class="report-line">
                    <div>
                        <span class="badge <?= e($statusClass) ?>">
                            <?= e($statusText) ?>
                        </span>

                        <div class="bar-track">
                            <div class="bar-fill"
                                 style="width: <?= $percentage ?>%;">
                            </div>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <strong>
                            <?= number_format($units) ?> units
                        </strong>
                        <div class="muted">
                            <?= number_format((int) $row['records']) ?>
                            records
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- CATEGORY REPORT -->
        <div class="panel">
            <div class="panel-header">
                <h2>Inventory by Category</h2>
            </div>

            <p class="report-description">
                Equipment quantities and estimated inventory value per category.
            </p>

            <?php if (!$categoryReport): ?>
                <div class="empty-state">
                    No categories to report yet.
                </div>
            <?php endif; ?>

            <?php foreach ($categoryReport as $row): ?>
                <div class="report-line category-line">
                    <div>
                        <strong>
                            <?= e($row['category'] ?? 'Uncategorized') ?>
                        </strong>

                        <div class="muted">
                            <?= number_format((int) $row['records']) ?>
                            records
                            ·
                            <?= number_format((int) $row['units']) ?>
                            units
                        </div>
                    </div>

                    <strong class="category-value">
                        ₱<?= number_format(
                            (float) $row['value_total'],
                            2
                        ) ?>
                    </strong>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- LOW STOCK REPORT -->
    <div class="panel low-stock-panel">

        <div class="panel-header">
            <h2 class="low-stock-heading">Low Stock Report</h2>

            <span class="badge low-stock-count">
                <?= count($lowStock) ?> records
            </span>
        </div>

        <p class="report-description">
            Equipment with a total quantity of five or fewer units.
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Equipment Name</th>
                        <th>Quantity</th>
                        <th>Location</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!$lowStock): ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                No low-stock equipment records.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($lowStock as $item): ?>
                        <tr>
                            <td class="asset-code">
                                <?= e($item['asset_code']) ?>
                            </td>

                            <td class="equipment-name">
                                <?= e($item['equipment_name']) ?>
                            </td>

                            <td>
                                <span class="badge low-stock-quantity">
                                    <?= number_format(
                                        (int) $item['quantity']
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <?= e($item['location']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- COMPLETE INVENTORY REPORT -->
    <div class="panel complete-report-panel">

        <div class="panel-header">
            <h2>Complete Inventory Report</h2>

            <span class="badge available">
                <?= count($allItems) ?> records
            </span>
        </div>

        <p class="report-description">
            Full inventory listing, including quantities, status, and recorded values.
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Equipment</th>
                        <th>Category</th>
                        <th>Total Qty</th>
                        <th>Available Qty</th>
                        <th>Status</th>
                        <th>Unit Price</th>
                        <th>Total Value</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!$allItems): ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                Add inventory records to generate a report.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($allItems as $item): ?>
                        <?php
                        $statusText = (string) ($item['status'] ?? '');
                        $statusKey = strtolower(trim($statusText));
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

                        $quantity = (int) ($item['quantity'] ?? 0);
                        $unitPrice = (float) ($item['unit_price'] ?? 0);
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
                                <?= number_format($quantity) ?>
                            </td>

                            <td>
                                <?= number_format(
                                    (int) ($item['available_quantity'] ?? 0)
                                ) ?>
                            </td>

                            <td>
                                <span class="badge <?= e($statusClass) ?>">
                                    <?= e($statusText) ?>
                                </span>
                            </td>

                            <td>
                                ₱<?= number_format($unitPrice, 2) ?>
                            </td>

                            <td>
                                <strong>
                                    ₱<?= number_format(
                                        $quantity * $unitPrice,
                                        2
                                    ) ?>
                                </strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
