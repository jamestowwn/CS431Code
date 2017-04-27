<?php
  session_start();
  //We'll build our display first (with pre-existing values if this is a reply/send)
  $displayHTML = '<form method=POST action="./messengerhome.php?loadpage=compose">';
  $displayHTML .= '<table cellpadding="2" cellspacing="1" border="0" width="796">';
  $displayHTML .= '  <col width="75"><col width="546">';
  $displayHTML .= '  <tr><td>From:</td>';
  $displayHTML .= '    <td><input size="100" type="text" name="sender" readonly="readonly" value="' . $_SESSION['username'] . '"></td></tr>';
  $displayHTML .= '  <tr><td>To:</td>';
  $displayHTML .= '    <td><input size="100" type="text" name="sendto" value="';
  if (isset($_POST['sendto'])) { $displayHTML .= $_POST['sendto']; }
  $displayHTML .= '"></td></tr>';
  $displayHTML .= '  <p><tr><td>Subject:</td>';
  $displayHTML .= '    <td><input size="100" type="text" name="subject" value="';
  if (isset($_POST['subject'])) {
    if (isset($_GET['asreply']) && $_GET['asreply'] == 'YES') { $displayHTML .= 'Re: '; }
    $displayHTML .= $_POST['subject'];
  }
  $displayHTML .= '"></td></tr>';
  $displayHTML .= '  <p><tr><td valign="top">Message:</td>';
  $displayHTML .= '    <td><textarea rows="20" cols="102" name="message">';
  if (isset($_POST['message'])) {
    if (isset($_GET['asreply']) && $_GET['asreply'] == "YES") {
      $displayHTML .= '&#13;&#10;&#13;&#10;-------------------------------------------------------------&#13;&#10;';
      $displayHTML .= 'In reply to the following message sent ';
      if (isset($_POST['sentdate'])) { $displayHTML .= 'on ' . $_POST['sentdate'] . ' '; }
      if (isset($_POST['sender'])) { $displayHTML .= 'by ' . $_POST['sender'] . ' '; }
      if (isset($_POST['receiver'])) { $displayHTML .= 'to ' . $_POST['receiver'] . ' '; }
      $displayHTML .= '&#13;&#10;-------------------------------------------------------------&#13;&#10;';
    }
    $displayHTML .= $_POST['message'];
  }
  $displayHTML .= '</textarea></td></tr>';
  $displayHTML .= '</table>';
  $displayHTML .= '<input type="submit" name="sendbutton" value="Send Message"></form>';


  if (isset($_POST['sendbutton'])) {
    try {
      mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
      mysql_select_db("cs431s17");

      $query = "SELECT * ";
      $query .= "FROM users ";
      $query .= "WHERE username = '" . mysql_escape_string($_POST['sendto']) . "';";
      $result = mysql_query($query);

      if (mysql_numrows($result) == 1) {
        $query = "INSERT INTO mailbox (subject, msgTime, msgText, sender_userID, receiver_userID, status) ";
        $query .= "VALUES ('" . mysql_escape_string($_POST['subject']) . "', NOW(), '" . mysql_escape_string($_POST['message']) . "', ";
        $query .= $_SESSION['userid'] . ", " . mysql_escape_string(mysql_result($result, 0, "userID")) . ", 'New');";

        $result = mysql_query($query);

        header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php");
        die();

      } else {
        $displayError = 'The user you were trying to send to was not found.';
      }
    } catch (Exception $e) {
    }
  }
?>
