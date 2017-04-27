<?php
/* Assistance taken from the following websites:
 * https://www.w3schools.com/php/php_file_upload.asp
 */
  session_start();
  $displayHTML = '';
  $displayError = '';
  $displayUser = '';
  
  $searchBar = '<tr><td valign="top"><form method="post" action="./photoshare.php?loadpage=searchimage">';
  $searchBar .= '<table cellpadding="1" width="798"><tr>';
  $searchBar .= '<td>Search For:</td>';
  $searchBar .= '<td><input size="80" type="text" name="searchVal" value=></td>';
  $searchBar .= '<td><input type="submit" name="search" value="Search Images"></td>';
  $searchBar .= '</tr></table>';
  $searchBar .= '</form></td></tr><tr></tr>';

  if (isset($_SESSION['userid']) && $_SESSION['userid'] != "") {
    $displayUser = $_SESSION['username'];

    $loadpage = "";
    if (isset($_GET['loadpage'])) { $loadpage = $_GET['loadpage']; }

    switch ($loadpage) {
      case "logout":
        session_destroy();
        header("Location: http://ecs.fullerton.edu/~cs431s17/HW4/login_HW4.php");
        die();
        break;      
      
      case "uploadphoto":
        if (isset($_POST['uploadimage'])) {
          if (isset($_POST['photoname']) && $_POST['photoname'] != '') {
          if (isset($_POST['photocaption']) && $_POST['photocaption'] != '') {
          if (getimagesize($_FILES["fileToUpload"]["tmp_name"]) !== false) {
            try {  
              mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
              mysql_select_db("cs431s17");

              $tmpName = $_FILES['fileToUpload']['tmp_name'];
              $fp = fopen($tmpName, 'r');
              $data = fread($fp, filesize($tmpName));
              $data = addslashes($data);
              fclose($fp);

              $query = "INSERT INTO photos (PhotoName, Caption, PhotoData, Upload_userID) VALUES (";
              $query .= "'" . mysql_escape_string($_POST['photoname']) . "', ";
              $query .= "'" . mysql_escape_string($_POST['photocaption']) . "', ";
              $query .= "'" . $data . "', ";
              $query .= "'" . mysql_escape_string($_SESSION['userid']) . "');";

              $result = mysql_query($query) or die(mysql_error());
              if (mysql_affected_rows() > 0) {
                $displayHTML = "<tr><td>Your file was successfully uploaded to the server!</td></tr>";
              } else {
                $displayError = "There was an error uploading your image to the server, please try again.";
              }

              mysql_close();
            } catch (Exception $e) {
              $displayError = "There was an error uploading your image to the server, please try again.";
            }
          } else {
            $displayError = "Submitted file does not meet our image file upload requirements. File upload cancelled.";
          } 
          } else {
            $displayError = "You must provide a caption for your image upload. File upload cancelled.";
          }
          } else {
            $displayError = "You must provide a name for your image upload. File upload cancelled.";
          }
        } else {
          $displayError = "Cannot upload your image that way, please use the appropriate form.";
        }
        break;

      case "upload":
        $searchBar = '';
        $displayHTML = '<tr><td><font size="6" color="blue"><b>Image Upload Page</b></font></td></tr>';
        $displayHTML .= '<tr><td><form method="post" action="./photoshare.php?loadpage=uploadphoto" enctype="multipart/form-data">';
        $displayHTML .= 'To upload an image to the server follow these steps:<br><br>';
        $displayHTML .= '<pre>  1) Press "Choose File" and select the image to upload<br>';
        $displayHTML .= '     *Note: uploaded files cannot exceed 2 MB in size<br><br>';
        $displayHTML .= '  2) Enter a name for the image and a caption<br><br>';
        $displayHTML .= '  3) Press "Upload Image" to save your image to the server<br><br><br></pre>';
        $displayHTML .= '<input type="file" name="fileToUpload" id="fileToUpload"><br><br>';
        $displayHTML .= 'Name<br><input type="text" size="73" name="photoname" id="photoname"><br>';
        $displayHTML .= 'Caption<br><textarea rows="7" cols="75" name="photocaption"></textarea><br><br>';
        $displayHTML .= '<input type="submit" value = "Upload Image" name="uploadimage">';
        $displayHTML .= '</form></td></tr>';
        
        break;

      case "showimage":
        if (isset($_GET['imageID']) && $_GET['imageID'] != "") {
          $displayHTML = '<tr><td><form name="returnToSearch" method="post" action="./photoshare.php?loadpage=searchimage">';
          $displayHTML .= '<a href="#" onclick="document.returnToSearch.submit(); return false;">Return to Previous</a>';
          $displayHTML .= '<input type="hidden" name="searchVal" value="' . $_SESSION['lastSearch'] . '"></form></td></tr>';

          try {
            mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
            mysql_select_db("cs431s17");
            
            $query = "SELECT u.Username, p.* ";
            $query .= "FROM photos p, users u ";
            $query .= "WHERE u.userID = p.Upload_userID ";
            $query .= "AND p.ImageID = " . mysql_escape_string($_GET['imageID']) . " ";

            $result = mysql_query($query);
            if (mysql_numrows($result) > 0) {
              $displayHTML .= '<tr><td><b>Image Name: </b>' . stripslashes(mysql_result($result, 0, "PhotoName")) . '</td></tr>';
              $displayHTML .= '<tr><td><b>Caption: </b><br><textarea rows="3" cols="100">' . stripslashes(mysql_result($result, 0, "Caption")) . '</textarea></td></tr>';
              $displayHTML .= '<tr><td><img src="data:image/jpeg;base64,' . base64_encode(mysql_result($result, 0, "PhotoData")) . '"/></td></tr>';

            } else {
              $displayError = 'The requested image could not be found at this time.';
            }

            mysql_close();
          } catch (Exception $e) {
            $displayError = 'There was an erro looking up your requested image, please try again.';
          }
        } else {
          $displayError = 'Not a valid image display request';
        }
        break;

      case "searchimage":       
        if (isset($_POST['searchVal']) && $_POST['searchVal'] != "") {
          $displayHTML = '<tr><td><font size="6" color="blue"><b>Image Search Results</b></font><br></td></tr>';
          $_SESSION['lastSearch'] = $_POST['searchVal'];

          try {
            mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
            mysql_select_db("cs431s17");

            $query = "SELECT u.Username, p.ImageID, p.PhotoName, p.Caption ";
            $query .= "FROM photos p, users u ";
            $query .= "WHERE u.UserID = p.Upload_userID ";
            $query .= "AND (p.PhotoName LIKE '%" . mysql_escape_string($_POST['searchVal']) . "%' ";
            $query .= "OR p.Caption LIKE '%" . mysql_escape_string($_POST['searchVal']) . "%') ";
            $query .= "ORDER BY p.ImageID;";

            $result = mysql_query($query) or die (mysql_error());
            
            $displayHTML .= '<tr><td><font size="4"><b>' . mysql_numrows($result) . ' image(s) found for search "' . $_POST['searchVal'] . '"</b></font></td></tr>';
            $displayHTML .= '<table cellpadding="2" cellspacing="0" border="1" width="796">';
            $displayHTML .= '<col width="50">';
            $displayHTML .= '<col width="150">';
            $displayHTML .= '<col width="446">';
            $displayHTML .= '<col width="150">';
            $displayHTML .= '<tr><th></th><th>Photo Name</th><th>Photo Caption</th><th>Uploaded By</th></tr>';

            for ($i = 0; $i < mysql_numrows($result); $i++) {
              $displayHTML .= '<tr><td align="center">';
              $displayHTML .= '<a href="./photoshare.php?loadpage=showimage&&imageID=' . mysql_result($result, $i, "ImageID") . '">';
              $displayHTML .= '<i class="fa fa-file-photo-o"><i></a></td>';
              $displayHTML .= '<td>' . stripslashes(mysql_result($result, $i, "PhotoName")) . '</td>';
              $displayHTML .= '<td>' . stripslashes(mysql_result($result, $i, "Caption")) . '</td>';
              $displayHTML .= '<td>' . stripslashes(mysql_result($result, $i, "Username")) . '</td></tr>';
            }

            $displayHTML .= '</table>';
            
            mysql_close();
          } catch (Exception $e) {
            $displayError = "There was an error pulling your search request from the database, please try again.";

          }
        } else { 
          $displayError = "Please enter a term to search images for.";

        }

      default:
        break;
    }
  } else {
    header("Location: http://ecs.fullerton.edu/~cs431s17/HW4/login_HW4.php");
    die();
  }
