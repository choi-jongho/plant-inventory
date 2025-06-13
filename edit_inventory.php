<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_inventory.php");
    exit;
}

$inventory = $conn->query("SELECT * FROM inventory WHERE InventoryID = $id")->fetch_assoc();
$plants = $conn->query("SELECT PlantID, Name FROM plants");
$suppliers = $conn->query("SELECT SupplierID, Name FROM suppliers");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plantID = $_POST['plant_id'];
    $supplierID = $_POST['supplier_id'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("UPDATE inventory SET PlantID=?, SupplierID=?, inv_quantity=?, LastUpdated=NOW() WHERE InventoryID=?");
    $stmt->bind_param("iiii", $plantID, $supplierID, $quantity, $id);
    $stmt->execute();

    $_SESSION['message'] = "Inventory updated.";
    $_SESSION['message_type'] = 'success';
    header("Location: manage_inventory.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Inventory</title>
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
        .btn.blue {
            background-color: #73946B;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn.blue:hover {
            background-color: #537D5D;
        }
        .btn.blue.btn-secondary {
            background-color: #ccc;
            color: #333;
        }
        .btn.blue.btn-secondary:hover {
            background-color: #bbb;
        }
    </style>
</head>
<body>

<main>
    <div class="container main">
        <h2>Edit Inventory</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Plant</label>
                <select name="plant_id" class="form-select" required>
                    <?php while($p = $plants->fetch_assoc()): ?>
                        <option value="<?= $p['PlantID'] ?>" <?= $inventory['PlantID'] == $p['PlantID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['Name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    <?php while($s = $suppliers->fetch_assoc()): ?>
                        <option value="<?= $s['SupplierID'] ?>" <?= $inventory['SupplierID'] == $s['SupplierID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['Name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required min="1" value="<?= $inventory['inv_quantity'] ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn blue">Update Inventory</button>
                <button type="button" class="btn blue btn-secondary" onclick="window.location.href='manage_inventory.php'">Cancel</button>
            </div>

        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
