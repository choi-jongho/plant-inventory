<?php
session_start();
include 'db.php';
include 'auth.php';
checkLogin();

$plants = $conn->query("SELECT PlantID, Name FROM plants");
$suppliers = $conn->query("SELECT SupplierID, Name FROM suppliers");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plantID = $_POST['plant_id'];
    $supplierID = $_POST['supplier_id'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("INSERT INTO inventory (PlantID, SupplierID, inv_quantity, LastUpdated) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $plantID, $supplierID, $quantity);
    $stmt->execute();

    $_SESSION['message'] = "Inventory record added.";
    $_SESSION['message_type'] = 'success';
    header("Location: manage_inventory.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            background-color: #D2D0A0;
            font-family: 'Segoe UI', sans-serif;
        }
        .wrapper {
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
        .btn-primary {
            background-color: #537D5D;
            border-color: #537D5D;
        }
        .btn-primary:hover {
            background-color: #73946B;
            border-color: #73946B;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include 'navbar.php'; ?>

    <main class="container main">
        <h2>Add Inventory</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Plant</label>
                <select name="plant_id" class="form-select" required>
                    <option value="" disabled selected>Select Plant</option>
                    <?php while($p = $plants->fetch_assoc()): ?>
                        <option value="<?= $p['PlantID'] ?>"><?= htmlspecialchars($p['Name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="" disabled selected>Select Supplier</option>
                    <?php while($s = $suppliers->fetch_assoc()): ?>
                        <option value="<?= $s['SupplierID'] ?>"><?= htmlspecialchars($s['Name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required min="1">
            </div>
            <button type="submit" class="btn btn-primary">Add Inventory</button>
        </form>
    </main>

    <?php include 'footer.php'; ?>
</div>
</body>
</html>
