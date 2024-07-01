<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

include "../config.php";

// Fetch records filtered by ID
$id_filter = isset($_GET['id']) ? $_GET['id'] : '';

$query = "SELECT * FROM importrecords WHERE 1";

// Apply ID filter if set
if ($id_filter) {
    $query .= " AND ID LIKE '%$id_filter%'";
}

// Order by Date in descending order
$query .= " ORDER BY Date DESC";

$result = $conn->query($query);

// If it's an AJAX request, return the JSON response and exit
if (isset($_GET['ajax'])) {
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode($records);
    exit;
}

// Handle the return confirmation
if (isset($_POST['confirm_return'])) {
    $return_id = $_POST['return_id'];
    $update_query = "UPDATE importrecords SET `Return` = 'Confirmed Return' WHERE ID = '$return_id'";
    if ($conn->query($update_query) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
    exit;
}

// Handle the cancel return confirmation
if (isset($_POST['cancel_return'])) {
    $return_id = $_POST['return_id'];
    $update_query = "UPDATE importrecords SET `Return` = '' WHERE ID = '$return_id'";
    if ($conn->query($update_query) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bamagate Delivery App</title>
    <link rel="stylesheet" href="../css/ReturnTeamStyle.css">
    <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

    <script>
        $(document).ready(function () {
            $(".hamburger .hamburger__inner").click(function () {
                $(".wrapper").toggleClass("active");
            });

            // Real-time filtering by ID
            $('#id-filter').on('input', function () {
                filterRecords();
            });

            function filterRecords() {
                var id = $('#id-filter').val();

                $.ajax({
                    url: '',
                    type: 'GET',
                    data: {
                        id: id,
                        ajax: true
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
                                tableBody += '<td>' + record.ShippingCity + '</td>';
                                tableBody += '<td>' + record.ShippingPhone + '</td>';
                                tableBody += '<td>' + record.OutstandingBalance + '</td>';
                                tableBody += '<td>' + record.Date + '</td>';
                                tableBody += '<td>' + record.DeliveryPartner + '</td>';
                                if (record.Return === 'Confirmed Return') {
                                    tableBody += '<td><button class="cancel-return" data-id="' + record.ID + '">Cancel Confirmed Return</button></td>';
                                } else {
                                    tableBody += '<td><button class="confirm-return" data-id="' + record.ID + '">Confirm Return</button></td>';
                                }
                                tableBody += '</tr>';
                            });
                        } else {
                            tableBody = '<tr><td colspan="10">No records found.</td></tr>';
                        }
                        $('.ImportRecordsTable tbody').html(tableBody);
                    }
                });
            }

            // Handle confirm return button click
            $(document).on('click', '.confirm-return', function () {
                var returnId = $(this).data('id');
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        confirm_return: true,
                        return_id: returnId
                    },
                    success: function (response) {
                        alert(response);
                        filterRecords(); // Refresh the table
                    }
                });
            });

            // Handle cancel return button click
            $(document).on('click', '.cancel-return', function () {
                var returnId = $(this).data('id');
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        cancel_return: true,
                        return_id: returnId
                    },
                    success: function (response) {
                        alert(response);
                        filterRecords(); // Refresh the table
                    }
                });
            });

            // Handle "All Records" button click
            $('#all-records-btn').click(function () {
                window.location.href = 'ReturnOrders.php';
            });

            // Initial load of records
            filterRecords();
        });
    </script>

    <style>
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-container div {
            width: 105%;
        }

        input[type="text"] {
            width: 20%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            margin-left: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .ImportRecordsTable {
            max-height: 580px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .ImportRecordsTable table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .ImportRecordsTable th,
        .ImportRecordsTable td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        /* CSS for buttons */
        button {
            padding: 10px 50px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .cancel-return {
            background-color: #dc3545;
        }

        .cancel-return:hover {
            background-color: #c82333;
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

            <!-- Side Bar -->
            <?php include './Components/ReturnTeamSidebar.php'; ?>

            <!-- Content Container -->
            <div class="container">
                <div class="filter-container">
                    <div>
                        <label for="id-filter">Search by ID:</label>
                        <input type="text" id="id-filter" name="id-filter" value="<?php echo $id_filter; ?>">
                        <button id="all-records-btn">All Records</button>
                    </div>
                </div>
                <div class="ImportRecordsTable">
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['ID'] . "</td>";
                                    echo "<td>" . $row['FinancialStatus'] . "</td>";
                                    echo "<td>" . $row['ShippingName'] . "</td>";
                                    echo "<td>" . $row['ShippingAddress1'] . "</td>";
                                    echo "<td>" . $row['ShippingCity'] . "</td>";
                                    echo "<td>" . $row['ShippingPhone'] . "</td>";
                                    echo "<td>" . $row['OutstandingBalance'] . "</td>";
                                    echo "<td>" . $row['Date'] . "</td>";
                                    echo "<td>" . $row['DeliveryPartner'] . "</td>";
                                    if ($row['Return'] === 'Confirmed Return') {
                                        echo '<td><button class="cancel-return" data-id="' . $row['ID'] . '">Cancel Confirmed Return</button></td>';
                                    } else {
                                        echo '<td><button class="confirm-return" data-id="' . $row['ID'] . '">Confirm Return</button></td>';
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10'>No records found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="credit"></footer>
</body>

</html>