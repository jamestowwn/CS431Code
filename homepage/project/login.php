<?php
  session_start();
  $error = "";

  $redirect = 0;
  if (!isset($_POST['unm'])) { 
    //not a login attempt from login.php

  //otherwise, we just load default login page
  } else { //This is a login attempt 
    $db = new mysqli('ecsmysql', 'cs431s17', 'ohnaeree', 'cs431s17');
    if ($db->connect_errno) {
      $error = 'Error accessing system database.';

    } else {
    //check the user against the database
      $query = "SELECT * 
                FROM users 
                WHERE username = '" . mysqli_real_escape_string($db, $_POST['unm']) . "' 
                AND password = password('" . mysqli_real_escape_string($db, $_POST['psw']) . "');";        

      if (!$result = $db->query($query)) {
        $error = 'Error verifying user existence.';

      } else {
        if ($result->num_rows === 1) { //We have a valid user          
          $row = $result->fetch_assoc();
  
          $_SESSION['username'] = $_POST['unm'];
          $_SESSION['userid'] = $row['userID'];
          $_SESSION['userstatus'] = $row['status'];

          $redirect = 1;

        } else {   
          $error = 'User/Pass Invalid Please Try Again';

        }
      }
      $result->free();
    }
    $db->close(); 
  }
  if ($redirect == 1) {
    header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php");
    die();
  }
?> 
<html>
  <head>
    <title>CS 431 Message Forum</title>
  </head>
  <body>
    <h1>Log in to CS431 Message Forum</h1>
    <hr>

    <form method=POST action="./login.php">
    <b>Please enter your username and password</b>
    <p>
    <div>
      <label for="username"><b>USERNAME: </b></label>
      <input size="20" name="unm" value>
      <p>
      <label for="psw"><b>PASSWORD: </b></label>
      <input size="20" type="password" name="psw" value>
      <p>
      <font id="invalidUser" color="red">
        <?php
          if(isset($error) && !empty($error)) {?>
          <span class="error"><?= $error; ?></span>
        <?php
          } ?>
      </font>
      <hr>
      <input type="submit" value="Log In">
      <p>
      <a href="./register.php">Register for the site</a>
    </div>
    </form>
  </body>
</html>

