<?php
include "../config.php";

// Function to fetch records based on date and delivery partner
function fetchRecordsByDateAndPartner($date, $deliveryPartner)
{
    global $conn;
    $query = "SELECT * FROM importrecords WHERE DATE(Date) = ? AND DeliveryPartner = ?";
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
    fputcsv($output, array('Waybill ID', 'ID', 'Financial Status', 'Shipping Name', 'Shipping Address', 'Shipping City', 'Shipping Phone', 'Outstanding Balance', 'Date', 'Delivery Partner'));

    // Write records
    foreach ($records as $record) {
        fputcsv($output, array(
            $lastWaybillId++, // Incrementing Waybill ID
            $record['ID'],
            $record['FinancialStatus'],
            $record['ShippingName'],
            $record['ShippingAddress1'],
            $record['ShippingCity'],
            $record['ShippingPhone'],
            $record['OutstandingBalance'],
            $record['Date'],
            $record['DeliveryPartner']
        )
        );
    }

    fclose($output);
    exit();
}
?>