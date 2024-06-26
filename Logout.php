<?php
session_start();
if (isset($_POST['logout'])) {
    // Destroy the session
    session_destroy();
    // Redirect to login page
    header('Location: login.php');
    exit;
}
?>