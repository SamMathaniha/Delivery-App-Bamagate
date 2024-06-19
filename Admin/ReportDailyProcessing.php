<?php
include "../config.php";

// Function to fetch records based on date
function fetchRecordsByDate($date)
{
    global $conn;
    $query = "SELECT * FROM importrecords WHERE DATE(Date) = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        return [];
    }
    $stmt->bind_param("s", $date);
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

// Function to fetch all records
function fetchAllRecords()
{
    global $conn;
    $query = "SELECT * FROM importrecords";
    $result = $conn->query($query);
    if ($result === false) {
        return [];
    }
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    return $records;
}

// Handle AJAX request to fetch records
if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $records = fetchRecordsByDate($date);
    echo json_encode($records);
    exit;
}

// Handle AJAX request to update ShippingCity and DeliveryPartner
if (isset($_POST['id']) && isset($_POST['shippingCity'])) {
    $id = $_POST['id'];
    $shippingCity = trim(ucwords(strtolower($_POST['shippingCity'])));

    // Determine the DeliveryPartner based on the new ShippingCity
    $query = "SELECT DeliveryPartner FROM shippingcity WHERE CityName = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo json_encode(['success' => false]);
        exit;
    }
    $stmt->bind_param("s", $shippingCity);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false]);
        exit;
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $deliveryPartner = $row['DeliveryPartner'];
    } else {
        $deliveryPartner = "Unknown";
    }

    $updateQuery = "UPDATE importrecords SET ShippingCity = ?, DeliveryPartner = ? WHERE ID = ?";
    $stmt = $conn->prepare($updateQuery);
    if ($stmt === false) {
        echo json_encode(['success' => false]);
        exit;
    }
    $stmt->bind_param("sss", $shippingCity, $deliveryPartner, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'shippingCity' => $shippingCity, 'deliveryPartner' => $deliveryPartner]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Handle request to download the daily processing report
if (isset($_GET['download']) && isset($_GET['date'])) {
    $date = $_GET['date'];

    // Fetch records based on date
    $records = fetchRecordsByDate($date);

    if (isset($_GET['format']) && $_GET['format'] === 'xlsx') {
        // Download as XLSX (Excel) format
        downloadAsXlsx($records);
    } else {
        // Download as CSV format (default)
        downloadAsCsv($records);
    }
}

// Function to download as CSV
function downloadAsCsv($records)
{
    if (count($records) > 0) {
        $filename = "DailyProcessingReport_" . date("Y-m-d") . ".csv";

        // Send headers to force download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename);

        // Open a file pointer connected to php://output
        $fp = fopen('php://output', 'w');

        // Write CSV headers
        fputcsv($fp, array_keys($records[0]));

        // Write CSV rows
        foreach ($records as $record) {
            fputcsv($fp, $record);
        }

        // Close file pointer
        fclose($fp);
        exit;
    } else {
        echo "No records found for the selected date.";
        exit;
    }
}

