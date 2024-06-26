<?php
include '../config.php';

session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// Function to check if NIC already exists
function isNICExists($nic)
{
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM SystemUsers WHERE NIC='$nic'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

// Insert user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $phoneNo = $_POST['phoneNo'];
    $nic = $_POST['nic'];
    $userType = $_POST['userType'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate phone number
    if (!isValidPhoneNumber($phoneNo)) {
        $phone_error = "Phone number should be 10 digits";
    } elseif ($password !== $confirm_password) {
        $password_error = "Passwords do not match";
    } elseif (isNICExists($nic)) {
        $nic_error = "User with this NIC already exists";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $sql = "INSERT INTO SystemUsers (Name, PhoneNo, NIC, UserType, Password) VALUES ('$name', '$phoneNo', '$nic', '$userType', '$hashed_password')";

        if (mysqli_query($conn, $sql)) {
            header("Location: RegisterSystemUsers.php");
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}

// Delete user (your existing delete logic)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM SystemUsers WHERE ID='$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: RegisterSystemUsers.php");
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

function isValidPhoneNumber($phoneNo)
{
    // Check if phone number is numeric and 10 digits long
    return is_numeric($phoneNo) && strlen($phoneNo) === 10;
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

    <!-- Other CSS -->
    <link rel="stylesheet" href="../css/Other.css">

    <!--  CSS -->
    <link rel="stylesheet" href="../css/AdminRegisterStyle.css">

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

                <!-- Registration Form -->
                <div class="form-container">
                    <h2>Register User</h2>
                    <form id="registrationForm" action="RegisterSystemUsers.php" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="phoneNo">Phone Number:</label>
                                <input type="number" id="phoneNo" name="phoneNo" required>
                                <span id="phoneNo_error" class="error-message"></span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nic">NIC:</label>
                                <input type="text" id="nic" name="nic" required>
                                <?php if (isset($nic_error)) { ?>
                                    <span class="error-message"><?php echo $nic_error; ?></span>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="userType">User Type:</label>
                                <select id="userType" name="userType" required>
                                    <option value="" disabled selected>Select User Type</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Data Management">Data Management Team</option>
                                    <option value="Delivery Team">Delivery Team</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password:</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <span id="password_error" class="error-message"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="submit" id="submitBtn" name="register" value="Register" class="form-submit"
                                disabled>
                        </div>
                    </form>
                </div>

                <!-- User Details Table -->
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone No</th>
                            <th>NIC</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch user details from the database
                        $result = mysqli_query($conn, "SELECT * FROM SystemUsers");
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td>" . $row['Name'] . "</td>";
                            echo "<td>" . $row['PhoneNo'] . "</td>";
                            echo "<td>" . $row['NIC'] . "</td>";
                            echo "<td>" . $row['UserType'] . "</td>";
                            echo "<td>
                                    <form action='RegisterSystemUsers.php' method='post' style='display:inline;'>
                                        <input type='hidden' name='id' value='" . $row['ID'] . "'>
                                        <input type='hidden' name='name' value='" . $row['Name'] . "'>
                                        <input type='hidden' name='nic' value='" . $row['NIC'] . "'>
                                        <input type='hidden' name='userType' value='" . $row['UserType'] . "'>
                                        <input type='hidden' name='password' value='" . $row['Password'] . "'>
                                    </form>
                                    <form action='RegisterSystemUsers.php' method='post' style='display:inline;'>
                                        <input type='hidden' name='id' value='" . $row['ID'] . "'>
                                        <button type='submit' name='delete' class='delete-btn'>Delete</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <footer class="credit"></footer>

    <script>
        $(document).ready(function () {
            // Password match validation
            $('#confirm_password').on('keyup', function () {
                var password = $('#password').val();
                var confirm_password = $(this).val();

                if (password !== confirm_password) {
                    $('#password_error').text('Passwords do not match');
                    $('#submitBtn').removeClass('ready').prop('disabled', true);
                } else {
                    $('#password_error').text('');
                    enableSubmitButton();
                }
            });

            // Phone number validation
            $('#phoneNo').on('keyup', function () {
                var phoneNo = $(this).val();

                if (phoneNo.length !== 10 || !$.isNumeric(phoneNo)) {
                    $('#phoneNo_error').text('Should be 10 digits');
                    $('#submitBtn').removeClass('ready').prop('disabled', true);
                } else {
                    $('#phoneNo_error').text('');
                    enableSubmitButton();
                }
            });

            // Enable submit button if all fields are valid
            function enableSubmitButton() {
                if ($('#name').val() && $('#phoneNo').val() && $('#nic').val() && $('#userType').val() && $('#password').val() && $('#confirm_password').val()) {
                    $('#submitBtn').addClass('ready').prop('disabled', false);
                }
            }
        });
    </script>

</body>

</html>