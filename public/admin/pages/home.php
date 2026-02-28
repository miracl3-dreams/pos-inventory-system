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
    WHERE DATE(sale_date) = CURDATE()
");
$stmt->execute();
$todaySales = $stmt->fetch(PDO::FETCH_ASSOC)["today_sales"];

$stmt = $link_id->prepare("
    SELECT COUNT(*) as total_trans
    FROM sales
    WHERE DATE(sale_date) = CURDATE()
");
$stmt->execute();
$todayTrans = $stmt->fetch(PDO::FETCH_ASSOC)["total_trans"];

$stmt = $link_id->prepare("
    SELECT COUNT(*) as low_stock
    FROM products
    WHERE stock_qty <= reorder_level
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
    ORDER BY sale_date DESC
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
                        <h3 id="todaySales">₱
                            <?= number_format($todaySales, 2); ?>
                        </h3>
                        <p>Today's Sales</p>
                    </div>
                </div>

                <div class="card card-green">
                    <div class="card-icon">🛒</div>
                    <div class="card-info">
                        <h3 id="todayTrans">
                            <?= number_format($todayTrans); ?>
                        </h3>
                        <p>Today's Transactions</p>
                    </div>
                </div>

                <div class="card card-red">
                    <div class="card-icon">⚠️</div>
                    <div class="card-info">
                        <h3 id="lowStockCount">
                            <?= number_format($lowStockCount); ?>
                        </h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>

                <div class="card card-orange">
                    <div class="card-icon">🚚</div>
                    <div class="card-info">
                        <h3 id="supplierCount">
                            <?= number_format($supplierCount); ?>
                        </h3>
                        <p>Total Suppliers</p>
                    </div>
                </div>

            </div>

            <div class="recent-activity">
                <h3>Recent Sales</h3>
                <table border="1" style="width:100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Total Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentSales)): ?>
                            <?php foreach ($recentSales as $sale): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($sale['id']); ?>
                                    </td>
                                    <td>₱
                                        <?= number_format($sale['total_amount'], 2); ?>
                                    </td>
                                    <td>
                                        <?= date("M d, Y h:i A", strtotime($sale['sale_date'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No recent sales found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

</body>

</html>