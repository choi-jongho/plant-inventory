<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

$notificationMessage = isset($_SESSION['message']) ? $_SESSION['message'] : "";
$messageType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
unset($_SESSION['message']);
unset($_SESSION['message_type']);

$sql = "SELECT 
            i.InventoryID, 
            p.Name AS plant_name, 
            c.category_name, 
            s.Name AS supplier_name, 
            i.inv_quantity, 
            i.LastUpdated
        FROM inventory i
        INNER JOIN plants p ON i.PlantID = p.PlantID
        INNER JOIN suppliers s ON i.SupplierID = s.SupplierID
        LEFT JOIN category c ON p.category_id = c.category_id
        ORDER BY p.Name ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        html, body {
            height: 100%;
        }
        body {
            background-color: #D2D0A0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        .container.main {
            background: white;
            margin-top: 3rem;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #537D5D;
        }
        .table thead {
            background-color: #73946B;
            color: white;
        }
        .btn-success {
            background-color: #537D5D;
            border-color: #537D5D;
        }
        .btn-success:hover {
            background-color: #73946B;
            border-color: #73946B;
        }
        .btn-warning {
            background-color: #9EBC8A;
            border-color: #9EBC8A;
            color: black;
        }
        .btn-warning:hover {
            background-color: #73946B;
            border-color: #73946B;
            color: white;
        }
        .btn-danger {
            background-color: #C0392B;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1050;
            font-size: 14px;
            max-width: 300px;
            word-wrap: break-word;
            animation: slideInRight 0.3s ease-out;
        }
        .notification.error {
            background-color: #dc3545;
        }
        .notification.warning {
            background-color: #ffc107;
            color: #212529;
        }
        .notification.info {
            background-color: #17a2b8;
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .notification-banner {
            background-color: #28a745;
            color: white;
            padding: 12px 0;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<main>

    <?php if (!empty($notificationMessage)): ?>
        <div class="container mt-3">
            <div id="notification-message" class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?php if ($messageType === 'success'): ?>
                    <i class="fas fa-check-circle me-2"></i>
                <?php elseif ($messageType === 'error' || $messageType === 'danger'): ?>
                    <i class="fas fa-exclamation-circle me-2"></i>
                <?php elseif ($messageType === 'warning'): ?>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                <?php else: ?>
                    <i class="fas fa-info-circle me-2"></i>
                <?php endif; ?>
                <?= htmlspecialchars($notificationMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>


    <div class="container main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Current Inventory</h2>
            <a href="add_inventory.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Add Inventory</a>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search inventory...">
            </div>
        </div>


        <table class="table table-bordered text-center align-middle shadow-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plant Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['InventoryID']) ?></td>
                            <td><?= htmlspecialchars($row['plant_name']) ?></td>
                            <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>
                            <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                            <td><?= htmlspecialchars($row['inv_quantity']) ?></td>
                            <td><?= htmlspecialchars($row['LastUpdated']) ?></td>
                            <td>
                                <a href="edit_inventory.php?id=<?= $row['InventoryID'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['InventoryID'] ?>">
                                    Delete
                                </button>
                                <div class="modal fade" id="deleteModal<?= $row['InventoryID'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $row['InventoryID'] ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title" id="deleteModalLabel<?= $row['InventoryID'] ?>">Confirm Deletion</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete inventory for <strong><?= htmlspecialchars($row['plant_name']) ?></strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <a href="delete_inventory.php?id=<?= $row['InventoryID'] ?>" class="btn btn-danger">Yes, Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No inventory records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notif = document.getElementById('notification-message');
        if (notif) {
            setTimeout(() => {
                notif.style.display = 'none';
            }, 2000);
        }
        const searchInput = document.getElementById("searchInput");
        searchInput.addEventListener("keyup", function () {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll("table tbody tr");
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });
    });
</script>


</body>
</html>
