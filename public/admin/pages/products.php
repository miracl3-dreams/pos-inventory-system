<?php
require_once "../../include/session.php";
require_once "../../config/db_config.php";
require_once "../../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "ADMIN") {
    header("Location: ../../login.php");
    exit;
}

function redirectToPage($msg = '')
{
    $url = "dashboard.php?page=products";
    if ($msg) {
        $url .= "&msg=" . urlencode($msg);
    }
    header("Location: $url");
    exit;
}

$editProduct = null;
$searchTerm = trim($_GET['search'] ?? '');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = (int) ($_POST["product_id"] ?? 0);
    $barcode = trim($_POST["barcode"] ?? "");
    $product_name = trim($_POST["product_name"] ?? "");
    $category_id = (int) ($_POST["category_id"] ?? 0);
    $supplier_id = (int) ($_POST["supplier_id"] ?? 0);
    $cost_price = (float) ($_POST["cost_price"] ?? 0);
    $selling_price = (float) ($_POST["selling_price"] ?? 0);
    $stock_qty = (int) ($_POST["stock_qty"] ?? 0);
    $reorder_level = (int) ($_POST["reorder_level"] ?? 0);
    $unit = trim($_POST["unit"] ?? "");

    if ($product_name === "" || $barcode === "") {
        redirectToPage("Product name and barcode required.");
    }

    if ($action === "add") {
        $stmt = $link_id->prepare("SELECT product_id FROM products WHERE barcode = ? LIMIT 1");
        $stmt->execute([$barcode]);
        if ($stmt->fetch()) {
            redirectToPage("Product already exists.");
        }
        $data = [
            "barcode" => $barcode,
            "product_name" => $product_name,
            "category_id" => $category_id,
            "supplier_id" => $supplier_id,
            "cost_price" => $cost_price,
            "selling_price" => $selling_price,
            "stock_qty" => $stock_qty,
            "reorder_level" => $reorder_level,
            "unit" => $unit,
            "is_active" => 1,
        ];
        PDO_InsertRecord($link_id, "products", $data, true);
        redirectToPage("Product added successfully.");
    }

    if ($action === "edit" && $id > 0) {
        $stmt = $link_id->prepare("SELECT product_id FROM products WHERE barcode = ? AND product_id != ? LIMIT 1");
        $stmt->execute([$barcode, $id]);
        if ($stmt->fetch()) {
            redirectToPage("Product already exists.");
        }
        $stmt = $link_id->prepare("UPDATE products SET barcode = ?, product_name = ?, category_id = ?, supplier_id = ?, cost_price = ?, selling_price = ?, stock_qty = ?, reorder_level = ?, unit = ?, updated_at = ? WHERE product_id = ?");
        $stmt->execute([$barcode, $product_name, $category_id, $supplier_id, $cost_price, $selling_price, $stock_qty, $reorder_level, $unit, date("Y-m-d H:i:s"), $id]);
        redirectToPage("Product updated successfully.");
    }
}

