<?php
session_start();
include './config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $username = $_POST['username'];
   $password = $_POST['password'];

   // Prepare and execute query
   $sql = "SELECT * FROM SystemUsers WHERE Name = ?";
   $stmt = mysqli_prepare($conn, $sql);
   mysqli_stmt_bind_param($stmt, 's', $username);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);

   if ($result && mysqli_num_rows($result) == 1) {
      $row = mysqli_fetch_assoc($result);

      // Verify password
      if (password_verify($password, $row['Password'])) {
         $_SESSION['username'] = $username;
         $_SESSION['userType'] = $row['UserType'];
         $_SESSION['userFullName'] = $row['FullName']; // Assuming there's a column 'FullName' in your table

         // Redirect based on user type
         switch ($row['UserType']) {
            case 'Admin':
               header('Location: ./Admin/index.php');
               break;
            case 'Data Management':
               header('Location: ./DataManagementTeam/DataManagementTeamIndex.php');
               break;
            case 'Delivery Team':
               header('Location: ./DeliveryTeam/DeliveryTeamIndex.php');
               break;
            case 'Order Return Team':
               header('Location: ./ReturnTeam/ReturnTeamIndex.php');
               break;
            default:
               $error_message = 'Invalid user type';
         }
         exit;
      } else {
         $error_message = 'Invalid username or password';
      }
   } else {
      $error_message = 'Invalid username or password';
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="author" content="CodeHim">
   <title>Login</title>
   <!-- Style CSS -->
   <link rel="stylesheet" href="./css/LoginStyle.css">
   <style>
      .error-message {
         color: red;
         margin-top: 10px;
         text-align: center;
      }
   </style>
</head>

<body>
   <main class="cd__main">
      <div class="login">
         <p class="AppTitle">Delivery Management System</p>
         <h2 class="active">Login</h2>
         <form action="login.php" method="post">
            <input type="text" class="text" name="username" required>
            <span>username</span>
            <br><br>
            <input type="password" class="text" name="password" required>
            <span>password</span>
            <br><br>
            <?php if (isset($error_message)) { ?>
               <p class="error-message"><?php echo $error_message; ?></p>
            <?php } ?>
            <button type="submit" class="signin">Sign In</button>
            <hr>
            <div class="CompanyLogo">
               <img src="./img/BamagateLogo.png" alt="Bamagate Logo">
            </div>

         </form>
      </div>
   </main>
</body>

</html>