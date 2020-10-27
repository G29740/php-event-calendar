<?php
require_once 'dbconnection.php';
require_once 'calendar.php';

$m = date("m",time());
$y = date("Y",time());
if (! empty ( $_POST ['month'] )) {
   $m = $_POST ['month'];
}
if (! empty ( $_POST ['year'] )) {
   $y = $_POST ['year'];
}
echo getCalendar($con,$m,$y);
?>