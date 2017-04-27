<?php
  session_start();

  if (!isset($_SESSION['userstatus']) || $_SESSION['userstatus'] == '') { $_SESSION['userstatus'] = 'Guest'; }
  if (isset($_GET['threadid']) && $_GET['threadid'] != '' && $_GET['threadid'] != 0) { 

    $query = "SELECT t.*, f.forumName, f.picture, u.username, p.postID, p.text, f.moderator_userID 
              FROM thread t, forum f, users u, users u2, post p 
              WHERE t.forumID = f.forumID 
              AND t.start_userID = u.userID 
              AND f.moderator_userID = u2.userID 
              AND t.forumID = p.forumID 
              AND t.threadID = p.threadID 
              AND p.isFirst = 1 
              AND (";
    if (isset($_SESSION['userid'])) { $query .= "f.moderator_userID = " . $_SESSION['userid'] . " OR "; }
    $query .= "t.status <> 'Removed') 
              AND t.threadID = " . mysqli_real_escape_string($db, $_GET['threadid']) . " 
              LIMIT 1;";

    if (!$result = $db->query($query)) {
      $displayError = 'The requested thread cannot be viewed at this time. Please try again.';

    } else {
      if ($result->num_rows === 1) {
        $rowThread = $result->fetch_assoc();
        $result->free();
        $banned = 0;


        if ($_SESSION['userstatus'] != 'Guest') {
          $query = "SELECT *
                    FROM ban b
                    WHERE expiration > NOW()
                    AND forumID = " . $rowThread['forumID'] . "
                    AND userID = " . $_SESSION['userid'];
          if ((!$result = $db->query($query) || $result->num_rows > 0 || $_SESSION['userstatus'] == 'Banned') && $_SESSION['userstatus'] != 'Admin') { $banned = 1; }
        }
        $displayHTML = '<h3><img style="max-width: 20px; max-height: 20px;" src="data:image/jpeg;base64,' . base64_encode($rowThread['picture']) . '"/>
                          <a href="./messengerhome.php?loadpage=forums&&forumid=' . $rowThread['forumID'] . '"> ' .
                          stripslashes($rowThread['forumName']) . ' Forum</a><br>
                          <font size="3px">(Moderator: ' . stripslashes($rowThread['username']) . ')</font></h3>
                        <h1>' . stripslashes($rowThread['title']) . '</h1>';

        if ($banned == 0 && ($_SESSION['userstatus'] == 'Admin' || $_SESSION['userid'] == $rowThread['moderator_userID'] || $_SESSION['userid'] == $rowThread['start_userID'])) {
          $displayHTML .= '<div align="right"><a href="./messengerhome.php?loadpage=editthread&&forumid=' . $rowThread['forumID'] . '&&threadid=' . $rowThread['threadID'] . '">Edit Thread</a></div>';
        }
        $displayHTML .= '<textarea rows="7" cols="115" readonly="readonly">' . stripslashes($rowThread['text']) . '</textarea><br><br>';    

        if ($banned === 1) {
          $displayHTML .= '<font color="red">You are currently banned from posting or editing in this thread</font>';

        } else {
          $displayHTML .= '<a href="./messengerhome.php?loadpage=createpost&&forumid=' . $rowThread['forumID'] . '&&threadid=' . $rowThread['threadID'] . '">Create new post</a>';
        }

        $query = "SELECT p.*, u.username 
                  FROM post p, users u 
                  WHERE p.poster_userID = u.userID 
                  AND p.forumID = " . $rowThread['forumID'] . " 
                  AND p.threadID = " . $rowThread['threadID'] . " 
                  AND p.isFirst = 0 
                  ORDER BY p.postID ";
        if (!isset($_GET['sort']) || $_GET['sort'] != "ASC") { $query .= " DESC"; }

        if (!$result = $db->query($query)) {
          $displayError = 'Posts for this thread could not be loaded at this time. Please try again.';

        } else {
          $displayHTML .= '<table cellpadding="1" cellspacing="0" border="1" width = "796">
                             <col width="150">
                             <col width="596">';
          while($rowPost = $result->fetch_assoc()) {
            $displayHTML .= '<tr><td style="padding-left: 8px;">' . stripslashes($rowPost['username']) . '<br>' . $rowPost['addedDate'];
            if ((isset($_SESSION['userid']) && $_SESSION['userid'] == $rowThread['moderator_userID'] && $_SESSION['userid'] != $rowPost['poster_userID']) || $_SESSION['userstatus'] == 'Admin') {
              $displayHTML .= '<br><a href="./messengerhome.php?loadpage=banuser&&forumid=' . $rowThread['forumID'] . '">BAN USER</a>';
            }
            $displayHTML .=  '</td>
                              <td style="padding-left: 8px;">';
            if ($banned == 0) {
              if ((isset($_SESSION['userid']) && ($_SESSION['userid'] == $rowPost['poster_userID'] || $_SESSION['userid'] == $rowThread['moderator_userID'])) || $_SESSION['userstatus'] == 'Admin') {
                $displayHTML .= '<div align="right" style="padding-right: 8px;"><a href="./messengerhome.php?loadpage=editpost&&forumid=' . $rowThread['forumID'] . '&&threadid=' . $rowThread['threadID'] . '&&postid=' . $rowPost['postID'] . '">Edit</a></div><br>';
              }
            }
            $displayHTML .= stripslashes($rowPost['text']) . '</td></tr>';
          }        
          $displayHTML .= '</table>';
          $result->free();

        }
      } else {
        $displayError = "The requested thread could not be found.";

      }
    }
  } else {  
    $displayError = "The requested page could not be loaded.";
  }
?>
