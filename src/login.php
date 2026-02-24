<?php
session_start();

require_once "../config/db_config.php";
require_once "../include/lx.pdodb.php";

if (isset($_SESSION["user_id"])) {
    header("Location: ../public/cashier/dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $xuname = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($xuname === "" || $password === "") {
        $error = "Please fill up all fields.";
    } else {

        $stmt = $link_id->prepare("
            SELECT user_id, full_name, username, password, role, is_active 
            FROM users 
            WHERE username = ? 
            LIMIT 1
        ");
        $stmt->execute([$xuname]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid username or password.";
        } elseif ((int) $user["is_active"] !== 1) {
            $error = "Your account is inactive. Contact administrator.";
        } elseif (!password_verify($password, $user["password"])) {
            $error = "Invalid username or password.";
        } else {

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_full_name"] = $user["full_name"];
            $_SESSION["user_username"] = $user["username"];
            $_SESSION["user_role"] = $user["role"];

            if ($user["role"] === "ADMIN") {
                header("Location: ../public/admin/dashboard.php");
            } else {
                header("Location: ../public/cashier/dashboard.php");
            }

            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../include/header.php" ?>
    <title>POS Login</title>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <h2 class="form-title">Welcome Back!</h2>
            <p class="form-subtitle">Please enter your details to login</p>

            <?php if ($error != "") {
                echo "<p class='error-msg'>$error</p>";
            } ?>

            <form action="login.php" method="POST" id="loginForm">
                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="text" name="username" placeholder="Username" required>
                </div>

                <div class="input-group password-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="passwordField" placeholder="Password" required>
                    <span class="toggle-password" id="togglePassword">👁️</span>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-text">Login</span>
                    <div class="loader" id="loader"></div>
                </button>
            </form>

            <p class="footer-text">
                Don't have an account? <a href="register.php">Sign Up</a>
            </p>
        </div>

        <script src="../assets/js/login.js"></script>

    </div>
</body>

</html>