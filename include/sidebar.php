<?php
$role = $_SESSION["user_role"] ?? '';
$current_page = $_GET['page'] ?? 'home';
?>

<div class="sidebar">

    <div class="sidebar-top">
        <div class="logo">Tindahan<span>POS</span></div>

        <div class="menu-toggle" id="dashboard-burger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>

    <nav id="nav-menu">

        <?php if ($role === 'ADMIN'): ?>

            <a href="dashboard.php?page=home" class="<?= $current_page === 'home' ? 'active' : '' ?>">
                Dashboard
            </a>

            <a href="dashboard.php?page=categories" class="<?= $current_page === 'categories' ? 'active' : '' ?>">
                Categories
            </a>

            <a href="dashboard.php?page=products" class="<?= $current_page === 'products' ? 'active' : '' ?>">
                Products
            </a>

            <a href="dashboard.php?page=suppliers" class="<?= $current_page === 'suppliers' ? 'active' : '' ?>">
                Suppliers
            </a>

            <a href="dashboard.php?page=sales" class="<?= $current_page === 'sales' ? 'active' : '' ?>">
                Sales
            </a>

            <a href="dashboard.php?page=transactions" class="<?= $current_page === 'transactions' ? 'active' : '' ?>">
                Transactions
            </a>

        <?php elseif ($role === 'CASHIER'): ?>

            <a href="dashboard.php?page=home" class="<?= $current_page === 'home' ? 'active' : '' ?>">
                Dashboard
            </a>

            <a href="dashboard.php?page=pos" class="<?= $current_page === 'pos' ? 'active' : '' ?>">
                POS
            </a>

            <!-- <a href="dashboard.php?page=sales" class="< / ?= $current_page === 'sales' ? 'active' : '' ?>">
                Sales
            </a> -->

        <?php endif; ?>

        <div class="logout-container">
            <a href="../../src/settings.php" class="settings">Settings</a>
            <a href="../../src/logout.php" class="logout">Logout</a>
        </div>

    </nav>

</div>