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
    $url = "dashboard.php?page=suppliers";
    if ($msg) {
        $url .= "&msg=" . urlencode($msg);
    }
    header("Location: $url");
    exit;
}

$editSupplier = null;
$searchTerm = trim($_GET['search'] ?? '');
$searchValue = "%$searchTerm%";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = (int) ($_POST["supplier_id"] ?? 0);
    $supplier_name = trim($_POST["supplier_name"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if ($supplier_name === "") {
        redirectToPage("Supplier name is required.");
    }

    if ($action === "add") {
        $data = [
            "supplier_name" => $supplier_name,
            "address" => $address,
        ];
        PDO_InsertRecord($link_id, "suppliers", $data, true);
        redirectToPage("Supplier added successfully.");
    }

    if ($action === "edit" && $id > 0) {
        $stmt = $link_id->prepare("UPDATE suppliers SET supplier_name = ?, address = ? WHERE supplier_id = ?");
        $stmt->execute([$supplier_name, $address, $id]);
        redirectToPage("Supplier updated successfully.");
    }
}

if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];
    if ($id > 0) {
        $stmt = $link_id->prepare("UPDATE suppliers WHERE supplier_id = ?");
        $stmt->execute([$id]);
        redirectToPage("Supplier archived.");
    }
}

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    if ($id > 0) {
        $stmt = $link_id->prepare("SELECT * FROM suppliers WHERE supplier_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $editSupplier = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$limit = 10;

$page = (isset($_GET['pg']) && is_numeric($_GET['pg'])) ? (int) $_GET['pg'] : 1;
$page = max($page, 1);

$offset = ($page - 1) * $limit;

$whereSql = "WHERE 1=1";
$params = [];

if ($searchTerm !== '') {
    $whereSql .= " AND (supplier_name LIKE ?)";
    $params = [$searchValue, $searchValue, $searchValue];
}

$countStmt = $link_id->prepare("SELECT COUNT(*) FROM suppliers $whereSql");
$countStmt->execute($params);
$totalRecords = $countStmt->fetchColumn();

$totalPages = ceil($totalRecords / $limit);

$sql = "
SELECT *
FROM suppliers
$whereSql
ORDER BY supplier_id DESC
LIMIT $limit OFFSET $offset
";

$stmt = $link_id->prepare($sql);
$stmt->execute($params);

$suppliersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Suppliers</title>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <p>Manage <span>Suppliers</span></p>
        </div>
        <div class="header-right">
            <form method="GET" class="search-container">
                <input type="hidden" name="page" value="suppliers">
                <input type="text" name="search" class="search-input-inline" placeholder="Search supplier..."
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
                <a href="dashboard.php?page=suppliers" class="btn-clear-action">Clear Search</a>
            <?php endif; ?>

            <button class="btn-primary" onclick="openModal()">+ Add Supplier</button>
        </div>
    </div>

    <div id="supplierModal" class="modal" style="<?= $editSupplier ? 'display:block;' : 'display:none;'; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?= $editSupplier ? 'Edit Supplier' : 'Add New Supplier'; ?></h3>
                <span class="close-btn" style="cursor:pointer" onclick="closeModal()">&times;</span>
            </div>

            <form method="POST" action="dashboard.php?page=suppliers">
                <div class="form-body">
                    <input type="hidden" name="action" value="<?= $editSupplier ? 'edit' : 'add' ?>">
                    <input type="hidden" name="supplier_id" value="<?= $editSupplier['supplier_id'] ?? ''; ?>">

                    <div class="input-group">
                        <label>Supplier Name</label>
                        <div class="input-with-icon">
                            <input type="text" name="supplier_name" required
                                value="<?= htmlspecialchars($editSupplier['supplier_name'] ?? ''); ?>">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Complete Address</label>
                        <textarea name="address"
                            rows="3"><?= htmlspecialchars($editSupplier['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit"
                        class="btn-primary"><?= $editSupplier ? 'Update Supplier' : 'Save Supplier'; ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="global-container">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suppliersList)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center;">No suppliers found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($suppliersList as $sup): ?>
                            <tr>
                                <td data-label="Supplier Name"><strong><?= htmlspecialchars($sup["supplier_name"]); ?></strong>
                                </td>
                                <td data-label="Address"><?= htmlspecialchars($sup["address"]); ?></td>
                                <td data-label="Actions">
                                    <div class="action-btns">
                                        <a href="dashboard.php?page=suppliers&edit=<?= $sup["supplier_id"]; ?>"
                                            class="btn-edit">Edit</a>
                                        <a href="#" class="btn-delete"
                                            onclick="return confirmDelete(<?= $sup['supplier_id']; ?>)">Delete</a>
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
                <a href="dashboard.php?page=suppliers&pg=<?= $page - 1 ?>&search=<?= urlencode($searchTerm) ?>">Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="dashboard.php?page=suppliers&pg=<?= $i ?>&search=<?= urlencode($searchTerm) ?>"
                    class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="dashboard.php?page=suppliers&pg=<?= $page + 1 ?>&search=<?= urlencode($searchTerm) ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script>
        function openModal() {
            document.getElementById("supplierModal").style.display = "block";
        }
        function closeModal() {
            window.location.href = "dashboard.php?page=suppliers";
        }
        function confirmDelete(id) {
            alertify.confirm("Delete Confirmation", "Are you sure you want to delete this supplier?",
                function () { window.location.href = "dashboard.php?page=suppliers&delete=" + id; },
                function () { alertify.error("Cancelled"); }
            );
            return false;
        }
        window.onclick = function (event) {
            let modal = document.getElementById("supplierModal");
            if (event.target == modal) { closeModal(); }
        }
        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('msg')) {
                alertify.success(urlParams.get('msg'));
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?page=suppliers";
                window.history.replaceState({}, document.title, cleanUrl);
            }
        };
    </script>
</body>

</html>