<?php
require_once "../../../include/session.php";
require_once "../../../config/db_config.php";
require_once "../../../include/lx.pdodb.php";

if (!isset($_SESSION["user_id"])) {
    exit("Unauthorized");
}

$sale_id = (int) ($_GET['sale_id'] ?? 0);

if ($sale_id > 0) {
    $stmt = $link_id->prepare("
        SELECT si.*, p.product_name, p.unit 
        FROM sale_items si
        JOIN products p ON si.product_id = p.product_id
        WHERE si.sale_id = ?
    ");
    $stmt->execute([$sale_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($items) {
        echo '<div style="margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">';
        echo '<strong>Transaction ID:</strong> #' . $sale_id;
        echo '</div>';
        echo '<table style="width:100%; border-collapse: collapse; font-size: 14px;">';
        echo '<thead>
                <tr style="text-align:left; border-bottom: 2px solid #eee;">
                    <th style="padding: 8px;">Item</th>
                    <th style="padding: 8px;">Qty</th>
                    <th style="padding: 8px;">Price</th>
                    <th style="padding: 8px; text-align:right;">Subtotal</th>
                </tr>
              </thead>';
        echo '<tbody>';

        $total = 0;
        foreach ($items as $item) {
            $total += $item['subtotal'];
            echo '<tr style="border-bottom: 1px solid #f9f9f9;">';
            echo '<td style="padding: 8px;">' . htmlspecialchars($item['product_name']) . '</td>';
            echo '<td style="padding: 8px;">' . $item['quantity'] . ' ' . htmlspecialchars($item['unit']) . '</td>';
            echo '<td style="padding: 8px;">₱' . number_format($item['price'], 2) . '</td>';
            echo '<td style="padding: 8px; text-align:right;">₱' . number_format($item['subtotal'], 2) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '<tfoot>';
        echo '<tr>
                <td colspan="3" style="padding: 10px; text-align:right; font-weight:bold;">Grand Total:</td>
                <td style="padding: 10px; text-align:right; font-weight:bold; color: #e74c3c; font-size: 16px;">₱' . number_format($total, 2) . '</td>
              </tr>';
        echo '</tfoot>';
        echo '</table>';
    } else {
        echo "No items found for this transaction.";
    }
} else {
    echo "Invalid Transaction ID.";
}
?>