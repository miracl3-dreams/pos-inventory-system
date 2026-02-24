<?php
session_start();

header("Content-Type: application/json");

require_once "../../config/db_config.php";
require_once "../../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "ADMIN") {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

try {

    $stmt = $link_id->query("
        SELECT IFNULL(SUM(total_amount),0)
        FROM sales
        WHERE DATE(created_at) = CURDATE()
    ");
    $todaySales = $stmt->fetchColumn();

    $stmt = $link_id->query("
        SELECT COUNT(*)
        FROM sales
        WHERE DATE(created_at) = CURDATE()
    ");
    $todayTrans = $stmt->fetchColumn();

    $stmt = $link_id->query("
        SELECT COUNT(*)
        FROM products
        WHERE quantity <= reorder_level
    ");
    $lowStockCount = $stmt->fetchColumn();

    $stmt = $link_id->query("
        SELECT COUNT(*)
        FROM suppliers
    ");
    $supplierCount = $stmt->fetchColumn();

    echo json_encode([
        "todaySales" => $todaySales,
        "todayTrans" => $todayTrans,
        "lowStockCount" => $lowStockCount,
        "supplierCount" => $supplierCount
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => "Server Error"]);
}