<?php
  session_start(); 

  if (isset($_SESSION['userid']) && $_SESSION['userid'] != '' && isset($_GET['forumid']) && $_GET['forumid'] != '' && isset($_GET['threadid']) && $_GET['threadid'] != '' && isset($loadpage) && $loadpage != '') {

    switch ($loadpage) {
      case 'createpost':
        $displayHTML = '<font size="6" color="blue"><b>Create Post</b></font><br><br>
                        <form method="post" action="./messengerhome.php?loadpage=createnewpost&&forumid=' . $_GET['forumid'] . '&&threadid=' . $_GET['threadid'] . '" enctype="multipart/form-data">
                          Your Post<br><textarea rows="7" cols="75" name="threadpost"></textarea><br><br>
                          <input type="hidden" value="' . $_GET['forumid'] . '" name="forumid">
                          <input type="hidden" value="' . $_GET['threadid'] . '" name="threadid">
                          <input type="submit" value="Submit New Post" name="createpost">
                        </form>';
        break;

      case 'createthread':
        $displayHTML = '<font size="6" color="blue"><b>Create a New Thread</b></font><br><br>
                        <form method="post" action="./messengerhome.php?loadpage=createnewthread&&forumid=' . $_GET['forumid'] . '&&threadid=0" enctype="multipart/form-data">
                          Thread Title<br><input type="text" size="73" name="threadtitle" id="threadtitle" value=><br><br>
                          Topic Post<br><textarea rows="7" cols="75" name="firstpost"></textarea><br><br>
                          <input type="hidden" value="' . $_GET['forumid'] . ' name="forumid">
                          <input type="hidden" value="0" name="threadid">
                          <input type="submit" value="Submit New Thread" name="createthread">
                        </form>';
        break;

      case 'createnewpost':
        if (isset($_POST['createpost'])) {
          $query = "INSERT INTO post (forumID, threadID, addedDate, text, poster_userID, isFirst) VALUES (" .
                    mysqli_real_escape_string($db, $_GET['forumid']) . ", " . mysqli_real_escape_string($db, $_GET['threadid']) . ", NOW(), ";
          if (isset($_POST['threadpost']) && $_POST['threadpost'] != '') {
            $query .= "'" . mysqli_real_escape_string($db, $_POST['threadpost']) . "', ";

          } else {
            $query .= "NULL, ";

          }
          $query .= $_SESSION['userid'] . ", 0);";
          $db->query($query);

          header('Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=threads&&threadid=' . $_GET['threadid']);
          die();

        } else {
          $displayError = 'New post requests can only be made from a post request form within a thread.';

        }
        break;

      case 'createnewthread':
        if (isset($_POST['createthread'])) {
          $query = "INSERT INTO thread (forumID, title, addedDate, status, start_userID) VALUES (" . 
                   mysqli_real_escape_string($db, $_GET['forumid']) . ", '";
          if (isset($_POST['threadtitle']) && $_POST['threadtitle'] != '') {
            $query .= mysql_escape_string($_POST['threadtitle']) . "', NOW(), 'Open', " . $_SESSION['userid'] . ");";
            if ($db->query($query) === TRUE) {
              $query = "SELECT LAST_INSERT_ID() AS threadID";
              if (!$result = $db->query($query)) {
                $displayError = "Your thread could not be created at this time.";

              } else {
                $threadid = 0;
                if ($result->num_rows === 1) {
                  $row = $result->fetch_assoc();
                  $threadid = $row['threadID'];
                  $query = "INSERT INTO post (forumID, threadID, addedDate, text, poster_userID, isFirst) VALUES (" . mysqli_real_escape_string($db, $_GET['forumid']) . ", " .
                           $threadid . ", NOW(), "; 
                  if (isset($_POST['firstpost']) && $_POST['firstpost'] != '') {
                    $query .= "'" . mysqli_real_escape_string($_POST['firstpost']) . "', ";
                  } else {
                    $query .= "null, ";
                  }
                  $query .= $_SESSION['userid'] . ", 1);";
                  $db->query($query);
                }
 
                header("Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=threads&&threadid=" . $threadid);
                die();

              }
            } else {
              $displayError = "Your thread could not be created at this time.";
            }
          } else {
            $displayError = "You must provide a title for your thread. Thread creation cancelled.";
          }
        } else {
          $displayError = "New thread requests can only be made from a thread request form within a forum.";
        }        
        break;

      case 'editpost':
        if (isset($_GET['postid']) && $_GET['postid'] != '') {
          $query = "SELECT p.*, u.username, f.moderator_userID
                    FROM post p
                      INNER JOIN thread t ON p.threadID = t.threadID
                      INNER JOIN users u ON p.poster_userID = u.userID
                      INNER JOIN forum f ON p.forumID = f.forumID
                      LEFT OUTER JOIN ban b ON p.forumID = b.forumID
                        AND b.userID = " . $_SESSION['userid'] . "
                        AND b.expiration > NOW()
                    WHERE p.postID = " . $_GET['postid'] . "
                    AND b.userID IS NULL ";
          if ($_SESSION['userstatus'] != 'Admin') { $query .= "AND (p.poster_userID = " . $_SESSION['userid'] . " OR f.moderator_userID = " . $_SESSION['userid'] . ") "; }
          $query .= "LIMIT 1";

          if (!$result = $db->query($query)) {
            $displayError = 'The post you wish to edit could not be found at this time.';

          } else {
            if ($result->num_rows === 1) {
              $row = $result->fetch_assoc();
              $displayHTML = '<font size="6" color="blue"><b>Edit Post Page</b></font><br><br>
                              <form method="post" action="./messengerhome.php?loadpage=pushpostchange" enctype="multipart/form-data">
                                Post<br>
                                <textarea rows="7" cols="75" name="updatedpost">' . stripslashes($row['text']) . '</textarea><br><br>
                                <input type="hidden" value="' . $_GET['forumid'] . '" name="forumid">
                                <input type="hidden" value="' . $_GET['threadid'] . '" name="threadid">
                                <input type="hidden" value="' . $_GET['postid'] . '" name="postid">
                                <input type="submit" value="Commit Post Changes" name="changepost">
                              </form>';

            } else { //unauthorized user
              $displayError = "Unauthorized user";
              $displayHTML = "<tr><td>You are not currently authorized to make changes to the requested post.</td></tr>";
  
            }
          }
        } else {
          $displayError = "Requested post to edit could not be found. Please return to the forums";
        }
        break;

      case 'editthread':
        $query = "SELECT t.*, u.username, f.moderator_userID, p.text
                  FROM thread t 
                    INNER JOIN users u ON t.start_userID = u.userID 
                    INNER JOIN forum f ON t.forumID = f.forumID
                    LEFT OUTER JOIN ban b ON t.forumID = b.forumID
                      AND b.userID = " . $_SESSION['userid'] . " 
                      AND b.expiration > NOW()
                    LEFT OUTER JOIN post p ON p.threadID = t.threadID
                      AND p.isFirst = 1
                  WHERE t.threadID = " . $_GET['threadid'] . "
                  AND b.userID IS NULL ";
        if ($_SESSION['userstatus'] != 'Admin') { $query .= "AND (t.start_userID = " . $_SESSION['userid'] . " OR f.moderator_userID = " . $_SESSION['userid'] . ") "; }
        $query .= "LIMIT 1";

        if (!$result = $db->query($query)) {
          $displayError = 'The thread you wish to edit could not be found at this time.';

        } else {          
          if ($result->num_rows === 1) { 
            $row = $result->fetch_assoc();
            $displayHTML = '<font size="6" color="blue"><b>Edit Thread Page</b></font><br><br>
                            <form method="post" action="./messengerhome.php?loadpage=pushthreadchange" enctype="multipart/form-data">
                              Thread Title<br><input type="text" size="73" name="threadtitle" id="threadtitle" value="' . stripslashes($row['title']) . '"><br><br>
                              <textarea rows="7" cols="75" name="firstpost">' . stripslashes($row['text']) . '</textarea><br><br>
                              <input type="hidden" value="' . $_GET['forumid'] . '" name="forumid">
                              <input type="hidden" value="' . $_GET['threadid'] . '" name="threadid">
                              <input type="submit" value="Commit Thread Changes" name="changethread">
                            </form>';

          } else { //unauthorized user
            $displayError = "Unauthorized user";
            $displayHTML = "<tr><td>You are not currently authorized to make changes to the requested thread.</td></tr>";

          }          
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
