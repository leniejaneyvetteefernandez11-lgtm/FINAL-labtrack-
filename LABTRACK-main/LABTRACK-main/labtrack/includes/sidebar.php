
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <a class="brand" href="dashboard.php">
        <span class="brand-mark small">LT</span>
        <span>
            <strong>LABTRACK</strong>
            <small>LAB MANAGEMENT</small>
        </span>
    </a>

    <div class="nav-label">MAIN MENU</div>

    <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
       href="dashboard.php">
        <span class="nav-icon">▦</span> Dashboard
    </a>

    <a class="nav-link <?= $currentPage === 'inventory.php' ? 'active' : '' ?>"
       href="inventory.php">
        <span class="nav-icon">▤</span> Inventory Management
    </a>

    <a class="nav-link <?= $currentPage === 'equipment.php' ? 'active' : '' ?>"
       href="equipment.php">
        <span class="nav-icon">▣</span> Equipment Status
    </a>

    <a class="nav-link <?= $currentPage === 'reports.php' ? 'active' : '' ?>"
       href="reports.php">
        <span class="nav-icon">▥</span> Reports
    </a>

    <div class="nav-label">ACCOUNT</div>

    <a class="nav-link logout-link" href="logout.php">
        <span class="nav-icon">↪</span> Logout
    </a>

    <div class="sidebar-bottom">
        <div class="admin-avatar">A</div>
        <div class="admin-info">
            <strong><?= e($_SESSION['admin_name'] ?? 'Administrator') ?></strong>
            <small>System Administrator</small>
        </div>
        <span class="online-dot"></span>
    </div>
</aside>
