<?php
  session_start();
  if (isset($_GET['messageid']) && $_GET['messageid'] != "") {
    try {
      mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
      mysql_select_db("cs431s17");

      $query = "UPDATE mailbox ";
      $query .= "SET status = 'Deleted' ";
      $query .= "WHERE messageID = " . mysql_escape_string($_GET['messageid']) . " ";
      $query .= "AND receiver_userID = " . $_SESSION['userid'] . ";";
      $result = mysql_query($query);

      mysql_close();
      header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=inbox");
      die();

    } catch (Exception $e) {
    }
  }
?>
