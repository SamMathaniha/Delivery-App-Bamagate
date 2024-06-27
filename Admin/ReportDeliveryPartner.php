<?php
include "../config.php";

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

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

// Handle AJAX request to fetch records
if (isset($_GET['date']) && isset($_GET['deliveryPartner'])) {
    $date = $_GET['date'];
    $deliveryPartner = $_GET['deliveryPartner'];
    $records = fetchRecordsByDateAndPartner($date, $deliveryPartner);
    echo json_encode($records);
    exit();
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

        /* Styles for Delivery Partner Select Box */
        #delivery-partner {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
            width: 250px;
            margin-left: 20px;
            text-align: center;
        }

        /* Optional: Style for the label next to the select box */
        .DatePicker label {
            margin-right: 10px;
        }

        #last-waybill-id {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
            width: 250px;
            margin-left: 20px;
            text-align: center;
        }

        .download-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
            width: 250px;
            margin-right: 100px;
            float: right;
        }
    </style>
    <script>
        $(document).ready(function () {
            $(".hamburger .hamburger__inner").click(function () {
                $(".wrapper").toggleClass("active");
            });

            $(".top_navbar .fas").click(function () {
                $(".profile_dd").toggleClass("active");
            });

            $('#date-filter, #delivery-partner').on('change', function () {
                fetchRecords();
            });

            $('#download-records').on('click', function () {
                downloadRecords();
            });

            function fetchRecords() {
                var selectedDate = $('#date-filter').val();
                var deliveryPartner = $('#delivery-partner').val();

                if (selectedDate && deliveryPartner) {
                    $.ajax({
                        url: 'ReportDeliveryPartner.php',
                        type: 'GET',
                        data: {
                            date: selectedDate,
                            deliveryPartner: deliveryPartner
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
                                    tableBody += '<td>' + record.ShippingCity.trim() + '</td>';
                                    tableBody += '<td>' + record.ShippingPhone + '</td>';
                                    tableBody += '<td>' + record.OutstandingBalance + '</td>';
                                    tableBody += '<td>' + record.Date + '</td>';
                                    tableBody += '<td>' + record.DeliveryPartner + '</td>';
                                    tableBody += '<td>' + record.DistrictName + '</td>';
                                    tableBody += '</tr>';
                                });
                            } else {
                                tableBody = '<tr><td colspan="10">No records found.</td></tr>';
                            }
                            $('.recordsTable tbody').html(tableBody);
                            $('.recordsTable').show();
                        }
                    });
                }
            }

            function downloadRecords() {
                var selectedDate = $('#date-filter').val();
                var deliveryPartner = $('#delivery-partner').val();
                var lastWaybillId = $('#last-waybill-id').val();

                if (selectedDate && deliveryPartner && lastWaybillId) {
                    // Check if there are records to download
                    $.ajax({
                        url: 'ReportDeliveryPartner.php',
                        type: 'GET',
                        data: {
                            date: selectedDate,
                            deliveryPartner: deliveryPartner
                        },
                        success: function (data) {
                            var records = JSON.parse(data);
                            if (records.length > 0) {
                                // If records are found, initiate download
                                window.location.href = 'download_records.php?date=' + selectedDate + '&deliveryPartner=' + deliveryPartner + '&lastWaybillId=' + lastWaybillId;
                            } else {
                                // If no records found, disable download button
                                swal('Error', 'No records found to download.', 'error');
                            }
                        }
                    });
                } else {
                    swal('Error', 'Please enter all required fields.', 'error');
                }
            }

        });

    </script>

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

                <!-- Date Picker and Delivery Partner Selection -->
                <div class="DatePicker">

                    <input type="date" id="date-filter" name="date-filter">
                    <select id="delivery-partner" name="delivery-partner">
                        <option value="">Select Delivery Partner</option>
                        <option value="Courier">Courier</option>
                        <option value="Direct">Direct</option>
                    </select>
                    <input type="text" id="last-waybill-id" name="last-waybill-id" placeholder="Enter waybill First ID">
                    <button id="download-records" class="download-button">Download Records</button>
                </div>


                <!-- Records Table -->
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
                                <th>District Name</th>
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

</body>

</html>