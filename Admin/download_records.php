<?php
include "../config.php";

// Function to fetch records based on date and delivery partner
function fetchRecordsByDateAndPartner($date, $deliveryPartner)
{
    global $conn;
    $query = "SELECT ir.*, sc.DistrictName 
              FROM importrecords ir
              LEFT JOIN shippingcity sc ON ir.ShippingCity = sc.CityName
              WHERE DATE(ir.Date) = ? AND ir.DeliveryPartner = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param("ss", $date, $deliveryPartner);
    if (!$stmt->execute()) {
        return [];
    }
    $result = $stmt->get_result();
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    return $records;
}

// Handle download request
if (isset($_GET['date']) && isset($_GET['deliveryPartner']) && isset($_GET['lastWaybillId'])) {
    $date = $_GET['date'];
    $deliveryPartner = $_GET['deliveryPartner'];
    $lastWaybillId = intval($_GET['lastWaybillId']); // Ensure it's an integer

    // Fetch records
    $records = fetchRecordsByDateAndPartner($date, $deliveryPartner);

    // If no records found, display alert and exit
    if (empty($records)) {
        echo "<script>alert('Empty Records');</script>";
        exit();
    }

    // Create CSV content
    $csvFileName = 'delivery_records_' . date('Y-m-d') . '.csv';
    $output = fopen('php://output', 'w');

    // Set headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=' . $csvFileName);

    // Write headers
    fputcsv($output, array('Waybill ID', 'Order Number', 'Receiver Name', 'Delivery Address', 'District Name', 'City', 'Receiver Phone', 'COD'));

    // Write records
    foreach ($records as $record) {
        fputcsv(
            $output,
            array(
                $lastWaybillId++, // Incrementing Waybill ID
                $record['ID'],
                $record['ShippingName'],
                $record['ShippingAddress1'],
                $record['DistrictName'],
                $record['ShippingCity'],
                $record['ShippingPhone'],
                $record['OutstandingBalance'],
                // Adding District Name
            )
        );
    }

    fclose($output);
    exit();
}
?>