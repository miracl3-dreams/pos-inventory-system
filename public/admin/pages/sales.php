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
    $url = "dashboard.php?page=sales";
    if ($msg)
        $url .= "&msg=" . urlencode($msg);
    header("Location: $url");
    exit;
}

$searchTerm = trim($_GET['search'] ?? '');

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "sell") {
    $product_id = (int) $_POST["product_id"];
    $qty = (int) $_POST["quantity"];
    $user_id = $_SESSION["user_id"];

    if ($product_id > 0 && $qty > 0) {
        try {
            $link_id->beginTransaction();

            $stmt = $link_id->prepare("SELECT product_name, selling_price, stock_qty FROM products WHERE product_id = ? FOR UPDATE");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product)
                throw new Exception("Product not found");
            if ($product["stock_qty"] < $qty)
                throw new Exception("Insufficient stock");

            $subtotal = $product["selling_price"] * $qty;

            $stmt = $link_id->prepare("INSERT INTO sales (sale_date, total_amount, payment_method, cashier_id) VALUES (NOW(), ?, 'CASH', ?)");
            $stmt->execute([$subtotal, $user_id]);
            $sale_id = $link_id->lastInsertId();

            $stmt = $link_id->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sale_id, $product_id, $qty, $product["selling_price"], $subtotal]);

            $stmt = $link_id->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE product_id = ?");
            $stmt->execute([$qty, $product_id]);

            $link_id->commit();
            redirectToPage("Sale recorded successfully. Transaction ID: #$sale_id");
        } catch (Exception $e) {
            $link_id->rollBack();
            redirectToPage($e->getMessage());
        }
    }
}

$limit = 10;
$page = max((int) ($_GET['pg'] ?? 1), 1);
$offset = ($page - 1) * $limit;

$params = [];
$searchQuery = "";
if ($searchTerm !== '') {
    $searchQuery = " AND product_name LIKE ?";
    $params[] = "%$searchTerm%";
}

$countStmt = $link_id->prepare("SELECT COUNT(*) FROM products WHERE 1=1 $searchQuery");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$stmt = $link_id->prepare("SELECT product_id, product_name, selling_price, stock_qty, unit FROM products WHERE 1=1 $searchQuery ORDER BY product_name ASC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sales</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Manage <span>Sales</span></p>
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
        </div>
    </div>

    <div id="saleModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal_title">Sell Product</h3>
                <span class="close-btn" style="cursor:pointer" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <div class="form-body">
                    <input type="hidden" name="action" value="sell">
                    <input type="hidden" name="product_id" id="modal_product_id">
                    <div class="input-group">
                        <label id="modal_product_name" style="font-weight: bold; color: #2c3e50;"></label>
                        <div class="input-with-icon">
                            <input type="number" name="quantity" min="1" placeholder="Enter quantity" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm Sale</button>
                </div>
            </form>
        </div>
    </div>

    <div class="global-container">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">No products found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td data-label="Product"><strong><?= htmlspecialchars($prod["product_name"]); ?></strong></td>
                                <td data-label="Price">₱<?= number_format($prod["selling_price"], 2); ?></td>
                                <td data-label="Stock"><?= $prod["stock_qty"]; ?>         <?= htmlspecialchars($prod["unit"]); ?></td>
                                <td data-label="Action">
                                    <button
                                        onclick="sellProduct(<?= $prod['product_id'] ?>, '<?= addslashes($prod['product_name']) ?>')"
                                        class="btn-primary">Sell</button>
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
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="dashboard.php?page=sales&pg=<?= $i ?>&search=<?= urlencode($searchTerm) ?>"
                    class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <script>
        function sellProduct(id, name) {
            document.getElementById("modal_product_id").value = id;
            document.getElementById("modal_product_name").innerText = "Product: " + name;
            document.getElementById("saleModal").style.display = "flex";
        }
        function closeModal() {
            document.getElementById("saleModal").style.display = "none";
        }
        window.onclick = function (event) {
            if (event.target == document.getElementById("saleModal")) {
                closeModal();
            }
        }
    </script>
</body>

</html>