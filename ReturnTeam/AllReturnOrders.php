<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

include "../config.php";

// Initialize variables for date filtering
$date_filter_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_filter_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

// Query to fetch confirmed return orders with optional date filtering
$query = "SELECT * FROM importrecords WHERE `Return` = 'Confirmed Return'";

// Add date filtering if dates are provided
if (!empty($date_filter_start) && !empty($date_filter_end)) {
    // Assuming your Date column is in a format that MySQL DATE function can handle
    $query .= " AND DATE(Date) BETWEEN '$date_filter_start' AND '$date_filter_end'";
}

// Order by Date in descending order
$query .= " ORDER BY Date DESC";

$result = $conn->query($query);
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




    <style>
        .ImportRecordsTable {
            max-height: 580px;
            overflow-y: auto;
            margin-top: 25px;
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

        /* Button styles */
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Date picker styles */
        input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 180px;
            box-sizing: border-box;
        }

        /* Adjust for the span between date inputs */
        form span {
            margin: 0 8px;
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

                <!-- Date filter form -->
                <form method="get" action="AllReturnOrders.php">
                    <label for="date_start">Filter by Date:</label>
                    <input type="date" id="date_start" name="date_start" value="<?php echo $date_filter_start; ?>">
                    <span>to</span>
                    <input type="date" id="date_end" name="date_end" value="<?php echo $date_filter_end; ?>">
                    <button type="submit">Apply Filter</button>
                </form>
                <?php
                if ($result->num_rows > 0) {
                    echo "<div class='ImportRecordsTable'>";
                    echo "<table>";
                    echo "<thead><tr><th>ID</th><th>Financial Status</th><th>Shipping Name</th><th>Shipping Address</th><th>Shipping City</th><th>Shipping Phone</th><th>Outstanding Balance</th><th>Date</th><th>Delivery Partner</th></tr></thead>";
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
                        echo "</tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<p>No confirmed return orders found.</p>";
                }
                ?>
            </div>
        </div>
    </div>
    <footer class="credit"> </footer>
</body>

</html>