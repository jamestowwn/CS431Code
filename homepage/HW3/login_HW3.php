<?php
/* The following sites used as reference in development:
 * http://stackoverflow.com/questions/7547908/use-php-to-change-a-html-elements-inner-text
 */  
  session_start();
  $error = "";

  if (!isset($_POST['unm'])) { //not a login attempt from login_HW3.php
    if (isset($_SESSION['userid']) && $_SESSION['userid'] != "") { //This is a continuing session, let's see if it's valid
  //    header("Location: http://ecs.fullerton.edu/~cs431s17/HW3/messengerhome.php");
  //    die();
    } //otherwise, we just load default login page
  } else { //This is a login attempt 
    //check the user against the database
    try {
      mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
      mysql_select_db("cs431s17");
   
      $query = "SELECT * ";
      $query .= "FROM users ";
      $query .= "WHERE Username = '" . mysql_escape_string($_POST['unm']) . "' ";
      $query .= "AND Password = password('" . mysql_escape_string($_POST['psw']) . "');";
        
      $result = mysql_query($query);
      if (mysql_numrows($result) == 1) { //We have a valid user
        $row = mysql_fetch_array($result);

        $_SESSION['username'] = $_POST['unm'];
        $_SESSION['userid'] = $row["userID"];
        header("Location: http://ecs.fullerton.edu/~cs431s17/HW3/messengerhome.php?loadpage=inbox");
        die();

      } else {   
        $error = 'User/Pass Invalid Please Try Again';
      }
      mysql_close();
    } catch (Exception $e) {
      $error = 'Error accessing system database.';
    }
  }
?> 
<html>
  <head>
    <title>Messenger Log In</title>
  </head>
  <body>
    <h1>Log in to Messenger</h1>
    <hr>

    <form method=POST action="./login_HW3.php">
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

