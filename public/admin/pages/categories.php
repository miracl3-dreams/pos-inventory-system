<?php

require_once "../../include/session.php";
require_once "../../config/db_config.php";
require_once "../../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "ADMIN") {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "add") {

    $name = trim($_POST["category_name"] ?? "");
    $desc = trim($_POST["description"] ?? "");

    if ($name === "") {
        die("Category name is required.");
    }

    try {

        $stmt = $link_id->prepare("
            SELECT category_id 
            FROM categories 
            WHERE category_name = ?
            LIMIT 1
        ");
        $stmt->execute([$name]);

        if ($stmt->fetch()) {
            die("Category already exists.");
        }

        $data = [
            "category_name" => $name,
            "description" => $desc,
            "is_active" => 1
        ];

        $result = PDO_InsertRecord($link_id, "categories", $data, true);

        if ($result !== true) {
            die("Insert failed: " . $result);
        }

        header("Location: categories.php?success=1");
        exit;

    } catch (PDOException $e) {
        die("System Error: " . $e->getMessage());
    }
}

if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];

    if ($id > 0) {

        try {

            $stmt = $link_id->prepare("
                UPDATE categories 
                SET is_active = 0 
                WHERE category_id = ?
            ");
            $stmt->execute([$id]);

        } catch (PDOException $e) {
            die("Delete failed: " . $e->getMessage());
        }
    }

    header("Location: categories.php");
    exit;
}

try {

    $stmt = $link_id->query("
        SELECT c.*,
        (
            SELECT COUNT(*) 
            FROM products p 
            WHERE p.category_id = c.category_id
        ) AS total_products
        FROM categories c
        ORDER BY c.category_id DESC
    ");

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Fetch error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <!-- <span class="close" onclick="closeModal()">&times;</span> -->
            </div>
            <form method="POST" class="category-form">
                <input type="hidden" name="action" value="add">
                <div class="form-body">
                    <div class="input-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" placeholder="e.g. Beverages">
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

    <div class="global-container">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
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
                            <td data-label="Category Name"><strong><?= htmlspecialchars($cat["category_name"]); ?></strong>
                            </td>
                            <td data-label="Description"><?= htmlspecialchars($cat["description"]); ?></td>
                            <td data-label="ProductsCount"><?= $cat["total_products"]; ?></td>
                            <td data-label="Status">
                                <span class="status-badge <?= $cat["is_active"] ? 'active' : 'inactive'; ?>">
                                    <?= $cat["is_active"] ? "Active" : "Inactive"; ?>
                                </span>
                            </td>
                            <td data-label="Actions">
                                <div class="action-btns">
                                    <a href="?edit=<?= $cat["category_id"]; ?>" class="btn-edit">Edit</a>
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