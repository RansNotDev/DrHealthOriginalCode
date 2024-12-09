<?php
session_start();
require 'vendor/autoload.php'; // Include the Composer autoload file for PHPMailer
include('func1.php');
include('navbardoc.php');

// Check if the doctor name session variable is set
if (!isset($_SESSION['dname'])) {
  // Redirect to login page or show an error message
  header("Location: login.php");
  exit();
}

$con = new mysqli("localhost", "root", "", "myhmsdb");
$doctor = $_SESSION['dname'];

if ($con->connect_error) {
  die("Connection failed: " . $con->connect_error);
}

// Function to delete past availability dates
function deletePastAvailabilityDates($con, $doctor)
{
  $currentDate = date("Y-m-d");  // Get the current date

  // Prepare and execute the query to delete past availability dates
  $stmt = $con->prepare("DELETE FROM availabilitytb WHERE doctor = ? AND available_date < ?");
  $stmt->bind_param("ss", $doctor, $currentDate);
  $stmt->execute();
}

// Automatically delete past availability dates
deletePastAvailabilityDates($con, $doctor);

function updateAppointmentStatus($con, $id, $status)
{
  $stmt = $con->prepare("UPDATE appointmenttb SET doctorStatus = ? WHERE ID = ?");
  $stmt->bind_param("ii", $status, $id);
  return $stmt->execute();
}

function generateReferenceNumber()
{
  return strtoupper(bin2hex(random_bytes(8))); // Generate a 16-character unique reference number
}

function send_email($to, $subject, $message)
{
  $mail = new PHPMailer\PHPMailer\PHPMailer();
  try {
    //Server settings
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'lithiumsevidal@gmail.com';              // SMTP username
    $mail->Password   = 'rlvl grnz nfcn gfgd';                        // SMTP password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom('lithiumsevidal@gmail.com', 'D.R. Health Medical and Diagnostic Center');    // Set the sender's email address and name
    $mail->addAddress($to);                                     // Add a recipient

    // Content
    $mail->isHTML(true);                                        // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $message;

    $mail->send();
    return true;
  } catch (Exception $e) {
    return false;
  }
}

if (isset($_GET['cancel'])) {
  if (updateAppointmentStatus($con, $_GET['ID'], 0)) {
    echo "<script>alert('Your appointment successfully cancelled');</script>";
  }
}

