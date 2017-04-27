<?php
  session_start();
  //We'll build our display first (with pre-existing values if this is a reply/send)
  $displayHTML = '<form method=POST action="./messengerhome.php?loadpage=compose">
                    <table cellpadding="2" cellspacing="1" border="0" width="796">
                      <col width="75"><col width="546">
                      <tr><td>From:</td>
                          <td><input size="100" type="text" name="sender" readonly="readonly" value="' . $_SESSION['username'] . '"></td></tr>
                      <tr><td>To:</td>
                          <td><input size="100" type="text" name="sendto" value="';
  if (isset($_POST['sendto'])) { $displayHTML .= $_POST['sendto']; }
  $displayHTML .= '"></td></tr>
                      <p>
                      <tr><td>Subject:</td>
                          <td><input size="100" type="text" name="subject" value="';
  if (isset($_POST['subject'])) {
    if (isset($_GET['asreply']) && $_GET['asreply'] == 'YES') { $displayHTML .= 'Re: '; }
    $displayHTML .= $_POST['subject'];
  }
  $displayHTML .= '"></td></tr>
                      <p>
                      <tr><td valign="top">Message:</td>
                          <td><textarea rows="20" cols="102" name="message">';
  if (isset($_POST['message'])) {
    if (isset($_GET['asreply']) && $_GET['asreply'] == "YES") {
      $displayHTML .= '&#13;&#10;&#13;&#10;-------------------------------------------------------------&#13;&#10;
                       In reply to the following message sent ';
      if (isset($_POST['sentdate'])) { $displayHTML .= 'on ' . $_POST['sentdate'] . ' '; }
      if (isset($_POST['sender'])) { $displayHTML .= 'by ' . $_POST['sender'] . ' '; }
      if (isset($_POST['receiver'])) { $displayHTML .= 'to ' . $_POST['receiver'] . ' '; }
      $displayHTML .= '&#13;&#10;-------------------------------------------------------------&#13;&#10;';
    }
    $displayHTML .= $_POST['message'];
  }
  $displayHTML .= '</textarea></td></tr>
                    </table>
                    <input type="submit" name="sendbutton" value="Send Message"></form>';

  if (isset($_POST['sendbutton'])) {
    $query = "SELECT * 
              FROM users 
              WHERE username = '" . mysqli_real_escape_string($db, $_POST['sendto']) . "';";
    if (!$result = $db->query($query)) {
      $displayError = 'Error accessing the user list to send your message.';

    } else { 
      if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $query = "INSERT INTO mailbox (subject, msgTime, msgText, sender_userID, receiver_userID, status) 
                  VALUES ('" . mysqli_real_escape_string($db, $_POST['subject']) . "', NOW(), '" 
                  . mysqli_real_escape_string($db, $_POST['message']) . "', "
                  . $_SESSION['userid'] . ", " . mysqli_real_escape_string($db, $row['userID']) . ", 'New');";
        if ($db->query($query) === FALSE) {
          $displayError = 'There was an error delivering your message. Please try again. ' . $query;

        } else {
          header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=inbox");
          die();

        }
      } else {
        $displayError = 'The user you were trying to send to was not found.';
      }
      $result->free();
    }
  }
?>
