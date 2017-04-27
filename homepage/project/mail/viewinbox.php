<?php
  session_start();
  $displayHTML = '<h1>Inbox</h1>
                  <table cellpadding="2" cellspacing="0" border="1" width="796">
                    <col width="50">
                    <col width="150">
                    <col width="150">
                    <col width="396">
                    <col width="50">
                    <tr><th></th><th>From</th><th>Date Sent</th><th>Subject</th></tr>';

    $query = "SELECT u.username, m.* 
              FROM mailbox m, users u 
              WHERE u.userID = m.sender_userID 
              AND m.receiver_userID = " . $_SESSION['userid'] . " 
              AND m.status <> 'Deleted' 
              ORDER BY m.MessageID DESC;";
    if (!$result = $db->query($query)) {
      $displayError = 'Error contacting server for your inbox info. Please contact site administrator.';

    } else {
      while($row = $result->fetch_assoc()) {          
        $displayHTML .= '<tr';
        if ($row['status'] == 'New') { $displayHTML .= ' style="font-weight:bold"'; }

        $displayHTML .= '><td><a href="./messengerhome.php?loadpage=showmessage&&view=inbox&&messageid=' . $row['messageID'] . '">View</a></td>
                          <td>' . stripslashes($row['username']) . '</td>
                          <td>' . $row['msgTime'] . '</td>
                          <td>' . stripslashes($row['subject']) . '</td>
                          <td><a href="./messengerhome.php?loadpage=delmessage&&messageid=' . $row['messageID'] . '">DEL</a></td></tr>';
      }
      $result->free();
    }
    $displayHTML .= '</table>';
?>