if (isset($_GET['confirm'])) {
  $id = $_GET['ID'];

  // Generate the next queue number
  $result = $con->query("SELECT MAX(queue_number) AS max_queue FROM appointmenttb WHERE doctor = '$doctor'");
  $row = $result->fetch_assoc();
  $next_queue_number = $row['max_queue'] + 1;

  // Generate a unique reference number
  $reference_number = generateReferenceNumber();

  // Update appointment status, queue number, reference number, and userStatus
  $stmt = $con->prepare("UPDATE appointmenttb SET doctorStatus = 2, queue_number = ?, reference_number = ?, userStatus = 2 WHERE ID = ?");
  $stmt->bind_param("isi", $next_queue_number, $reference_number, $id);

  if ($stmt->execute()) {
    $stmt = $con->prepare("SELECT p.email FROM patreg p JOIN appointmenttb a ON p.pid = a.pid WHERE a.ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    $patientEmail = $patient['email'];
    $subject = "Appointment Confirmation";
    $message = "<html><body>";
    $message .= "<h1>Appointment Confirmation</h1>";
    $message .= "<p>Your appointment with $doctor has been confirmed. Your queue number is $next_queue_number and your reference number is $reference_number.</p>";
    $message .= "</body></html>";

    if (send_email($patientEmail, $subject, $message)) {
      echo "<script>alert('Appointment confirmed and email sent successfully');</script>";
    } else {
      echo "<script>alert('Appointment confirmed, but failed to send email');</script>";
    }
    header("Location: doctor-panel.php");
    exit();
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_time'])) {
  $start_time = date("H:i:s", strtotime($_POST['start_time']));
  $end_time = date("H:i:s", strtotime($_POST['end_time']));

  $stmt = $con->prepare("UPDATE doctb SET start_time = ?, end_time = ? WHERE username = ?");
  $stmt->bind_param("sss", $start_time, $end_time, $doctor);
  if ($stmt->execute()) {
    echo "<script>alert('Availability times updated successfully');</script>";
  } else {
    echo "<script>alert('Failed to update availability times');</script>";
  }
}

$stmt = $con->prepare("SELECT start_time, end_time FROM doctb WHERE username = ?");
$stmt->bind_param("s", $doctor);
$stmt->execute();
$result = $stmt->get_result();
$times = $result->fetch_assoc();
$start_time = date("h:i A", strtotime($times['start_time']));
$end_time = date("h:i A", strtotime($times['end_time']));

// Set default active division to 'dashboard' if not provided
$active_div = $_POST['active_div'] ?? 'dashboard';

$appointments_query = $con->prepare("SELECT * FROM appointmenttb WHERE doctor = ?");
$appointments_query->bind_param("s", $doctor);
$appointments_query->execute();
$appointments_results = $appointments_query->get_result();

$prescriptions_query = $con->prepare("SELECT * FROM prestb WHERE doctor = ?");
$prescriptions_query->bind_param("s", $doctor);
$prescriptions_query->execute();
$prescriptions_results = $prescriptions_query->get_result();

$patients_query = $con->prepare("SELECT a.pid, a.fname, a.lname, a.gender, a.email, a.contact, a.appdate
                                  FROM appointmenttb a
                                  JOIN patreg p ON a.pid = p.pid
                                  WHERE a.doctor = ? AND a.doctorStatus = 2");

$results_per_page = 10; // Number of results per page
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($current_page - 1) * $results_per_page;

$patients_query->bind_param("s", $doctor);
$patients_query->execute();
$patients_results = $patients_query->get_result();

$search_results = [];
if (isset($_POST['search_submit'])) {
  $pid = $_POST['pid'];
  $search_query = "";
  if ($active_div == "appointments") {
    $search_query = "SELECT * FROM appointmenttb WHERE pid = ? AND doctor = ?";
  } elseif ($active_div == "prescriptions") {
    $search_query = "SELECT pid, ID, fname, lname, appdate FROM prestb WHERE pid = ? AND doctor = ?";
  } elseif ($active_div == "patients") {
    $search_query = "SELECT a.pid, a.fname, a.lname, a.gender, a.email, a.contact, a.appdate
                          FROM appointmenttb a
                          JOIN patreg p ON a.pid = p.pid
                          WHERE a.pid = ? AND a.doctor = ?";
  }

  if (!empty($search_query)) {
    $stmt = $con->prepare($search_query);
    $stmt->bind_param("is", $pid, $doctor);
    $stmt->execute();
    $search_results = $stmt->get_result();
    if ($active_div == "appointments") {
      $appointments_results = $search_results;
    } elseif ($active_div == "prescriptions") {
      $prescriptions_results = $search_results;
    } elseif ($active_div == "patients") {
      $patients_results = $search_results;
    }
  }
}

function get_doctor_info($con)
{
  // Get the logged-in doctor username from the session
  $doctor = $_SESSION['dname'];

  // Prepare the SQL query to get doctor info
  $query = "SELECT first_name, middle_name, last_name, age, contact_number FROM doctb WHERE username = ?";
  $stmt = $con->prepare($query);
  $stmt->bind_param("s", $doctor);  // Bind the doctor's username
  $stmt->execute();
  $result = $stmt->get_result();

  // Check if doctor data is found
  if ($result->num_rows > 0) {
    return $result->fetch_assoc();  // Return the data as an associative array
  } else {
    return false;  // Return false if no data is found
  }
}
$doctor_info = get_doctor_info($con);
function update_doctor_info($con, $first_name, $middle_name, $last_name, $age, $contact_no, $new_password = null)
{
  // Get the logged-in doctor username from the session
  $doctor = $_SESSION['dname'];

  // Combine first, middle, and last names to create a new username
  $new_username = "Dr. " . $first_name . " " . $middle_name . " " . $last_name;

  if (!empty($new_password)) {
    // If a new password is provided, update it along with other details
    $query = "UPDATE doctb SET first_name = ?, middle_name = ?, last_name = ?, age = ?, contact_number = ?, password = ?, username = ? WHERE username = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $age, $contact_no, $new_password, $new_username, $doctor);
  } else {
    // If no new password is provided, exclude the password field
    $query = "UPDATE doctb SET first_name = ?, middle_name = ?, last_name = ?, age = ?, contact_number = ?, username = ? WHERE username = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sssssss", $first_name, $middle_name, $last_name, $age, $contact_no, $new_username, $doctor);
  }

  $stmt->execute();

  // Update session with the new username
  $_SESSION['dname'] = $new_username;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
  // Collect the form data
  $first_name = $_POST['first_name'];
  $middle_name = $_POST['middle_name'];
  $last_name = $_POST['last_name'];
  $age = $_POST['age'];
  $contact_no = $_POST['contact_no'];
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  // Check if the password fields are filled
  if (!empty($new_password) || !empty($confirm_password)) {
    // Validate that the new password and confirmation match
    if ($new_password !== $confirm_password) {
      echo "<script>alert('New password and confirm password do not match.');</script>";
    } else {
      // Fetch the current password from the database
      $stmt = $con->prepare("SELECT password FROM doctb WHERE username = ?");
      $stmt->bind_param("s", $_SESSION['dname']);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $stored_password = $row['password'];

      // Check if the current password matches
      if ($current_password !== $stored_password) {
        echo "<script>alert('Current password is incorrect.');</script>";
      } else {
        // Update doctor information, including the new password
        update_doctor_info(
          $con,
          $first_name,
          $middle_name,
          $last_name,
          $age,
          $contact_no,
          $new_password
        );
        echo "<script>alert('Doctor information updated successfully.');</script>";
      }
    }
  } else {
    // If password fields are empty, only update other information
    update_doctor_info(
      $con,
      $first_name,
      $middle_name,
      $last_name,
      $age,
      $contact_no
    );
    echo "<script>alert('Doctor information updated successfully.');</script>";
  }
}

// Get the logged-in doctor's name
$dname = $_SESSION['dname'];
// Initialize query
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])) {
  $pid = isset($_POST['pid']) ? $_POST['pid'] : '';
  $status_filter = isset($_POST['status_filter']) ? $_POST['status_filter'] : '';

  // Base query with doctor filter
  $query = "SELECT * FROM appointmenttb WHERE doctor = '$dname'";

  // Add filters
  if (!empty($pid)) {
    $query .= " AND pid LIKE '%$pid%'";
  }

  if (!empty($status_filter)) {
    if ($status_filter === 'Pending') {
      $query .= " AND userStatus = 1 AND doctorStatus = 1";
    } elseif ($status_filter === 'Confirmed') {
      $query .= " AND userStatus = 2 AND doctorStatus = 2";
    } elseif ($status_filter === 'Cancelled') {
      $query .= " AND ((userStatus = 0 AND doctorStatus = 1) OR (userStatus = 1 AND doctorStatus = 0))";
    }
  }

  // Execute query
  $appointments_results = mysqli_query($con, $query);
} else {
  // Default query for logged-in doctor's appointments
  $appointments_results = mysqli_query($con, "SELECT * FROM appointmenttb WHERE doctor = '$dname'");
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>D.R. Health Medical and Diagnostic Center</title>
  <link rel="shortcut icon" type="image/x-icon" href="./images/logo.png" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="./font-awesome/css/font-awesome.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.8.2/alpine.js" defer></script>
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

  <style>
    /* Custom Dark Pastel Color Palette */
    :root {
      --pastel-blue: #4a6fa5;
      --pastel-green: #88b04b;
      --pastel-purple: #6a4ca5;
      --pastel-orange: #e6955e;
      --pastel-gray: #c8c8c8;
      --dark-gray: #2e2e2e;
      --white: #FAF8F6;
    }

    /* Global Styling */
    body {
      background-color: var(--white);
      font-family: 'Arial', sans-serif;

      margin: 0;
      padding: 0;
    }

    /* Navigation Bar */
    .navbar {
      background-color: #0D409E;
      /* Set background to primary color (blue) */
      color: var(--white);
      /* Set text color to white */

    }

    /* Navigation links */
    .navbar a {
      color: var(--white);
      /* White text for navigation links */
      text-decoration: none;
      /* Remove underline */
      margin: 0 1rem;
      /* Add spacing between links */
      font-size: 1rem;
      /* Adjust font size */
      transition: opacity 0.3s ease;
      /* Smooth hover effect */
    }




    /* Sidebar */
    /* Sidebar Styles */
    .sidebar {
      background-color: #0D409E;
      color: var(--white);
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .sidebar button {
      display: block;
      color: var(--white);
      padding: 0.8rem 1.2rem;
      border: none;
      border-radius: 10px;
      margin-bottom: 0.5rem;
      transition: background-color 0.3s ease;
    }

    .sidebar button:hover {
      background-color: var(--pastel-green);
      color: var(--white);
    }

    /* Card Styles */
    .card {
      background-color: var(--white);
      border-radius: 15px;
      border: 1px solid var(--pastel-gray);
      padding: 1.5rem;
      box-shadow: 0 6px 10px var(--shadow-light);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px var(--shadow-dark);
      color: #0D409E;
    }

    .card h5 {
      font-size: 1.5rem;
      color: var(--dark-gray);
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .card span {
      font-size: 3rem;
      color: #0D409E;
    }

    .card-icon {
      font-size: 3rem;
      color: #0D409E;
      margin-bottom: -1px;
    }

    /* Button Styles */
    .btn-blue {
      background-color: #0D409E;
      color: var(--white);
      border-radius: 10px;
      padding: 0.8rem 1.2rem;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn-blue:hover {
      background-color: var(--pastel-purple);
    }

    .btn-green {
      background-color: var(--pastel-green);
      color: var(--white);
      border-radius: 10px;
      padding: 0.8rem 1.2rem;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .btn-green:hover {
      background-color: var(--pastel-orange);
    }

    /* Table Container */
    .table-container {
      max-height: 400px;
      overflow-y: auto;
      overflow-x: hidden;
      border: 1px solid var(--pastel-gray);
      border-radius: 10px;
      box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    /* Table Header */
    table thead {
      background-color: #0D409E;
      color: var(--white);
      position: sticky;
      top: 0;
      z-index: 2;
    }

    table thead th {
      padding: 1rem;
      text-align: left;
      font-size: 1.1rem;
      font-weight: 700;
      border-bottom: 3px solid var(--pastel-gray);
    }

    /* Table Rows */
    table tbody tr:nth-child(odd) {
      background-color: #f9f9f9;
    }

    table tbody tr:nth-child(even) {
      background-color: #ffffff;
    }

    table tbody tr:hover {
      background-color: #e8f4f8;
    }

    table tbody td {
      padding: 1rem;
      font-size: 0.95rem;
      color: var(--dark-gray);
    }

    /* Scrollbar Customization */
    .table-container::-webkit-scrollbar {
      width: 8px;
    }

    .table-container::-webkit-scrollbar-thumb {
      background-color: #d3d3d3;
      border-radius: 4px;
    }

    .table-container::-webkit-scrollbar-thumb:hover {
      background-color: #a9a9a9;
    }

    /* Link Styling */
    a {
      color: var(--dark-gray);
      text-decoration: none;
    }

    a:hover {
      color: var(--white);
      background-color: #88b04b;
    }
  </style>


</head>

<body class="bg-light-bg">
  <div class="flex h-screen space-x-6 p-4">
    <!-- Sidebar -->
    <div class="sidebar w-64 p-6 space-y-6 hidden sm:block md:block">
      <div class="text-center text-2xl font-bold">D.R. Health Medical and Diagnostic Center</div>
      <nav>
        <button onclick="showDiv('dashboard')" class="block py-2 px-4 rounded hover:bg-pastel-green">
          <i class="fa fa-tachometer-alt"></i> Dashboard
        </button>
        <button onclick="showDiv('appointments')" class="block py-2 px-4 rounded hover:bg-pastel-green">
          <i class="fa fa-calendar-alt"></i> Appointments
        </button>
        <button onclick="showDiv('prescriptions')" class="block py-2 px-4 rounded hover:bg-pastel-green">
          <i class="fa fa-file-prescription"></i> Prescription List
        </button>
        <button onclick="showDiv('patients')" class="block py-2 px-4 rounded hover:bg-pastel-green">
          <i class="fa fa-user"></i> Patient List
        </button>
        <button onclick="showDiv('availability')" class="block py-2 px-4 rounded hover:bg-pastel-green">
          <i class="fa fa-clock"></i> Availability
        </button>
        <button onclick="showDiv('profile')" class="block py-2 px-4 rounded hover:bg-pastel-green">
          <i class="fa fa-clock"></i> Profile
        </button>
      </nav>
    </div>

    <!-- Main content -->

    <div class="flex-1 overflow-y-auto">
      <div class="text-left text-2xl font-bold">Welcome <?php echo $_SESSION['dname']; ?>!</div>

      <div id="dashboard" class="<?php echo $active_div == 'dashboard' ? '' : 'hidden'; ?>">
        <div class="content-wrapper">
          <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="card">
              <span class="card-icon"><i class="fa fa-calendar"></i></span>
              <h3 class="card-title text-xl font-bold mb-2">View Appointments</h3>
              <p><button onclick="showDiv('appointments')" class="btn-blue text-white px-4 py-2">Appointment List</button></p>
            </div>
            <div class="card">
              <span class="card-icon"><i class="fa fa-prescription-bottle-alt"></i></span>
              <h3 class="card-title text-xl font-bold mb-2">Prescriptions</h3>
              <p><button onclick="showDiv('prescriptions')" class="btn-blue text-white px-4 py-2">Prescription List</button></p>
            </div>
            <div class="card">
              <span class="card-icon"><i class="fa fa-user"></i></span>
              <h3 class="card-title text-xl font-bold mb-2">Patient List</h3>
              <p><button onclick="showDiv('patients')" class="btn-blue text-white px-4 py-2">Patient List</button></p>
            </div>
            <div class="card">
              <span class="card-icon"><i class="fa fa-clock"></i></span>
              <h3 class="card-title text-xl font-bold mb-2">Availability</h3>
              <p><button onclick="showDiv('availability')" class="btn-blue text-white px-4 py-2">Set Availability</button></p>
            </div>
          </div>
        </div>
      </div>

      <div id="appointments" class="<?php echo $active_div == 'appointments' ? '' : 'hidden'; ?> mt-8">
        <form class="flex mb-4" method="post" action="">
          <input type="hidden" name="active_div" value="appointments">

          <!-- Patient ID Search -->
          <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Enter the Patient ID" name="pid" value="<?php echo isset($_POST['pid']) ? $_POST['pid'] : ''; ?>">

          <!-- Appointment Status Filter -->
          <select name="status_filter" class="form-input mr-2 p-2 border rounded">
            <option value="">All Statuses</option>
            <option value="Pending" <?php echo isset($_POST['status_filter']) && $_POST['status_filter'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Confirmed" <?php echo isset($_POST['status_filter']) && $_POST['status_filter'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
            <option value="Cancelled" <?php echo isset($_POST['status_filter']) && $_POST['status_filter'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
          </select>

          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" name="search_submit" style="background-color: #0D409E;">Search</button>
        </form>

        <!-- Table -->
        <table class="table-auto w-full">
          <thead>
            <tr>
              <th class="py-2 px-4">Queue Number</th>
              <th class="py-2 px-4">Reference Number</th>
              <th class="py-2 px-4">Patient ID</th>
              <th class="py-2 px-4">First Name</th>
              <th class="py-2 px-4">Last Name</th>
              <th class="py-2 px-4">Appointment Date</th>
              <th class="py-2 px-4">Appointment Time</th>
              <th class="py-2 px-4">Current Status</th>
              <th class="py-2 px-4">Action</th>
              <th class="py-2 px-4">Prescribe</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_array($appointments_results)): ?>
              <tr class="border-b">
                <td class="py-2 px-4"><?php echo $row['queue_number']; ?></td>
                <td class="py-2 px-4"><?php echo $row['reference_number']; ?></td>
                <td class="py-2 px-4"><?php echo $row['pid']; ?></td>
                <td class="py-2 px-4"><?php echo $row['fname']; ?></td>
                <td class="py-2 px-4"><?php echo $row['lname']; ?></td>
                <td class="py-2 px-4"><?php echo $row['appdate']; ?></td>
                <td class="py-2 px-4"><?php echo date("g:i A", strtotime($row['apptime'])); ?></td>
                <td class="py-2 px-4">
                  <?php
                  if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 1)) {
                    echo "<strong>Pending</strong>";
                  } elseif (($row['userStatus'] == 0) && ($row['doctorStatus'] == 1)) {
                    echo "<strong>Cancelled by Patient</strong>";
                  } elseif (($row['userStatus'] == 1) && ($row['doctorStatus'] == 0)) {
                    echo "<strong>Cancelled by You</strong>";
                  } elseif (($row['userStatus'] == 2) && ($row['doctorStatus'] == 2)) {
                    echo "<strong>Confirmed</strong>";
                  }
                  ?>
                </td>

                <td class="py-2 px-4">
                  <?php
                  if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 1)) {
                    $query_prescription = "SELECT * FROM prestb WHERE ID = '" . $row['ID'] . "'";
                    $result_prescription = mysqli_query($con, $query_prescription);
                    if (mysqli_num_rows($result_prescription) == 0) {
                  ?>
                      <div class="inline-flex space-x-2">
                        <a href="doctor-panel.php?ID=<?php echo $row['ID']; ?>&cancel=update"
                          onClick="return confirm('Are you sure you want to cancel this appointment?')"
                          class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700">
                          <i class="fas fa-times"></i> <!-- Cancel Icon -->
                        </a>

                        <a href="doctor-panel.php?ID=<?php echo $row['ID']; ?>&confirm=update"
                          onClick="return confirm('Are you sure you want to confirm this appointment?')"
                          class="bg-green-600 text-white p-2 rounded-full hover:bg-green-700">
                          <i class="fas fa-check"></i> <!-- Confirm Icon -->
                        </a>
                      </div>
                  <?php } else {
                      echo "-";
                    }
                  } elseif ($row['doctorStatus'] == 2) {
                    echo "-";
                  } else {
                    echo "Cancelled";
                  } ?>
                </td>



                <td class="py-2 px-4">
                  <?php
                  if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 1)) { // Appointment pending
                    $query_prescription = "SELECT * FROM prestb WHERE ID = '" . $row['ID'] . "'";
                    $result_prescription = mysqli_query($con, $query_prescription);

                    if (mysqli_num_rows($result_prescription) > 0) {
                      echo "<strong>PRESCRIBED</strong>";
                    } else {
                      // Disabled button if doctor has not confirmed
                      echo '<button class="bg-green-600 text-white px-4 py-2 rounded opacity-50 cursor-not-allowed" disabled>Prescribe</button>';
                    }
                  } elseif ($row['doctorStatus'] == 2) { // Appointment confirmed
                    $query_prescription = "SELECT * FROM prestb WHERE ID = '" . $row['ID'] . "'";
                    $result_prescription = mysqli_query($con, $query_prescription);

                    if (mysqli_num_rows($result_prescription) > 0) {
                      echo "<strong>PRESCRIBED</strong>";
                    } else {
                      // Enabled button if doctor has confirmed
                      echo '<a href="prescribe.php?pid=' . $row['pid'] . '&ID=' . $row['ID'] . '&fname=' . $row['fname'] . '&lname=' . $row['lname'] . '&appdate=' . $row['appdate'] . '&apptime=' . $row['apptime'] . '" class="bg-green-600 text-white px-4 py-2 rounded">Prescribe</a>';
                    }
                  } else {
                    echo "-";
                  }
                  ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <div id="prescriptions" class="<?php echo $active_div == 'prescriptions' ? '' : 'hidden'; ?> mt-8">

        <form class="flex mb-4" method="post" action="">
          <input type="hidden" name="active_div" value="prescriptions">
          <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Enter the Patient ID" name="pid">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" name="search_submit" style="background-color: #0D409E" ;>Search</button>
        </form>
        <table class="table-auto w-full">
          <thead>
            <tr>
              <th class="py-2 px-4">Patient ID</th>
              <th class="py-2 px-4">First Name</th>
              <th class="py-2 px-4">Last Name</th>
              <th class="py-2 px-4">Appointment Date</th>
              <th class="py-2 px-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_array($prescriptions_results)): ?>
              <tr class="border-b">
                <td class="py-2 px-4"><?php echo $row['pid']; ?></td>
                <td class="py-2 px-4"><?php echo $row['fname']; ?></td>
                <td class="py-2 px-4"><?php echo $row['lname']; ?></td>
                <td class="py-2 px-4"><?php echo $row['appdate']; ?></td>
                <td class="py-2 px-4">
                  <a href="view-pres.php?id=<?php echo $row['ID']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded" target="_blank">View</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

      </div>

      <div id="patients" class="<?php echo $active_div == 'patients' ? '' : 'hidden'; ?> mt-8">

        <form class="flex mb-4" method="post" action="">
          <input type="hidden" name="active_div" value="patients">
          <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Enter the Patient ID" name="pid">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" name="search_submit" style="background-color: #0D409E" ;>Search</button>
        </form>
        <table class="table-auto w-full">
          <thead>
            <tr>
              <th class="py-2 px-4">Patient ID</th>
              <th class="py-2 px-4">First Name</th>
              <th class="py-2 px-4">Last Name</th>
              <th class="py-2 px-4">Gender</th>
              <th class="py-2 px-4">Email</th>
              <th class="py-2 px-4">Contact</th>
              <th class="py-2 px-4">Appointment Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($patients_results)): ?>
              <tr class="border-b">
                <td class="py-2 px-4"><?php echo $row['pid']; ?></td>
                <td class="py-2 px-4"><?php echo $row['fname']; ?></td>
                <td class="py-2 px-4"><?php echo $row['lname']; ?></td>
                <td class="py-2 px-4"><?php echo $row['gender']; ?></td>
                <td class="py-2 px-4"><?php echo $row['email']; ?></td>
                <td class="py-2 px-4"><?php echo $row['contact']; ?></td>
                <td class="py-2 px-4"><?php echo $row['appdate']; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <div id="availability" class="<?php echo $active_div == 'availability' ? '' : 'hidden'; ?> mt-8">
        <h2 class="text-2xl font-bold mb-4">Set Your Availability</h2>
        <form method="post" action="">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
              <input type="text" name="start_time" id="start_time" value="<?php echo $start_time; ?>" class="form-input mt-1 block w-full p-2 border rounded" placeholder="08:00 AM">
            </div>
            <div>
              <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
              <input type="text" name="end_time" id="end_time" value="<?php echo $end_time; ?>" class="form-input mt-1 block w-full p-2 border rounded" placeholder="05:00 PM">
            </div>
          </div>
          <button type="submit" name="update_time" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Update Time</button>
        </form>
        <div class="flex justify-between mb-4 mt-8">
          <button id="prevMonth" class="bg-blue-600 text-white px-4 py-2 rounded">Previous</button>
          <h3 id="currentMonth" class="text-xl font-bold"></h3>
          <button id="nextMonth" class="bg-blue-600 text-white px-4 py-2 rounded">Next</button>
        </div>
        <form method="post" action="save_availability.php">
          <div id="calendar" class="grid grid-cols-7 gap-2"></div>
          <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Save Availability</button>
        </form>
      </div>

      <div id="profile" class="<?php echo $active_div == 'profile' ? '' : 'hidden'; ?> mt-8">
        <form method="post" action="">
          <div class="grid grid-cols-2 gap-4">
            <!-- First Name -->
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
              <input type="text" name="first_name" id="first_name" class="form-input mt-1 block w-full p-2 border rounded" placeholder="First Name" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" value="<?= $doctor_info['first_name'] ?? ''; ?>">
            </div>

            <!-- Middle Name -->
            <div>
              <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
              <input type="text" name="middle_name" id="middle_name" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Middle Name" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" value="<?= $doctor_info['middle_name'] ?? ''; ?>">
            </div>

            <!-- Last Name -->
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
              <input type="text" name="last_name" id="last_name" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Last Name" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" value="<?= $doctor_info['last_name'] ?? ''; ?>">
            </div>

            <!-- Age -->
            <div>
              <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
              <input type="number" name="age" id="age" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Age" min="0" value="<?= $doctor_info['age'] ?? ''; ?>">
            </div>

            <!-- Contact No. -->
            <div>
              <label for="contact_no" class="block text-sm font-medium text-gray-700">Contact No.</label>
              <input type="tel" name="contact_no" id="contact_no" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Contact No." pattern="\d{11}" title="Enter a valid 11-digit phone number" value="<?= $doctor_info['contact_number'] ?? ''; ?>">
            </div>

            <!-- Current Password -->
            <div>
              <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
              <input type="password" name="current_password" id="current_password" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Current Password">
            </div>

            <!-- New Password -->
            <div>
              <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
              <input type="password" name="new_password" id="new_password" class="form-input mt-1 block w-full p-2 border rounded" placeholder="New Password">
            </div>

            <!-- Confirm Password -->
            <div>
              <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
              <input type="password" name="confirm_password" id="confirm_password" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Confirm Password">
            </div>
          </div>

          <button type="submit" name="update_info" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">Update Info</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Structure -->
  <div id="loadingModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded shadow-lg flex flex-col items-center">
      <img src="images/loading.gif" alt="Loading" class="w-16 h-16 mb-4">
      <p class="text-lg font-semibold">Sending email, please wait...</p>
    </div>
  </div>

  <script>
    function showLoadingModal() {
      document.getElementById('loadingModal').classList.remove('hidden');
    }

    function hideLoadingModal() {
      document.getElementById('loadingModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
      const confirmButtons = document.querySelectorAll('a[href*="&confirm=update"]');

      confirmButtons.forEach(button => {
        button.addEventListener('click', function(event) {
          event.preventDefault();
          showLoadingModal();

          // Execute the original link action after a slight delay to allow the modal to show
          setTimeout(() => {
            window.location.href = this.href;
          }, 100);
        });
      });
    });
  </script>


  <script>
    function showDiv(divId) {
      const divs = document.querySelectorAll('.flex-1 > div');
      divs.forEach(div => {
        div.classList.add('hidden');
      });
      document.getElementById(divId).classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      const currentMonthEl = document.getElementById('currentMonth');
      const prevMonthBtn = document.getElementById('prevMonth');
      const nextMonthBtn = document.getElementById('nextMonth');

      let currentDate = new Date();

      function fetchAvailability(callback) {
        fetch('fetch_availability.php')
          .then(response => response.json())
          .then(data => {
            callback(data);
          });
      }

      function renderCalendar(date, availability) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        calendarEl.innerHTML = '';
        currentMonthEl.textContent = `${monthNames[month]} ${year}`;

        dayNames.forEach(day => {
          const dayEl = document.createElement('div');
          dayEl.className = 'font-bold text-center';
          dayEl.textContent = day;
          calendarEl.appendChild(dayEl);
        });

        for (let i = 0; i < firstDayOfMonth; i++) {
          const emptyCell = document.createElement('div');
          emptyCell.className = 'bg-gray-200';
          calendarEl.appendChild(emptyCell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
          const dateValue = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
          const dayEl = document.createElement('div');
          dayEl.className = 'bg-white p-2 rounded shadow text-center'; // Adjusted padding
          const label = document.createElement('label');
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = 'available_dates[]';
          checkbox.value = dateValue;
          if (availability.includes(dateValue)) {
            checkbox.checked = true;
          }
          label.appendChild(checkbox);
          label.appendChild(document.createTextNode(` ${day}`));
          dayEl.appendChild(label);
          calendarEl.appendChild(dayEl);
        }
      }

      prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        fetchAvailability(availability => {
          renderCalendar(currentDate, availability);
        });
      });

      nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        fetchAvailability(availability => {
          renderCalendar(currentDate, availability);
        });
      });

      fetchAvailability(availability => {
        renderCalendar(currentDate, availability);
      });
    });
  </script>

</body>

</html>