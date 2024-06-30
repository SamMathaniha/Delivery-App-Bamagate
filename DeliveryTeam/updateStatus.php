<?php
include "../config.php";

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get record_id and action from POST request
    $record_id = $_POST['record_id'];
    $action = $_POST['action']; // action can be 'packing' or 'fulfilled'

    // Determine which status to update based on action
    if ($action === 'packing') {
        $query = "UPDATE importrecords SET PackingStatus = 'Completed' WHERE UniqueID = ?";
    } elseif ($action === 'fulfilled') {
        $query = "UPDATE importrecords SET FulfilledStatus = 'Completed' WHERE UniqueID = ?";
    } else {
        echo "Invalid action specified.";
        exit();
    }

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $record_id);

    if ($stmt->execute()) {
        echo ucfirst($action) . " status updated successfully.";
    } else {
        echo "Failed to update " . $action . " status.";
    }

    $stmt->close();
}
?>