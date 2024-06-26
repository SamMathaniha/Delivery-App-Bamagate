<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  header('Location: ../login.php');
  exit;
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
        <?php include './Components/Sidebar.php'; ?>
        <header class="intro">
          <h1>Bamagate Delivery App</h1>
        </header>
      </div>
    </div>
  </div>
  <footer class="credit"></footer>
</body>

</html>