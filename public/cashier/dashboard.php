<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../src/login.php");
    exit;
}

if ($_SESSION["user_role"] !== "CASHIER") {
    header("Location: ../admin/dashboard.php");
    exit;
}

include "../../include/header.php";

$page = $_GET['page'] ?? 'home';

$allowed_pages = [
    'home',
    'pos',
    'sales'
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