<?php
require_once "../config/db_config.php";
require_once "../include/lx.pdodb.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $xfname = trim($_POST["full_name"] ?? "");
    $xuname = trim($_POST["username"] ?? "");
    $rawPassword = $_POST["password"] ?? "";

    if ($xfname === "" || $xuname === "" || $rawPassword === "") {
        die("Please fill in all required fields.");
    }

    try {

        $stmt = $link_id->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$xuname]);
        $xeuser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($xeuser) {
            die("Username already exists. Please choose a different username.");
        }

        $xpassword = password_hash($rawPassword, PASSWORD_DEFAULT);

        $arr_user = array();
        $arr_user["full_name"] = $xfname;
        $arr_user["username"] = $xuname;
        $arr_user["password"] = $xpassword;
        $arr_user["role"] = "CASHIER";
        $arr_user["is_active"] = 1;

        $result = PDO_InsertRecord($link_id, "users", $arr_user, true);

        if ($result === true) {
            header("Location: login.php");
            exit;
        } else {
            die("Registration failed: " . $result);
        }

    } catch (PDOException $e) {
        die("System error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../include/header.php"); ?>
    <title>POS Registration</title>
</head>

<body>
    <div class="register">
        <h2 style="text-align: center; margin-bottom: 20px; color: #333;">Register New User</h2>
        <form action="register.php" method="POST">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-group password-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" id="passwordField" placeholder="Password" required>
                <span class="toggle-password" id="togglePassword">👁️</span>
            </div>

            <button type="submit">Register</button>
        </form>
        <p style="text-align: center; font-size: 12px; margin-top: 15px; color: #777;">
            Already have an account? <a href="login.php"
                style="color: #2ecc71; text-decoration: none; font-weight: 700;">Log in</a>
        </p>
    </div>

    <script src="../assets/js/register.js"></script>

</body>

</html>