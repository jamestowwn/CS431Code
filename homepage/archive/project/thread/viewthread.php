<?php
  session_start();

  if (!isset($_SESSION['userstatus']) || $_SESSION['userstatus'] == '') { $_SESSION['userstatus'] = "Guest"; }
  if (isset($_GET['threadid']) && $_GET['threadid'] != "" && $_GET['threadid'] != 0) { //The user wants to see a specific thread
    try {
      mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
      mysql_select_db("cs431s17");

      $query = "SELECT t.*, f.forumName, f.picture, u.username, p.postID, p.text, f.moderator_userID ";
      $query .= "FROM thread t, forum f, users u, users u2, post p ";
      $query .= "WHERE t.forumID = f.forumID AND t.start_userID = u.userID AND f.moderator_userID = u2.userID ";
      $query .= "AND t.forumID = p.forumID AND t.threadID = p.threadID AND p.isFirst = 1 ";
      $query .= "AND (";
      if (isset($_SESSION['userid'])) { $query .= "f.moderator_userID = " . $_SESSION['userid'] . " OR "; }
      $query .= "t.status <> 'Removed') ";
      $query .= "AND t.threadID = " . mysql_escape_string($_GET['threadid']) . " ";
      $query .= "LIMIT 1;";
      $resultThread = mysql_query($query);

      if (mysql_numrows($resultThread) == 1) {
        $banned = 0;
        $displayHTML = '<h3>';
        if (mysql_result($resultThread, 0, "picture") != NULL) {
          $displayHTML .= '<img style="max-width: 20px; max-height: 20px;" src="data:image/jpeg;base64,' . base64_encode(mysql_result($resultThread, 0, "picture")) . '"/>';
        }
        $displayHTML .= '<a href="./messengerhome.php?loadpage=forums&&forumid=' . mysql_result($resultThread, 0, "forumID") . '">';
        $displayHTML .= stripslashes(mysql_result($resultThread, 0, "forumName")) . ' Forum</a><br>';
        $displayHTML .= '<font size="3px">(Moderator: ' . stripslashes(mysql_result($resultThread, 0, "username")) . ')</font></h3>';
        $displayHTML .= '<h1>' . stripslashes(mysql_result($resultThread, 0, "title")) . '</h1>';
        $displayHTML .= '<textarea rows="7" cols="110" readonly="readonly">' . stripslashes(mysql_result($resultThread, 0, "text")) . '</textarea><br><br>';
        
        if ($_SESSION['userstatus'] != "Guest") {
          $query = "SELECT * FROM ban b WHERE expiration > NOW() AND forumID = " . mysql_result($resultThread, 0, "forumID") . " AND userID = " . $_SESSION['userid'] . ";";
          $result = mysql_query($query);
          
          if ((mysql_numrows($result) > 0 || $_SESSION['userstatus'] == "Banned") && $_SESSION['userstatus'] != 'Admin') {
            $banned = 1;
            $displayHTML .= '<font color="red">You are currently banned from posting or editing in this thread</font>';
          } else {
            $displayHTML .= '<a href="./messengerhome.php?loadpage=createpost&&forumid=' . mysql_result($resultThread, 0, "forumID") . '&&threadid=' . mysql_result($resultThread, 0, "threadID") . '">Create new post</a>';
          }
        }

        $query = "SELECT p.*, u.username FROM post p, users u "; 
        $query .= "WHERE p.poster_userID = u.userID ";
        $query .= "AND p.forumID = " . mysql_result($resultThread, 0, "forumID") . " ";
        $query .= "AND p.threadID = " . mysql_result($resultThread, 0, "threadID") . " ";
        $query .= "AND p.isFirst = 0 ";
        $query .= "ORDER BY p.postID ";
        if (!isset($_GET['sort']) || $_GET['sort'] != "ASC") { $query .= " DESC"; }
        $query .= ";";
        $resultPost = mysql_query($query);

        $displayHTML .= '<table cellpadding="1" cellspacing="0" border="1" width = "796">'; 
        $displayHTML .= '  <col width="150">';
        $displayHTML .= '  <col width="596">';
        for ($i = 0; $i < mysql_numrows($resultPost); $i++) {
          $displayHTML .= '<tr>';
          $displayHTML .= '<td style="padding-left: 8px;">' . stripslashes(mysql_result($resultPost, $i, "username")) . '<br>' . mysql_result($resultPost, $i, "addedDate");
          if ((isset($_SESSION['userid']) && $_SESSION['userid'] == mysql_result($resultThread, 0, "moderator_userID")) || $_SESSION['userstatus'] == 'Admin') {
            $displayHTML .= '<br><a href="./messengerhome.php?loadpage=banuser&&forumid=' . mysql_result($resultThread, 0, "forumID") . '">BAN USER</a>';
          }
          $displayHTML .=  '</td>';
          $displayHTML .= '<td style="padding-left: 8px;">';
          if ($banned == 0) {
            if ((isset($_SESSION['userid']) && ($_SESSION['userid'] == mysql_result($resultPost, $i, "poster_userID") || $_SESSION['userid'] == mysql_result($resultThread, 0, "moderator_userID"))) || $_SESSION['userstatus'] == 'Admin') {
              $displayHTML .= '<div align="right" style="padding-right: 8px;"><a href="./messengerhome.php?loadpage=editthread&&forumid=" . mysql_result($resultThread, 0, "forumID") . "&&threadid=" . mysql_result($resultThread, 0, "threadID") . ">Edit</a></div><br>';
            }
          }
          $displayHTML .= stripslashes(mysql_result($resultPost, $i, "text")) . '</td></tr>';
        }

        $displayHTML .= '</table>';
      } else {
        $displayError = "The requested thread could not be found.";
      }
    } catch (Exception $e) {
      $displayError = "There was an error displaying your requested thread. Please return to 'View Forums' and try again.";
    }
  } else { //The user wants to view all threads 
    $displayError = "The requested page could not be loaded.";
  }
?>
