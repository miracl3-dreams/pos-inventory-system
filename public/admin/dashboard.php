<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../src/login.php");
    exit;
}

if ($_SESSION["user_role"] !== "ADMIN") {
    header("Location: ../cashier/dashboard.php");
    exit;
}

include "../../include/header.php";

$page = $_GET['page'] ?? 'home';

$allowed_pages = [
    'home',
    'categories',
    'products',
    'suppliers',
    'stock',
    'purchases',
];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}
?>

<div class="dashboard-wrapper">

    <?php include "../../include/sidebar.php"; ?>

    <div class="main-content">

        <?php
        include "pages/" . $page . ".php";
        ?>

    </div>

</div>

</body>

</html>

<script src="/pos_inventory_system/assets/js/burger-a-menu.js"></script>
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>