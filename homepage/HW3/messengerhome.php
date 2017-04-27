<?php
  session_start();
  $displayHTML = '';
  $displayError = '';
  $displayUser = '';

  if (isset($_SESSION['userid']) && $_SESSION['userid'] != "") {
    $displayUser = $_SESSION['username'];
    if (isset($_GET['loadpage'])) {
      $loadpage = $_GET['loadpage'];
    } else {
      $loadpage = "index";
    }
    switch ($loadpage) {
      case "logout":
        session_destroy();
        header("Location: http://ecs.fullerton.edu/~cs431s17/HW3/login_HW3.php");
        die();
        break;

      case "compose":
        //We'll build our display first (with pre-existing values if this is a reply/send)
        $displayHTML = '<form method=POST action="./messengerhome.php?loadpage=compose">';
        $displayHTML .= '<table cellpadding="2" cellspacing="1" border="0" width="796">';
        $displayHTML .= '  <col width="75"><col width="546">';
        $displayHTML .= '  <tr><td>From:</td>';
        $displayHTML .= '    <td><input size="100" type="text" name="sender" readonly="readonly" value="' . $_SESSION['username'] . '"></td></tr>';
        $displayHTML .= '  <tr><td>To:</td>';
        $displayHTML .= '    <td><input size="100" type="text" name="sendto" value="';
        if (isset($_POST['sendto'])) { $displayHTML .= $_POST['sendto']; }
        $displayHTML .= '"></td></tr>';
        $displayHTML .= '  <p><tr><td>Subject:</td>';
        $displayHTML .= '    <td><input size="100" type="text" name="subject" value="';
        if (isset($_POST['subject'])) {
          if (isset($_GET['asreply']) && $_GET['asreply'] == 'YES') { $displayHTML .= 'Re: '; }
          $displayHTML .= $_POST['subject'];  
        }
        $displayHTML .= '"></td></tr>';
        $displayHTML .= '  <p><tr><td valign="top">Message:</td>';
        $displayHTML .= '    <td><textarea rows="20" cols="102" name="message">';
        if (isset($_POST['message'])) { 
          if (isset($_GET['asreply']) && $_GET['asreply'] == "YES") {
            $displayHTML .= '&#13;&#10;&#13;&#10;-------------------------------------------------------------&#13;&#10;';
            $displayHTML .= 'In reply to the following message sent ';
            if (isset($_POST['sentdate'])) { $displayHTML .= 'on ' . $_POST['sentdate'] . ' '; }
            if (isset($_POST['sender'])) { $displayHTML .= 'by ' . $_POST['sender'] . ' '; }
            if (isset($_POST['receiver'])) { $displayHTML .= 'to ' . $_POST['receiver'] . ' '; }
            $displayHTML .= '&#13;&#10;-------------------------------------------------------------&#13;&#10;';
          }
          $displayHTML .= $_POST['message']; 
        }
        $displayHTML .= '</textarea></td></tr>';
        $displayHTML .= '</table>';
        $displayHTML .= '<input type="submit" name="sendbutton" value="Send Message"></form>';

        
        if (isset($_POST['sendbutton'])) {
          try {
            mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
            mysql_select_db("cs431s17");

            $query = "SELECT * ";
            $query .= "FROM users ";
            $query .= "WHERE Username = '" . mysql_escape_string($_POST['sendto']) . "';";
            $result = mysql_query($query);

            if (mysql_numrows($result) == 1) {
              $query = "INSERT INTO mailbox (Subject, MsgTime, MsgText, Sender_userID, Receiver_userID, Status) ";
              $query .= "VALUES ('" . mysql_escape_string($_POST['subject']) . "', NOW(), '" . mysql_escape_string($_POST['message']) . "', ";
              $query .= $_SESSION['userid'] . ", " . mysql_escape_string(mysql_result($result, 0, "UserID")) . ", 'New');";

              $result = mysql_query($query);              
              
              header("Location: http://ecs.fullerton.edu/~cs431s17/HW3/messengerhome.php");
              die();

            } else {
              $displayError = 'The user you were trying to send to was not found.';
            }
          } catch (Exception $e) {
          }          
        }
        break;

      case "sent":
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

          $query = "SELECT u.Username, m.* ";
          $query .= "FROM mailbox m, users u ";
          $query .= "WHERE u.UserID = m.Receiver_userID ";
          $query .= "AND m.Sender_userID = " . $_SESSION['userid'] . " ";
          $query .= "ORDER BY m.MessageID DESC;";

          $result = mysql_query($query);
          for ($i = 0; $i < mysql_numrows($result); $i++) {
            $displayHTML .= '<tr><td><a href="./messengerhome.php?loadpage=showmessage&&view=sent&&messageid=' . mysql_result($result, $i, "MessageID") . '">View</a></td>';
            $displayHTML .= '<td>' . mysql_result($result, $i, "Username") . '</td>';
            $displayHTML .= '<td>' . mysql_result($result, $i, "MsgTime") . '</td>';
            $displayHTML .= '<td>' . mysql_result($result, $i, "Subject") . '</td></tr>';
          }
          $displayHTML .= '</table>';

          mysql_close();
        } catch (Exception $e) {
        }
        break;

      case "showmessage":
        if (isset($_GET['view']) && $_GET['view'] == "sent") {
          $sender = "sender";
          $receiver = "sendto";
        } else {
          $sender = "sendto";
          $receiver = "receiver";
        }
        if (isset($_GET['messageid']) && $_GET['messageid'] != "") {
          try { 
            mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
            mysql_select_db("cs431s17");

            $query = "SELECT u1.Username AS Sender, u2.Username AS Receiver, m.* ";
            $query .= "FROM mailbox m, users u1, users u2 ";
            $query .= "WHERE m.Sender_userID = u1.UserID AND m.Receiver_userID = u2.UserID ";
            $query .= "AND m.MessageID = " . mysql_escape_string($_GET['messageid']) . " ";
            $query .= "AND (m.Sender_userID = " . $_SESSION['userid'] . " ";
            $query .= "OR m.Receiver_userID = " . $_SESSION['userid'] . ");";
            $result = mysql_query($query);

            if (mysql_numrows($result) > 0) {
              $displayHTML = '<form method=POST action="./messengerhome.php?loadpage=compose&&asreply=YES">';
              $displayHTML .= '<table cellpadding="2" cellspacing="1" border="0" width="796">';
              $displayHTML .= '  <col width="75"><col width="546">';
              $displayHTML .= '  <tr><td>From:</td>';
              $displayHTML .= '    <td><input size="100" type="text" name="' . $sender . '" readonly="readonly" value="' . mysql_result($result, 0, "Sender") . '"></td></tr>';
              $displayHTML .= '  <tr><td>To:</td>';
              $displayHTML .= '    <td><input size="100" type="text" name="' . $receiver . '" readonly="readonly" value="' . mysql_result($result, 0, "Receiver") . '"></td></tr>';
              $displayHTML .= '  <tr><td>Sent:</td>';
              $displayHTML .= '    <td><input size="100" type="text" name="sentdate" readonly="readonly" value="' . mysql_result($result, 0, "MsgTime") . '"></td></tr>';
              $displayHTML .= '  <p><tr><td>Subject:</td>';
              $displayHTML .= '    <td><input size="100" type="text" name="subject" readonly="readonly" value="' . mysql_result($result, 0, "Subject") . '"></td></tr>';
              $displayHTML .= '  <p><tr><td valign="top">Message:</td>';
              $displayHTML .= '    <td><textarea rows="20" cols="102" name="message" readonly="readonly">' . mysql_result($result, 0, "MsgText") . '</textarea></td></tr>';
              $displayHTML .= '</table>';
              $displayHTML .= '<input type="submit" value="Reply"></form>';

              if (isset($_GET['view']) && $_GET['view'] == "inbox") {
                $query = "UPDATE mailbox SET Status = 'Read' WHERE MessageID = " . mysql_escape_string($_GET['messageid']) . ";";
                $result = mysql_query($query);           
              }
            } else {
              $displayError = "Your requested message could not be located at this time.";
            }
            mysql_close();
          } catch (Exception $e) {
            $displayError = "There was an error accessing your requested message.  Please try again.";
          }
        } else {
          $displayError = "System could not locate the message you are trying to access.";
        }
        break;

      case "delmessage":
        if (isset($_GET['messageid']) && $_GET['messageid'] != "") {
          try {
            mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
            mysql_select_db("cs431s17");

            $query = "UPDATE mailbox ";
            $query .= "SET Status = 'Deleted' ";
            $query .= "WHERE MessageID = " . mysql_escape_string($_GET['messageid']) . " ";
            $query .= "AND Receiver_userID = " . $_SESSION['userid'] . ";";
            $result = mysql_query($query);

            mysql_close();
            header("Location: http://ecs.fullerton.edu/~cs431s17/HW3/messengerhome.php?loadpage=inbox");
            die();
           
          } catch (Exception $e) {
          }
        }
        break;

      default: //handles "inbox" as well
        $displayHTML = '<h1>Inbox</h1>';
        $displayHTML .= '<table cellpadding="2" cellspacing="0" border="1" width="796">';
        $displayHTML .= '  <col width="50">';
        $displayHTML .= '  <col width="150">';
        $displayHTML .= '  <col width="150">';
        $displayHTML .= '  <col width="396">';
        $displayHTML .= '  <col width="50">';
        $displayHTML .= '  <tr><th></th><th>From</th><th>Date Sent</th><th>Subject</th></tr>';

        try {
          mysql_connect("ecsmysql", "cs431s17", "ohnaeree");
          mysql_select_db("cs431s17");

          $query = "SELECT u.Username, m.* ";
          $query .= "FROM mailbox m, users u ";
          $query .= "WHERE u.UserID = m.Sender_userID ";
          $query .= "AND m.Receiver_userID = " . $_SESSION['userid'] . " ";
          $query .= "AND m.Status <> 'Deleted' ";
          $query .= "ORDER BY m.MessageID DESC;";
            
          $result = mysql_query($query);
          for ($i = 0; $i < mysql_numrows($result); $i++) {
            $displayHTML .= '<tr';
            if (mysql_result($result, $i, "Status") == 'New') { $displayHTML .= ' style="font-weight:bold"'; }

            $displayHTML .= '><td><a href="./messengerhome.php?loadpage=showmessage&&view=inbox&&messageid=' . mysql_result($result, $i, "MessageID") . '">View</a></td>';
            $displayHTML .= '<td>' . mysql_result($result, $i, "Username") . '</td>';
            $displayHTML .= '<td>' . mysql_result($result, $i, "MsgTime") . '</td>';
            $displayHTML .= '<td>' . mysql_result($result, $i, "Subject") . '</td>';
            $displayHTML .= '<td><a href="./messengerhome.php?loadpage=delmessage&&messageid=' . mysql_result($result, $i, "MessageID") . '">DEL</a></td></tr>';
          }

          $displayHTML .= '</table>';
            
          mysql_close();
        } catch (Exception $e) {
        }
        break;
    }

  } else {
    header("Location: http://ecs.fullerton.edu/~cs431s17/HW3/login_HW3.php");
    die();
  }
