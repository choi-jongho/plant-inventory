<?php
session_start();
include 'db.php';
include 'navbar.php';
include 'auth.php';
checkLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['category_name'];
    $stmt = $conn->prepare("UPDATE category SET category_name=? WHERE category_id=?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Category updated successfully.";
    $_SESSION['message_type'] = 'success';
    header("Location: manage_categories.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM category WHERE category_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-dark-green: #537D5D;
            --primary-green: #73946B;
            --secondary-light-green: #9EBC8A;
            --accent-beige: #D2D0A0;
        }

        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        body {
            background-color: var(--accent-beige);
            font-family: 'Segoe UI', sans-serif;
        }

        main {
            flex: 1;
        }

        h2 {
            color: var(--primary-dark-green);
        }

        .container.main {
            background-color: white;
            max-width: 600px;
            padding: 2rem;
            margin-top: 3rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .btn.blue {
            background-color: var(--primary-dark-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 10px;
        }

        .btn.blue:hover {
            background-color: var(--primary-green);
        }

        footer {
            background-color: var(--primary-dark-green);
            color: white;
            text-align: center;
            padding: 12px 0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <main>
        <div class="container main">
            <h2>Edit Category</h2>
            <form method="post">
                <label for="category_name">Category Name:</label>
                <input type="text" name="category_name" id="category_name" value="<?= htmlspecialchars($result['category_name']) ?>" required>
                <button type="submit" class="btn blue"><i class="fa-solid fa-check"></i> Update</button>
                <button type="button" class="btn blue" onclick="window.location.href='manage_categories.php'"><i class="fa-solid fa-arrow-left"></i> Back</button>
            </form>
        </div>
    </main>
    <footer>
        <div class="container">
            <small>&copy; <?= date('Y') ?> Plant Inventory System. All rights reserved.</small>
        </div>
    </footer>
</body>
</html>

