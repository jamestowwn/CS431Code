<?php
  session_start();
  $displayHTML = '';
  $displayError = '';
  $displayUser = 'Guest';

  $displayMenu .= '<tr><td><a href="./messengerhome.php?loadpage=forums">View Forums</a></td></tr>';

  $displayHeader = '<tr><td><a href="./login.php">Sign In</a></td>
                        <td><a href="./register.php">Register</a></td></tr>';

  $redirect = '';

  if ((isset($_SESSION['userid']) && $_SESSION['userid'] != '') || isset($_GET['loadpage'])) {
    if (isset($_GET['loadpage'])) {
      $loadpage = $_GET['loadpage'];
    } else {
      $loadpage = 'forums';
    }
    $db = new mysqli('ecsmysql', 'cs431s17', 'ohnaeree', 'cs431s17');
    if ($db->connect_errno) {
      $displayError = 'There was an error connecting to the site database. Contact site administrator for assistance.';
      $loadpage = '';
    }

    switch ($loadpage) {
      case 'banuser':
        //include 'forum/banuser.php';
        break;

      case 'chat':
        //include 'chat/';
        break;

      case 'compose':
        include 'mail/compose.php';
        break;

      case 'createforum':
        include 'forum/editforum.php';
        break;
      
      case 'createpost':
        include 'thread/editthread.php';
        break;
 
      case 'createthread':
        include 'thread/editthread.php';
        break;

      case 'createnewforum':
        include 'forum/editforum.php';
        break;
      
      case 'createnewpost':
        include 'thread/editthread.php';
        break;

      case 'createnewthread':
        include 'thread/editthread.php';
        break;

      case 'delmessage':
        include 'mail/deletemessage.php';
        break;

      case 'editforum':
        include 'forum/editforum.php';
        break;

      case 'editpost':
        include 'thread/editthread.php';
        break;

      case 'editthread':
        include 'thread/editthread.php';
        break;

      case 'forums':
        include 'forum/viewforums.php';
        break;

      case 'inbox':
        include 'mail/viewinbox.php';
        break;

      case 'logout':
        session_destroy();
        $redirect = 'Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php';
        break;

      case 'pushforumchange':
        include 'forum/editforum.php';
        if ($displayError == '' && $displayHTML == '') {
          $redirect = 'Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=forums';
        }
        break;

      case 'pushpostchange':
        include 'thread/pushchange.php';
        if ($displayError == '' && $displayHTML == '') {
          $redirect = 'Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=threads';
          if (isset($_POST['threadid'])) { $redirect .= '&&threadid=' . $_POST['threadid']; }
        }
        break;

      case 'pushthreadchange':
        include 'thread/pushchange.php';
        if ($displayError == '' && $displayHTML == '') {
          $redirect = 'Location: http://ecs.fullerton.edu/~cs431s17/project/messengerhome.php?loadpage=threads';
          if (isset($_POST['threadid'])) { $redirect .= '&&threadid=' . $_POST['threadid']; }
        }
        break;

      case 'sent':
        include 'mail/viewsent.php';
        break;

      case 'showmessage':
        include 'mail/showmessage.php';
        break;

      case 'threads':
        include 'thread/viewthread.php';
        break;

      default: 
        $displayHTML = '<font size="6" color="green">Feel free to check your mail, go to the forums, or visit chat.</font>';
        break;
    }

    $db->close();
    if ($redirect != '') {
      header($redirect);
      die();
    }
    if (isset($_SESSION['userid']) && $_SESSION['userid'] != '') {
      $displayUser = $_SESSION['username'];
      $displayHeader = '<tr><td><a href="./messengerhome.php?loadpage=logout">Sign Out</a></td></tr>';
  
      if (isset($_SESSION['userstatus']) && $_SESSION['userstatus'] != 'Banned') {
        $displayMenu .= '<tr><td><a href="./messengerhome.php?loadpage=createforum&&forumid=0">Request New Forum</a></td></tr>';
      }
      $displayMenu .= '<tr><td><br></td></tr>
                       <tr><td><a href="./messengerhome.php?loadpage=compose">Compose Message</a></td></tr>
                       <tr><td><br></td></tr>
                       <tr><td><a href="./messengerhome.php?loadpage=inbox">Inbox</a></td></tr>
                       <tr><td><a href="./messengerhome.php?loadpage=sent">Sent</a></td></tr>
                       <tr><td><br></td></tr>';

      if (isset($_SESSION['userstatus']) && $_SESSION['userstatus'] != 'Banned') {
        $displayMenu .= '<tr><td><a href="./messengerhome.php?loadpage=chat">View Chat Rooms</a></td></tr>';
      } else {
        $displayHeader .= '<tr><td><font color="red">(CURRENTLY BANNED)</font></td></tr>';
      }
    }
  } else {  //This is a guest
    $displayHTML = '<font size="6" color="green">Feel free to Sign In or Register for the site.<br>
                                                 You may also browse the forums, but cannot contribute.</font>';
  }
?>
<html>
<head>
  <meta charset="utf-8">

  <title>CS431 Forum Messenger Application</title>
</head>
<table cellpadding="5" cellspacing="0" border="0" align="right">
  <?php
    if (isset($displayHeader) && !empty($displayHeader)) {
      echo $displayHeader;
    }
  ?>
</table>
<h1>Welcome 
  <?php
    if(isset($displayUser) && !empty($displayUser)) { echo $displayUser . " "; }
  ?>
</h1>
<h2>to the CS 431 Forum Messenger Application</h2>

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
            <?php
              if(isset($displayMenu) && !empty($displayMenu)) {
                echo $displayMenu;
              }
            ?>
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
