<?php
  function authenticate_user() {
    header('WWW-Authenticate: Basic realm="Secret Stash"');
    header("HTTP/1.0 401 Unauthorized");
    exit; 
  }
  if (!isset($_SERVER['PHP_AUTH_USER'])) { 
    authenticate_user(); 

  } else { // Connect to the MySQL database
    mysql_connect(“ecsmysql","authenticator","secret")
    or die("Can't connect to database server!");
    mysql_select_db(“mydb") or die("Can't select database!");

    $query = "SELECT username, pswd FROM userauth WHERE username='$_SERVER[PHP_AUTH_USER]' AND pswd=MD5('$_SERVER[PHP_AUTH_PW]')";
    $result = mysql_query($query);

    // If nothing was found, reprompt the user for the login information.
    if (mysql_num_rows($result) == 0) { 
      authenticate_user(); 
    } else { 
      echo "Welcome to the secret archive!"; 
    }
  } 
?> 

