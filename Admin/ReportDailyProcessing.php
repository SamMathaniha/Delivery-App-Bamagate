<?php
include "../config.php";

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// Check for the no_records session variable and set a JavaScript variable if it exists
$noRecords = false;
if (isset($_SESSION['no_records']) && $_SESSION['no_records']) {
    $noRecords = true;
    unset($_SESSION['no_records']);
}

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

// Handle request to download the daily processing report
if (isset($_GET['download']) && isset($_GET['date'])) {
    $date = $_GET['date'];

    // Fetch records based on date
    $records = fetchRecordsByDate($date);

    // Download as CSV format
    downloadAsCsv($records);
}

// Handle AJAX request to fetch records
if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $records = fetchRecordsByDate($date);
    echo json_encode($records);
    exit();
}

// Handle AJAX request to update shipping city
if (isset($_POST['id']) && isset($_POST['shippingCity'])) {
    $id = $_POST['id'];
    $shippingCity = $_POST['shippingCity'];

    // Update the shipping city in the database
    $query = "UPDATE importrecords SET ShippingCity = ? WHERE ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $shippingCity, $id);
    $stmt->execute();

    // Determine the new delivery partner based on updated shipping city
    $deliveryPartnerQuery = "SELECT DeliveryPartner FROM shippingcity WHERE CityName = ?";
    $stmt = $conn->prepare($deliveryPartnerQuery);
    $stmt->bind_param("s", $shippingCity);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $deliveryPartner = $row['DeliveryPartner'];
    } else {
        $deliveryPartner = "Unknown";
    }

    // Update the delivery partner in the database
    $updateDeliveryPartnerQuery = "UPDATE importrecords SET DeliveryPartner = ? WHERE ID = ?";
    $stmt->bind_param("ss", $deliveryPartner, $id);
    $stmt->execute();

    echo json_encode(['success' => true, 'shippingCity' => $shippingCity, 'deliveryPartner' => $deliveryPartner]);
    exit();
}

// Function to download as CSV
function downloadAsCsv($records)
{
    if (count($records) > 0) {
        $filename = "DailyProcessingReport_" . date("Y-m-d") . ".csv";

        // Send headers to force download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename);
        header('Cache-Control: max-age=0');
        header('Expires: 0');
        header('Pragma: public');

        // Open a file pointer connected to php://output
        $fp = fopen('php://output', 'w');

        // Write CSV headers
        fputcsv($fp, ['ID', 'ShippingName', 'ShippingCity', 'ShippingPhone', 'OutstandingBalance', 'DeliveryPartner']);

        // Write CSV rows
        foreach ($records as $record) {
            fputcsv($fp, [
                $record['ID'],
                $record['ShippingName'],
                $record['ShippingCity'],
                $record['ShippingPhone'],
                $record['OutstandingBalance'],
                $record['DeliveryPartner']
            ]);
        }

        // Close file pointer
        fclose($fp);
        exit;
    } else {
        // Set session variable and redirect back to the page
        $_SESSION['no_records'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
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

    <!-- SweetAlert -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/sweetalert/dist/sweetalert.css">

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
            max-height: 500px;
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

        .DatePicker {
            margin-left: 250px;
        }

        .download-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 250px;
            float: right;
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
                                <div class="dd_item">
                                    <form method="post" action="../logout.php">
                                        <button type="submit" name="logout"
                                            style="background: none; border: none; color: inherit; cursor: pointer;">Logout</button>
                                    </form>
                                </div>
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

                <div class="DatePicker">
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
                                    if (record.DeliveryPartner.trim() === 'Unknown') {
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
                            swal('Success', 'Shipping City and Delivery Partner updated successfully.', 'success');
                            $('.editable[data-id="' + id + '"]').text(result.shippingCity);
                            $('.recordsTable tbody').find('tr').each(function () {
                                if ($(this).find('td:first').text() === id) {
                                    $(this).find('td:last').text(result.deliveryPartner);
                                }
                            });
                        } else {
                            swal('Error', 'Failed to update Shipping City.', 'error');
                        }
                    }
                });
            });

            // Handle the download button click
            $('#download-report').on('click', function () {
                var selectedDate = $('#date-filter').val();
                if (selectedDate) {
                    window.location.href = '?download=true&date=' + selectedDate; // Download as CSV format
                } else {
                    swal('Warning', 'Please select a date to download the report.', 'warning');
                }
            });

            // Display SweetAlert if there are no records to download
            <?php if ($noRecords): ?>
                swal('No records found', 'No records found for the selected date.', 'warning');
            <?php endif; ?>

        });
    </script>

</body>

</html>