if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];
    if ($id > 0) {
        $stmt = $link_id->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        redirectToPage("Product deleted.");
    }
}

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    if ($id > 0) {
        $stmt = $link_id->prepare("SELECT * FROM products WHERE product_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$limit = 10;
$page = (isset($_GET['pg']) && is_numeric($_GET['pg'])) ? (int) $_GET['pg'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON c.category_id = p.category_id LEFT JOIN suppliers s ON s.supplier_id = p.supplier_id WHERE 1=1";
$countParams = [];
if ($searchTerm !== '') {
    $searchValue = "%$searchTerm%";
    $countSql .= " AND (p.product_name LIKE ? OR p.barcode LIKE ? OR c.category_name LIKE ? OR s.supplier_name LIKE ?)";
    $countParams = [$searchValue, $searchValue, $searchValue, $searchValue];
}
$countStmt = $link_id->prepare($countSql);
$countStmt->execute($countParams);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$sql = "SELECT p.*, c.category_name, s.supplier_name FROM products p LEFT JOIN categories c ON c.category_id = p.category_id LEFT JOIN suppliers s ON s.supplier_id = p.supplier_id WHERE 1=1";
if ($searchTerm !== '') {
    $sql .= " AND (p.product_name LIKE ? OR p.barcode LIKE ? OR c.category_name LIKE ? OR s.supplier_name LIKE ?)";
}
$sql .= " ORDER BY p.product_id DESC LIMIT ? OFFSET ?";

$stmt = $link_id->prepare($sql);
$idx = 1;
if ($searchTerm !== '') {
    $searchValue = "%$searchTerm%";
    $stmt->bindValue($idx++, $searchValue);
    $stmt->bindValue($idx++, $searchValue);
    $stmt->bindValue($idx++, $searchValue);
    $stmt->bindValue($idx++, $searchValue);
}
$stmt->bindValue($idx++, (int) $limit, PDO::PARAM_INT);
$stmt->bindValue($idx++, (int) $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories_query = $link_id->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$categories = ($categories_query) ? $categories_query->fetchAll(PDO::FETCH_ASSOC) : [];

$suppliers_query = $link_id->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
$suppliers = ($suppliers_query) ? $suppliers_query->fetchAll(PDO::FETCH_ASSOC) : [];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Products</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Manage <span>Products</span></p>
        </div>
        <div class="header-right">
            <form method="GET" class="search-container">
                <input type="hidden" name="page" value="products">
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
                <a href="dashboard.php?page=products" class="btn-clear-action">Clear Search</a>
            <?php endif; ?>

            <button class="btn-primary" onclick="openModal()">+ Add Product</button>
        </div>
    </div>

    <div id="productModal" class="modal" style="<?= $editProduct ? 'display:block;' : 'display:none;'; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?= $editProduct ? 'Edit Product' : 'Add New Product'; ?></h3>
                <span class="close-btn" style="cursor:pointer" onclick="closeModal()">&times;</span>
            </div>

            <form method="POST" action="dashboard.php?page=products">
                <div class="form-body">
                    <input type="hidden" name="action" value="<?= $editProduct ? 'edit' : 'add' ?>">
                    <input type="hidden" name="product_id" value="<?= $editProduct['product_id'] ?? ''; ?>">

                    <div class="input-group">
                        <label>Product Barcode</label>
                        <div class="input-with-icon">
                            <input type="text" name="barcode" required
                                value="<?= htmlspecialchars($editProduct['barcode'] ?? ''); ?>">
                            <i class="fa-solid fa-barcode"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Product Name</label>
                        <div class="input-with-icon">
                            <input type="text" name="product_name" required
                                value="<?= htmlspecialchars($editProduct['product_name'] ?? ''); ?>">
                            <i class="fa-brands fa-product-hunt"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Product Category</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id']; ?>" <?= (isset($editProduct['category_id']) && $editProduct['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Product Supplier</label>
                        <select name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $sup): ?>
                                <option value="<?= $sup['supplier_id']; ?>" <?= (isset($editProduct['supplier_id']) && $editProduct['supplier_id'] == $sup['supplier_id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($sup['supplier_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Product Cost Price</label>
                        <div class="input-with-icon">
                            <input type="number" step="0.01" name="cost_price" required
                                value="<?= $editProduct['cost_price'] ?? ''; ?>">
                            <i class="fa-solid fa-peso-sign"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Product Sell Price</label>
                        <div class="input-with-icon">
                            <input type="number" step="0.01" name="selling_price" required
                                value="<?= $editProduct['selling_price'] ?? ''; ?>">
                            <i class="fa-solid fa-peso-sign"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Stock Quantity</label>
                        <div class="input-with-icon">
                            <input type="number" name="stock_qty" required
                                value="<?= $editProduct['stock_qty'] ?? ''; ?>">
                            <i class="fa-solid fa-certificate"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Reorder Level</label>
                        <div class="input-with-icon">
                            <input type="number" name="reorder_level" required
                                value="<?= $editProduct['reorder_level'] ?? ''; ?>">
                            <i class="fa-solid fa-bars-staggered"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Product Unit(Pcs)</label>
                        <div class="input-with-icon">
                            <input type="text" name="unit" required
                                value="<?= htmlspecialchars($editProduct['unit'] ?? ''); ?>">
                            <i class="fa-solid fa-1"></i>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit"
                        class="btn-primary"><?= $editProduct ? 'Update Product' : 'Save Product'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="global-container">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Stock Quantity</th>
                        <th>Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No products found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td data-label="Product Name"><strong>
                                        <?= htmlspecialchars($prod["product_name"]); ?>
                                    </strong></td>
                                <td data-label="Category Name">
                                    <?= htmlspecialchars($prod["category_name"]); ?>
                                </td>
                                <td data-label="Supplier Name">
                                    <?= htmlspecialchars($prod["supplier_name"]); ?>
                                </td>
                                <td data-label="Cost Price">₱
                                    <?= number_format($prod["cost_price"], 2); ?>
                                </td>
                                <td data-label="Selling Price">₱
                                    <?= number_format($prod["selling_price"], 2); ?>
                                </td>
                                <td data-label="Stock Quantity">
                                    <?= $prod["stock_qty"]; ?>
                                </td>
                                <td data-label="Unit">
                                    <?= htmlspecialchars($prod["unit"]); ?>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-btns">
                                        <a href="dashboard.php?page=products&edit=<?= $prod["product_id"]; ?>"
                                            class="btn-edit">Edit</a>
                                        <a href="#" class="btn-delete"
                                            onclick="return confirmDelete(<?= $prod['product_id']; ?>)">Delete</a>
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
            document.getElementById("productModal").style.display = "block";
        }
        function closeModal() {
            window.location.href = "dashboard.php?page=products";
        }
        function confirmDelete(id) {
            alertify.confirm("Delete Confirmation", "Are you sure you want to delete this product?",
                function () { window.location.href = "dashboard.php?page=products&delete=" + id; },
                function () { alertify.error("Delete cancelled"); }
            );
            return false;
        }
        window.onclick = function (event) {
            let modal = document.getElementById("productModal");
            if (event.target == modal) {
                closeModal();
            }
        }
        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('msg')) {
                const msg = urlParams.get('msg');
                if (msg.toLowerCase().includes('success') || msg.toLowerCase().includes('added') || msg.toLowerCase().includes('updated')) {
                    alertify.success(msg);
                } else {
                    alertify.error(msg);
                }

                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?page=products";
                window.history.replaceState({}, document.title, cleanUrl);
            }
        };
    </script>
    <?php if ($editProduct): ?>
        <script>openModal();</script>
    <?php endif; ?>
</body>

</html>