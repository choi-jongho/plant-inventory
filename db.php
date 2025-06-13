<?php
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $db = 'plant_inventory';
    
    $conn = new mysqli($host, $user, $password, $db);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set character set
    $conn->set_charset("utf8mb4");
?>