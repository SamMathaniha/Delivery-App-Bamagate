<?php
include "../config.php";

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get record_id, action, and remarks from POST request
    $record_id = $_POST['record_id'];
    $action = $_POST['action']; // action can be 'packing', 'fulfilled', 'call', 'dispatch', or 'remarks'

    // Determine which status or remarks to update based on action
    switch ($action) {
        case 'packing':
            $query = "UPDATE importrecords SET PackingStatus = 'Completed' WHERE UniqueID = ?";
            break;
        case 'fulfilled':
            $query = "UPDATE importrecords SET FulfilledStatus = 'Completed' WHERE UniqueID = ?";
            break;
        case 'call':
            $query = "UPDATE importrecords SET CallStatus = 'Completed' WHERE UniqueID = ?";
            break;
        case 'dispatch':
            $query = "UPDATE importrecords SET DispatchStatus = 'Completed' WHERE UniqueID = ?";
            break;
        case 'remarks':
            $remarks = $_POST['remarks'];
            $query = "UPDATE importrecords SET ItemRemarks = ? WHERE UniqueID = ?";
            break;
        default:
            echo "Invalid action specified.";
            exit();
    }

    // Prepare and execute the query
    if ($action === 'remarks') {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $remarks, $record_id);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $record_id);
    }

    if ($stmt->execute()) {
        echo ucfirst($action) . " updated successfully.";
    } else {
        echo "Failed to update " . $action . ".";
    }

    $stmt->close();
}
?>