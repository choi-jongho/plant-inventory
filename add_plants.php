<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

$categories = [];
$res = $conn->query("SELECT category_id, category_name FROM category ORDER BY category_name");
while ($r = $res->fetch_assoc()) $categories[] = $r;

$suppliers = [];
$res = $conn->query("SELECT SupplierID, Name FROM suppliers ORDER BY Name");
while ($r = $res->fetch_assoc()) $suppliers[] = $r;

if (isset($_POST['add_supplier'])) {
    $supplierName = $conn->real_escape_string($_POST['supplier_name']);
    $contactEmail = $conn->real_escape_string($_POST['supplier_email']);
    $phoneNumber = $conn->real_escape_string($_POST['supplier_phone']);

    if ($supplierName && $contactEmail && $phoneNumber) {
        $stmt = $conn->prepare("INSERT INTO suppliers (Name, ContactEmail, PhoneNumber) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $supplierName, $contactEmail, $phoneNumber);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Supplier added successfully!';
            header("Location: add_plants.php");
            exit();
        } else {
            $_SESSION['message'] = 'Error adding supplier: '.$stmt->error.'';
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = 'All supplier fields are required.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_supplier'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $scientificName = $conn->real_escape_string($_POST['scientific_name']);
    $categoryId = (int)$_POST['category_id'];
    $quantity = (int)$_POST['quantity'];
    $location = $conn->real_escape_string($_POST['location']);
    $supplierId = (int)$_POST['supplier_id'];
    $imagePath = '';

    if (!empty($_FILES['plant_image']['name']) && $_FILES['plant_image']['error'] === UPLOAD_ERR_OK) {
        $dir = 'uploads/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file = time().'_'.basename($_FILES['plant_image']['name']);
        $dest = $dir.$file;
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($_FILES['plant_image']['type'],$allowed) && move_uploaded_file($_FILES['plant_image']['tmp_name'],$dest)) {
            $imagePath = $dest;
        } else {
            $_SESSION['message'] = 'Invalid image or upload failed.';
        }
    }

    if (!isset($_SESSION['message'])) {
        $stmt = $conn->prepare("INSERT INTO plants (Name, ScientificName, category_id, Location, ImagePath, LastUpdated) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssiss", $name, $scientificName, $categoryId, $location, $imagePath);
        if ($stmt->execute()) {
            $newPlantID = $stmt->insert_id;
            $stmt->close();

            $inv = $conn->prepare("INSERT INTO inventory (PlantID, SupplierID, inv_quantity, LastUpdated) VALUES (?, ?, ?, NOW())");
            $inv->bind_param("iii", $newPlantID, $supplierId, $quantity);
            if ($inv->execute()) {
                $_SESSION['message'] = "Plant added successfully.";
                $_SESSION['message_type'] = 'success';
                $inv->close();
                header("Location: plants.php");
                exit();
            } else {
                $_SESSION['message'] = "Inventory insert failed: ".$inv->error."";
                $_SESSION['message_type'] = 'error';
                $inv->close();
            }
        } else {
            $_SESSION['message'] = "DB Error: ".$stmt->error."";
            $_SESSION['message_type'] = 'error';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Plant - Plant Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-dark-green: #537D5D;
            --primary-green: #73946B;
            --secondary-light-green: #9EBC8A;
            --accent-beige: #D2D0A0;
        }

        body {
            background-color: var(--accent-beige);
            font-family: 'Segoe UI', sans-serif;
        }

        .notification {
            position: fixed;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            z-index: 9999;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0%, 90% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }

        .card-header {
            background-color: var(--primary-dark-green);
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-dark-green);
            border-color: var(--primary-dark-green);
        }

        .btn-primary:hover {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }

        .btn-secondary {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-light-green);
            border-color: var(--secondary-light-green);
            color: var(--primary-dark-green);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(115, 148, 107, 0.25);
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #ddd;
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">🌱 Add New Plant</h3>
                <a href="plants.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Inventory
                </a>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Plant Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scientific_name" class="form-label">Scientific Name</label>
                                <input type="text" class="form-control" id="scientific_name" name="scientific_name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category['category_id']) ?>">
                                            <?= htmlspecialchars($category['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="supplier_id" class="form-select" required>
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($suppliers as $sup): ?>
                                            <option value="<?= $sup['SupplierID'] ?>"><?= $sup['Name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                                        + Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Greenhouse A, Section 2" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="plant_image" class="form-label">Plant Image</label>
                        <input type="file" class="form-control" id="plant_image" name="plant_image" accept="image/*" onchange="previewImage(this)">
                        <div class="form-text">Supported formats: JPG, JPEG, PNG, GIF. (Max: 5MB)</div>
                        <img id="image_preview" class="image-preview" alt="Image Preview">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-check"></i> Add Plant
                        </button>
                        <a href="plants.php" class="btn btn-secondary">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST">
                <input type="hidden" name="add_supplier" value="1">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Supplier Name *</label>
                            <input type="text" class="form-control" name="supplier_name" required>
                        </div>
                        <div class="mb-3">
                            <label>Contact Email *</label>
                            <input type="email" class="form-control" name="supplier_email" required>
                        </div>
                        <div class="mb-3">
                            <label>Phone Number *</label>
                            <input type="text" class="form-control" name="supplier_phone" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Supplier</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('image_preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        document.querySelector('form').addEventListener('submit', function (e) {
            const name = document.getElementById('name').value.trim();
            const categoryId = document.getElementById('category_id').value;
            const quantity = document.getElementById('quantity').value;
            const location = document.getElementById('location').value.trim();

            if (!name || !categoryId || !quantity || !location) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            if (quantity < 0) {
                e.preventDefault();
                alert('Quantity cannot be negative.');
                return false;
            }
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
