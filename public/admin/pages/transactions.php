<?php
require_once "../../include/session.php";
require_once "../../config/db_config.php";
require_once "../../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "ADMIN") {
    header("Location: ../../login.php");
    exit;
}

$dateFilter = $_GET['date'] ?? '';
$limit = 10;
$page = max((int) ($_GET['pg'] ?? 1), 1);
$offset = ($page - 1) * $limit;

$whereClause = " WHERE 1=1";
$params = [];

if ($dateFilter !== '') {
    $whereClause .= " AND DATE(s.sale_date) = ?";
    $params[] = $dateFilter;
}

$countStmt = $link_id->prepare("SELECT COUNT(*) FROM sales s $whereClause");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$sql = "SELECT s.*, u.full_name as cashier_name 
        FROM sales s 
        LEFT JOIN users u ON s.cashier_id = u.user_id 
        $whereClause 
        ORDER BY s.sale_date DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $link_id->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Sales <span>Transactions</span></p>
        </div>
        <div class="header-right">
            <!-- <form method="GET" class="search-container">
                <input type="hidden" name="page" value="transactions">
                <input type="date" name="date" class="search-input-inline" value="< / ?= htmlspecialchars($dateFilter) ?>">
                <button type="submit" class="search-icon-btn">Filter</button>
                < / ?php if ($dateFilter): ?>
                    <a href="dashboard.php?page=transactions" class="btn-secondary"
                        style="text-decoration:none; padding: 5px 10px;">Clear</a>
                < / ?php endif; ?>
            </form> -->
        </div>
    </div>

    <div class="global-container">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Trans ID</th>
                        <th>Date & Time</th>
                        <th>Cashier</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No transactions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td data-label="Trans ID">#<?= $tx['sale_id']; ?></td>
                                <td data-label="Date & Time"><?= date("M d, Y h:i A", strtotime($tx['sale_date'])); ?></td>
                                <td data-label="Cashier"><?= htmlspecialchars($tx['cashier_name'] ?? 'System'); ?></td>
                                <td data-label="Total Amount"><strong>₱<?= number_format($tx['total_amount'], 2); ?></strong>
                                </td>
                                <td data-label="Payment"><?= $tx['payment_method']; ?></td>
                                <td data-label="Action">
                                    <button class="btn-primary" onclick="viewReceipt(<?= $tx['sale_id']; ?>)">View</button>
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
                <a href="dashboard.php?page=transactions&pg=<?= $i ?>&date=<?= urlencode($dateFilter) ?>"
                    class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <div id="receiptModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Transaction Details</h3>
                <span class="close-btn" onclick="closeReceipt()">&times;</span>
            </div>
            <div id="receiptContent" class="form-body">
                <p style="text-align:center;">Loading details...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeReceipt()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function viewReceipt(saleId) {
            const modal = document.getElementById("receiptModal");
            const content = document.getElementById("receiptContent");
            modal.style.display = "flex";

            fetch(`../admin/api/fetch_receipt.php?sale_id=${saleId}`)
                .then(response => response.text())
                .then(data => {
                    content.innerHTML = data;
                })
                .catch(err => {
                    content.innerHTML = "Error loading details.";
                    console.error(err);
                });
        }

        function closeReceipt() {
            document.getElementById("receiptModal").style.display = "none";
        }
    </script>
</body>

</html>