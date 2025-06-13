<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
  $_SESSION['message'] = "Error: Plant ID is required.";
  $_SESSION['message_type'] = 'error';
  header("Location: plants.php");
  exit();
}

$plantID = $_GET['id'];

$sql = "SELECT p.*, c.category_name, inv.inv_quantity, inv.SupplierID 
    FROM Plants p 
    LEFT JOIN category c ON p.category_id = c.category_id 
    LEFT JOIN inventory inv ON p.PlantID = inv.PlantID
    WHERE p.PlantID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $plantID);
$stmt->execute();
$result = $stmt->get_result();
$plant = $result->fetch_assoc();

if (!$plant) {
  $_SESSION['message'] = "Error: Plant not found.";
  $_SESSION['message_type'] = 'error';
  header("Location: plants.php");
  exit();
}

$categorySql = "SELECT category_id, category_name FROM category ORDER BY category_name";
$categoryResult = $conn->query($categorySql);
$categories = [];
if ($categoryResult) {
  while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $scientificName = $_POST['scientific_name'];
  $categoryId = (int)$_POST['category_id'];
  $inv_quantity = (int)$_POST['inv_quantity'];
  $location = $_POST['location'];
  $lastUpdated = date("Y-m-d H:i:s");

  $imagePath = $plant['ImagePath'];
  if (!empty($_FILES["plant_image"]["name"])) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = basename($_FILES["plant_image"]["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;

    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];

    if (in_array($fileType, $allowedTypes)) {
      if ($_FILES["plant_image"]["size"] <= 5000000) {
        if (move_uploaded_file($_FILES["plant_image"]["tmp_name"], $targetFilePath)) {
          if (!empty($plant['ImagePath']) && $plant['ImagePath'] != 'images/default.jpg' && file_exists($plant['ImagePath'])) {
            unlink($plant['ImagePath']);
          }
          $imagePath = $targetFilePath;
        } else {
          $_SESSION['message'] = "Warning: Failed to upload image.";
          $_SESSION['message_type'] = 'warning';
        }
      } else {
        $_SESSION['message'] = "Warning: Image too large (max 5MB).";
        $_SESSION['message_type'] = 'warning';
      }
    } else {
      $_SESSION['message'] = "Warning: Invalid file type.";
      $_SESSION['message_type'] = 'warning';
    }
  }

  $sql = "UPDATE Plants SET Name=?, ScientificName=?, category_id=?, Location=?, LastUpdated=?, ImagePath=? WHERE PlantID=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssisssi", $name, $scientificName, $categoryId, $location, $lastUpdated, $imagePath, $plantID);

  if ($stmt->execute()) {
    $supplierID = isset($plant['SupplierID']) ? (int)$plant['SupplierID'] : null;

    if ($supplierID) {
      $sqlInventory = "INSERT INTO inventory (PlantID, inv_quantity, SupplierID)
               VALUES (?, ?, ?)
               ON DUPLICATE KEY UPDATE inv_quantity = VALUES(inv_quantity), SupplierID = VALUES(SupplierID)";
      $stmtInventory = $conn->prepare($sqlInventory);
      $stmtInventory->bind_param("iii", $plantID, $inv_quantity, $supplierID);
      $stmtInventory->execute();
      $stmtInventory->close();
    }

    $_SESSION['message'] = "Plant updated successfully!";
    $_SESSION['message_type'] = 'success';
                  
  } else {
    $_SESSION['message'] = "Update failed. Please try again.";
    $_SESSION['message_type'] = 'error';
  }

  header("Location: plants.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Plant Record</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    margin: 0;
  }

  h2 {
    color: var(--primary-dark-green);
    text-align: center;
    margin: 2rem 0 1rem;
  }

  .container-main {
    padding: 2rem;
  }

  .form-container {
    background-color: var(--secondary-light-green);
    border-radius: 8px;
    padding: 2rem;
    max-width: 700px;
    margin: auto;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }

  label {
    font-weight: 600;
    color: #333;
  }

  .btn-success {
    background-color: var(--primary-dark-green);
    border-color: var(--primary-dark-green);
  }

  .btn-success:hover {
    opacity: 0.9;
  }

  .image-section {
    text-align: center;
    margin-bottom: 2rem;
  }

  .current-image, .no-image {
    max-width: 150px;
    height: 150px;
    border-radius: 8px;
    border: 2px solid #ddd;
    object-fit: cover;
  }

  .no-image {
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 14px;
  }

  .image-upload-info {
    background-color: #e8f5e8;
    border: 1px solid var(--primary-green);
    border-radius: 4px;
    padding: 8px;
    margin-top: 10px;
    font-size: 0.875rem;
    color: var(--primary-dark-green);
    text-align: center;
  }

  @media (max-width: 768px) {
    .current-image, .no-image {
    max-width: 120px;
    height: 120px;
    }
  }
  </style>
</head>
<body>

<div class="container-main">
  <h2>🌿 Edit Plant Information</h2>

  <div class="form-container">
  <div class="image-section">
    <label>Current Image</label><br>
    <?php 
    $currentImage = !empty($plant['ImagePath']) ? $plant['ImagePath'] : '';
    if (!empty($currentImage)): ?>
      <img id="currentImage" src="<?= htmlspecialchars($currentImage) ?>" class="current-image" alt="Current Image"
      onerror="this.style.display='none'; document.getElementById('noImagePlaceholder').style.display='flex';">
      <div id="noImagePlaceholder" class="no-image" style="display: none;">Image Not Found</div>
    <?php else: ?>
      <div class="no-image">No Image Available</div>
    <?php endif; ?>
  </div>

  <form action="edit_plants.php?id=<?= $plantID ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="edit_id" value="<?= htmlspecialchars($plant['PlantID']) ?>" />

    <div class="row">
    <div class="col-md-6">
      <div class="mb-3">
      <label>Plant Name</label>
      <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($plant['Name']) ?>" required>
      </div>
      <div class="mb-3">
      <label>Scientific Name</label>
      <input type="text" class="form-control" name="scientific_name" value="<?= htmlspecialchars($plant['ScientificName']) ?>" required>
      </div>
      <div class="mb-3">
      <label>Category</label>
      <select class="form-select" name="category_id" required>
        <option value="">Select Category</option>
        <?php foreach ($categories as $category): ?>
        <option value="<?= htmlspecialchars($category['category_id']) ?>" <?= $plant['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($category['category_name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
      </div>
    </div>

    <div class="col-md-6">
      <div class="mb-3">
      <label>Inventory Quantity</label>
      <input type="number" class="form-control" name="inv_quantity" value="<?= htmlspecialchars($plant['inv_quantity']) ?>" min="0" required>
      </div>
      <div class="mb-3">
      <label>Location</label>
      <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($plant['Location']) ?>" required>
      </div>
      <div class="mb-3">
      <label>Upload New Image</label>
      <input type="file" class="form-control" name="plant_image" accept="image/*">
      <div class="form-text">Supported: JPG, JPEG, PNG, GIF (Max: 5MB)</div>
      </div>
    </div>
    </div>
    <div class="row">
      <div class="col-12 text-center mt-4">
        <button type="submit" class="btn btn-success me-2 px-4"><i class="fa-solid fa-check"></i> Save Changes</button>
        <button type="button" class="btn btn-secondary px-4" onclick="window.location.href='plants.php'"><i class="fa-solid fa-xmark"></i> Cancel</button>
      </div>
    </div>
  </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>
