<?php
include "../config.php";

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Handle AJAX request to reset status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_id']) && isset($_POST['action'])) {
    $recordId = $_POST['record_id'];
    $action = $_POST['action']; // action can be 'packing' or 'fulfilled'

    // Determine which status to reset based on action
    if ($action === 'packing') {
        $query = "UPDATE importrecords SET PackingStatus = '' WHERE UniqueID = ?";
    } elseif ($action === 'fulfilled') {
        $query = "UPDATE importrecords SET FulfilledStatus = '' WHERE UniqueID = ?";
    } else {
        echo "Invalid action specified.";
        exit();
    }

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $recordId);

    if ($stmt->execute()) {
        echo ucfirst($action) . " status reset successfully.";
    } else {
        echo "Failed to reset " . $action . " status.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>