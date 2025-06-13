<?php
session_start();
include 'db.php';
include 'auth.php';
checkLogin();


if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE SupplierID = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier deleted successfully.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error deleting supplier.";
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();
}
header("Location: manage_suppliers.php");
exit;
?>