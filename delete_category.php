<?php
session_start();
include 'db.php';
include 'auth.php';
checkLogin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM category WHERE category_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Category deleted successfully.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error deleting category.";
        $_SESSION['message_type'] = 'error';
    }

    $stmt->close();
}
header("Location: manage_categories.php");
exit;
