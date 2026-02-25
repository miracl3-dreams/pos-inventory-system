<?php

require_once "../../config/db_config.php";
require_once "../../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "ADMIN") {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST["action"]) && $_POST["action"] === "add") {

    $name = trim($_POST["category_name"]);
    $desc = trim($_POST["description"]);

    if ($name !== "") {

        $stmt = $link_id->prepare("SELECT category_id FROM categories WHERE category_name = ?");
        $stmt->execute([$name]);

        if (!$stmt->fetch()) {

            $data = [
                "category_name" => $name,
                "description" => $desc,
                "is_active" => 1
            ];

            PDO_InsertRecord($link_id, "categories", $data, true);
        }
    }

    header("Location: categories.php");
    exit;
}

if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];

    $stmt = $link_id->prepare("UPDATE categories SET is_active = 0 WHERE category_id = ?");
    $stmt->execute([$id]);

    header("Location: categories.php");
    exit;
}

$stmt = $link_id->query("
    SELECT c.*, 
    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) AS total_products
    FROM categories c
    ORDER BY c.category_id DESC
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Categories</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Manage <span>Categories</span></p>
        </div>
        <div class="header-right">
            <button class="btn-primary" onclick="openModal()">+ Add Category</button>
        </div>
    </div>

    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Category</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" class="category-form">
                <input type="hidden" name="action" value="add">
                <div class="form-body">
                    <div class="input-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" placeholder="e.g. Beverages" required>
                    </div>
                    <div class="input-group">
                        <label>Category Description</label>
                        <textarea name="description" placeholder="Enter description here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <div class="category-container">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>#<?= str_pad($cat["category_id"], 3, "0", STR_PAD_LEFT); ?></td>
                            <td><strong><?= htmlspecialchars($cat["category_name"]); ?></strong></td>
                            <td><?= htmlspecialchars($cat["description"]); ?></td>
                            <td><?= $cat["total_products"]; ?></td>
                            <td>
                                <span class="status-badge <?= $cat["is_active"] ? 'active' : 'inactive'; ?>">
                                    <?= $cat["is_active"] ? "Active" : "Inactive"; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?delete=<?= $cat["category_id"]; ?>" class="btn-delete"
                                        onclick="return confirm('Delete this category?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>

<script>
    function openModal() {
        document.getElementById("categoryModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("categoryModal").style.display = "none";
    }

    window.onclick = function (event) {
        let modal = document.getElementById("categoryModal");
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
<script src="/pos_inventory_system/assets/js/burger-a-menu.js"></script>