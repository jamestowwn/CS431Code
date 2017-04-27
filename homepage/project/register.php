<?php
  session_start();
  $invalidName = '';
  $userTaken = '';
  $invalidPass = '';
  $redirect = 0;

  if (isset($_POST['userName'])) { 
  //validate our post fields
  //first up - full name
    if (!isset($_POST['fullName']) || $_POST['fullName'] == '') { $invalidName = 'Please enter your full name.'; }

  //second - username
    if (!isset($_POST['userName']) || $_POST['userName'] == "") {
      $userTaken = 'Please enter a valid username';

    } else {
      $db = new mysqli('ecsmysql', 'cs431s17', 'ohnaeree', 'cs431s17');
      if ($db->connect_errno) {
        $invalidPass = 'Error accessing system datbase.';

      } else {
        $query = "SELECT * 
                  FROM users 
                  WHERE username = '" . mysqli_real_escape_string($db, $_POST['userName']) . "';";        
        if (!$result = $db->query($query)) {
          $invalidPass = 'Error verifying verifying requested username against existing usernames.';

        } else {
          if ($result->num_rows > 0) { $userTaken = 'Entered username is taken. Please try a different username.'; }

        }
        $result->free();
      }
    
    //lastly validate password 
      if (!isset($_POST['passwrd']) || $_POST['passwrd'] == '') { $invalidPass = 'You must enter a valid password.'; }

      if ($invalidName == '' && $userTaken == '' && $invalidPass == '') {
        $query = "INSERT INTO users (userFullName, username, password) 
                  VALUES ('" . mysqli_real_escape_string($db, $_POST['fullName']) . "', 
                  '" . mysqli_real_escape_string($db, $_POST['userName']) . "', 
                  password('" . mysqli_real_escape_string($db, $_POST['passwrd']) . "'));";
        if ($db->query($query) === FALSE) {
          $invalidPass = 'Failed to create user account, please try again.';

        } else {        
          $query = "SELECT * 
                    FROM users 
                    WHERE userID = LAST_INSERT_ID();";
          if (!$result = $db->query($query)) {
            $invlaidPass = 'Error locating your account after it was created.  Please try to login with your new credentials.';

          } else {
            $row = $result->fetch_assoc();
 
            //looks good, lets set our session variables and go to the messenger page
            $_SESSION['userid'] = $row['userID'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['userstatus'] = $row['status'];

            //looks good, lets set our session variables and go to the messenger page
            $redirect = 1;
            $result->free();
          }
        }
      }
      $db->close();
    }
  } //otherwise, this is just an attempt to register, load the page
  if ($redirect === 1) {
    header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=forums");
    die();
  }
?>
<html>
  <head>
    <title>Forum Registration</title>
  </head>
  <body>
    <h1>Please register with our forum or go to the login page:</h1>
    <hr>

    <form method=POST action="./register.php">

    <b>Please enter your user information and submit:</b>
    <p>
    <table cellpadding="5" cellspacing="0" border="0" width="700">
      <col width="200">
      <col width="200">
      <col width="300">
      <tr>
        <td align="right"><b>Your full name:</b></td>
        <td><input size=20 maxlength=255 type="text" name="fullName" value=""></td>
        <td>
          <font id="invalidName" color="red">
            <?php
              if(isset($invalidName) && !empty($invalidName)) {?>
              <span class="error"><?= $invalidName; ?></span>
            <?php
              } ?>
          </font>
        </td>
      </tr>
      <tr>
        <td align="right"><b>Your desired username:</b></label></td>
        <td><input size=20 maxlength=50 type="text" name="userName" value=""></td>
        <td>
          <font id="userTaken" color="red">
            <?php
              if(isset($userTaken) && !empty($userTaken)) {?>
              <span class="error"><?= $userTaken; ?></span>
            <?php
              } ?>
          </font>
        </td>
      </tr>
      <tr>
        <td align="right"><b>Your password:</b></td>
        <td><input size=20 maxlength=20 type="password" name="passwrd" value=""></td>
        <td>
          <font id="invalidPass" color="red">
            <?php
              if(isset($invalidPass) && !empty($invalidPass)) {?>
              <span class="error"><?= $invalidPass; ?></span>
            <?php
              } ?>
          </font>
        </td>
    </table>
    <hr>
    <input type="submit" value="Register"> <input type="reset" value="Reset Form">
    </form>
    <p>
    <a href="./login.php">Return to login page</a>
  </body>
</html>

