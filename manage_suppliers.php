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

    $result = $conn->query("SELECT * FROM suppliers");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Suppliers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #D2D0A0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        main {
            flex: 1;
        }
        h2 {
            color: #537D5D;
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
        .container.main {
            background-color: white;
            padding: 2rem;
            margin-top: 3rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .table thead {
            background-color: #73946B;
            color: white;
        }
        footer {
            background-color: #537D5D;
            color: white;
            text-align: center;
            padding: 12px 0;
            margin-top: auto;
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
            <h2 class="m-0">Manage Suppliers</h2>
            <a href="add_supplier.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Add Supplier</a>
        </div>

        <div class="mb-3">
            <input type="text" id="searchSuppliers" class="form-control" placeholder="🔍 Search suppliers...">
        </div>


        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Supplier Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['SupplierID']) ?></td>
                        <td><?= htmlspecialchars($row['Name']) ?></td>
                        <td><?= htmlspecialchars($row['ContactEmail']) ?></td>
                        <td><?= htmlspecialchars($row['PhoneNumber']) ?></td>
                        <td>
                            <a href="edit_supplier.php?id=<?= $row['SupplierID'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $row['SupplierID'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this supplier?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let deleteId = null;
    
    function confirmDelete(id) {
        deleteId = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (deleteId) {
            window.location.href = 'delete_supplier.php?id=' + deleteId;
        }
    });
    
    document.addEventListener('DOMContentLoaded', () => {
        const notif = document.getElementById('notification-message');
        if (notif) {
            setTimeout(() => notif.style.display = 'none', 5000);
        }
    });

    document.getElementById('searchSuppliers').addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');
        rows.forEach(row => {
            const rowText = Array.from(row.cells).map(cell => cell.textContent.toLowerCase()).join(' ');
            row.style.display = rowText.includes(filter) ? '' : 'none';
        });
    });
</script>
</body>
</html>
