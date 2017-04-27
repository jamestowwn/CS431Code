<?php
  session_start();
  $displayHTML = '<h1>Sent Messages</h1>';
  $displayHTML .= '<table cellpadding="2" cellspacing="0" border="1" width="796">';
  $displayHTML .= '  <col width="50">';
  $displayHTML .= '  <col width="150">';
  $displayHTML .= '  <col width="150">';
  $displayHTML .= '  <col width="396">';
  $displayHTML .= '  <tr><th></th><th>To</th><th>Date Sent</th><th>Subject</th></tr>';

  try {
    mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
    mysql_select_db("cs431s17");

    $query = "SELECT u.username, m.* ";
    $query .= "FROM mailbox m, users u ";
    $query .= "WHERE u.userID = m.receiver_userID ";
    $query .= "AND m.sender_userID = " . $_SESSION['userid'] . " ";
    $query .= "ORDER BY m.messageID DESC;";

    $result = mysql_query($query);
    for ($i = 0; $i < mysql_numrows($result); $i++) {
      $displayHTML .= '<tr><td><a href="./messengerhome.php?loadpage=showmessage&&view=sent&&messageid=' . mysql_result($result, $i, "messageID") . '">View</a></td>';
      $displayHTML .= '<td>' . mysql_result($result, $i, "username") . '</td>';
      $displayHTML .= '<td>' . mysql_result($result, $i, "msgTime") . '</td>';
      $displayHTML .= '<td>' . mysql_result($result, $i, "subject") . '</td></tr>';
    }
    $displayHTML .= '</table>';

    mysql_close();
  } catch (Exception $e) {
  }
?>
