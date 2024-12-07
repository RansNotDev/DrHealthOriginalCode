<?php
session_start();
include('func1.php');
$con = mysqli_connect("localhost", "root", "", "myhmsdb");
$doctor = $_SESSION['dname'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['available_dates'])) {
  $dates = $_POST['available_dates'];

  // Clear previous entries
  mysqli_query($con, "DELETE FROM availabilitytb WHERE doctor = '$doctor'");

  foreach ($dates as $date) {
    $query = "INSERT INTO availabilitytb (doctor, available_date) VALUES ('$doctor', '$date')";
    mysqli_query($con, $query);
  }
  
  echo "<script>alert('Availability saved!'); window.location.href='doctor-panel.php';</script>";
} else {
  echo "<script>alert('No dates selected!'); window.location.href='doctor-panel.php';</script>";
}
?>
