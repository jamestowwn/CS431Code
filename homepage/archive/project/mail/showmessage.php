<?php
  session_start();
  if (isset($_GET['view']) && $_GET['view'] == "sent") {
    $sender = "sender";
    $receiver = "sendto";
  } else {
    $sender = "sendto";
    $receiver = "receiver";
  }
  if (isset($_GET['messageid']) && $_GET['messageid'] != "") {
    try {
      mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
      mysql_select_db("cs431s17");

      $query = "SELECT u1.username AS Sender, u2.username AS Receiver, m.* ";
      $query .= "FROM mailbox m, users u1, users u2 ";
      $query .= "WHERE m.sender_userID = u1.userID AND m.receiver_userID = u2.userID ";
      $query .= "AND m.messageID = " . mysql_escape_string($_GET['messageid']) . " ";
      $query .= "AND (m.sender_userID = " . $_SESSION['userid'] . " ";
      $query .= "OR m.receiver_userID = " . $_SESSION['userid'] . ");";
      $result = mysql_query($query);

      if (mysql_numrows($result) > 0) {
        $displayHTML = '<form method=POST action="./messengerhome.php?loadpage=compose&&asreply=YES">';
        $displayHTML .= '<table cellpadding="2" cellspacing="1" border="0" width="796">';
        $displayHTML .= '  <col width="75"><col width="546">';
        $displayHTML .= '  <tr><td>From:</td>';
        $displayHTML .= '    <td><input size="100" type="text" name="' . $sender . '" readonly="readonly" value="' . mysql_result($result, 0, "sender") . '"></td></tr>';
        $displayHTML .= '  <tr><td>To:</td>';
        $displayHTML .= '    <td><input size="100" type="text" name="' . $receiver . '" readonly="readonly" value="' . mysql_result($result, 0, "receiver") . '"></td></tr>';
        $displayHTML .= '  <tr><td>Sent:</td>';
        $displayHTML .= '    <td><input size="100" type="text" name="sentdate" readonly="readonly" value="' . mysql_result($result, 0, "msgTime") . '"></td></tr>';
        $displayHTML .= '  <p><tr><td>Subject:</td>';
        $displayHTML .= '    <td><input size="100" type="text" name="subject" readonly="readonly" value="' . mysql_result($result, 0, "subject") . '"></td></tr>';
        $displayHTML .= '  <p><tr><td valign="top">Message:</td>';
        $displayHTML .= '    <td><textarea rows="20" cols="102" name="message" readonly="readonly">' . mysql_result($result, 0, "msgText") . '</textarea></td></tr>';
        $displayHTML .= '</table>';
        $displayHTML .= '<input type="submit" value="Reply"></form>';

        if (isset($_GET['view']) && $_GET['view'] == "inbox") {
          $query = "UPDATE mailbox SET status = 'Read' WHERE messageID = " . mysql_escape_string($_GET['messageid']) . ";";
          $result = mysql_query($query);
        }
      } else {
        $displayError = "Your requested message could not be located at this time.";
      }
      mysql_close();
    } catch (Exception $e) {
      $displayError = "There was an error accessing your requested message.  Please try again.";
    }
  } else {
    $displayError = "System could not locate the message you are trying to access.";
  }
?>
