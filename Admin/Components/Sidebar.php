<?php
// Get the current page file name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  <style>
    .submenu {
      margin-left: 20px;
    }

    .arrow {
      margin-left: 50px;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div class="sidebar__inner">
      <div class="profile">
        <div class="img">
          <img src="../img/pic.png" alt="profile_pic">
        </div>
        <div class="profile_info">
          <p>Welcome</p>
          <p class="profile_name">User's Name</p>
        </div>
      </div>
      <ul>
        <li>
          <a href="index.php" <?php if ($current_page === 'index.php')
            echo 'class="active"'; ?>>
            <span class="icon"><i class="fas fa-dice-d6"></i></span>
            <span class="title">Dashboard</span>
          </a>
        </li>
        <li class="has-submenu">
          <a href="#" <?php if ($current_page === 'ImportOrders.php' || $current_page === 'ViewAllRecords.php')
            echo 'class="active"'; ?>>
            <span class="icon"><i class="fab fa-delicious"></i></span>
            <span class="title">Orders</span>
            <span class="arrow"><i class="fas fa-chevron-down"></i></span>
          </a>
          <ul class="submenu">
            <li><a href="ImportOrders.php">Import Orders</a></li>
            <li><a href="ViewAllRecords.php">View All Records</a></li>
          </ul>
        </li>
        <li>
          <a href="#">
            <span class="icon"><i class="fab fa-elementor"></i></span>
            <span class="title">Add Orders</span>
          </a>
        </li>
        <li>
          <a href="#">
            <span class="icon"><i class="fas fa-chart-pie"></i></span>
            <span class="title">Returned</span>
          </a>
        </li>
        <li>
          <a href="#">
            <span class="icon"><i class="fas fa-border-all"></i></span>
            <span class="title">Reports</span>
          </a>
        </li>
      </ul>
    </div>
  </div>

</body>

</html>

<script>
  // JavaScript for toggling submenu
  document.addEventListener('DOMContentLoaded', function () {
    const hasSubmenuItems = document.querySelectorAll('.has-submenu');

    hasSubmenuItems.forEach(item => {
      const menuItem = item.querySelector('a');
      const submenu = item.querySelector('.submenu');

      menuItem.addEventListener('click', function (e) {
        e.preventDefault();
        submenu.classList.toggle('show');
      });
    });
  });
</script>

<style>
  .submenu {
    display: none;
  }

  .submenu.show {
    display: block;
  }
</style>