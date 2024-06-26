<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  header('Location: ../login.php');
  exit;
}

include "../config.php";

// Get the timestamp from the query parameter
$timestamp = isset($_GET['timestamp']) ? intval($_GET['timestamp']) : 0;

// Fetch records from the database that were added during the current upload
$query = "SELECT * FROM importrecords WHERE Timestamp = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $timestamp);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Bamagate Delivery App - Orders</title>

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

  <script>
    $(document).ready(function () {
      $(".hamburger .hamburger__inner").click(function () {
        $(".wrapper").toggleClass("active");
      });

      $(".top_navbar .fas").click(function () {
        $(".profile_dd").toggleClass("active");
      });

      // Check for success, duplicates, and error parameters in URL
      const urlParams = new URLSearchParams(window.location.search);
      const success = urlParams.get('success');
      if (success) {
        swal({
          title: 'New records Added Successfully',
          text: 'Added To Database...',
          icon: 'success',
          timer: 2000,
          buttons: false
        });
      }

      const duplicates = urlParams.get('duplicates');
      if (duplicates) {
        swal({
          title: 'Same Record Found',
          text: `Total Rejected Records: ${duplicates}`,
          icon: 'warning'
        });
      }

      const error = urlParams.get('error');
      if (error === 'invalid_file') {
        swal({
          title: 'Invalid File !!!',
          text: 'Please upload a valid file.',
          icon: 'error'
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

      <!-- SideBar -->
      <?php include './Components/Sidebar.php' ?>

      <div class="container">
        <div class="ImportForm">
          <form method="post" action="importData.php" enctype="multipart/form-data">
            <input type="file" name="file">
            <input type="submit" name="submit" value="Import">
          </form>
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
                <th>DeliveryPartner</th>
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
                  echo "<td>" . $row['DeliveryPartner'] . "</td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='7'>No records found</td></tr>";
              }
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