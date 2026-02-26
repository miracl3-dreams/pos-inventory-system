<?php
require_once "../../include/session.php";
require_once "../../config/db_config.php";
require_once "../../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "ADMIN") {
    header("Location: ../../login.php");
    exit;
}

function redirectToPage($status = '', $msg = '')
{
    $url = "dashboard.php?page=categories";
    if ($status && $msg) {
        $url .= "&status=$status&msg=" . urlencode($msg);
    }
    header("Location: $url");
    exit;
}

$editCategory = null;
$searchTerm = trim($_GET['search'] ?? '');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $name = trim($_POST["category_name"] ?? "");
    $desc = trim($_POST["description"] ?? "");
    $id = (int) ($_POST["category_id"] ?? 0);

    if ($name === "") {
        redirectToPage('error', 'Category name is required.');
    }

    if ($action === "add") {
        $stmt = $link_id->prepare("SELECT category_id FROM categories WHERE category_name = ? LIMIT 1");
        $stmt->execute([$name]);

        if (!$stmt->fetch()) {
            $data = [
                "category_name" => $name,
                "description" => $desc,
                "is_active" => 1
            ];
            PDO_InsertRecord($link_id, "categories", $data, true);
            redirectToPage('success', 'Category added successfully!');
        } else {
            redirectToPage('error', 'Category name already exists.');
        }
    }

    if ($action === "edit" && $id > 0) {
        $stmt = $link_id->prepare("SELECT category_id FROM categories WHERE category_name = ? AND category_id != ? LIMIT 1");
        $stmt->execute([$name, $id]);

        if (!$stmt->fetch()) {
            $stmt = $link_id->prepare("UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?");
            $stmt->execute([$name, $desc, $id]);
            redirectToPage('success', 'Category updated successfully!');
        } else {
            redirectToPage('error', 'Category name already exists.');
        }
    }
}

if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];
    if ($id > 0) {
        $stmt = $link_id->prepare("UPDATE categories SET is_active = 0 WHERE category_id = ?");
        $stmt->execute([$id]);
        redirectToPage('success', 'Category archived successfully!');
    }
}

if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];
    if ($id > 0) {
        $stmt = $link_id->prepare("SELECT * FROM categories WHERE category_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $editCategory = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$limit = 10;
$page = isset($_GET['pg']) && is_numeric($_GET['pg']) ? (int) $_GET['pg'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) FROM categories c WHERE 1=1";
$countParams = [];

if ($searchTerm !== '') {
    $countSql .= " AND (c.category_name LIKE ? OR c.description LIKE ?)";
    $countParams[] = "%$searchTerm%";
    $countParams[] = "%$searchTerm%";
}

$countStmt = $link_id->prepare($countSql);
$countStmt->execute($countParams);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$sql = "SELECT c.*, 
       (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id AND p.is_active = 1) AS total_products 
        FROM categories c WHERE 1=1";

$params = [];
if ($searchTerm !== '') {
    $sql .= " AND (c.category_name LIKE ? OR c.description LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

$sql .= " ORDER BY c.category_id DESC LIMIT $limit OFFSET $offset";
$stmt = $link_id->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <form method="GET" class="search-container">
                <input type="hidden" name="page" value="categories">
                <input type="text" name="search" class="search-input-inline" placeholder="Search..."
                    value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-icon-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>

            <?php if ($searchTerm !== ''): ?>
                <a href="dashboard.php?page=categories" class="btn-clear-action">Clear Search</a>
            <?php endif; ?>

            <button class="btn-primary" onclick="openModal()">+ Add Category</button>
        </div>
    </div>

    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <?= $editCategory ? 'Edit Category' : 'Add New Category'; ?>
                </h3>
                <span class="close-btn" style="cursor:pointer" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="dashboard.php?page=categories">
                <div class="form-body">
                    <input type="hidden" name="action" value="<?= $editCategory ? 'edit' : 'add'; ?>">
                    <input type="hidden" name="category_id" value="<?= $editCategory['category_id'] ?? ''; ?>">
                    <div class="input-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" required
                            value="<?= htmlspecialchars($editCategory['category_name'] ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label>Category Description</label>
                        <textarea name="description"
                            rows="4"><?= htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class=" modal-footer">
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
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">No categories found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td data-label="Name"><strong>
                                        <?= htmlspecialchars($cat["category_name"]); ?>
                                    </strong></td>
                                <td data-label="Description">
                                    <?= htmlspecialchars($cat["description"]); ?>
                                </td>
                                <td data-label="Products">
                                    <?= $cat["total_products"]; ?>
                                </td>
                                <td data-label="Status">
                                    <span class="status-badge <?= $cat["is_active"] ? 'active' : 'inactive'; ?>">
                                        <?= $cat["is_active"] ? "Active" : "Archived"; ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-btns">
                                        <a href="dashboard.php?page=categories&edit=<?= $cat["category_id"]; ?>"
                                            class="btn-edit">Edit</a>
                                        <a href="#" class="btn-delete"
                                            onclick="return confirmDelete(<?= $cat['category_id']; ?>)">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="dashboard.php?page=categories&pg=<?= $page - 1 ?>&search=<?= urlencode($searchTerm) ?>">Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="dashboard.php?page=categories&pg=<?= $i ?>&search=<?= urlencode($searchTerm) ?>"
                    class="<?= ($i == $page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="dashboard.php?page=categories&pg=<?= $page + 1 ?>&search=<?= urlencode($searchTerm) ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script>
        function openModal() {
            document.getElementById("categoryModal").style.display = "block";
        }
        function closeModal() {
            window.location.href = "dashboard.php?page=categories";
        }
        function confirmDelete(id) {
            alertify.confirm("Delete Confirmation", "Are you sure you want to archive this category?",
                function () { window.location.href = "dashboard.php?page=categories&delete=" + id; },
                function () { alertify.error("Delete cancelled"); }
            );
            return false;
        }
        window.onclick = function (e) {
            let m = document.getElementById("categoryModal");
            if (e.target == m) {
                closeModal();
            }
        };
        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('status') && urlParams.has('msg')) {
                const status = urlParams.get('status');
                const msg = urlParams.get('msg');
                if (status === 'success') { alertify.success(msg); }
                else if (status === 'error') { alertify.error(msg); }
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?page=categories";
                window.history.replaceState({}, document.title, cleanUrl);
            }
        };
    </script>
    <?php if ($editCategory): ?>
        <script>openModal();</script>
    <?php endif; ?>
</body>

</html>