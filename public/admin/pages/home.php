<?php

require_once "../../config/db_config.php";
require_once "../../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../src/login.php");
    exit;
}

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "ADMIN") {
    header("Location: ../cashier/dashboard.php");
    exit;
}

$stmt = $link_id->prepare("
    SELECT IFNULL(SUM(total_amount),0) as today_sales
    FROM sales
    WHERE DATE(created_at) = CURDATE()
");
$stmt->execute();
$todaySales = $stmt->fetch(PDO::FETCH_ASSOC)["today_sales"];

$stmt = $link_id->prepare("
    SELECT COUNT(*) as total_trans
    FROM sales
    WHERE DATE(created_at) = CURDATE()
");
$stmt->execute();
$todayTrans = $stmt->fetch(PDO::FETCH_ASSOC)["total_trans"];

$stmt = $link_id->prepare("
    SELECT COUNT(*) as low_stock
    FROM products
    WHERE quantity <= reorder_level
");
$stmt->execute();
$lowStockCount = $stmt->fetch(PDO::FETCH_ASSOC)["low_stock"];

$stmt = $link_id->prepare("
    SELECT COUNT(*) as supplier_total
    FROM suppliers
");
$stmt->execute();
$supplierCount = $stmt->fetch(PDO::FETCH_ASSOC)["supplier_total"];

$stmt = $link_id->prepare("
    SELECT *
    FROM sales
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute();
$recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
</head>

<body>

    <div class="home-wrapper">
        <div class="content">
            <header class="header">
                <div class="header-left">
                    <p>Welcome to POS System,
                        <span><?= htmlspecialchars($_SESSION["user_full_name"] ?? "Admin"); ?></span>
                    </p>
                </div>
            </header>

            <div class="dashboard-cards">

                <div class="card card-blue">
                    <div class="card-icon">₱</div>
                    <div class="card-info">
                        <h3 id="todaySales">₱ 0.00</h3>
                        <p>Today's Sales</p>
                    </div>
                </div>

                <div class="card card-green">
                    <div class="card-icon">🛒</div>
                    <div class="card-info">
                        <h3 id="todayTrans">0</h3>
                        <p>Today's Transactions</p>
                    </div>
                </div>

                <div class="card card-red">
                    <div class="card-icon">⚠️</div>
                    <div class="card-info">
                        <h3 id="lowStockCount">0</h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>

                <div class="card card-orange">
                    <div class="card-icon">🚚</div>
                    <div class="card-info">
                        <h3 id="supplierCount">0</h3>
                        <p>Total Suppliers</p>
                    </div>
                </div>

            </div>

            <div class="recent-activity">
                <h3>Recent Sales</h3>
            </div>
        </div>
    </div>

</body>

</html>