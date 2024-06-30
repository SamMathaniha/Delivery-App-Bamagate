<?php
include "../config.php";

session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Handle AJAX request to reset status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_id'])) {
    $recordId = $_POST['record_id'];
    $action = $_POST['action']; // action can be 'packing', 'fulfilled', 'call', or 'dispatch'

    // Determine which status to reset based on action
    switch ($action) {
        case 'packing':
            $query = "UPDATE importrecords SET PackingStatus = '' WHERE UniqueID = ?";
            break;
        case 'fulfilled':
            $query = "UPDATE importrecords SET FulfilledStatus = '' WHERE UniqueID = ?";
            break;
        case 'call':
            $query = "UPDATE importrecords SET CallStatus = '' WHERE UniqueID = ?";
            break;
        case 'dispatch':
            $query = "UPDATE importrecords SET DispatchStatus = '' WHERE UniqueID = ?";
            break;
        default:
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
} else {
    echo "Invalid request.";
}
?>