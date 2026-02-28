<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = "localhost";
$DB_USERNAME = "root";
$DB_PASS = "";
$DB_NAME = "pos_db";
$DB_CONNECTIONSTRING = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$DB_OPTION = array(
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);

try {
    $link_id = new PDO(
        $DB_CONNECTIONSTRING,
        $DB_USERNAME,
        $DB_PASS,
        $DB_OPTION
    );

    // echo "DATABASE CONNECTED!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
