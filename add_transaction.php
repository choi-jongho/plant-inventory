<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plantID = (int)$_POST['plant_id'];
    $type = $_POST['transaction_type'];
    $quantity = (int)$_POST['quantity'];

    if ($plantID <= 0 || $quantity <= 0) {
        $_SESSION['message'] = "Invalid plant ID or quantity.";
        $_SESSION['message_type'] = 'error';
        header("Location: view_transactions.php");
        exit;
    }

    if (!in_array($type, ['purchase', 'distribution'])) {
        $_SESSION['message'] = "Invalid transaction type.";
        $_SESSION['message_type'] = 'error';
        header("Location: view_transactions.php");
        exit;
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("SELECT inv_quantity FROM inventory WHERE PlantID = ?");
        $stmt->bind_param("i", $plantID);
        $stmt->execute();
        $result = $stmt->get_result();
        $inventory = $result->fetch_assoc();
        $stmt->close();

        if (!$inventory) {
            throw new Exception("Plant not found in inventory.");
        }

        if ($type === 'purchase') {
            $stmt = $conn->prepare("INSERT INTO transactions (PlantID, TransactionType, trans_quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $plantID, $type, $quantity);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE inventory SET inv_quantity = inv_quantity + ?, LastUpdated = NOW() WHERE PlantID = ?");
            $stmt->bind_param("ii", $quantity, $plantID);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Purchase transaction recorded successfully.";
            $_SESSION['message_type'] = 'success';
            
        } elseif ($type === 'distribution') {
            if ($inventory['inv_quantity'] < $quantity) {
                throw new Exception("Not enough stock for distribution. Available: " . $inventory['inv_quantity']);
            }

            $stmt = $conn->prepare("INSERT INTO transactions (PlantID, TransactionType, trans_quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $plantID, $type, $quantity);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE inventory SET inv_quantity = inv_quantity - ?, LastUpdated = NOW() WHERE PlantID = ?");
            $stmt->bind_param("ii", $quantity, $plantID);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Distribution transaction recorded successfully.";
            $_SESSION['message_type'] = 'success';
        }

        $conn->commit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error: " . htmlspecialchars($e->getMessage()) . "";
    }

    header("Location: view_transactions.php");
    exit;
}

try {
    $plants = $conn->query("SELECT PlantID, Name FROM plants ORDER BY Name");
    if (!$plants) {
        throw new Exception("Failed to load plants");
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Error loading plants: " . htmlspecialchars($e->getMessage()) . "";
    $plants = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Transaction</title>
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
        .btn-primary {
            background-color: #537D5D;
            border-color: #537D5D;
        }
        .btn-primary:hover {
            background-color: #73946B;
            border-color: #73946B;
        }
        .btn-secondary {
            margin-left: 10px;
        }
    </style>
</head>
<body>
<main>
    <div class="container main">
        <h2>Add Transaction</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        }
        ?>
        
        <?php if ($plants !== false): ?>
        <form method="POST" id="transactionForm">
            <div class="mb-3">
                <label for="plant_id" class="form-label">Select Plant</label>
                <select class="form-select" name="plant_id" id="plant_id" required>
                    <option value="" disabled selected>Select a plant...</option>
                    <?php while($p = $plants->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($p['PlantID']) ?>"><?= htmlspecialchars($p['Name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="transaction_type" class="form-label">Transaction Type</label>
                <select class="form-select" name="transaction_type" id="transaction_type" required>
                    <option value="" disabled selected>Select transaction type...</option>
                    <option value="purchase">Purchase (Add Stock)</option>
                    <option value="distribution">Distribution (Remove Stock)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit Transaction</button>
            <a href="view_transactions.php" class="btn btn-secondary">Cancel</a>
        </form>
        <?php else: ?>
        <div class="alert alert-danger">
            Unable to load plant data. Please check your database connection and try again.
        </div>
        <a href="view_transactions.php" class="btn btn-secondary">Go Back</a>
        <?php endif; ?>
    </div>
</main>

<script>
document.getElementById('transactionForm')?.addEventListener('submit', function(e) {
    const plantId = document.getElementById('plant_id').value;
    const transactionType = document.getElementById('transaction_type').value;
    const quantity = document.getElementById('quantity').value;
    
    if (!plantId || !transactionType || !quantity || quantity < 1) {
        e.preventDefault();
        alert('Please fill in all fields with valid values.');
        return false;
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
