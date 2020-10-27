<?php
mysqli_report(MYSQLI_REPORT_STRICT); // To force PHP to throw exception if errors

// Connection to MySQL database, sets $con to null (or die()) if problem connecting
try {
   $con = mysqli_connect("localhost", "root", "", "calendar_db"); //database credentials
}
catch (Exception $e) {
   $con = null; //this can be replaced by die()
}
?>