<?php
  session_start(); 

  if (isset($_SESSION['userid']) && $_SESSION['userid'] != '' && isset($_GET['forumid']) && $_GET['forumid'] != '' && isset($loadpage) && $loadpage != '') {

    switch ($loadpage) {
      case 'createforum':
        $displayHTML = '<font size="6" color="blue"><b>Create a New Forum Page</b></font><br><br>
                        <form method="post" action="./messengerhome.php?loadpage=createnewforum&&forumid=0" enctype="multipart/form-data">
                          Forum Image<br><input type="file" name="filetoupload" id="filetoupload"><br><br>
                          Forum Name<br><input type="text" size="73" name="forumname" id="forumname" value=><br><br>
                          Forum Description<br><textarea rows="7" cols="75" name="forumdescription"></textarea><br><br>
                          <input type="hidden" value="0" name="forumid">
                          <input type="submit" value="Submit Forum Request" name="createforum">
                        </form>';
        break;

      case 'createnewforum':
        if (isset($_POST['createforum'])) {
          $query = "INSERT INTO forum (forumName, description, picture, moderator_userID) VALUES ('";

          if (isset($_POST['forumname']) && $_POST['forumname'] != '') {
            $query .= mysqli_real_escape_string($db, $_POST['forumname']) . "', ";

            if (isset($_POST['forumdescription']) && $_POST['forumdescription'] != '') {
              $query .= "'" . mysqli_real_escape_string($db, $_POST['forumdescription']) . "', ";

            } else {
              $query .= "NULL, ";

            }
            if (getimagesize($_FILES["filetoupload"]["tmp_name"]) !== FALSE) {
              $tmpName = $_FILES['filetoupload']['tmp_name'];
              $fp = fopen($tmpName, 'r');
              $data = fread($fp, filesize($tmpName));
              $data = addslashes($data);
              fclose($fp);
              $query .= "'" . $data . "', ";
            } else {
              $query .= "null, ";
            }
            $query .= $_SESSION['userid'] . ");";
            if ($db->query($query) === FALSE) {
              $displayError = 'There was an error creating your forum request. Please contact the site administrator for assistance.';

            } else {
              $displayHTML = 'Your forum request for the new [' . $_POST['forumname'] . '] forum has been submitted.<br>';

            }
          } else {
            $displayError = 'You must provide a name for this forum. Forum creation was cancelled.';

          }
        } else {
          $displayError = 'New Forum requests can only be made from a new forum request form.';
        }        
        break;

      case 'editforum':
        $query = "SELECT f.*, u.username 
                  FROM forum f, users u 
                  WHERE f.moderator_userID = u.userID 
                  AND f.forumID = " . $_GET['forumid'] . " ";
        if ($_SESSION['userstatus'] != 'Admin') { $query .= "AND moderator_userID = " . $_SESSION['userid']; }
        
        if (!$result = $db->query($query)) {
          $displayError = 'Forum page could not be loaded for editing.';

        } else {
          if ($result->num_rows === 1) {
          //This user is authorized to edit this forum
            $row = $result->fetch_assoc();
            $displayHTML = '<font size="6" color="blue"><b>Edit Forum Page</b></font><br><br>
                            <form method="post" action="./messengerhome.php?loadpage=pushforumchange&&forumid=' . $_GET['forumid'] . '" enctype="multipart/form-data">
                              Forum Image<br><input type="file" name="filetoupload" id="filetoupload"><br>
                              <img src="data:image/jpeg;base64,' . base64_encode($row['picture']) . '"/><br>
                              Forum Name<br><input type="text" size="73" name="forumname" id="forumname" value="' . stripslashes($row['forumName']) . '"><br><br>
                              Forum Description<br><textarea rows="7" cols="75" name="forumdescription">' . stripslashes($row['description']) . '</textarea><br><br>
                              <input type="hidden" value="' . $_GET['forumid'] . '" name="forumid">';
            if ($_SESSION['userstatus'] == 'Admin') {
              $displayHTML .= '<select name="forumstatus"><option ';
              if (mysql_result($result, 0, "status") == "Review") { $displayHTML .= 'selected="selected" '; }
              $displayHTML .= 'value="Review">Review</option><option ';
              if (mysql_result($result, 0, "status") == "Live") { $displayHTML .= 'selected="selected" '; }
              $displayHTML .= 'value="Live">Live</option></select><br><br>
                               Moderator<br><input type="text" size="30" name="moderator" id="moderator" value="' . stripslashes($row['username']) . '"><br><br>';
            }
            $displayHTML .= '<input type="submit" value="Commit Forum Changes" name="changeforum">
                             </form>';

          } else { //unauthorized user
            $displayError = 'Unauthorized user';
            $displayHTML = '<tr><td>You are not currently authorized to make changes to the requested forum.</td></tr>';

          }          
        }
        break;
   
      case 'pushforumchange':
        if (isset($_POST['changeforum'])) {
          $query = "UPDATE forum SET ";

          if (isset($_POST['forumname']) && $_POST['forumname'] != '') {
            $query .= "forumName = '" . mysqli_real_escape_string($db, $_POST['forumname']) . "'";

            if (isset($_POST['forumdescription']) && $_POST['forumdescription'] != '') {
              $query .= ", description = '" . mysqli_real_escape_string($db, $_POST['forumdescription']) . "'";
            }

            if (getimagesize($_FILES['filetoupload']['tmp_name']) !== FALSE) {
              $tmpName = $_FILES['filetoupload']['tmp_name'];
              $fp = fopen($tmpName, 'r');
              $data = fread($fp, filesize($tmpName));
              $data = addslashes($data);
              fclose($fp);
              $query .= ", picture = '" . $data . "'";
            }
              
            if ($_SESSION['userstatus'] == 'Admin') {
              $query .= ", status = '" . $_POST['forumstatus'] . "'";

              $tmpQ = "SELECT userID
                       FROM users
                       WHERE username = '" . mysqli_real_escape_string($_POST['moderator']) . "';";              
              if (!$result = $db->query($query)) {
                $displayError = 'Could not verify the validity of the submitted moderator. Please re-run your forum change request.';

              } else {                
                if ($result->num_rows === 1) {
                  $row = $result->fetch_assoc();
                  $query .= ", moderator_userID = " . $row['userID'];

                } else {
                  $displayError .= 'Submitted moderator user could not be found, forum was assigned no moderator';
                  $query .= ", moderator_userID = 0";

                }
              }
            }
            $query .= " WHERE forumID = " . $_POST['forumid'];
            $db->query($query);

          } else {
            $displayError = 'You must provide a name for this forum. Forum update cancelled.';

          }
        } else {
          $displayError = 'Forum changes can only be made from a forum change request form.';
        }
        break;

      default:
        break;
    }
  } else {
    $displayHTML .= 'The requested page could not be displayed.';
    $displayError .= 'You are not authorized to perform the action requested';

  }
?>
