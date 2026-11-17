
<?php
require_once 'auth.php';
require_once 'config.php';

$page_title = 'Equipment Inventory';

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');

$message = '';
$error = '';

/* DELETE EQUIPMENT */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_id'])) {

    $deleteId = (int) $_POST['delete_id'];

    try {
        $stmt = $pdo->prepare(
            "DELETE FROM equipment WHERE id = ?"
        );
        $stmt->execute([$deleteId]);

        header('Location: inventory.php?deleted=1');
        exit;
    } catch (PDOException $e) {
        $error = 'Unable to delete this equipment. It may be linked to other records.';
    }
}

if (isset($_GET['deleted'])) {
    $message = 'Equipment deleted successfully.';
}

/* LOAD INVENTORY STATISTICS */
$totalEquipment = (int) $pdo->query(
    "SELECT COUNT(*) FROM equipment"
)->fetchColumn();

$totalQuantity = (int) $pdo->query(
    "SELECT COALESCE(SUM(quantity), 0) FROM equipment"
)->fetchColumn();

$totalAvailable = (int) $pdo->query(
    "SELECT COALESCE(SUM(available_quantity), 0) FROM equipment"
)->fetchColumn();

$lowStock = (int) $pdo->query(
    "SELECT COUNT(*) FROM equipment
     WHERE quantity > 0
     AND available_quantity <= 5"
)->fetchColumn();

$maintenance = (int) $pdo->query(
    "SELECT COUNT(*) FROM equipment
     WHERE LOWER(status) LIKE '%maintenance%'"
)->fetchColumn();

/* CATEGORY FILTER OPTIONS */
$categories = $pdo->query(
    "SELECT DISTINCT category FROM equipment
     WHERE category IS NOT NULL AND category <> ''
     ORDER BY category"
)->fetchAll(PDO::FETCH_COLUMN);

/* SEARCH AND FILTER */
$sql = "SELECT * FROM equipment WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (
        asset_code LIKE ?
        OR equipment_name LIKE ?
        OR category LIKE ?
        OR location LIKE ?
    )";

    $term = "%$search%";
    array_push($params, $term, $term, $term, $term);
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($status !== '') {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$equipmentList = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<style>
.inventory-page {
    color: #1e293b;
}

.inventory-page .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 24px;
}

.inventory-page .page-heading h2 {
    margin: 0 0 6px;
    font-size: 27px;
    font-weight: 800;
}

.inventory-page .page-heading p {
    margin: 0;
    color: #64748b;
}

