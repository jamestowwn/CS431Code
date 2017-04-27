<?php
/* The following sites used as reference in development:
http://stackoverflow.com/questions/2548566/go-back-to-previous-page
http://wang.ecs.fullerton.edu/cpsc431/ (page source)
 */

// Read in the potentially posted variables
$cid = $_POST['classid'];
$lname = $_POST['lastname'];
$fname = $_POST['firstname'];

// Read in the classlist file
$classlist = file('classlist.txt');

/* Create a multidimensional array to hold each student's indvidual values
 * [0] = id, [1] = lastname, [2] = firstname_middlename
 */
foreach($classlist as $student) {
     if (trim($student) != '') {  //ignore blank lines
     // Explode the value in each line by the comma delimiter into an array of array values
          $student_array[] = explode(',', $student);
     }
}

// print the page header values
echo '<H1>Class Search Results:</H1><HR>';
echo '<b>Below are the results of your submitted search fields:</b><p>';
echo '<table cellpadding="1" cellspacing="1" border="1">';
echo '<tr><th>ID</th><th>First Name Middle Name</th><th>Last Name</th></tr>';

// now we print the matching results
foreach($student_array as $student) {
     $print_rec = FALSE;
     if ($cid != '' && $cid == $student[0]) {
          $print_rec = TRUE;
     } elseif ($lname != '' && strcasecmp($lname, $student[1]) == 0) {
          $print_rec = TRUE;
     } elseif ($fname != '' && strncasecmp($fname, $student[2], strlen($fname)) == 0) {
          $print_rec = TRUE;
     }
     
     if ($print_rec == TRUE) {
          // Our output wants lastname displayed after firstname_middlename so we just change index order in display
          echo '<tr><td>' . $student[0] . '</td><td>' . $student[2] . '</td><td>' . $student[1] . '</td></tr>';
     }
}
echo '</table><HR>';
echo "<a href=\"javascript:history.go(-1)\">Return to Search</a>";
?>
