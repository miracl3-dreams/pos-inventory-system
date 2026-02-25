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
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td>#
                                <?= str_pad($prod["product_id"], 3, "0", STR_PAD_LEFT); ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($prod["product_name"]); ?>
                                </strong></td>
                            <td>
                                <?= htmlspecialchars($prod["description"]); ?>
                            </td>
                            <td>
                                <?= $prod["category_name"]; ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $prod["is_active"] ? 'active' : 'inactive'; ?>">
                                    <?= $prod["is_active"] ? "Active" : "Inactive"; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?delete=<?= $prod["product_id"]; ?>" class="btn-delete"
                                        onclick="return confirm('Delete this product?')">Delete</a>
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
        document.getElementById("productModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("productModal").style.display = "none";
    }

    window.onclick = function (event) {
        let modal = document.getElementById("productModal");
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
<script src="/pos_inventory_system/assets/js/burger-a-menu.js"></script>