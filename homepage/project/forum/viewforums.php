<?php
  session_start();

  if (!isset($_SESSION['userstatus']) || $_SESSION['userstatus'] == '') { $_SESSION['userstatus'] = 'Guest'; }
  if (isset($_GET['forumid']) && $_GET['forumid'] != '' && $_GET['forumid'] != 0) { 
  //The user wants to see a specific forum
    $query = "SELECT f.*, u.username 
              FROM forum f, users u 
              WHERE f.moderator_userID = u.userID 
              AND f.forumID = " . mysqli_real_escape_string($db, $_GET['forumid']) . " 
              AND f.status = 'Live';";

    if (!$result = $db->query($query)) {
      $displayError = 'Could not contact the server at this time. If error persists, please contact site administrator.';

    } else {
      if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $displayHTML = '<h1>
                        <img style="max-width: 100px; max-height: 100px;" src="data:image/jpeg;base64,' . base64_encode($row['picture']) . '"/>' .
                        stripslashes($row['forumName']) . ' Forum</h1>
                        <h4>Moderator: ' . stripslashes($row['username']) . '</h4><br>';
        $result->free();
        
        if ($_SESSION['userstatus'] != 'Guest') {
          $query = "SELECT * 
                    FROM ban b 
                    WHERE expiration > NOW() 
                    AND forumID = " . mysqli_real_escape_string($db, $_GET['forumid']) . " 
                    AND userID = " . $_SESSION['userid'] . ";";
          if (!$result = $db->query($query) || $result->num_rows > 0 || $_SESSION['userstatus'] == 'Banned') {
            $displayHTML .= '<font color="red">You are currently banned from posting in this forum</font>';

          } else {
            $displayHTML .= '<a href="./messengerhome.php?loadpage=createthread&&forumid=' . $_GET['forumid'] . '&&threadid=0">Create new thread</a>';
          }          
        }

        $displayHTML .= '<table cellpadding="1" cellspacing="0" border="1" width = "796">
                           <col width="500">
                           <col width="246">
                           <col width="50">
                           <tr><th>Thread Title</th><th>Posted By/Date</th><th>Status</th>';

        $query = "SELECT t.*, u.username FROM thread t, users u, forum f 
                  WHERE t.start_userID = u.userID 
                  AND t.forumID = f.forumID 
                  AND t.forumID = " . mysqli_real_escape_string($db, $_GET['forumid']) . " 
                  AND ( ";
        if (isset($_SESSION['userid'])) { $query .= "f.moderator_userID = " . $_SESSION['userid'] . " OR "; }
        $query .= "t.status <> 'Removed') 
                  ORDER BY t.addedDate DESC;";
         
        if (!$result = $db->query($query)) {
          $displayError = "Could not load forum threads. Please try again.";

        } else {
          while ($row = $result->fetch_assoc()) {
            $displayHTML .= '<tr><td><a href="./messengerhome.php?loadpage=threads&&threadid=' . $row['threadID'] . '">' . stripslashes($row['title']) . '</a></td>
                                 <td style="padding-left: 10px;">' . stripslashes($row['username']) . '<br>' . $row['addedDate'] . '</td>
                                 <td>X</td></tr>';
          }
          $result->free();
        }
        $displayHTML .= '</table>';

      } else {
        $displayError = "The requested forum could not be found.";
      }
    }

  } else { 
  //The user wants to view all forums
    $displayHTML = '<h1>Forums</h1>
                    <table cellpadding="2" cellspacing="0" border="0" width = "796">
                      <col width="100">
                      <col width="621">
                      <col width="75">';
      
    $query = "SELECT f.*, u.username 
              FROM forum f, users u 
              WHERE f.moderator_userID = u.userID ";
    if ($_SESSION['userstatus'] != 'Admin') { $query .= "AND f.status = 'Live' "; }
    $query .= "ORDER BY f.forumName";
    
    if (!$result =  $db->query($query)) {
      $displayError = "The forums list could not be loaded at this time.  Please try again.";

    } else {
      while($row = $result->fetch_assoc()) {
        $displayHTML .= '<tr>
                           <td><img style="max-width: 100px; max-height: 100px;" src="data:image/jpeg;base64,' . base64_encode($row['picture']) . '"/></td>
                           <td><a style="font-size: 50px;" href="./messengerhome.php?loadpage=forums&&forumid=' . $row['forumID'] . '">' . stripslashes($row['forumName']). '</a>
                               <div> (moderator: ' . stripslashes($row['username']) . ')</div><br>
                               <p>' . stripslashes($row['description']) . '</p></td>';
        if ($_SESSION['userstatus'] == 'Admin' || (isset($_SESSION['userid']) && $row['moderator_userID'] == $_SESSION['userid'])) {
          $displayHTML .= '<td><a href="./messengerhome.php?loadpage=editforum&&forumid=' . $row['forumID'] .  '">EDIT';
          if ($_SESSION['userstatus'] == 'Admin' && $row['status'] == 'Review') { $displayHTML .= '<br>(Under Review)'; }
          $displayHTML .= '</a></td>';
        }
        $displayHTML .= '</tr>';
      }
      $result->free();
    }
  }
?>
