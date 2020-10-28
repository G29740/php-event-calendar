<?php
include 'dbconnection.php';

function getCalendar($connection, $m, $y) {
   $calendarHTML = $errorHTML = ''; //variable to contain calendar output (and error messages if any)
   $weekDayNumber = array(
      6,
      0,
      1,
      2,
      3,
      4,
      5
   ); //week day numbers, 0: Monday, 6: Sunday
   $monthNames = array(
      'JAN',
      'FEB',
      'MAR',
      'APR',
      'MAY',
      'JUN',
      'JUL',
      'AUG',
      'SEP',
      'OCT',
      'NOV',
      'DEC'
   );
   $dayNames = array(
      'MON',
      'TUE',
      'WED',
      'THU',
      'FRI',
      'SAT',
      'SUN'
   );
   $eventsArray = array(); //array of events
   $eventTopic = '';
   $eventDate = '';
   $eventStreetNumber = '';
   $eventStreetName = '';
   $eventPostalCode = '';
   $eventLocality = '';
   $eventStartTime = '';
   $eventEndTime = '';

   $modalHTMLarray = array();

   //calculate next/previous month/year based on the given parameters
   $prevMonth = $m - 1;
   $prevYear = $nextYear = $y;
   $nextMonth = $m + 1;
   if ($m - 1 == 0) {
      $prevMonth = 12;
      $prevYear = $y - 1;
   }
   if ($m + 1 == 13) {
      $nextMonth = 1;
      $nextYear = $y + 1;
   }

   $t = mktime(12, 0, 0, $m, 1, $y); // Timestamp of 1st day of the month in the given year
   
   //build calendar
   $calendarHTML .= '<table id="calendar"><tbody>';

   //build calendar header (month+year and navigation buttons)
   $calendarHTML .= '<tr class="calendar-nav">';
   $calendarHTML .= '<td class="prev" data-prev-month="' . $prevMonth . '" data-prev-year="' . $prevYear . '" colspan="1">&lt;</td>';
   $calendarHTML .= '<td colspan="5" style="font-size: 24px">' . $monthNames[$m - 1] . ' ' . $y . '</td>';
   $calendarHTML .= '<td class="next" data-next-month="' . $nextMonth . '" data-next-year="' . $nextYear . '" colspan="1">&gt;</td></tr>';

   //build day names
   $calendarHTML .= '<tr class="calendar-day-names-header">';
   for ($i = 0;$i < 7;$i++) {
      $calendarHTML .= '<td>' . $dayNames[$i] . '</td>';
   }
   $calendarHTML .= '</tr>';

   //build days of the month (cells)
   for ($i = 0;$i < 6;$i++) { //calendar in 6 rows
      $calendarHTML .= '<tr class="calendar-days">';
      for ($j = 0;$j < 7;$j++) //calendar in 7 columns
      {
         $w = $weekDayNumber[(int)date('w', $t) ]; //get numeric representation of the day from the timestamp, then use the number as index of the $weekDayNumber array to get the right day number
         $m2 = (int)date('n', $t); //get numeric representation of the month from the timestamp, without leading zeros
         if (($w == $j) && ($m2 == $m)) { //if day number falls within the given month
            $d = date('d', $t); //get day of the month (01 to 31)
            $calendarHTML .= '<td';

            $tdClasses = array(); //array of classes used to contain nothing or "today" or "event" or "today+event"
            if ($d == date("d", time()) && $m == date("m", time()) && $y == date("Y", time())) { //if date in the calendar matches current date, add class to $tdClasses array
               array_push($tdClasses, "today");
            }

            //if there is connection to the DB, build calendar, otherwise display error
            if ($connection != null) {
               $eventSql = "SELECT * FROM event WHERE YEAR(date) = ? AND MONTH(date) = ? AND DAY(date) = ?"; //SQL query to select event details from given day, month and year
               if ($stmt = mysqli_prepare($connection, $eventSql)) {
                  mysqli_stmt_bind_param($stmt, "iii", $y, $m, $d);
                  if (mysqli_stmt_execute($stmt)) {
                     mysqli_stmt_store_result($stmt);
                     if (mysqli_stmt_num_rows($stmt) == 1) { //if event record found
                        $eventDetailsArray = array();
                        array_push($tdClasses, "eventday"); //add class to $tdClasses array
                        mysqli_stmt_bind_result($stmt, $eventId, $eventTopic, $eventDate, $eventStreetNumber, $eventStreetName, $eventPostalCode, $eventLocality, $eventStartTime, $eventEndTime);

                        mysqli_stmt_fetch($stmt);

                        array_push($eventDetailsArray, $eventId, $eventTopic, $eventDate, $eventStreetNumber, $eventStreetName, $eventPostalCode, $eventLocality, $eventStartTime, $eventEndTime); //populate $eventDetailsArray
                        array_push($eventsArray, $eventDetailsArray); //insert current array of event details in another array of events (2 dimensional array)
                        
                     }
                  }
                  else {
                     $errorHTML = "<div class=\"alert alert-danger role=\"alert\">Cannot retrieve event details from database, please contact administrator!</div>";
                  }
                  mysqli_stmt_free_result($stmt);
                  mysqli_stmt_close($stmt);
               }
               else {
                  $errorHTML = "<div class=\"alert alert-danger role=\"alert\">Cannot retrieve event details from database, error in SQL query, please contact administrator!</div>";
               }
            }
            else {
               $errorHTML = "<div class=\"alert alert-danger role=\"alert\">Cannot retrieve event details from database, please contact administrator!</div>";
            }

            //continue building calendar cell with class names
            if (count($tdClasses) > 0) {
               $calendarHTML .= ' class="';
               for ($k = 0;$k < count($tdClasses);$k++) {
                  $calendarHTML .= $tdClasses[$k] . ' ';
               }
               $calendarHTML = rtrim($calendarHTML);
               $calendarHTML .= '"';
            }

            //insert modal info if the day has an event
            if (in_array("eventday", $tdClasses)) {
               $calendarHTML .= ' data-toggle="modal" data-target="#eventModal-' . $eventId . '">' . date('j', $t);
            }
            else {
               $calendarHTML .= '>' . date('j', $t);
            }
            
            $calendarHTML .= '</td>'; //end cell
            $t += 86400; //move to the next day
         }
         else {
            $calendarHTML .= '<td class="emptyday">&nbsp;</td>'; //build an empty cell
            
         }
      }
      $calendarHTML .= '</tr>';
   }
   $calendarHTML .= '</tbody></table>'; //end build calendar
   
   //build modal dialog to display event details when user clicks on a calendar cell with an event attached
   foreach ($eventsArray as $ea) {
      $addressString = $ea[3] . ', ' . $ea[4] . ', ' . $ea[6] . ' ' . $ea[5];

      $modalHTML = '';
      $modalHTML .= '<div class="modal fade" id="eventModal-' . $ea[0] . '" tabindex="-1" role="dialog" aria-labelledby="modelLabel" aria-hidden="true">';
      $modalHTML .= '<div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
      $modalHTML .= '<div class="modal-content">';
      $modalHTML .= '<div class="modal-header">';
      $modalHTML .= '<h5 class="modal-title">Event title here</h5>';
      $modalHTML .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>';
      $modalHTML .= '</div>';

      $modalHTML .= '<div class="modal-body">';
      $modalHTML .= '<div class="row">';
      $modalHTML .= '<div class="col-lg-6">';
      $modalHTML .= '<p style="height: 400px; border: 2px solid #CEDADA">Insert an image here if needed...</p>';
      $modalHTML .= '<p style="height: 400px; border: 2px solid #CEDADA">Insert Google Map here if needed...</p>';
      $modalHTML .= '</div>';
      $modalHTML .= '<div class="col-lg-6">';
      $modalHTML .= '<h2>Topic</h2>';
      $modalHTML .= '<p>' . $ea[1] . '</p><hr>';
      $modalHTML .= '<h2>Address</h2>';
      $modalHTML .= '<p>' . $addressString . '</p><hr>';
      $modalHTML .= '<h2>Times</h2>';
      $modalHTML .= '<p>' . date('h:i A', strtotime($ea[7])) . ' - ' . date('h:i A', strtotime($ea[8])) . '</p>';
      $modalHTML .= '</div>';
      $modalHTML .= '</div>';
      $modalHTML .= '</div>';

      $modalHTML .= '</div>';
      $modalHTML .= '</div>';
      $modalHTML .= '</div>';

      array_push($modalHTMLarray, $modalHTML); //add modal content to an array
   }

   //add all modals to calendar output
   foreach ($modalHTMLarray as $mm) {
      $calendarHTML .= $mm;
   }
   //append any error message to calendar output
   $calendarHTML .= $errorHTML;
   return $calendarHTML;
}