?>
<html>
<head>
  <meta charset="utf-8">

  <title>CS431 Messenger</title>
</head>
<table cellpadding="0" cellspacing="0" border="0" windth="700" align="right">
  <tr>
    <td><a href="messengerhome.php?loadpage=logout">Sign Out</a></td>
  </tr>
</table>
<h1>Welcome 
  <?php
    if(isset($displayUser) && !empty($displayUser)) { echo $displayUser . " "; }
  ?>
</h1>
<h2>to the CS 431 Messenger Application by James Mitchell</h2>

<body>
  <table cellpadding="0" cellspacing="0" border="0" width="1000">
    <?php
      if (isset($displayError) && !empty($displayError)) {
        echo '<tr><td><font id="errorMsg" color="red">' . $displayError . '</font></td></tr>';
      }
    ?>
    <tr>
      <td valign="top"><table cellpadding="1" cellspacing="0" border="0" width="198">
        <tr>
          <td valign="top">
            <tr>
              <td><a href="./messengerhome.php?loadpage=compose">Compose Message</a></td>
            </tr>
            <tr><td><br></td></tr>
            <tr>
              <td><a href="./messengerhome.php?loadpage=inbox">Inbox</a></td>
            </tr>
            <tr>
              <td><a href="./messengerhome.php?loadpage=sent">Sent</a></td>
            </tr>
          </td>
        </tr>
      </table></td>
      <td>
        <?php
          if(isset($displayHTML) && !empty($displayHTML)) {
            echo $displayHTML;
          }
        ?>
      </td>
    </tr>
  </table>
</body>
</html>
