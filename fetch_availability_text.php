<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('func.php');

header('Content-Type: application/json');

$con = mysqli_connect("localhost", "root", "", "myhmsdb");

if (!$con) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if (!isset($_GET['doctor'])) {
    echo json_encode(['error' => 'Doctor not specified']);
    exit;
}

$doctor = mysqli_real_escape_string($con, $_GET['doctor']);
$appdate = isset($_GET['appdate']) ? mysqli_real_escape_string($con, $_GET['appdate']) : null;

// Fetching doctor's start and end time
$time_query = "SELECT start_time, end_time FROM doctb WHERE username = '$doctor'";
$time_result = mysqli_query($con, $time_query);

if (!$time_result || mysqli_num_rows($time_result) == 0) {
    echo json_encode(['error' => 'Doctor time query failed or doctor not found']);
    exit;
}

$time_row = mysqli_fetch_assoc($time_result);
$start_time = date("g:i A", strtotime($time_row['start_time']));
$end_time = date("g:i A", strtotime($time_row['end_time']));

// Fetching availability dates
$query = "SELECT available_date FROM availabilitytb WHERE doctor = '$doctor'";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($con)]);
    exit;
}

$availability = [];
while ($row = mysqli_fetch_assoc($result)) {
    $availability[] = $row['available_date'];
}

// Fetching booked times for the selected date
$booked_times = [];
if ($appdate) {
    $booked_query = "SELECT apptime FROM appointmenttb WHERE doctor = '$doctor' AND appdate = '$appdate'";
    $booked_result = mysqli_query($con, $booked_query);

    while ($row = mysqli_fetch_assoc($booked_result)) {
        $booked_times[] = date("g:i A", strtotime($row['apptime']));
    }
}

$response = [
    'availability' => count($availability) > 0 ? $availability : ['No availability for this doctor.'],
    'start_time' => $start_time,
    'end_time' => $end_time,
    'booked_times' => $booked_times
];

echo json_encode($response);
?>
