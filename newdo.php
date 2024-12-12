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
function deletePastAvailabilityDates($con, $doctor) {
    $currentDate = date("Y-m-d");  // Get the current date

    // Prepare and execute the query to delete past availability dates
    $stmt = $con->prepare("DELETE FROM availabilitytb WHERE doctor = ? AND available_date < ?");
    $stmt->bind_param("ss", $doctor, $currentDate);
    $stmt->execute();
}

// Automatically delete past availability dates
deletePastAvailabilityDates($con, $doctor);

function updateAppointmentStatus($con, $id, $status) {
    $stmt = $con->prepare("UPDATE appointmenttb SET doctorStatus = ? WHERE ID = ?");
    $stmt->bind_param("ii", $status, $id);
    return $stmt->execute();
}

function generateReferenceNumber() {
    return strtoupper(bin2hex(random_bytes(8))); // Generate a 16-character unique reference number
}

function send_email($to, $subject, $message) {
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
  nav {
    background-color: var(--pastel-blue);
    color: var(--white);
    
  }

  /* Sidebar */
  .sidebar {
    background-color: var(--pastel-blue);
    color: var(--white);
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  .sidebar button {
    display: block;
    
    color: white;
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

  /* Cards */
  .card {
    background-color: var(--white);
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    height: 200px;
    width: 200px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
  }

  .card h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--dark-gray);
    margin-top: 1rem;
  }

  .card-icon {
    font-size: 3rem;
    color: var(--pastel-blue);
    margin-bottom: 1rem;
  }

  /* Buttons */
  .btn-blue {
    background-color: var(--pastel-blue);
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

  /* Table */
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  table thead {
    background-color: var(--pastel-blue);
    color: var(--white);
  }

  table thead th {
    padding: 1rem;
    text-align: left;
    font-size: 1rem;
    font-weight: 600;
    border-bottom: 3px solid var(--pastel-gray);
  }

  table tbody tr {
    border-bottom: 1px solid var(--pastel-gray);
    transition: background-color 0.2s ease;
  }

  table tbody tr:hover {
    background-color: var(--pastel-gray);
  }

  table tbody td {
    padding: 1rem;
    font-size: 0.9rem;
    color: var(--dark-gray);
  }

  /* Content Wrapper */
  .content-wrapper {
    padding: 2rem;
    border-radius: 15px;
    background-color: var(--white);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
</style>


</head>
<body class="bg-light-bg">
  <div class="flex h-screen space-x-6 p-4">
    <!-- Sidebar -->
    <div class="sidebar w-64 p-6 space-y-6">
      
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
</nav>

    </div>
    <!-- Main content -->

    <div class="flex-1 overflow-y-auto">
      <div class="text-left text-2xl font-bold">Welcome Dr. <?php echo $_SESSION['dname']; ?>!</div>

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
      <h2 class="text-2xl font-bold mb-4">Appointments</h2>
      <form class="flex mb-4" method="post" action="">
        <input type="hidden" name="active_div" value="appointments">
        <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Enter the Patient ID" name="pid">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" name="search_submit">Search</button>
      </form>
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
              if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 1)) {
                $query_prescription = "SELECT * FROM prestb WHERE ID = '" . $row['ID'] . "'";
                $result_prescription = mysqli_query($con, $query_prescription);
                if (mysqli_num_rows($result_prescription) > 0) {
                  echo "<strong>PRESCRIBED</strong>";
                } else {
              ?>
              <a href="prescribe.php?pid=<?php echo $row['pid']; ?>&ID=<?php echo $row['ID']; ?>&fname=<?php echo $row['fname']; ?>&lname=<?php echo $row['lname']; ?>&appdate=<?php echo $row['appdate']; ?>&apptime=<?php echo $row['apptime']; ?>"
                class="bg-green-600 text-white px-4 py-2 rounded">Prescribe</a>
              <?php }
              } elseif ($row['doctorStatus'] == 2) {
                $query_prescription = "SELECT * FROM prestb WHERE ID = '" . $row['ID'] . "'";
                $result_prescription = mysqli_query($con, $query_prescription);
                if (mysqli_num_rows($result_prescription) > 0) {
                  echo "<strong>PRESCRIBED</strong>";
                } else {
              ?>
              <a href="prescribe.php?pid=<?php echo $row['pid']; ?>&ID=<?php echo $row['ID']; ?>&fname=<?php echo $row['fname']; ?>&lname=<?php echo $row['lname']; ?>&appdate=<?php echo $row['appdate']; ?>&apptime=<?php echo $row['apptime']; ?>"
                class="bg-green-600 text-white px-4 py-2 rounded">Prescribe</a>
              <?php }
              } else {
                echo "-";
              } ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div id="prescriptions" class="<?php echo $active_div == 'prescriptions' ? '' : 'hidden'; ?> mt-8">
      <h2 class="text-2xl font-bold mb-4">Prescription List</h2>
      <form class="flex mb-4" method="post" action="">
        <input type="hidden" name="active_div" value="prescriptions">
        <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Enter the Patient ID" name="pid">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" name="search_submit">Search</button>
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
      <h2 class="text-2xl font-bold mb-4">Patient List</h2>
      <form class="flex mb-4" method="post" action="">
        <input type="hidden" name="active_div" value="patients">
        <input class="form-input mr-2 p-2 border rounded" type="text" placeholder="Enter the Patient ID" name="pid">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" name="search_submit">Search</button>
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
