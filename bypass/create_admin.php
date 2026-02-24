<!-- < / ?php
require_once "../config/db_config.php";
require_once "../include/lx.pdodb.php";

$adminPassword = password_hash("admin123", PASSWORD_DEFAULT);

$arr_admin = array();
$arr_admin["full_name"] = "Administrator";
$arr_admin["username"] = "admin";
$arr_admin["password"] = $adminPassword;
$arr_admin["role"] = "ADMIN";

$result = PDO_InsertRecord($link_id, "users", $arr_admin, true);

echo "<hr>";
var_dump($result); -->