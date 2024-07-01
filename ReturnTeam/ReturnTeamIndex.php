<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

include "../config.php";

// Function to fetch total number of records
function fetchTotalRecords()
{
    global $conn;
    $total_query = "SELECT COUNT(*) AS total FROM importrecords";
    $total_result = $conn->query($total_query);
    $total_row = $total_result->fetch_assoc();
    return $total_row['total'];
}

// Fetch total number of records
$total_records = fetchTotalRecords();

// Calculate number of pages needed
$records_per_table = 10;
$total_pages = ceil($total_records / $records_per_table);

// Determine current page
$page = isset($_GET['page']) && $_GET['page'] <= $total_pages ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_table;

// Filters
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$id_filter = isset($_GET['id']) ? $_GET['id'] : '';

// Fetch records for the current page or all records if 'view_all' is set
$query = "SELECT * FROM importrecords WHERE 1";

// Apply date filter if set
if ($date_filter) {
    $query .= " AND DATE(Date) = '$date_filter'";
}

// Apply ID filter if set
if ($id_filter) {
    $query .= " AND ID LIKE '%$id_filter%'";
}

// Check if date filter is applied and fetch all records if 'view_all' is set
if ($date_filter || $id_filter || isset($_GET['view_all'])) {
    $query .= " ORDER BY ID DESC";
} else {
    $query .= " ORDER BY ID DESC LIMIT $records_per_table OFFSET $offset";
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bamagate Delivery App</title>

    <link rel="stylesheet" href="../css/ReturnTeamStyle.css">

    <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>


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

    <style>
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-container div {
            width: 48%;
        }

        input[type="date"],
        input[type="text"],
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        button {
            background-color: purple;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #44b555;
        }

        .pagination-container {
            margin-top: 20px;
            display: flex;
            justify-content: flex-start;
            /* Align items to the left */
            overflow-x: auto;
            /* Enable horizontal scrolling */
            white-space: nowrap;
            /* Prevent wrapping */
            padding: 10px 0;
            /* Add some padding for aesthetics */
        }

        .pagination-container::-webkit-scrollbar {
            height: 6px;
            /* Custom scrollbar height */
        }

        .pagination-container::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 10px;
        }

        .pagination button {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            color: black;
            padding: 6px 4px;
            text-decoration: none;
            transition: background-color 0.3s;
            border-radius: 4px;
            display: inline-block;
            margin: 0 2px;
            /* Adjust margin for spacing */
        }

        .pagination button.active {
            background-color: purple;
            color: white;
            border: 1px solid navy;
        }

        .pagination button:hover:not(.active) {
            background-color: #ddd;
        }



        .ImportRecordsTable {
            max-height: 450px;
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
    </style>

    <script>
        $(document).ready(function () {
            $(".hamburger .hamburger__inner").click(function () {
                $(".wrapper").toggleClass("active");
            });

            // Real-time filtering by ID and date
            $('#id-filter, #date-filter').on('input change', function () {
                filterRecords();
            });

            // View All Records
            $('#view-all').on('click', function () {
                window.location.href = 'ReturnTeamIndex.php';
            });

            function filterRecords() {
                var date = $('#date-filter').val();
                var id = $('#id-filter').val();

                $.ajax({
                    url: '',
                    type: 'GET',
                    data: {
                        date: date,
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
                                tableBody += '<td>' + record.Packing + '</td>';
                                tableBody += '<td>' + record.Fulfilled + '</td>';
                                tableBody += '<td>' + record.Call + '</td>';
                                tableBody += '<td>' + record.Dispatch + '</td>';
                                tableBody += '<td>' + record.ItemRemarks + '</td>';
                                tableBody += '</tr>';
                            });
                        } else {
                            tableBody = '<tr><td colspan="8">No records found.</td></tr>';
                        }
                        $('.ImportRecordsTable tbody').html(tableBody);
                    }
                });
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

            <!-- Side Bar -->
            <?php include './Components/ReturnTeamSidebar.php'; ?>


            <!-- Content Container -->
            <div class="container">
                <div class="filter-container">
                    <div>
                        <label for="date-filter">Filter by Date:</label>
                        <input type="date" id="date-filter" name="date-filter" value="<?php echo $date_filter; ?>">
                    </div>
                    <div>
                        <label for="id-filter">Search by ID:</label>
                        <input type="text" id="id-filter" name="id-filter" value="<?php echo $id_filter; ?>">
                    </div>
                </div>
                <div>
                    <button id="view-all">View All Records</button>
                </div>
                <?php
                if ($result->num_rows > 0) {
                    echo "<div class='ImportRecordsTable'>";
                    echo "<table>";
                    echo "<thead><tr><th>ID</th><th>Financial Status</th><th>Shipping Name</th><th>Shipping Address</th><th>Shipping City</th><th>Shipping Phone</th><th>Outstanding Balance</th><th>Date</th><th>DeliveryPartner</th><th>Packing</th><th>Fulfilled</th><th>Call</th><th>Dispatch</th><th>ItemRemarks</th></tr></thead>";
                    echo "<tbody>";
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
                        echo "<td>" . $row['PackingStatus'] . "</td>";
                        echo "<td>" . $row['FulfilledStatus'] . "</td>";
                        echo "<td>" . $row['CallStatus'] . "</td>";
                        echo "<td>" . $row['DispatchStatus'] . "</td>";
                        echo "<td>" . $row['ItemRemarks'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<p>No records found.</p>";
                }
                ?>
                <div class="pagination-container">
                    <div class="pagination">
                        <?php
                        if (!$date_filter && !isset($_GET['view_all'])) {
                            for ($i = 1; $i <= $total_pages; $i++) {
                                echo "<button onclick='location.href=\"?page=$i\";' " . ($i == $page ? "class='active'" : "") . ">$i</button>";
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <footer class="credit"> </footer>
</body>

</html>