.inventory-page .add-btn {
    background: linear-gradient(135deg, #2457d6, #573be8);
    color: white;
    padding: 12px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    box-shadow: 0 5px 14px #2457d633;
}

.inventory-page .add-btn:hover {
    opacity: .9;
}

.inventory-page .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.inventory-page .stat-card {
    padding: 21px;
    border-radius: 16px;
    box-shadow: 0 5px 18px #0f172a0a;
    border: 1px solid #ffffff80;
}

.inventory-page .stat-card.blue {
    background: linear-gradient(135deg, #eaf1ff, #dbeafe);
    color: #1d4ed8;
}

.inventory-page .stat-card.green {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #15803d;
}

.inventory-page .stat-card.orange {
    background: linear-gradient(135deg, #fff7d6, #fde68a);
    color: #a16207;
}

.inventory-page .stat-card.red {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #b91c1c;
}

.inventory-page .stat-label {
    font-size: 13px;
    font-weight: 700;
}

.inventory-page .stat-number {
    font-size: 30px;
    font-weight: 800;
    margin-top: 9px;
}

.inventory-page .panel {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 22px;
    box-shadow: 0 5px 18px #0f172a08;
}

.inventory-page .panel h3 {
    margin: 0 0 18px;
    font-size: 18px;
}

.inventory-page .filters {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto auto;
    gap: 10px;
    align-items: center;
}

.inventory-page input,
.inventory-page select {
    width: 100%;
    min-width: 0;
    padding: 11px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #fff;
    color: #334155;
}

.inventory-page .filter-btn,
.inventory-page .reset-btn {
    padding: 11px 15px;
    border-radius: 9px;
    border: 0;
    text-decoration: none;
    text-align: center;
    font-weight: 700;
    cursor: pointer;
}

.inventory-page .filter-btn {
    background: #2457d6;
    color: white;
}

.inventory-page .reset-btn {
    background: #f1f5f9;
    color: #475569;
}

.inventory-page .table-wrap {
    width: 100%;
    overflow-x: auto;
}

.inventory-page table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

.inventory-page th {
    background: #f8fafc;
    color: #64748b;
    text-align: left;
    padding: 13px 12px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
}

.inventory-page td {
    padding: 14px 12px;
    border-bottom: 1px solid #edf2f7;
    font-size: 14px;
}

.inventory-page tbody tr:hover {
    background: #f8faff;
}

.inventory-page .asset-code {
    color: #2457d6;
    font-weight: 800;
}

.inventory-page .stock {
    font-weight: 800;
}

.inventory-page .badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    background: #e2e8f0;
    color: #475569;
}

.inventory-page .badge.available {
    background: #dcfce7;
    color: #166534;
}

.inventory-page .badge.maintenance {
    background: #fef3c7;
    color: #92400e;
}

.inventory-page .badge.unavailable {
    background: #fee2e2;
    color: #991b1b;
}

.inventory-page .badge.low {
    background: #ffedd5;
    color: #9a3412;
}

.inventory-page .action-link {
    display: inline-block;
    padding: 7px 10px;
    border-radius: 7px;
    background: #eaf1ff;
    color: #2457d6;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

.inventory-page .delete-btn {
    display: inline-block;
    padding: 7px 10px;
    border: 0;
    border-radius: 7px;
    background: #fee2e2;
    color: #b91c1c;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.inventory-page .alert {
    padding: 13px 16px;
    margin-bottom: 18px;
    border-radius: 10px;
    background: #dcfce7;
    color: #166534;
}

.inventory-page .alert.error {
    background: #fee2e2;
    color: #991b1b;
}

.inventory-page .empty {
    padding: 35px;
    text-align: center;
    color: #64748b;
}

@media (max-width: 1000px) {
    .inventory-page .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .inventory-page .filters {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 560px) {
    .inventory-page .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .inventory-page .stat-card {
        padding: 15px;
    }

    .inventory-page .stat-number {
        font-size: 24px;
    }

    .inventory-page .filters {
        grid-template-columns: 1fr;
    }

    .inventory-page .panel {
        padding: 15px;
    }
}

@media print {
    .inventory-page .add-btn,
    .inventory-page .filters,
    .inventory-page .actions,
    .inventory-page .delete-form {
        display: none !important;
    }

    .inventory-page .panel,
    .inventory-page .stat-card {
        box-shadow: none;
        break-inside: avoid;
    }
}
</style>

<div class="inventory-page">

    <div class="page-heading">
        <div>
            <h2>Equipment Inventory</h2>
            <p>Manage, monitor, and track computer laboratory equipment.</p>
        </div>

        <a href="equipment_add.php" class="add-btn">
            + Add Equipment
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-label">Equipment Types</div>
            <div class="stat-number">
                <?= number_format($totalEquipment) ?>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-label">Total Items</div>
            <div class="stat-number">
                <?= number_format($totalQuantity) ?>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-label">Low Stock Items</div>
            <div class="stat-number">
                <?= number_format($lowStock) ?>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-label">Under Maintenance</div>
            <div class="stat-number">
                <?= number_format($maintenance) ?>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3>Search and Filter Equipment</h3>

        <form method="GET" action="inventory.php" class="filters">
            <input
                type="search"
                name="search"
                placeholder="Search asset code, equipment, category..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option
                        value="<?= htmlspecialchars($cat) ?>"
                        <?= $category === $cat ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status">
                <option value="">All Statuses</option>
                <option value="Available"
                    <?= $status === 'Available' ? 'selected' : '' ?>>
                    Available
                </option>
                <option value="In Use"
                    <?= $status === 'In Use' ? 'selected' : '' ?>>
                    In Use
                </option>
                <option value="Under Maintenance"
                    <?= $status === 'Under Maintenance' ? 'selected' : '' ?>>
                    Under Maintenance
                </option>
                <option value="Unavailable"
                    <?= $status === 'Unavailable' ? 'selected' : '' ?>>
                    Unavailable
                </option>
            </select>

            <button type="submit" class="filter-btn">Search</button>

            <a href="inventory.php" class="reset-btn">Reset</a>
        </form>
    </div>

    <div class="panel">
        <h3>Equipment List (<?= count($equipmentList) ?> records)</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Equipment Name</th>
                        <th>Category</th>
                        <th>Total Qty.</th>
                        <th>Available</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($equipmentList) > 0): ?>
                    <?php foreach ($equipmentList as $item): ?>
                        <?php
                        $currentStatus = strtolower(
                            trim($item['status'] ?? '')
                        );

                        $badgeClass = 'badge';

                        if ($currentStatus === 'available') {
                            $badgeClass .= ' available';
                        } elseif (strpos($currentStatus, 'maintenance') !== false) {
                            $badgeClass .= ' maintenance';
                        } elseif (
                            $currentStatus === 'unavailable'
                            || $currentStatus === 'damaged'
                        ) {
                            $badgeClass .= ' unavailable';
                        }

                        $isLow = (int)$item['quantity'] > 0
                            && (int)$item['available_quantity'] <= 5;
                        ?>

                        <tr>
                            <td class="asset-code">
                                <?= htmlspecialchars($item['asset_code'] ?? '') ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($item['equipment_name'] ?? '') ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['category'] ?? '') ?>
                            </td>

                            <td>
                                <?= number_format((int)$item['quantity']) ?>
                            </td>

                            <td class="stock">
                                <span class="<?= $isLow ? 'badge low' : '' ?>">
                                    <?= number_format((int)$item['available_quantity']) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['location'] ?? '') ?>
                            </td>

                            <td>
                                <span class="<?= $badgeClass ?>">
                                    <?= htmlspecialchars($item['status'] ?? 'Unknown') ?>
                                </span>
                            </td>

                            <td class="actions">
                                <a
                                    class="action-link"
                                    href="equipment_edit.php?id=<?= (int)$item['id'] ?>"
                                >Edit</a>

                                <form
                                    method="POST"
                                    class="delete-form"
                                    style="display:inline"
                                    onsubmit="return confirm('Are you sure you want to delete this equipment?');"
                                >
                                    <input
                                        type="hidden"
                                        name="delete_id"
                                        value="<?= (int)$item['id'] ?>"
                                    >
                                    <button type="submit" class="delete-btn">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty">
                            No equipment found. Try another search or add new equipment.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px; text-align:right;">
            <button
                type="button"
                class="filter-btn"
                onclick="window.print()"
            >
                Print Inventory
            </button>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
