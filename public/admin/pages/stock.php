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
    $url = "dashboard.php?page=stocks";
    if ($msg) {
        $url .= "&msg=" . urlencode($msg);
    }
    header("Location: $url");
    exit;
}

$editAdjustment = null;
$searchTerm = trim($_GET['search'] ?? '');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $product_id = (int) ($_POST["product_id"] ?? 0);
    $new_qty = (int) ($_POST["new_quantity"] ?? 0);
    $reason = trim($_POST["reason"] ?? "");
    $user_id = $_SESSION["user_id"];

    if ($action === "adjust" && $product_id > 0) {
        try {
            $link_id->beginTransaction();

            $stmt = $link_id->prepare("SELECT stock_qty FROM products WHERE product_id = ? FOR UPDATE");
            $stmt->execute([$product_id]);
            $current_stock = $stmt->fetchColumn();

            if ($current_stock === false) {
                throw new Exception("Product not found.");
            }

            $difference = $new_qty - $current_stock;
            $movement_type = ($difference > 0) ? 'ADJUST_IN' : 'ADJUST_OUT';

            $stmtUpdate = $link_id->prepare("UPDATE products SET stock_qty = ? WHERE product_id = ?");
            $stmtUpdate->execute([$new_qty, $product_id]);

            $adjData = [
                "product_id" => $product_id,
                "old_quantity" => $current_stock,
                "new_quantity" => $new_qty,
                "difference" => $difference,
                "reason" => $reason,
                "adjusted_by" => $user_id,
                "adjusted_at" => date("Y-m-d H:i:s")
            ];
            PDO_InsertRecord($link_id, "stock_adjustments", $adjData);
            $adjustment_id = $link_id->lastInsertId();

            $moveData = [
                "product_id" => $product_id,
                "movement_type" => $movement_type,
                "quantity" => abs($difference),
                "reference_table" => 'stock_adjustments',
                "reference_id" => $adjustment_id,
                "notes" => $reason,
                "created_by" => $user_id,
                "created_at" => date("Y-m-d H:i:s")
            ];
            PDO_InsertRecord($link_id, "stock_movements", $moveData);

            $link_id->commit();
            redirectToPage("Stock adjusted successfully.");
        } catch (Exception $e) {
            $link_id->rollBack();
            redirectToPage("Error: " . $e->getMessage());
        }
    }
}

$limit = 10;
$page = (isset($_GET['pg']) && is_numeric($_GET['pg'])) ? (int) $_GET['pg'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$countSql = "SELECT COUNT(*) FROM products WHERE 1=1";
$countParams = [];
if ($searchTerm !== '') {
    $countSql .= " AND (product_name LIKE ? OR barcode LIKE ?)";
    $countParams = ["%$searchTerm%", "%$searchTerm%"];
}
$countStmt = $link_id->prepare($countSql);
$countStmt->execute($countParams);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$sql = "SELECT product_id, barcode, product_name, stock_qty, unit FROM products WHERE 1=1";
if ($searchTerm !== '') {
    $sql .= " AND (product_name LIKE ? OR barcode LIKE ?)";
}
$sql .= " ORDER BY product_name ASC LIMIT ? OFFSET ?";

$stmt = $link_id->prepare($sql);
$idx = 1;
if ($searchTerm !== '') {
    $searchValue = "%$searchTerm%";
    $stmt->bindValue($idx++, $searchValue);
    $stmt->bindValue($idx++, $searchValue);
}
$stmt->bindValue($idx++, (int) $limit, PDO::PARAM_INT);
$stmt->bindValue($idx++, (int) $offset, PDO::PARAM_INT);
$stmt->execute();
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Stock Management</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Stock <span>Adjustments</span></p>
        </div>
        <div class="header-right">
            <form method="GET" class="search-container">
                <input type="hidden" name="page" value="stocks">
                <input type="text" name="search" class="search-input-inline" placeholder="Search product..."
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
                <a href="dashboard.php?page=stocks" class="btn-clear-action">Clear Search</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="adjustModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Adjust Stock Level</h3>
                <span class="close-btn" style="cursor:pointer" onclick="closeModal()">&times;</span>
            </div>

            <form method="POST" action="dashboard.php?page=stocks">
                <div class="form-body">
                    <input type="hidden" name="action" value="adjust">
                    <input type="hidden" name="product_id" id="modal_product_id">

                    <div class="input-group">
                        <label>Product Name</label>
                        <input type="text" id="modal_product_name" readonly>
                    </div>

                    <div class="input-group">
                        <label>Current Quantity</label>
                        <input type="text" id="modal_current_qty" readonly>
                    </div>

                    <div class="input-group">
                        <label>New Quantity</label>
                        <input type="number" name="new_quantity" id="modal_new_qty" required min="0">
                    </div>

                    <div class="input-group">
                        <label>Adjustment Reason</label>
                        <textarea name="reason" required
                            placeholder="e.g., Damaged items, Physical count correction..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>

    <div class="global-container">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <!-- <th>Barcode</th> -->
                        <th>Product Name</th>
                        <th>Current Stock</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stocks)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">No stocks found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stocks as $st): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($st["product_name"]); ?></strong></td>
                                <td><?= $st["stock_qty"]; ?></td>
                                <td><?= htmlspecialchars($st["unit"]); ?></td>
                                <td>
                                    <?php if ($st["stock_qty"] <= 0): ?>
                                        <span class="status-badge inactive">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="status-badge active">In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button type="button"
                                            onclick="openAdjustModal(<?= $st['product_id']; ?>, '<?= addslashes($st['product_name']); ?>', <?= $st['stock_qty']; ?>)">
                                            Adjust
                                        </button>
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
                <a href="dashboard.php?page=stocks&pg=<?= $page - 1 ?>&search=<?= urlencode($searchTerm) ?>">Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="dashboard.php?page=stocks&pg=<?= $i ?>&search=<?= urlencode($searchTerm) ?>"
                    class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="dashboard.php?page=stocks&pg=<?= $page + 1 ?>&search=<?= urlencode($searchTerm) ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script>
        function openAdjustModal(id, name, qty) {
            document.getElementById("modal_product_id").value = id;
            document.getElementById("modal_product_name").value = name;
            document.getElementById("modal_current_qty").value = qty;
            document.getElementById("modal_new_qty").value = qty;
            document.getElementById("adjustModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("adjustModal").style.display = "none";
        }

        window.onclick = function (event) {
            let modal = document.getElementById("adjustModal");
            if (event.target == modal) { closeModal(); }
        }

        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('msg')) {
                const msg = urlParams.get('msg');
                if (msg.toLowerCase().includes('success')) {
                    alertify.success(msg);
                } else {
                    alertify.error(msg);
                }
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?page=stocks";
                window.history.replaceState({}, document.title, cleanUrl);
            }
        };
    </script>
</body>

</html>