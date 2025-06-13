<?php
session_start();
include 'db.php';
include 'auth.php';
checkLogin();

// Handle deletion request
if (isset($_POST['delete_id'])) {
    $plantID = $_POST['delete_id'];

    $sql = "DELETE FROM Plants WHERE PlantID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $plantID);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Plant deleted successfully.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error deleting plant.";
        $_SESSION['message_type'] = 'error';
    }

    header("Location: plants.php"); // Redirect after deletion
    exit();
}
?>