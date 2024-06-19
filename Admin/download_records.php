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
if (isset($_GET['date']) && isset($_GET['deliveryPartner'])) {
    $date = $_GET['date'];
    $deliveryPartner = $_GET['deliveryPartner'];

    // Fetch records
    $records = fetchRecordsByDateAndPartner($date, $deliveryPartner);

    // Create CSV content
    $csvFileName = 'delivery_records_' . date('Y-m-d') . '.csv';
    $output = fopen('php://output', 'w');

    // Set headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=' . $csvFileName);

    // Write headers
    fputcsv($output, array('ID', 'Financial Status', 'Shipping Name', 'Shipping Address', 'Shipping City', 'Shipping Phone', 'Outstanding Balance', 'Date', 'Delivery Partner'));

    // Write records
    foreach ($records as $record) {
        fputcsv($output, array(
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