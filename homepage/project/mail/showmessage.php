<?php
  session_start();
  if (isset($_GET['view']) && $_GET['view'] == 'sent') {
    $sender = 'sender';
    $receiver = 'sendto';
  } else {
    $sender = 'sendto';
    $receiver = 'receiver';
  }
  if (isset($_GET['messageid']) && $_GET['messageid'] != '') {
    $query = "SELECT u1.username AS Sender, u2.username AS Receiver, m.* 
              FROM mailbox m, users u1, users u2 
              WHERE m.sender_userID = u1.userID 
              AND m.receiver_userID = u2.userID 
              AND m.messageID = " . mysqli_real_escape_string($db, $_GET['messageid']) . " 
              AND (m.sender_userID = " . $_SESSION['userid'] . " 
              OR m.receiver_userID = " . $_SESSION['userid'] . ");";
    if (!$result = $db->query($query)) {
      $displayError = 'Error loading the requested message. Please try again.';

    } else {
      if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $displayHTML = '<form method=POST action="./messengerhome.php?loadpage=compose&&asreply=YES">
                          <table cellpadding="2" cellspacing="1" border="0" width="796">
                            <col width="75"><col width="546">
                            <tr><td>From:</td>
                                <td><input size="100" type="text" name="' . $sender . '" readonly="readonly" value="' . stripslashes($row['Sender']) . '"></td></tr>
                            <tr><td>To:</td>
                                <td><input size="100" type="text" name="' . $receiver . '" readonly="readonly" value="' . stripslashes($row['Receiver']) . '"></td></tr>
                            <tr><td>Sent:</td>
                                <td><input size="100" type="text" name="sentdate" readonly="readonly" value="' . $row['msgTime'] . '"></td></tr>
                            <p>
                            <tr><td>Subject:</td>
                                <td><input size="100" type="text" name="subject" readonly="readonly" value="' . stripslashes($row['subject']) . '"></td></tr>
                            <p>
                            <tr><td valign="top">Message:</td>
                                <td><textarea rows="20" cols="102" name="message" readonly="readonly">' . stripslashes($row['msgText']) . '</textarea></td></tr>
                          </table>
                          <input type="submit" value="Reply">
                        </form>';

        if (isset($_GET['view']) && $_GET['view'] == 'inbox') {
          $query = "UPDATE mailbox SET status = 'Read' WHERE messageID = " . mysqli_real_escape_string($db, $_GET['messageid']) . ";";
          $db->query($query);

        }
      } else {
        $displayError = 'Your requested message could not be located at this time.';

      }
      $result->free();
    }
  } else {
    $displayError = 'System could not locate the message you are trying to access.';
  }
?>
