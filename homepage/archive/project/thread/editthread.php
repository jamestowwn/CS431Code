<?php
  session_start(); 

  if (isset($_SESSION['userid']) && $_SESSION['userid'] != "" && isset($_GET['forumid']) && $_GET['forumid'] != "" && isset($_GET['threadid']) && $_GET['threadid'] != "" && isset($loadpage) && $loadpage != "") {

    switch ($loadpage) {
      case "createpost":
        $displayHTML = '<font size="6" color="blue"><b>Create Post</b></font><br><br>';
        $displayHTML .= '<form method="post" action="./messengerhome.php?loadpage=createnewpost&&forumid=' . $_GET['forumid'] . '&&threadid=' . $_GET['threadid'] . '" enctype="multipart/form-data">';
        $displayHTML .= 'Your Post<br><textarea rows="7" cols="75" name="threadpost"></textarea><br><br>';
        $displayHTML .= '<input type="hidden" value="' . $_GET['forumid'] . '" name="forumid">';
        $displayHTML .= '<input type="hidden" value="' . $_GET['threadid'] . '" name="threadid">';
        $displayHTML .= '<input type="submit" value="Submit New Post" name="createpost">';
        $displayHTML .= '</form>';
        break;

      case "createthread":
        $displayHTML = '<font size="6" color="blue"><b>Create a New Thread</b></font><br><br>';
        $displayHTML .= '<form method="post" action="./messengerhome.php?loadpage=createnewthread&&forumid=' . $_GET['forumid'] . '&&threadid=0" enctype="multipart/form-data">';
        $displayHTML .= 'Thread Title<br><input type="text" size="73" name="threadtitle" id="threadtitle" value=><br><br>';
        $displayHTML .= 'Topic Post<br><textarea rows="7" cols="75" name="firstpost"></textarea><br><br>';
        $displayHTML .= '<input type="hidden" value="' . $_GET['forumid'] . ' name="forumid">';
        $displayHTML .= '<input type="hidden" value="0" name="threadid">';
        $displayHTML .= '<input type="submit" value="Submit New Thread" name="createthread">';
        $displayHTML .= '</form>';
        break;

      case "createnewpost":
        if (isset($_POST['createpost'])) {
          $query = "INSERT INTO post (forumID, threadID, addedDate, text, poster_userID, isFirst) VALUES (";
          $query .= mysql_escape_string($_GET['forumid']) . ", " . mysql_escape_string($_GET['threadid']) . ", NOW(), ";
          if (isset($_POST['threadpost']) && $_POST['threadpost'] != '') {
            $query .= "'" . mysql_escape_string($_POST['threadpost']) . "', ";
          } else {
            $query .= "null, ";
          }
          $query .= $_SESSION['userid'] . ", 0);";

          try {
            mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
            mysql_select_db("cs431s17");
            mysql_query($query);
            mysql_close();

            header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=threads&&threadid=" . $_GET['threadid']);
            die();
          } catch (Exception $e) {
            $displayError = "There was an error creating the forum, please try again.";
          }
        } else {
          $displayError = "New post requests can only be made from a post request form within a thread.";
        }
        break;

      case "createnewthread":
        if (isset($_POST['createthread'])) {
          $query = "INSERT INTO thread (forumID, title, addedDate, status, start_userID) VALUES (" . mysql_escape_string($_GET['forumid']) . ", '";

          if (isset($_POST['threadtitle']) && $_POST['threadtitle'] != '') {
            try {
              $query .= mysql_escape_string($_POST['threadtitle']) . "', NOW(), 'Open', " . $_SESSION['userid'] . ");";

              mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
              mysql_select_db("cs431s17");
              mysql_query($query);

              $query = "SELECT LAST_INSERT_ID() AS threadID";
              $result = mysql_query($query);

              $threadid = 0;
              if (mysql_numrows($result) == 1) {
                $threadid = mysql_result($result, 0, "threadID");
                $query = "INSERT INTO post (forumID, threadID, addedDate, text, poster_userID, isFirst) VALUES (" . mysql_escape_string($_GET['forumid']) . ", ";
                $query .= $threadid . ", NOW(), "; 
                if (isset($_POST['firstpost']) && $_POST['firstpost'] != '') {
                  $query .= "'" . mysql_escape_string($_POST['firstpost']) . "', ";
                } else {
                  $query .= "null, ";
                }
                $query .= $_SESSION['userid'] . ", 1);";
                mysql_query($query);
              }
              mysql_close();
 
              header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=threads&&threadid=" . $threadid);
              die();
             } catch (Exception $e) {
              $displayError = "There was an error creating the forum, please try again.";
            }
          } else {
            $displayError = "You must provide a title for your thread. Thread creation cancelled.";
          }
        } else {
          $displayError = "New thread requests can only be made from a thread request form within a forum.";
        }        
        break;

      case "editpost":

      case "editthread":
        try {          
          mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
          mysql_select_db("cs431s17");

          $query = "SELECT t.*, u.username, f.moderator_userID FROM thread t, forum f, users u ";
          $query = "WHERE t.forumID = f.forumID AND t.start_userID = u.userID AND t.threadID = " . $_GET['threadid'] . "; ";

          $result = mysql_query($query) or die(mysql_error());
          
          if (mysql_numrows($result) == 1) { //This user is authorized to edit this forum
            $displayHTML = '<font size="6" color="blue"><b>Edit Forum Page</b></font><br><br>';
            $displayHTML .= '<form method="post" action="./messengerhome.php?loadpage=pushforumchange&&forumid=' . $_GET['forumid'] . '" enctype="multipart/form-data">';
            $displayHTML .= 'Forum Image<br><input type="file" name="filetoupload" id="filetoupload"><br>';
            if (mysql_result($result, 0, "picture") != NULL) {
              $displayHTML .= '<img src="data:image/jpeg;base64,' . base64_encode(mysql_result($result, 0, "picture")) . '"/>';
            }
            $displayHTML .= '<br>';
            $displayHTML .= 'Forum Name<br><input type="text" size="73" name="forumname" id="forumname" value="' . stripslashes(mysql_result($result, 0, "forumName")) . '"><br><br>';
            $displayHTML .= 'Forum Description<br><textarea rows="7" cols="75" name="forumdescription">' . stripslashes(mysql_result($result, 0, "description")) . '</textarea><br><br>';
            $displayHTML .= '<input type="hidden" value="' . $_GET['forumid'] . '" name="forumid">';

            if ($_SESSION['userstatus'] == 'Admin') {
              $displayHTML .= '<select name="forumstatus"><option ';
              if (mysql_result($result, 0, "status") == "Review") { $displayHTML .= 'selected="selected" '; }
              $displayHTML .= 'value="Review">Review</option><option ';
              if (mysql_result($result, 0, "status") == "Live") { $displayHTML .= 'selected="selected" '; }
              $displayHTML .= 'value="Live">Live</option></select><br><br>';
              
              $displayHTML .= 'Moderator<br><input type="text" size="30" name="moderator" id="moderator" value="' . stripslashes(mysql_result($result, 0, "username")) . '"><br><br>';
            }
            $displayHTML .= '<input type="submit" value="Commit Forum Changes" name="changeforum">';
            $displayHTML .= '</form>';

          } else { //unauthorized user
            $displayError = "Unauthorized user";
            $displayHTML = "<tr><td>You are not currently authorized to make changes to the requested forum.</td></tr>";

          }          
          mysql_close();
        } catch (Exception $e) {
          $displayError = "There was an error pulling the requested forum information";
        }   
        break;
   
      case "pushpostchange":
        break;

      case "pushthreadchange":
        if (isset($_POST['changeforum'])) {
          $query = "UPDATE forum SET ";

          if (isset($_POST['forumname']) && $_POST['forumname'] != '') {
            try {
              $query .= "forumName = '" . mysql_escape_string($_POST['forumname']) . "'";

              if (isset($_POST['forumdescription']) && $_POST['forumdescription'] != '') {
                $query .= ", description = '" . mysql_escape_string($_POST['forumdescription']) . "'";
              }

              if (getimagesize($_FILES["filetoupload"]["tmp_name"]) !== false) {
                $tmpName = $_FILES["filetoupload"]["tmp_name"];
                $fp = fopen($tmpName, 'r');
                $data = fread($fp, filesize($tmpName));
                $data = addslashes($data);
                fclose($fp);
                $query .= ", picture = '" . $data . "'";
              }

              mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
              mysql_select_db("cs431s17");
              
              if ($_SESSION['userstatus'] == 'Admin') {
                $query .= ", status = '" . $_POST['forumstatus'] . "'";
                $result = mysql_query("SELECT userID FROM users WHERE username = '" . mysql_escape_string($_POST['moderator']) . "';") or die (mysql_error());
                if (mysql_numrows($result) == 1) {
                  $query .= ", moderator_userID = " . mysql_result($result, 0, "userID");
                } else {
                  $displayError .= 'Submitted moderator user could not be found, forum was assigned no moderator';
                  $query .= ", moderator_userID = 0";
                }
              }
              $query .= " WHERE forumID = " . $_POST['forumid'] . "; ";
              mysql_query($query);
 
              mysql_close();
            } catch (Exception $e) {
              $displayError = "There was an error updating the forum, please try again.";
            }
          } else {
            $displayError = "You must provide a name for this forum. Forum update cancelled.";
          }
        } else {
          $displayError = "Forum changes can only be made from a forum change request form.";
        }
        break;

      default:
        break;
    }
  } else {
    $displayHTML .= "The requested page could not be displayed.";
    $displayError .= "You are not authorized to perform the action requested";
  }
?>
