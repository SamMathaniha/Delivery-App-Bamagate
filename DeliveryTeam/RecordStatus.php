<?php
include "../config.php";

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// Function to fetch records based on date
function fetchRecordsByDate($date)
{
    global $conn;
    $query = "SELECT UniqueID, ID, ShippingName, ShippingCity, ShippingPhone, OutstandingBalance, DeliveryPartner, PackingStatus, FulfilledStatus, CallStatus, DispatchStatus, ItemRemarks FROM importrecords WHERE DATE(Date) = ?";
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

// Handle AJAX request to fetch records
if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $records = fetchRecordsByDate($date);
    echo json_encode($records);
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bamagate Delivery App</title>
    <link rel="stylesheet" href="../css/DeliveryTeamStyle.css">
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

        .recordsTable td {
            text-align: center;
        }

        .recordsTable button {
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 16px;
        }

        .recordsTable button i {
            margin-right: 5px;
        }

        .DatePicker {
            margin-left: 20px;
        }

        .recordsTable textarea {
            width: 100%;
            min-height: 50px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            /* Allow vertical resizing */
        }
    </style>
    <script>
        $(document).ready(function () {
            // Function to fetch records based on selected date
            $('#date-filter').on('change', function () {
                var selectedDate = $(this).val();
                if (selectedDate) {
                    $.ajax({
                        url: 'recordStatus.php', // PHP file to fetch records
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
                                    tableBody += '<td>' + record.UniqueID + '</td>';
                                    tableBody += '<td>' + record.ID + '</td>';
                                    tableBody += '<td>' + record.ShippingName + '</td>';
                                    tableBody += '<td>' + record.ShippingCity.trim() + '</td>';
                                    tableBody += '<td>' + record.ShippingPhone + '</td>';
                                    tableBody += '<td>' + record.OutstandingBalance + '</td>';
                                    tableBody += '<td>' + record.DeliveryPartner + '</td>';



                                    // PackingStatus
                                    if (record.PackingStatus === '') {
                                        tableBody += '<td><button class="update-status" style="background-color: red; color: white;" data-action="packing"><i class="fas fa-thumbs-down"></i></button></td>';
                                    } else if (record.PackingStatus === 'Completed') {
                                        tableBody += '<td><button class="reset-status" style="background-color: green; color: white;" data-action="packing"><i class="fas fa-thumbs-up"></i></button></td>';
                                    } else {
                                        tableBody += '<td>' + record.PackingStatus + '</td>';
                                    }

                                    // FulfilledStatus
                                    if (record.FulfilledStatus === '') {
                                        tableBody += '<td><button class="update-status" style="background-color: red; color: white;" data-action="fulfilled"><i class="fas fa-thumbs-down"></i></td>';
                                    } else if (record.FulfilledStatus === 'Completed') {
                                        tableBody += '<td><button class="reset-status" style="background-color: green; color: white;" data-action="fulfilled"><i class="fas fa-thumbs-up"></i></button></td>';
                                    } else {
                                        tableBody += '<td>' + record.FulfilledStatus + '</td>';
                                    }

                                    // CallStatus
                                    if (record.CallStatus === '') {
                                        tableBody += '<td><button class="update-status" style="background-color: red; color: white;" data-action="call"><i class="fas fa-thumbs-down"></i></button></td>';
                                    } else if (record.CallStatus === 'Completed') {
                                        tableBody += '<td><button class="reset-status" style="background-color: green; color: white;" data-action="call"><i class="fas fa-thumbs-up"></i></button></td>';
                                    } else {
                                        tableBody += '<td>' + record.CallStatus + '</td>';
                                    }

                                    // DispatchStatus
                                    if (record.DispatchStatus === '') {
                                        tableBody += '<td><button class="update-status" style="background-color: red; color: white;" data-action="dispatch"><i class="fas fa-thumbs-down"></i></button></td>';
                                    } else if (record.DispatchStatus === 'Completed') {
                                        tableBody += '<td><button class="reset-status" style="background-color: green; color: white;" data-action="dispatch"><i class="fas fa-thumbs-up"></i></button></td>';
                                    } else {
                                        tableBody += '<td>' + record.DispatchStatus + '</td>';
                                    }

                                    // Remarks
                                    tableBody += '<td><textarea class="remarks-input">' + record.ItemRemarks + '</textarea></td>';
                                    tableBody += '</tr>';
                                });
                            } else {
                                tableBody = '<tr><td colspan="11">No records found.</td></tr>';
                            }
                            $('.recordsTable tbody').html(tableBody);
                            $('.recordsTable').show();
                        }
                    });
                } else {
                    $('.recordsTable').hide();
                }
            });

            // Click event handler for updating status
            $('.recordsTable').on('click', '.update-status', function () {
                var recordId = $(this).closest('tr').find('td:first').text(); // Assuming ID is in the first column
                var action = $(this).data('action'); // Get the action (packing, fulfilled, call, dispatch)

                // AJAX call to update status
                $.ajax({
                    url: 'updateStatus.php', // PHP file to update status
                    type: 'POST',
                    data: { record_id: recordId, action: action },
                    success: function (response) {
                        // Refresh records table after successful update
                        $('#date-filter').trigger('change'); // Trigger date change to reload records
                        swal("Success", response, "success");
                    },
                    error: function (xhr, status, error) {
                        swal("Error", "Failed to update " + action + " status: " + error, "error");
                    }
                });
            });

            // Click event handler for resetting status
            $('.recordsTable').on('click', '.reset-status', function () {
                var recordId = $(this).closest('tr').find('td:first').text(); // Assuming ID is in the first column
                var action = $(this).data('action'); // Get the action (packing, fulfilled, call, dispatch)

                // AJAX call to reset status
                $.ajax({
                    url: 'resetStatus.php', // PHP file to handle reset
                    type: 'POST',
                    data: { record_id: recordId, action: action },
                    success: function (response) {
                        // Refresh records table after successful reset
                        $('#date-filter').trigger('change'); // Trigger date change to reload records
                        swal("Success", response, "success");
                    },
                    error: function (xhr, status, error) {
                        swal("Error", "Failed to reset " + action + " status: " + error, "error");
                    }
                });
            });

            // Click event handler for updating remarks
            $('.recordsTable').on('change', '.remarks-input', function () {
                var recordId = $(this).closest('tr').find('td:first').text(); // Assuming ID is in the first column
                var remarks = $(this).val(); // Get remarks from textarea

                // AJAX call to update remarks
                $.ajax({
                    url: 'updateStatus.php', // PHP file to update status
                    type: 'POST',
                    data: { record_id: recordId, action: 'remarks', remarks: remarks },
                    success: function (response) {
                        swal("Success", response, "success");
                    },
                    error: function (xhr, status, error) {
                        swal("Error", "Failed to update remarks: " + error, "error");
                    }
                });
            });


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
                <?php include './Components/DeliveryTeamSidebar.php' ?>

                <div class="DatePicker">
                    <label for="date-filter">Pick a Date:</label>
                    <input type="date" id="date-filter" name="date-filter">
                </div>
                <div class="recordsTable">
                    <table>
                        <thead>
                            <tr>
                                <th>UniqueID</th>
                                <th>ID</th>
                                <th>Shipping Name</th>
                                <th>Shipping City</th>
                                <th>Phone No</th>
                                <th>COD</th>
                                <th>Delivery Partner</th>
                                <th>Packing Status</th>
                                <th>Fulfilled Status</th>
                                <th>Call Status</th>
                                <th>Dispatch Status</th>
                                <th>Item OTS / Any remarks</th>

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
        });
    </script>

</body>

</html>