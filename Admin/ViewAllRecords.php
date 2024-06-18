<?php
include "../config.php";

// Fetch total number of records
$total_query = "SELECT COUNT(*) AS total FROM importrecords";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];

// Calculate number of pages needed
$records_per_table = 10;
$total_pages = ceil($total_records / $records_per_table);

// Determine current page
$page = isset($_GET['page']) && $_GET['page'] <= $total_pages ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_table;

// Fetch records for the current page
$query = "SELECT * FROM importrecords ORDER BY ID DESC LIMIT $records_per_table OFFSET $offset";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bamagate Delivery App - Orders</title>

    <script src="https://kit.fontawesome.com/b99e675b6e.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <!-- SideBar-Menu CSS -->
    <link rel="stylesheet" href="../css/styles.css">

    <!-- Other CSS -->
    <link rel="stylesheet" href="../css/Other.css">

    <style>
        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination button {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            margin: 0 4px;
        }

        .pagination button.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .pagination button:hover:not(.active) {
            background-color: #ddd;
        }

        .ImportRecordsTable {
            max-height: 500px;
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
                                <div class="dd_item">Logout</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="main_container">
            <!-- SideBar -->
            <?php include './Components/Sidebar.php' ?>

            <!-- Content Container -->
            <div class="container">
                <?php
                if ($result->num_rows > 0) {
                    $tableNumber = 1;
                    while ($row = $result->fetch_assoc()) {
                        if (($tableNumber - 1) % $records_per_table == 0) {
                            if ($tableNumber > 1) {
                                echo "</tbody></table></div>";
                            }
                            echo "<div class='ImportRecordsTable'>";
                            echo "<table>";
                            echo "<thead><tr><th>ID</th><th>Financial Status</th><th>Shipping Name</th><th>Shipping Address</th><th>Shipping City</th><th>Shipping Phone</th><th>Outstanding Balance</th></tr></thead>";
                            echo "<tbody>";
                        }
                        echo "<tr>";
                        echo "<td>" . $row['ID'] . "</td>";
                        echo "<td>" . $row['FinancialStatus'] . "</td>";
                        echo "<td>" . $row['ShippingName'] . "</td>";
                        echo "<td>" . $row['ShippingAddress1'] . "</td>";
                        echo "<td>" . $row['ShippingCity'] . "</td>";
                        echo "<td>" . $row['ShippingPhone'] . "</td>";
                        echo "<td>" . $row['OutstandingBalance'] . "</td>";
                        echo "</tr>";
                        $tableNumber++;
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<p>No records found.</p>";
                }
                ?>
                <div class="pagination">
                    <?php
                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo "<button onclick='location.href=\"?page=$i\";' " . ($i == $page ? "class='active'" : "") . ">$i</button>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <footer class="credit"> </footer>
</body>

</html>