<?php
include "../config.php";

session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Handle AJAX request to reset PackingStatus
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_id'])) {
    $recordId = $_POST['record_id'];

    // Update PackingStatus to empty in the database for the specified record ID
    $query = "UPDATE importrecords SET PackingStatus = '' WHERE UniqueID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $recordId);

    if ($stmt->execute()) {
        echo "Packing status reset successfully.";
    } else {
        echo "Failed to reset packing status.";
    }
} else {
    echo "Invalid request.";
}
?>