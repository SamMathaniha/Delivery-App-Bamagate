<?php
include "config.php";

if (isset($_POST["submit"])) {
    // Define allowed CSV MIME types
    $csvMimes = array(
        'text/csv',
        'application/csv',
        'application/vnd.ms-excel',
        'application/octet-stream' // General MIME type for CSV
    );

    // Get uploaded file MIME type
    $uploadedMimeType = trim(strtolower($_FILES['file']['type']));

    // Check if file is not empty and MIME type is in allowed types
    if (!empty($_FILES['file']['name']) && in_array($uploadedMimeType, $csvMimes)) {
        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            fgetcsv($csvFile); // Skip the first line (header)

            // Get current timestamp
            $timestamp = time();

            while (($line = fgetcsv($csvFile)) !== false) {
                $Name = $line[0];
                $FinancialStatus = $line[2];
                $ShippingName = $line[34];
                $ShippingAddress1 = $line[36];
                $ShippingCity = $line[39];
                $ShippingPhone = $line[43];
                $OutstandingBalance = $line[51];

                // Check if all required fields are present
                if (
                    !empty($Name) && !empty($FinancialStatus) && !empty($ShippingName) &&
                    !empty($ShippingAddress1) && !empty($ShippingCity) &&
                    !empty($ShippingPhone) && ($OutstandingBalance !== '')
                ) {

                    // Check if ID already exists in the database
                    $checkQuery = "SELECT COUNT(*) as count FROM importrecords WHERE ID = ?";
                    $stmt = $conn->prepare($checkQuery);
                    $stmt->bind_param("s", $Name);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    if ($row['count'] == 0) {
                        // Insert record into database with a timestamp
                        $insertQuery = "INSERT INTO importrecords (ID, FinancialStatus, ShippingName, ShippingAddress1, ShippingCity, ShippingPhone, OutstandingBalance, Timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($insertQuery);
                        $stmt->bind_param("sssssssi", $Name, $FinancialStatus, $ShippingName, $ShippingAddress1, $ShippingCity, $ShippingPhone, $OutstandingBalance, $timestamp);
                        $stmt->execute();
                    }
                }
            }
            fclose($csvFile);
        }
    } else {
        echo "Invalid File";
    }

    // Redirect to the Orders page with timestamp
    header("Location: Orders.php?timestamp=$timestamp");
    exit();
}
?>