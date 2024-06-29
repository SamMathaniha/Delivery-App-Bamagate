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

        .DatePicker {
            margin-left: 250px;
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
                <?php include './Components/DeliveryTeamSidebar.php' ?>

                <div class="DatePicker">
                    <label for="date-filter">Pick a Date:</label>
                    <input type="date" id="date-filter" name="date-filter">
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
                                    tableBody += '<td>' + record.ShippingCity.trim() + '</td>';
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
        });
    </script>

</body>

</html>