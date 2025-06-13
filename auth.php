<?php
include 'db.php';

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        // User not logged in, redirect to login page
        header("Location: login.php");
        exit();
    }
}