// Function to download as XLSX (Excel) format
function downloadAsXlsx($records)
{
    if (count($records) > 0) {
        // Define the filename
        $filename = "DailyProcessingReport_" . date("Y-m-d") . ".xlsx";

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $filename);
        header('Cache-Control: max-age=0');

        // Start writing the XLSX content
        $output = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $output .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
        $output .= '<body>';
        $output .= '<table>';

        // Write headers row
        $output .= '<tr>';
        foreach (array_keys($records[0]) as $header) {
            $output .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $output .= '</tr>';

        // Write data rows
        foreach ($records as $record) {
            $output .= '<tr>';
            foreach ($record as $value) {
                $output .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $output .= '</tr>';
        }

        $output .= '</table>';
        $output .= '</body></html>';

        // Output the content
        echo $output;
        exit;
    } else {
        echo "No records found for the selected date.";
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bamagate Delivery App</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <!-- SideBar-Menu CSS -->
    <link rel="stylesheet" href="../css/styles.css">
    <!-- Other CSS -->
    <link rel="stylesheet" href="../css/Other.css">

    <style>
        input[type="date"] {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .recordsTable {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
            display: none;
        }

        .recordsTable table {
            width: 100%;
            border-collapse: collapse;
        }

        .recordsTable th,
        .recordsTable td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .edit-icon {
            cursor: pointer;
            color: #007bff;
            margin-left: 5px;
        }

        .editable[contenteditable="true"] {
            outline: 2px dashed #007bff;
            padding: 2px;
            margin: -2px;
        }

        .download-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 20px;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="top_navbar">
            <div class="hamburger">
                <div class="hamburger__inner">
                    <div class="one"></div>
                    <div class="two"></div>
                    <div class="three"></div>
                </div>
            </div>
            <div class="menu">
                <div class="logo">
                    <img class="LogoStyle" src="../img/BamagateLogo.png" alt="profile_pic">
                </div>
                <div class="right_menu">
                    <ul>
                        <li><i class="fas fa-user"></i>
                            <div class="profile_dd">
                                <div class="dd_item">Profile</div>
                                <div class="dd_item">Change Password</div>
                                <div class="dd_item">Logout</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="main_container">

            <div class="container">

                <!-- Side Bar -->
                <?php include './Components/Sidebar.php' ?>
                <div>
                    <label for="date-filter">Pick a Date:</label>
                    <input type="date" id="date-filter" name="date-filter">
                    <div id="download-report" class="download-button">Download Daily Processing Report</div>
                </div>
                <div class="recordsTable">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Financial Status</th>
                                <th>Shipping Name</th>
                                <th>Shipping Address</th>
                                <th>Shipping City</th>
                                <th>Shipping Phone</th>
                                <th>Outstanding Balance</th>
                                <th>Date</th>
                                <th>Delivery Partner</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Records will be inserted here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <footer class="credit"> </footer>

    <script>
        $(document).ready(function () {
            $(".hamburger .hamburger__inner").click(function () {
                $(".wrapper").toggleClass("active")
            });

            $(".top_navbar .fas").click(function () {
                $(".profile_dd").toggleClass("active");
            });

            $('#date-filter').on('change', function () {
                var selectedDate = $(this).val();
                if (selectedDate) {
                    $.ajax({
                        url: '',
                        type: 'GET',
                        data: {
                            date: selectedDate
                        },
                        success: function (data) {
                            var records = JSON.parse(data);
                            var tableBody = '';
                            if (records.length > 0) {
                                records.forEach(function (record) {
                                    tableBody += '<tr>';
                                    tableBody += '<td>' + record.ID + '</td>';
                                    tableBody += '<td>' + record.FinancialStatus + '</td>';
                                    tableBody += '<td>' + record.ShippingName + '</td>';
                                    tableBody += '<td>' + record.ShippingAddress1 + '</td>';
                                    tableBody += '<td>';
                                    if (record.DeliveryPartner === "Unknown") {
                                        tableBody += '<span class="editable" contenteditable="true" data-id="' + record.ID + '">' + record.ShippingCity.trim() + '</span>';
                                        tableBody += ' <i class="fas fa-edit edit-icon" data-id="' + record.ID + '"></i>';
                                    } else {
                                        tableBody += record.ShippingCity.trim();
                                    }
                                    tableBody += '</td>';
                                    tableBody += '<td>' + record.ShippingPhone + '</td>';
                                    tableBody += '<td>' + record.OutstandingBalance + '</td>';
                                    tableBody += '<td>' + record.Date + '</td>';
                                    tableBody += '<td>' + record.DeliveryPartner + '</td>';
                                    tableBody += '</tr>';
                                });
                            } else {
                                tableBody = '<tr><td colspan="9">No records found.</td></tr>';
                            }
                            $('.recordsTable tbody').html(tableBody);
                            $('.recordsTable').show();
                        }
                    });
                } else {
                    $('.recordsTable').hide();
                }
            });

            // Handle the editing of the ShippingCity column
            $(document).on('click', '.edit-icon', function () {
                var $editable = $(this).siblings('.editable');
                $editable.focus();
            });

            $(document).on('blur', '.editable', function () {
                var id = $(this).data('id');
                var shippingCity = $(this).text().trim();
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        id: id,
                        shippingCity: shippingCity
                    },
                    success: function (response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            alert('Shipping City and Delivery Partner updated successfully.');
                            $('.editable[data-id="' + id + '"]').text(result.shippingCity);
                            $('.recordsTable tbody').find('tr').each(function () {
                                if ($(this).find('td:first').text() === id) {
                                    $(this).find('td:last').text(result.deliveryPartner);
                                }
                            });
                        } else {
                            alert('Failed to update Shipping City.');
                        }
                    }
                });
            });

            // Handle the download button click
            $('#download-report').on('click', function () {
                var selectedDate = $('#date-filter').val();
                if (selectedDate) {
                    window.location.href = '?download=true&date=' + selectedDate + '&format=xlsx'; // Specify format
                } else {
                    alert('Please select a date to download the report.');
                }
            });

        });
    </script>

</body>

</html>