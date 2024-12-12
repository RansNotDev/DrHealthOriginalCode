<?php
session_start();
include('func1.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
$doctor = $_SESSION['dname'];

$query = "SELECT available_date FROM availabilitytb WHERE doctor = '$doctor'";
$result = mysqli_query($con, $query);

$availability = [];
while ($row = mysqli_fetch_assoc($result)) {
  $availability[] = $row['available_date'];
}

echo json_encode($availability);
?>
