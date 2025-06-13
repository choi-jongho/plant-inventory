<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

$supplier_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Use prepared statement for SELECT
$stmt = $conn->prepare("SELECT * FROM suppliers WHERE SupplierID = ?");
$stmt->bind_param("i", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();
$supplier = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Use prepared statement for UPDATE
    $stmt = $conn->prepare("UPDATE suppliers SET Name=?, ContactEmail=?, PhoneNumber=? WHERE SupplierID=?");
    $stmt->bind_param("sssi", $name, $email, $phone, $supplier_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Supplier updated successfully.";
    $_SESSION['message_type'] = 'success';
    header("Location: manage_suppliers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Supplier</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --primary-dark-green: #537D5D;
      --primary-green: #73946B;
      --accent-beige: #D2D0A0;
    }

    body {
      background-color: var(--accent-beige);
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      padding: 2rem 1rem;
    }

    .form-box {
      background-color: white;
      max-width: 600px;
      margin: auto;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      color: var(--primary-dark-green);
    }

    label {
      font-weight: bold;
    }

    input[type="text"], input[type="email"], input[type="tel"] {
      width: 100%;
      padding: 10px;
      margin-top: 8px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .btn.green {
      background-color: var(--primary-dark-green);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
    }

    .btn.green:hover {
      background-color: var(--primary-green);
    }
  </style>
</head>
<body>

<div class="main-content">
  <div class="form-box">
    <h2><i class="fa-solid fa-pen-to-square"></i> Edit Supplier</h2>
    <form method="post">
      <label>Supplier Name:</label>
      <input type="text" name="name" value="<?= $supplier['Name'] ?>" required>
      <label>Email Address:</label>
      <input type="email" name="email" value="<?= $supplier['ContactEmail'] ?>" required>
      <label>Phone Number:</label>
      <input type="tel" name="phone" value="<?= $supplier['PhoneNumber'] ?>" required>
      <button type="submit" class="btn green"><i class="fa-solid fa-save"></i> Save Changes</button>
      <button type="button" class="btn green" onclick="window.location.href='manage_suppliers.php'"><i class="fa-solid fa-arrow-left"></i> Back</button>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
