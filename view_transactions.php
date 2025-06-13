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

    try {
        $sql = "SELECT t.TransactionID, t.PlantID, p.Name AS PlantName, t.TransactionType, t.trans_quantity, t.TransactionDate
                FROM transactions t
                LEFT JOIN plants p ON t.PlantID = p.PlantID
                ORDER BY t.TransactionDate DESC";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Database query failed: " . $conn->error);
        }
    } catch (Exception $e) {
        $notificationMessage = "Error: " . htmlspecialchars($e->getMessage()) . "";
        $result = false;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        body {
            background-color: #D2D0A0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }
        h2 {
            color: #537D5D;
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
        .btn-custom {
            background-color: #537D5D;
            border-color: #537D5D;
            color: white;
        }
        .btn-custom:hover {
            background-color: #73946B;
            border-color: #73946B;
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 0;
            color: #666;
        }
        .empty-state h4 {
            color: #537D5D;
            margin-bottom: 1rem;
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
            <h2 class="m-0">Transaction History</h2>
            <a href="add_transaction.php" class="btn btn-custom">+ Add Transaction</a>
        </div>

        <div class="mb-3">
            <input type="text" id="searchTransactions" class="form-control" placeholder="🔍 Search transactions...">
        </div>


        <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plant</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['TransactionID']) ?></td>
                    <td><?= htmlspecialchars($row['PlantName'] ?? 'Unknown Plant') ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['TransactionType'])) ?></td>
                    <td><?= htmlspecialchars($row['trans_quantity']) ?></td>
                    <td><?= htmlspecialchars($row['TransactionDate']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php elseif ($result): ?>
        <div class="empty-state">
            <h4>No Transactions Found</h4>
            <p>There are no transactions recorded yet.</p>
            <a href="add_transaction.php" class="btn btn-custom">Add Your First Transaction</a>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <h4>Unable to Load Transactions</h4>
            <p>There was an error loading the transaction data. Please try again.</p>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('searchTransactions').addEventListener('keyup', function () {
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