<!DOCTYPE html>
<html>

<head>
    <title>Admin Suppliers</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Manage <span>Suppliers</span></p>
        </div>
        <div class="header-right">
            <button class="btn-primary" onclick="openModal()">+ Add Supplier</button>
        </div>
    </div>

    <div id="supplierModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Supplier</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" class="category-form">
                <input type="hidden" name="action" value="add">
                <div class="form-body">
                    <div class="input-group">
                        <label>Supplier Name</label>
                        <input type="text" name="supplier_name" placeholder="e.g. Coca Cola" required>
                    </div>
                    <div class="input-group">
                        <label>Supplier Description</label>
                        <textarea name="description" placeholder="Enter description here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Supplier</button>
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
                        <th>Supplier Name</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $sup): ?>
                        <tr>
                            <td>#
                                <?= str_pad($sup["supplier_id"], 3, "0", STR_PAD_LEFT); ?>
                            </td>
                            <td><strong>
                                    <?= htmlspecialchars($sup["supplier_name"]); ?>
                                </strong></td>
                            <td>
                                <?= htmlspecialchars($sup["description"]); ?>
                            </td>
                            <td>
                                <?= $sup["category_name"]; ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $sup["is_active"] ? 'active' : 'inactive'; ?>">
                                    <?= $sup["is_active"] ? "Active" : "Inactive"; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?delete=<?= $sup["supplier_id"]; ?>" class="btn-delete"
                                        onclick="return confirm('Delete this supplier?')">Delete</a>
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
        document.getElementById("supplierModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("supplierModal").style.display = "none";
    }

    window.onclick = function (event) {
        let modal = document.getElementById("supplierModal");
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
<script src="/pos_inventory_system/assets/js/burger-a-menu.js"></script>