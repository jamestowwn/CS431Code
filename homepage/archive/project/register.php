<?php
  session_start();
  $invalidName = "";
  $userTaken = "";
  $invalidPass = "";
  $mysql = 0;

  if (isset($_POST['userName'])) { 
    try {
    //validate our post fields
    //first up - full name
      if (!isset($_POST['fullName']) || $_POST['fullName'] == "") { $invalidName = "Please enter your full name."; }

    //second - username
      if (!isset($_POST['userName']) || $_POST['userName'] == "") {
        $userTaken = "Please enter a valid username";

      } else {
        mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
        mysql_select_db("cs431s17");
        $mysql = 1;
 
        $query = "SELECT * ";
        $query .= "FROM users ";
        $query .= "WHERE username = '" . mysql_escape_string($_POST['userName']) . "';";
        $result = mysql_query($query);

        if (mysql_numrows($result) > 0) { $userTaken = "Entered username is taken. Please try a different username."; }
      }
    
    //lastly validate password 
      if (!isset($_POST['passwrd']) || $_POST['passwrd'] == "") { $invalidPass = "You must enter a valid password."; }

      if ($invalidName == "" && $userTaken == "" && $invalidPass == "") {
        $query = "INSERT INTO users (userFullName, username, password) ";
        $query .= "VALUES ('" . mysql_escape_string($_POST['fullName']) . "', ";
        $query .= "'" . mysql_escape_string($_POST['userName']) . "', ";
        $query .= "password('" . mysql_escape_string($_POST['passwrd']) . "'));";

        mysql_query($query);
        
        $_SESSION['username'] = $_POST['userName'];

        $query = "SELECT * ";
        $query .= "FROM users ";
        $query .= " WHERE username = '" . mysql_escape_string($_POST['userName']) . "';";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $_SESSION['userid'] = $row["userID"];
        $_SESSION['userstatus'] = "User";

        //looks good, lets set our session variables and go to the messenger page
        header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=forums");
        die();
      }
      if ($mysql == 1) { mysql_close(); }
      
    } catch (Exception $e) {
      $invalidPass = 'Error accessing system database.';
    }
  } //otherwise, this is just an attempt to register, load the page
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

