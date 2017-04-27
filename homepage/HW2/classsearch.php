<?php
/* The following sites used as reference in development:
 * http://stackoverflow.com/questions/2548566/go-back-to-previous-page
 * http://wang.ecs.fullerton.edu/cpsc431/ (page source)
 */

// Read in the search type and value
$searchType = $_POST['searchType'];
$searchVal = $_POST['searchVal'];

// Read in the classlist file
$classlist = file('classlist.txt');

// print the page header values
echo '<H1>Class Search Results:</H1><HR>';
echo '<b>Below are the results of your submitted search:</b><p>';
echo '<table cellpadding="1" cellspacing="1" border="1">';
echo '<tr><th>ID</th><th>First Name Middle Name</th><th>Last Name</th></tr>';

/* Write our function for outputting a table row for submitted user values. It is expected the
 * 3 values submitted will be id, fname, lname
 */
function outputStudent($id, $fn, $ln) {
     echo '<tr><td>' . $id . '</td><td>' . $fn . '</td><td>' . $ln . '</td></tr>';
}

// now we print the matching results
foreach($classlist as $student) {
     // read the values for this student into variables
     list($stu_id, $stu_lname, $stu_fname) = explode(',', $student);

     // check if one our search matches for this student and output the student if it does
     switch ($searchType) {
          case "cid":
               if ($searchVal == $stu_id) { 
                    outputStudent($stu_id, $stu_fname, $stu_lname); 
               }
               break;
          case "lname":
               if (strcasecmp($searchVal, $stu_lname) == 0) {
                    outputStudent($stu_id, $stu_fname, $stu_lname);
               }
               break;
          case "fname":
               if (strncasecmp($searchVal, $stu_fname, strlen($searchVal)) ==0) {
                    outputStudent($stu_id, $stu_fname, $stu_lname);
               }
               break;
     }
}
echo '</table><HR>';
echo "<a href=\"javascript:history.go(-1)\">Return to Search</a>";
?>
