<?php
include "../config.php";

// Add record
if (isset($_POST['add'])) {
    $cityName = $_POST['cityName'];
    $districtName = $_POST['districtName'];
    $deliveryPartner = $_POST['deliveryPartner'];

    // Check if the city name already exists
    $checkSql = "SELECT * FROM shippingcity WHERE CityName='$cityName'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('This City is already Available');</script>";
    } else {
        $sql = "INSERT INTO shippingcity (CityName, DistrictName, DeliveryPartner) VALUES ('$cityName', '$districtName', '$deliveryPartner')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('New record created successfully');</script>";

        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Update record
if (isset($_POST['update'])) {
    $cityName = $_POST['cityName'];
    $districtName = $_POST['districtName'];
    $deliveryPartner = $_POST['deliveryPartner'];

    $sql = "UPDATE shippingcity SET DistrictName='$districtName', DeliveryPartner='$deliveryPartner' WHERE CityName='$cityName'";
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Delete record
if (isset($_POST['delete'])) {
    $cityName = $_POST['cityName'];

    $sql = "DELETE FROM shippingcity WHERE CityName='$cityName'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Record deleted successfully');</script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Search record
$search = "";
if (isset($_POST['search'])) {
    $search = $_POST['search'];
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
        /* Search container styles */
        .search-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-container input[type=text] {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 300px;
            margin-right: 10px;
        }

        .search-container button {
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #0056b3;
        }

        .search-container button[type=button] {
            background-color: #dc3545;
        }

        /* Add/Update form styles */
        form {
            margin-bottom: 20px;
        }

        form input[type=text],
        form select {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        form button[type=submit],
        form button[type=button] {
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            margin-top: -10px;
        }

        form button[type=submit]:hover,
        form button[type=button]:hover {
            background-color: #0056b3;
        }



        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
            /* Ensure table layout remains fixed */
        }


        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #f2f2f2;
        }

        table td {
            text-align: left;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main_container {
                flex-direction: column;
            }

            .search-container input[type=text] {
                width: 100%;
                margin-right: 0;
            }
        }

        .add-update-form {
            display: flex;
            align-items: center;
            /* Align items vertically center */
        }

        .add-update-form input[type=text],
        .add-update-form select {
            flex: 1;
            /* Let inputs and select fill available space */
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
            /* Space between inputs */
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .add-update-form select {
            width: 50%;
            /* Adjust width of select */
        }

        .add-update-form button[type=submit],
        .add-update-form button[type=button] {
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .add-update-form button[type=submit]:hover,
        .add-update-form button[type=button]:hover {
            background-color: #0056b3;
        }


        /* Delete button and Edit button alignment */
        .content table td {
            text-align: center;
            /* Center align all content within table cells */
        }

        /* Delete button style */
        .content table td form button[type=submit] {
            margin-top: 10px;
            background-color: #dc3545;
            /* Red color */
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
        }

        .content table td form button[type=submit]:hover {
            background-color: #c82333;
            /* Darker red on hover */
        }

        /* Edit button style */
        .content table td button {
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            background-color: #28a745;
            /* Green color */
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }

        .content table td button:hover {
            background-color: #218838;
            /* Darker green on hover */
        }
    </style>
    <script>
        $(document).ready(function () {
            $(".hamburger .hamburger__inner").click(function () {
                $(".wrapper").toggleClass("active")
            });
            $(".top_navbar .fas").click(function () {
                $(".profile_dd").toggleClass("active");
            });
        });

        // Submit form on Enter key press in search input
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('input[name="search"]').addEventListener('keydown', function (event) {
                if (event.keyCode === 13) { // 13 is the Enter key
                    event.preventDefault();
                    this.closest('form').submit();
                }
            });
        });

        // Function to fill form fields for editing
        function fillForm(data) {
            document.getElementById('cityName').value = data.CityName;
            document.getElementById('districtName').value = data.DistrictName;
            document.getElementById('deliveryPartner').value = data.DeliveryPartner;
        }

        // Function to clear search input
        function clearSearch() {
            document.querySelector('input[name="search"]').value = '';
            // Optionally, you can submit the form here if needed
            // document.querySelector('form').submit();
        }
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
            <div class="container">
                <!-- Side Bar -->
                <?php include './Components/Sidebar.php' ?>

                <!-- Main -->
                <div class="content">

                    <!-- Search Form -->
                    <form method="post" action="">
                        <div class="search-container">
                            <input type="text" name="search" placeholder="Search by City Name"
                                value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit">Search</button>
                            <button type="button" onclick="clearSearch()">Clear</button>
                        </div>
                    </form>

                    <!-- Add/Update Form -->
                    <form method="post" action="">
                        <div class="add-update-form">
                            <input type="text" name="cityName" id="cityName" placeholder="City Name" required>
                            <select name="districtName" id="districtName" required>
                                <option value="" disabled selected>Select District</option>
                                <!-- Options for District -->
                                <?php
                                $districts = ["Ampara", "Anuradhapura", "Badulla", "Batticaloa", "Colombo", "Galle", "Gampaha", "Hambantota", "Jaffna", "Kalutara", "Kandy", "Kegalle", "Kilinochchi", "Kurunegala", "Mannar", "Matale", "Matara", "Moneragala", "Mullaitivu", "Nuwara Eliya", "Polonnaruwa", "Puttalam", "Ratnapura", "Trincomalee", "Vavuniya"];
                                foreach ($districts as $district) {
                                    echo "<option value='$district'>$district</option>";
                                }
                                ?>
                            </select>
                            <select name="deliveryPartner" id="deliveryPartner" required>
                                <option value="" disabled selected>Select Delivery Partner</option>
                                <option value="Courier">Courier</option>
                                <option value="Direct">Direct</option>
                            </select>
                            <button type="submit" name="add">Add</button>
                            <button type="submit" name="update">Update</button>
                        </div>
                    </form>

                    <table>
                        <thead>
                            <tr>
                                <th>City Name</th>
                                <th>District</th>
                                <th>Delivery Partner</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch records from shippingcity table
                            $sql = "SELECT * FROM shippingcity";
                            if ($search != "") {
                                $sql .= " WHERE CityName LIKE '%$search%'";
                            }
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                // Output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . $row["CityName"] . "</td>
                                            <td>" . $row["DistrictName"] . "</td>
                                            <td>" . $row["DeliveryPartner"] . "</td>
                                            <td>
                                                <form method='post' action='' style='display:inline-block;'>
                                                    <input type='hidden' name='cityName' value='" . $row["CityName"] . "'>
                                                    <button type='submit' name='delete'>Delete</button>
                                                </form>
                                                <button onclick='fillForm(" . json_encode($row) . ")'>Edit</button>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No records found</td></tr>";
                            }

                            // Close connection
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <footer class="credit"> </footer>
</body>

</html>