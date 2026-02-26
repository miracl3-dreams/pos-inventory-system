<!DOCTYPE html>
<html>

<head>
    <title>Admin Purchases</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Manage <span>Purchases</span></p>
        </div>
        <div class="header-right">
            <button class="btn-primary" onclick="openModal()">+ Add Purchase</button>
        </div>
    </div>

    <div id="purchaseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Purchase</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" class="purchase-form">
                <input type="hidden" name="action" value="add">
                <div class="form-body">
                    <div class="input-group">
                        <label>Purchase Name</label>
                        <input type="text" name="purchase_name" placeholder="e.g. Coca Cola" required>
                    </div>
                    <div class="input-group">
                        <label>Purchase Description</label>
                        <textarea name="description" placeholder="Enter description here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>

    <div class="global-container">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Purchase Name</th>
                        <th>Description</th>
                        <th>Purchase Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $pur): ?>
                        <tr>
                            <td>#
                                <?= str_pad($pur["purchase_id"], 3, "0", STR_PAD_LEFT); ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($pur["purchase_name"]); ?>
                                </strong></td>
                            <td>
                                <?= htmlspecialchars($pur["description"]); ?>
                            </td>
                            <td>
                                <?= $pur["quantity"]; ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $pur["is_active"] ? 'active' : 'inactive'; ?>">
                                    <?= $pur["is_active"] ? "Active" : "Inactive"; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?delete=<?= $pur["purchase_id"]; ?>" class="btn-delete"
                                        onclick="return confirm('Delete this purchase?')">Delete</a>
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
        document.getElementById("purchaseModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("purchaseModal").style.display = "none";
    }

    window.onclick = function (event) {
        let modal = document.getElementById("purchaseModal");
        if (event.target == modal) {
            closeModal();
        }
    }
</script>