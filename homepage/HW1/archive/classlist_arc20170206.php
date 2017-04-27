<?php
/* The following sites used as reference in development:
 * http://php.net/manual/en/function.usort.php
 * http://stackoverflow.com/questions/12449308/explode-a-txt-file-into-multidimensional-array
 * http://stackoverflow.com/questions/16510782/inserting-array-values-into-a-html-table
 * http://php.net/manual/en/function.file.php
 */

// Read in the classlist file
$classlist = file('classlist.txt');

/* Create a multidimensional array to hold each student's indvidual values
 * [0] = id, [1] = firstname_middlename, [2] = lastname
 */
foreach($classlist as $student) {
     if (trim($student) != '') {  //ignore blank lines
     // Explode the value in each line by the comma delimiter into an array of array valuse
          $student_array[] = explode(',', $student);
     }
}

//this is our sort function
function sortCompare($a, $b) {
     if ($a == $b) {
          return 0;
     }
     return ($a < $b) ? -1 : 1;
}

//now we sort our student arrays
usort($student_array, "sortCompare");

//now we print our student tables out
$counter = 6; //this is the only time counter will be 6
foreach($student_array as $student) {
     if ($counter > 4) {
     /* The maximum number of rows have been printed to our table or we are entering for the first time.
      * If this is a subsequent entry, then we must end our table.  In all cases we should start a new table
      * for the remaining entries and reset the counter.
      */
          if ($counter != 6) { //otherwise we are entering for the first time
               echo '</table>';
          }
          $counter = 0;
          echo '<table cellpadding="1" cellspacing="1" border="1">';
          echo '<tr><th>ID</th><th>First Name Middle Name</th><th>Last Name</th></tr>';
     }
     echo '<tr><td>' . $student[0] . '</td><td>' . $student[2] . '</td><td>' . $student[1] . '</td></tr>';
     $counter++;     
}
echo '</table>';
?>
