<?php
include "../config.php";

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
            $duplicateCount = 0;

            while (($line = fgetcsv($csvFile)) !== false) {
                $Name = $line[0];
                $FinancialStatus = $line[2];
                $ShippingName = $line[34];
                $ShippingAddress1 = $line[36];
                $ShippingCity = trim($line[39]); // Trim spaces around ShippingCity
                $ShippingPhone = $line[43];
                $OutstandingBalance = $line[51];

                // Check if all required fields are present
                if (
                    !empty($Name) && !empty($FinancialStatus) && !empty($ShippingName) &&
                    !empty($ShippingAddress1) && !empty($ShippingCity) &&
                    !empty($ShippingPhone) && ($OutstandingBalance !== '')
                ) {
                    // Adjust ShippingCity to have the first letter capitalized
                    $ShippingCity = ucwords(strtolower($ShippingCity));

                    // Determine DeliveryPartner based on ShippingCity from the database
                    $deliveryPartnerQuery = "SELECT DeliveryPartner FROM shippingcity WHERE CityName = ?";
                    $stmt = $conn->prepare($deliveryPartnerQuery);
                    $stmt->bind_param("s", $ShippingCity);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    if ($row) {
                        $DeliveryPartner = $row['DeliveryPartner'];
                    } else {
                        $DeliveryPartner = "Unknown";
                    }

                    // Check if ID already exists in the database
                    $checkQuery = "SELECT COUNT(*) as count FROM importrecords WHERE ID = ?";
                    $stmt = $conn->prepare($checkQuery);
                    $stmt->bind_param("s", $Name);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    if ($row['count'] == 0) {
                        // Insert record into database with a timestamp
                        $insertQuery = "INSERT INTO importrecords (ID, FinancialStatus, ShippingName, ShippingAddress1, ShippingCity, ShippingPhone, OutstandingBalance, Timestamp, DeliveryPartner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($insertQuery);
                        $stmt->bind_param("ssssssiss", $Name, $FinancialStatus, $ShippingName, $ShippingAddress1, $ShippingCity, $ShippingPhone, $OutstandingBalance, $timestamp, $DeliveryPartner);
                        $stmt->execute();
                    } else {
                        $duplicateCount++;
                    }
                }
            }
            fclose($csvFile);

            // Redirect to the Orders page with appropriate parameters
            $redirectUrl = "ImportRecords.php?timestamp=$timestamp";
            if ($duplicateCount > 0) {
                $redirectUrl .= "&duplicates=$duplicateCount";
            } else {
                $redirectUrl .= "&success=true";
            }
            header("Location: $redirectUrl");
            exit();
        }
    } else {
        // Redirect to the Orders page with error parameter
        header("Location: ImportRecords.php?error=invalid_file");
        exit();
    }
}
?>