?>
<html>
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>img { padding: 0; display: block; margin: 0 auto; max-height: 100%; max-width: 100%; }</style>

  <title>CS431 Image Share</title>
</head>
<table cellpadding="0" cellspacing="0" border="0" windth="700" align="right">
  <tr>
    <td><a href="photoshare.php?loadpage=logout">Sign Out</a></td>
  </tr>
</table>
<h1>Welcome 
  <?php
    if(isset($displayUser) && !empty($displayUser)) { echo $displayUser . " "; }
  ?>
</h1>
<h2>to the CS 431 Image Upload and Retrieval Application by James Mitchell</h2>

<body>
  <table cellpadding="0" cellspacing="0" border="0" width="1000">
    <tr><td width="1000"><p>
    <?php
      if (isset($displayError) && !empty($displayError)) {
        echo '<font id="errorMsg" color="red">' . $displayError . '</font>';
      } 
    ?>
    </p></td></tr>
  </table>
  <table cellpadding="0" cellspacing="0" border="0" width="1000">
    <tr>
      <td valign="top"><table cellpadding="1" cellspacing="0" border="0" width="200">
        <tr>
          <td valign="top">
            <tr><td><a href="./photoshare.php">Search Images</a></td></tr>
            <tr><td><a href="./photoshare.php?loadpage=upload">Upload Image</a></td></tr>
          </td>
        </tr>
      </table></td>
      <td valign="top"><table cellpadding="1" cellspacing"0" border="0" width="800">
        <?php
          if(isset($searchBar) && !empty($searchBar)) {
            echo $searchBar;
          }
        ?>
        <tr>
          <?php
            if(isset($displayHTML) && !empty($displayHTML)) {
              echo $displayHTML;
            }
          ?>
        </tr>
      </table></td>
    </tr>
  </table>
</body>
</html>
