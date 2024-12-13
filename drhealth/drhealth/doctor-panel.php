<?php
session_start();

// Check if the doctor name session variable is set
if (!isset($_SESSION['dname'])) {
  // Redirect to login page or show an error message
  header("Location: doctor.php");
  exit();
}

require 'vendor/autoload.php'; // Include the Composer autoload file for PHPMailer
include('func1.php');
include('navbardoc.php');



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
  // Ensure 'pid' is set before using it
  $pid = isset($_POST['pid']) ? $_POST['pid'] : '';  // Default to empty string if not set
  $search_query = "";

  // Check if doctor session is set
  if (!isset($_SESSION['dname'])) {
    header("Location: doctor.php");
    exit();
  }
  $doctor = $_SESSION['dname'];

  // Determine the query based on active_div
  if ($active_div == "appointments") {
    $search_query = "SELECT * FROM appointmenttb WHERE pid = ? AND doctor = ?";
  } elseif ($active_div == "prescriptions") {
    // Check if the pid is provided for filtering
    if (!empty($pid)) {
      // If patient ID is provided, filter by both patient ID and doctor
      $search_query = "SELECT pid, ID, fname, lname, appdate FROM prestb WHERE pid = ? AND doctor = ?";
    } else {
      // If no patient ID is provided, filter only by doctor
      $search_query = "SELECT pid, ID, fname, lname, appdate FROM prestb WHERE doctor = ?";
    }
  } elseif ($active_div == "patients") {
    $search_query = "SELECT a.pid, a.fname, a.lname, a.gender, a.email, a.contact, a.appdate
                     FROM appointmenttb a
                     JOIN patreg p ON a.pid = p.pid
                     WHERE a.doctor = ?";
  }

  // Prepare the query
  if ($search_query) {
    if ($stmt = $con->prepare($search_query)) {
      // Bind parameters and execute
      if (!empty($pid)) {
        // If pid is provided, bind both pid and doctor
        $stmt->bind_param('ss', $pid, $doctor); // 'ss' means two string parameters
      } else {
        // Only bind the doctor parameter if pid is empty
        $stmt->bind_param('s', $doctor); // 's' means one string parameter (doctor)
      }

      // Execute the query
      $stmt->execute();

      // Get the result
      $result = $stmt->get_result();

      // Store the results in an array
      while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
      }

      // Close the prepared statement
      $stmt->close();
    } else {
      echo "Error: Unable to prepare the query.";
    }
  }
}
// Assuming the connection to the database is already established
function get_doctor_info($con)
{
  $doctor = $_SESSION['dname'];
  $query = "SELECT first_name, middle_name, last_name, age, contact_number FROM doctb WHERE username = ?";
  $stmt = $con->prepare($query);
  $stmt->bind_param("s", $doctor);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    return $result->fetch_assoc();
  } else {
    return false;
  }
}
$doctor_info = get_doctor_info($con);
function update_doctor_info($con, $first_name, $middle_name, $last_name, $age, $contact_no, $new_password = null)
{
  $doctor = $_SESSION['dname'];
  $new_username = "Dr. " . $first_name . " " . $middle_name . " " . $last_name;

  if (substr($contact_no, 0, 3) !== '+639') {
    $contact_no = '+639' . $contact_no;
  }

  if (!empty($new_password)) {
    $query = "UPDATE doctb SET first_name = ?, middle_name = ?, last_name = ?, age = ?, contact_number = ?, password = ?, username = ? WHERE username = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $age, $contact_no, $new_password, $new_username, $doctor);
  } else {
    $query = "UPDATE doctb SET first_name = ?, middle_name = ?, last_name = ?, age = ?, contact_number = ?, username = ? WHERE username = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sssssss", $first_name, $middle_name, $last_name, $age, $contact_no, $new_username, $doctor);
  }

  if ($stmt->execute()) {
    $_SESSION['dname'] = $new_username;
    return true;
  } else {
    return false;
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
  $first_name = $_POST['first_name'];
  $middle_name = $_POST['middle_name'];
  $last_name = $_POST['last_name'];
  $age = $_POST['age'];
  $contact_no = $_POST['contact_no'];
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  if (!empty($new_password) || !empty($confirm_password)) {
    if ($new_password !== $confirm_password) {
      $_SESSION['alert_message'] = "New password and confirm password do not match.";
    } else {
      $stmt = $con->prepare("SELECT password FROM doctb WHERE username = ?");
      $stmt->bind_param("s", $_SESSION['dname']);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $stored_password = $row['password'];

      if (!password_verify($current_password, $stored_password)) {
        $_SESSION['alert_message'] = "Current password is incorrect.";
      } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        if (update_doctor_info(
          $con,
          $first_name,
          $middle_name,
          $last_name,
          $age,
          $contact_no,
          $hashed_password
        )) {
          $_SESSION['alert_message'] = "Doctor information updated successfully.";
        } else {
          $_SESSION['alert_message'] = "Error updating doctor information.";
        }
      }
    }
  } else {
    if (update_doctor_info(
      $con,
      $first_name,
      $middle_name,
      $last_name,
      $age,
      $contact_no
    )) {
      $_SESSION['alert_message'] = "Doctor information updated successfully.";
    } else {
      $_SESSION['alert_message'] = "Error updating doctor information.";
    }
  }

  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
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

          <!-- Search Field -->
          <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Search by Queue Number, Reference Number, First Name, or Last Name" name="search_term" value="<?php echo isset($_POST['search_term']) ? $_POST['search_term'] : ''; ?>">

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
            <?php
            // Capture search and filter inputs
            $searchTerm = isset($_POST['search_term']) ? mysqli_real_escape_string($con, $_POST['search_term']) : '';
            $statusFilter = isset($_POST['status_filter']) ? mysqli_real_escape_string($con, $_POST['status_filter']) : '';

            // Base query to fetch appointments for the logged-in doctor
            $doctor = $_SESSION['dname']; // Logged-in doctor's name
            $query = "SELECT * FROM appointmenttb WHERE doctor = '$doctor'"; // Filter by doctor name

            // Conditions for search and filter
            $conditions = [];
            if (!empty($searchTerm)) {
              $conditions[] = "(queue_number = '$searchTerm' OR pid = '$searchTerm' OR reference_number = '$searchTerm' OR fname = '$searchTerm' OR lname = '$searchTerm')";
            }
            if (!empty($statusFilter)) {
              if ($statusFilter == "Pending") {
                $conditions[] = "(userStatus = 1 AND doctorStatus = 1)";
              } elseif ($statusFilter == "Confirmed") {
                $conditions[] = "(userStatus = 2 AND doctorStatus = 2)";
              } elseif ($statusFilter == "Cancelled") {
                $conditions[] = "(userStatus = 0 OR doctorStatus = 0)";
              }
            }

            // Append conditions to query if necessary
            if (count($conditions) > 0) {
              $query .= " AND " . implode(" AND ", $conditions);
            }

            // Execute query
            $appointments_results = mysqli_query($con, $query);

            // Display results
            if (mysqli_num_rows($appointments_results) > 0) {
              while ($row = mysqli_fetch_array($appointments_results)) {
                echo "<tr class='border-b'>
                <td class='py-2 px-4'>{$row['queue_number']}</td>
                <td class='py-2 px-4'>{$row['reference_number']}</td>
                <td class='py-2 px-4'>{$row['pid']}</td>
                <td class='py-2 px-4'>{$row['fname']}</td>
                <td class='py-2 px-4'>{$row['lname']}</td>
                <td class='py-2 px-4'>{$row['appdate']}</td>
                <td class='py-2 px-4'>" . date("g:i A", strtotime($row['apptime'])) . "</td>
                <td class='py-2 px-4'>";

                if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 1)) {
                  echo "<strong>Pending</strong>";
                } elseif (($row['userStatus'] == 0) || ($row['doctorStatus'] == 0)) {
                  echo "<strong>Cancelled</strong>";
                } elseif (($row['userStatus'] == 2) && ($row['doctorStatus'] == 2)) {
                  echo "<strong>Confirmed</strong>";
                }

                echo "</td>";

                // Add action and prescribe columns
                echo "<td class='py-2 px-4'>Action Logic Here</td>
                  <td class='py-2 px-4'>Prescribe Logic Here</td>
              </tr>";
              }
            } else {
              echo "<tr><td colspan='10' class='text-center py-4'>No records found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
      <div id="prescriptions" class="<?php echo $active_div == 'prescriptions' ? '' : 'hidden'; ?> mt-8">
        <form class="flex mb-4" method="post" action="">
          <input type="hidden" name="active_div" value="prescriptions">
          <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Enter the Patient ID, First Name, or Last Name" name="search_term">
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
            <?php
            $search_results = [];
            if (isset($_POST['search_submit'])) {
              // Ensure that the search term is set
              $searchTerm = isset($_POST['search_term']) ? mysqli_real_escape_string($con, $_POST['search_term']) : '';
              $search_query = "SELECT pid, ID, fname, lname, appdate FROM prestb WHERE doctor = ?";

              // Add search conditions for pid, fname, or lname
              if (!empty($searchTerm)) {
                $search_query .= " AND (pid = ? OR fname = ? OR lname = ?)";
              }

              if ($stmt = $con->prepare($search_query)) {
                if (!empty($searchTerm)) {
                  // Bind parameters for doctor, pid, fname, and lname
                  $stmt->bind_param('ssss', $doctor, $searchTerm, $searchTerm, $searchTerm);
                } else {
                  // Bind parameters for doctor when no search term is provided
                  $stmt->bind_param('s', $doctor);
                }
                $stmt->execute();

                // Get the result
                $result = $stmt->get_result();

                // Store the results in an array
                while ($row = $result->fetch_assoc()) {
                  $search_results[] = $row;
                }

                // Close the prepared statement
                $stmt->close();
              }
            } else {
              // If no search term, fetch all prescriptions for the doctor
              $query = "SELECT pid, ID, fname, lname, appdate FROM prestb WHERE doctor = ?";
              if ($stmt = $con->prepare($query)) {
                $stmt->bind_param('s', $doctor);
                $stmt->execute();
                $result = $stmt->get_result();

                // Store the results in an array
                while ($row = $result->fetch_assoc()) {
                  $search_results[] = $row;
                }

                // Close the prepared statement
                $stmt->close();
              }
            }

            // Display results
            if (count($search_results) > 0) {
              foreach ($search_results as $row) {
                echo "<tr class='border-b'>
                        <td class='py-2 px-4'>{$row['pid']}</td>
                        <td class='py-2 px-4'>{$row['fname']}</td>
                        <td class='py-2 px-4'>{$row['lname']}</td>
                        <td class='py-2 px-4'>{$row['appdate']}</td>
                        <td class='py-2 px-4'>
                            <a href='view-pres.php?id={$row['ID']}' class='bg-blue-600 text-white px-4 py-2 rounded' target='_blank'>View</a>
                        </td>
                    </tr>";
              }
            } else {
              echo "<tr><td colspan='5' class='text-center py-4'>No records found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <div id="patients" class="<?php echo $active_div == 'patients' ? '' : 'hidden'; ?> mt-8">

        <form class="flex mb-4" method="post" action="">
          <input type="hidden" name="active_div" value="patients">
          <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Enter the Patient ID, First Name, Last Name, or Email" name="search_term">
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
            <?php
            // Initialize the search results array
            $patients_results = [];

            if (isset($_POST['search_submit'])) {
              // Capture the search term
              $searchTerm = isset($_POST['search_term']) ? mysqli_real_escape_string($con, $_POST['search_term']) : '';
              $search_query = "SELECT a.pid, a.fname, a.lname, a.gender, a.email, a.contact, a.appdate
                       FROM appointmenttb a
                       JOIN patreg p ON a.pid = p.pid
                       WHERE a.doctor = ?";

              // If search term is provided, add conditions for exact matches of pid, fname, lname, or email
              if (!empty($searchTerm)) {
                $search_query .= " AND (a.pid = ? OR a.fname = ? OR a.lname = ? OR p.email = ?)";
              }

              // Prepare the query
              if ($stmt = $con->prepare($search_query)) {
                // Bind parameters based on whether search term is provided
                if (!empty($searchTerm)) {
                  // Bind parameters for exact matches: doctor, pid, fname, lname, email
                  $stmt->bind_param('sssss', $doctor, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                } else {
                  // Bind parameters for doctor only (when no search term is entered)
                  $stmt->bind_param('s', $doctor);
                }

                // Execute the query
                $stmt->execute();

                // Get the result
                $result = $stmt->get_result();

                // Store the results in an array
                while ($row = $result->fetch_assoc()) {
                  $patients_results[] = $row;
                }

                // Close the prepared statement
                $stmt->close();
              }
            } else {
              // If no search term, fetch all patients for the doctor
              $query = "SELECT a.pid, a.fname, a.lname, a.gender, a.email, a.contact, a.appdate
                FROM appointmenttb a
                JOIN patreg p ON a.pid = p.pid
                WHERE a.doctor = ?";
              if ($stmt = $con->prepare($query)) {
                $stmt->bind_param('s', $doctor);
                $stmt->execute();
                $result = $stmt->get_result();

                // Store the results in an array
                while ($row = $result->fetch_assoc()) {
                  $patients_results[] = $row;
                }

                // Close the prepared statement
                $stmt->close();
              }
            }

            // Display results
            if (count($patients_results) > 0) {
              foreach ($patients_results as $row) {
                echo "<tr class='border-b'>
                <td class='py-2 px-4'>{$row['pid']}</td>
                <td class='py-2 px-4'>{$row['fname']}</td>
                <td class='py-2 px-4'>{$row['lname']}</td>
                <td class='py-2 px-4'>{$row['gender']}</td>
                <td class='py-2 px-4'>{$row['email']}</td>
                <td class='py-2 px-4'>{$row['contact']}</td>
                <td class='py-2 px-4'>{$row['appdate']}</td>
            </tr>";
              }
            } else {
              echo "<tr><td colspan='7' class='text-center py-4'>No records found</td></tr>";
            }
            ?>
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
      <?php if (isset($_SESSION['alert_message'])): ?>
        <script>
          alert('<?php echo $_SESSION['alert_message']; ?>');
        </script>
        <?php unset($_SESSION['alert_message']); ?>
      <?php endif; ?>
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
              <div class="relative">
                <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-700">+639 <span class="text-gray-500">|</span> </span>
                <input type="tel" name="contact_no" id="contact_no" class="form-input mt-1 block w-full pl-14 p-2 border rounded" placeholder="Enter Contact No." pattern="\d{9}" maxlength="9" minlength="9" title="Enter a valid 9-digit phone number" value="<?= substr($doctor_info['contact_number'], 4); ?>">
              </div>
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

        const today = new Date(); // Current date
        const currentDate = new Date(today.getFullYear(), today.getMonth(), today.getDate()); // Normalize current date

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
          const dateValue = new Date(year, month, day); // Construct the date object for the current day
          const formattedDateValue = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
          const dayEl = document.createElement('div');
          dayEl.className = 'bg-white p-2 rounded shadow text-center'; // Adjusted padding

          const label = document.createElement('label');
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = 'available_dates[]';
          checkbox.value = formattedDateValue;

          if (availability.includes(formattedDateValue)) {
            checkbox.checked = true;
          }

          // Disable checkbox for past dates
          if (dateValue < currentDate) {
            checkbox.disabled = true;
            dayEl.classList.add('opacity-50'); // Optional: Style past dates differently
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