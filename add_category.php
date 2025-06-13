<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO category (category_name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Category added successfully.";
    $_SESSION['message_type'] = 'success';
    header("Location: manage_categories.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Category</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome -->
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

    h2 {
      color: var(--primary-dark-green);
      margin-bottom: 1.5rem;
    }

    .main-content {
      flex: 1;
      padding: 2rem 1rem;
    }

    .form-box {
      background-color: #fff;
      max-width: 600px;
      margin: auto;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    label {
      font-weight: bold;
      margin-bottom: 0.5rem;
      display: block;
    }

    input[type="text"] {
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
      <h2><i class="fa-solid fa-plus"></i> Add Category</h2>
      <form method="post">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" required>
        <button type="submit" class="btn green"><i class="fa-solid fa-check"></i> Add</button>
        <button type="button" class="btn green" onclick="window.location.href='manage_categories.php'"><i class="fa-solid fa-arrow-left"></i> Back</button>
      </form>
    </div>
  </div>

  <?php include 'footer.php'; ?>

</body>
</html>
