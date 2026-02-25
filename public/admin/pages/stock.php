<!DOCTYPE html>
<html>

<head>
    <title>Admin Stock</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Manage <span>Stock</span></p>
        </div>
        <div class="header-right">
            <button class="btn-primary" onclick="openModal()">+ Add Stock</button>
        </div>
    </div>

    <div id="stockModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Stock</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" class="category-form">
                <input type="hidden" name="action" value="add">
                <div class="form-body">
                    <div class="input-group">
                        <label>Stock Name</label>
                        <input type="text" name="stock_name" placeholder="e.g. Coca Cola" required>
                    </div>
                    <div class="input-group">
                        <label>Stock Description</label>
                        <textarea name="description" placeholder="Enter description here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Product</button>
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
                        <th>Stock Name</th>
                        <th>Description</th>
                        <th>Stock Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $stck): ?>
                        <tr>
                            <td>#
                                <?= str_pad($stck["stock_id"], 3, "0", STR_PAD_LEFT); ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($stck["stock_name"]); ?>
                                </strong></td>
                            <td>
                                <?= htmlspecialchars($stck["description"]); ?>
                            </td>
                            <td>
                                <?= $stck["quantity"]; ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $stck["is_active"] ? 'active' : 'inactive'; ?>">
                                    <?= $stck["is_active"] ? "Active" : "Inactive"; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?delete=<?= $stck["stock_id"]; ?>" class="btn-delete"
                                        onclick="return confirm('Delete this stock?')">Delete</a>
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
        document.getElementById("stockModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("stockModal").style.display = "none";
    }

    window.onclick = function (event) {
        let modal = document.getElementById("stockModal");
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
<script src="/pos_inventory_system/assets/js/burger-a-menu.js"></script>