<?php
session_start();
include 'db.php';
include 'auth.php';
checkLogin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE InventoryID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $_SESSION['message'] = "Inventory record deleted.";
    $_SESSION['message_type'] = 'success';
}

header("Location: manage_inventory.php");
exit;
?>
