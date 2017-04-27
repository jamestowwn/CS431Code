<?php
  session_start();
  $displayHTML = '<h1>Inbox</h1>';
  $displayHTML .= '<table cellpadding="2" cellspacing="0" border="1" width="796">';
  $displayHTML .= '  <col width="50">';
  $displayHTML .= '  <col width="150">';
  $displayHTML .= '  <col width="150">';
  $displayHTML .= '  <col width="396">';
  $displayHTML .= '  <col width="50">';
  $displayHTML .= '  <tr><th></th><th>From</th><th>Date Sent</th><th>Subject</th></tr>';

  try {
    mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
    mysql_select_db("cs431s17");

    $query = "SELECT u.username, m.* ";
    $query .= "FROM mailbox m, users u ";
    $query .= "WHERE u.userID = m.sender_userID ";
    $query .= "AND m.receiver_userID = " . $_SESSION['userid'] . " ";
    $query .= "AND m.status <> 'Deleted' ";
    $query .= "ORDER BY m.MessageID DESC;";

    $result = mysql_query($query);
    for ($i = 0; $i < mysql_numrows($result); $i++) {
      $displayHTML .= '<tr';
      if (mysql_result($result, $i, "status") == 'New') { $displayHTML .= ' style="font-weight:bold"'; }

      $displayHTML .= '><td><a href="./messengerhome.php?loadpage=showmessage&&view=inbox&&messageid=' . mysql_result($result, $i, "messageID") . '">View</a></td>';
      $displayHTML .= '<td>' . mysql_result($result, $i, "Username") . '</td>';
      $displayHTML .= '<td>' . mysql_result($result, $i, "MsgTime") . '</td>';
      $displayHTML .= '<td>' . mysql_result($result, $i, "Subject") . '</td>';
      $displayHTML .= '<td><a href="./messengerhome.php?loadpage=delmessage&&messageid=' . mysql_result($result, $i, "messageID") . '">DEL</a></td></tr>';
    }

    $displayHTML .= '</table>';

    mysql_close();
  } catch (Exception $e) {
  }
?>
