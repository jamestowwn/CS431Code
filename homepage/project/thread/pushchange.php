<?php
  session_start();

  if (isset($_SESSION['userid']) && $_SESSION['userid'] != '' && isset($loadpage) && $loadpage != '') {

    switch ($loadpage) {
      case 'pushpostchange':
        if (isset($_POST['changepost'])) {
          $query = "UPDATE post SET text = '" . $_POST['updatedpost'] . "'
                   WHERE postid = " . $_POST['postid'];
          $db->query($query);

        } else {
          $displayError = "Post changes can only be made from a post change request form.";

        }
        break;

      case 'pushthreadchange':
        if (isset($_POST['changethread'])) {
          $query = "UPDATE thread SET ";

          if (isset($_POST['threadtitle']) && $_POST['threadtitle'] != '') {
            $query .= "title = '" . mysqli_real_escape_string($db, $_POST['threadtitle']) . "'
                      WHERE threadid = " . $_POST['threadid'];
            $db->query($query);

            $query = "UPDATE post SET text = '" . $_POST['firstpost'] . "'
                     WHERE threadid = " . $_POST['threadid'] . "
                     AND isFirst = 1";
            $db->query($query);

          } else {
            $displayError = "You must provide a title for the thread. Thread update cancelled.";
          }
        } else {
          $displayError = "Thread changes can only be made from a thread change request form.";
        }
        break;

      default:
        break;
    }

  } else {
    $displayHTML .= "The requested page could not be displayed.";
    $displayError .= "You are not authorized to perform the action requested";
  }
?>
