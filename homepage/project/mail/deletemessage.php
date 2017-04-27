<?php
  session_start();
  if (isset($_GET['messageid']) && $_GET['messageid'] != '') {
    $query = "UPDATE mailbox SET status = 'Deleted' 
              WHERE messageID = " . mysqli_real_escape_string($db, $_GET['messageid']) . " 
              AND receiver_userID = " . $_SESSION['userid'] . ";";
    if ($db->query($query) === FALSE) {
      $displayError = 'There was an error deleting your message. Contact site administrator for assisstance.';
    }

    header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=inbox");
    die();

  }
?>
