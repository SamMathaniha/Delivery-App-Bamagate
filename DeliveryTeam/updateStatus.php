<?php
include "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_id'])) {
    $record_id = $_POST['record_id'];

    // Update the PackingStatus to 'Completed' for the given record ID
    $query = "UPDATE importrecords SET PackingStatus = 'Completed' WHERE uniqueID = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die('Error preparing statement.');
    }
    $stmt->bind_param("i", $record_id);
    if (!$stmt->execute()) {
        die('Error updating record: ' . $stmt->error);
    }
    echo 'Packing status updated successfully.';
} else {
    echo 'Invalid request.';
}
?>