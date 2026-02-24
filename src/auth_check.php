<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /pos_inventory_system/src/login.php");
    exit;
}

if (isset($required_role) && $_SESSION["user_role"] !== $required_role) {
    header("Location: /pos_inventory_system/public/cashier/dashboard.php");
    exit;
}