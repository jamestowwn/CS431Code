<?php
  session_start();
  $displayHTML = '<h1>Sent Messages</h1>
                  <table cellpadding="2" cellspacing="0" border="1" width="796">
                    <col width="50">
                    <col width="150">
                    <col width="150">
                    <col width="396">
                    <tr><th></th><th>To</th><th>Date Sent</th><th>Subject</th></tr>';

  $query = "SELECT u.username, m.* 
            FROM mailbox m, users u 
            WHERE u.userID = m.receiver_userID 
            AND m.sender_userID = " . $_SESSION['userid'] . " 
            ORDER BY m.messageID DESC;";

  if (!$result = $db->query($query)) {
    $displayError = 'Your mailbox could not be accessed at this time. Please try again later.';

  } else {
    while($row = $result->fetch_assoc()) {
      $displayHTML .= '<tr><td><a href="./messengerhome.php?loadpage=showmessage&&view=sent&&messageid=' . $row['messageID'] . '">View</a></td>
                           <td>' . stripslashes($row['username']) . '</td>
                           <td>' . $row['msgTime'] . '</td>
                           <td>' . stripslashes($row['subject']) . '</td></tr>';
    }
    $displayHTML .= '</table>';
  }
?>
