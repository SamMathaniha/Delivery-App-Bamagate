<?php
include "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_id'])) {
    $record_id = $_POST['record_id'];

    // Extract numeric part of ID (ignoring first character)
    $numeric_id = substr($record_id, 1);

    // Update the PackingStatus to 'Completed' for the extracted numeric ID
    $query = "UPDATE importrecords SET PackingStatus = 'Completed' WHERE ID = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Error preparing statement.');
    }
    $stmt->bind_param("i", $numeric_id);
    if (!$stmt->execute()) {
        die('Error updating record: ' . $stmt->error);
    }
    echo 'Packing status updated successfully.';
} else {
    echo 'Invalid request.';
}
?>