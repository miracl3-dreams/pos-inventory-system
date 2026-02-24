<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php include("../include/header.php"); ?>
</head>

<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">Tindahan<span>POS</span></div>

            <div class="nav-links" id="nav-list">
                <a href="../src/login.php" class="btn-nav">Login</a>
                <a href="../src/register.php" class="btn-nav btn-register">Register</a>
            </div>

            <div class="menu-toggle" id="mobile-menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
    </header>

    <main class="hero-section">
        <div class="hero-content">
            <h1>Welcome to TindahanPOS</h1>
            <p>Your one-stop solution for efficient inventory management and seamless sales tracking.</p>
            <div class="hero-btns">
                <a href="../src/login.php" class="btn-primary">Get Started</a>
                <a href="../src/register.php" class="btn-secondary">Sign Up</a>
            </div>
    </main>

    <?php include("../include/footer.php"); ?>

    <script src="../assets/js/script.js"></script>
</body>

</html>