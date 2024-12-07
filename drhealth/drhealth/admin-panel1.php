<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D.R. Health Medical and Diagnostic Center</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="./font-awesome/css/font-awesome.min.css">
        <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    

    <script>
    toastr.options = {
      "closeButton": true,
      "debug": false,
      "newestOnTop": false,
      "progressBar": true,
      "positionClass": "toast-top-right",
      "preventDuplicates": false,
      "onclick": null,
      "showDuration": "300",
      "hideDuration": "1000",
      "timeOut": "5000",
      "extendedTimeOut": "1000",
      "showEasing": "swing",
      "hideEasing": "linear",
      "showMethod": "fadeIn",
      "hideMethod": "fadeOut"
    };
</script>

    <script>
      // Function to show the section based on the hash
      function showDiv(divId) {
          const divs = document.querySelectorAll('.section');
          divs.forEach(div => {
              div.classList.add('hidden');
          });

          const targetDiv = document.getElementById(divId);
          if (targetDiv) {
              targetDiv.classList.remove('hidden');
          } else {
              console.warn(`Div with ID ${divId} not found.`);
          }
      }


      // Function to handle URL hash changes and show the correct section
      function handleHashChange() {
        const hash = window.location.hash.substring(1); // Remove '#' from hash
        if (hash) {
          showDiv(hash);  // Show the corresponding section
        }
      }

      // On page load, check if a hash is present in the URL and show the correct section
      window.onload = function() {
        handleHashChange();
      };

      // Listen for hash changes
      window.onhashchange = handleHashChange;
    </script>
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

      body {
        background-color: var(--white);

      }
      nav {
    background-color: var(--pastel-blue);
    color: var(--white);
    
  }

      /* Sidebar */
      .sidebar {
        background-color:  #0D409E;
        color: var(--white);
        border-radius: 15px;
      }

      .sidebar button:hover {
        background-color: var(--pastel-green);
      }

      /* Cards */
      .card {
        background-color: var(--white);
        border-radius: 15px;
        border: 1px solid var(--pastel-gray);
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
      }

      .card h5 {
        font-size: 1.25rem;
        color: var(--dark-gray);
      }

      .card span {
        font-size: 3rem;
        color:  #0D409E;
      }

      /* Buttons */
      .btn-blue {
        background-color:  #0D409E;
        color: var(--white);
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        display: inline-block;
        text-align: center;
        cursor: pointer;
      }

      .btn-blue:hover {
        background-color:  #0D409E;
      }

      /* Input and Select styling */
      .form-input, .form-select {
        background-color: var(--white);
        border: 1px solid var(--pastel-gray);
        padding: 0.5rem;
        border-radius: 10px;
        width: 100%;
      }

      /* Table Headers */
     .table-container {
    max-height: 450px; /* Define max height for scrolling rows */
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
    top: 0; /* Keeps the header fixed at the top */
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
    background-color: #f9f9f9; /* Light off-white for odd rows */
}

table tbody tr:nth-child(even) {
    background-color: #ffffff; /* White for even rows */
}

table tbody tr:hover {
    background-color: #e8f4f8; /* Soft sky-blue hover effect */
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
    background-color: #d3d3d3; /* Sky gray scrollbar */
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background-color: #a9a9a9; /* Slightly darker gray on hover */
}

/* Links Styling */
a {
    color: var(--dark-gray);
    text-decoration: none;
}

a:hover {
    color: var(--white);
    background-color:#88b04b ; /* Sky blue background for hover effect */
}

    </style>
</head>
<body class="bg-gray-100">

<?php 
$con=mysqli_connect("localhost","root","","myhmsdb");

include('newfunc.php');
include('navbaradmin.php');

if(isset($_POST['docsub']))
{
    $doctor = $_POST['doctor'];
    $dpassword = $_POST['dpassword'];
    $demail = $_POST['demail'];
    $spec = $_POST['special'];

    $query = "INSERT INTO doctb (username, password, email, spec, status) VALUES (?, ?, ?, ?, 'active')";
    $stmt = $con->prepare($query);
    if($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($con->error));
    }

    $stmt->bind_param("ssss", $doctor, $dpassword, $demail, $spec);
    if($stmt->execute()) {
        echo "<script>
                alert('Doctor added successfully!');
                window.location.href = 'admin-panel1.php';
              </script>";
        exit();
    } else {
        echo 'Execute failed: ' . htmlspecialchars($stmt->error);
    }
}

if(isset($_POST['change_status'])) {
    $demail = $_POST['demail'];
    $newStatus = $_POST['new_status'];

    $query = "UPDATE doctb SET status = ? WHERE email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $newStatus, $demail);
    $stmt->execute();

    if ($stmt) {
        echo "<script>
                alert('Doctor status updated successfully!');
                window.location.hash = 'list-doc';
              </script>";
    } else {
        echo "<script>
                alert('Unable to update doctor status. Please try again.');
                window.location.hash = 'list-doc';
              </script>";
    }
}
?>

<div class="flex h-screen space-x-6 p-4">
  <!-- Sidebar -->
  <nav class="sidebar w-64 p-6 space-y-6">
    <div class="text-center text-2xl font-bold">D.R. Health Medical and Diagnostic Center</div>
    
    <ul>
        <li><a href="#dashboard" class="block w-full py-2 px-4 rounded hover:bg-pastel-green active"> <i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="#list-doc" class="block w-full py-2 px-4 rounded hover:bg-pastel-green"> <i class="fa fa-user-md"></i> Doctor List</a></li>
        <li><a href="#list-pat" class="block w-full py-2 px-4 rounded hover:bg-pastel-green"> <i class="fa fa-user"></i> Patient List</a></li>
        <li><a href="#list-app" class="block w-full py-2 px-4 rounded hover:bg-pastel-green"> <i class="fa fa-calendar-alt"></i> Appointment Details</a></li>
        <li><a href="#add-doctor" class="block w-full py-2 px-4 rounded hover:bg-pastel-green"> <i class="fa fa-user-plus"></i> Add Doctor</a></li>
        <li><a href="#list-archived" class="block w-full py-2 px-4 rounded hover:bg-pastel-green"> <i class="fa fa-archive"></i> Archived Doctor</a></li>
        <li><a href="#monthly-reports" class="block w-full py-2 px-4 rounded hover:bg-pastel-green"> <i class="fa fa-file-alt"></i> Monthly Reports</a></li>
    </ul>
</nav>


  <!-- Main Content -->
  <main class="flex-1 p-6">
    <h3 class="text-2xl font-bold mb-4">Welcome, Admin!</h3>

 <!-- Dashboard -->
 <section id="dashboard" class="section">
      <h4 class="text-xl font-semibold mb-4">Dashboard</h4>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
    <!-- Doctor List Card -->
    <div class="card bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
        <span class="text-4xl text-blue-500"><i class="fa fa-calendar"></i></span>
        <h5 class="mt-4 text-lg font-semibold text-gray-800">Doctor List</h5>
        <p class="mt-2">
            <button onclick="showDiv('list-doc')" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-800 transition duration-300" style="background:   #0D409E";>
    View Doctors
</button>

        </p>
    </div>

    <!-- Patient List Card -->
    <div class="card bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
        <span class="text-4xl text-blue-500"><i class="fa fa-history"></i></span>
        <h5 class="mt-4 text-lg font-semibold text-gray-800">Patient List</h5>
        <p class="mt-2">
            <button onclick="showDiv('list-pat')" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-800 transition duration-300" style="background:   #0D409E";>
    View Patients
</button>

        </p>
    </div>

    <!-- Appointment Details Card -->
    <div class="card bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
        <span class="text-4xl text-blue-500"><i class="fa fa-file-text"></i></span>
        <h5 class="mt-4 text-lg font-semibold text-gray-800">Appointment Details</h5>
        <p class="mt-2">
          <button onclick="showDiv('list-app')" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-800 transition duration-300" style="background:   #0D409E";>
    View Appointments
</button>

            
        </p>
    </div>

    <!-- Add Doctor Card -->
    <div class="card bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
        <span class="text-4xl text-blue-500"><i class="fa fa-user-md"></i></span>
        <h5 class="mt-4 text-lg font-semibold text-gray-800">Add Doctor</h5>
        <p class="mt-2">
            <button onclick="showDiv('add-doctor')" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-800 transition duration-300" style="background:   #0D409E";>
    Add Doctor
</button> 

        </p>
    </div>

    <!-- Archived Doctor Card -->
    <div class="card bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
        <span class="text-4xl text-blue-500"><i class="fa fa-archive"></i></span>
        <h5 class="mt-4 text-lg font-semibold text-gray-800">Archived Doctor</h5>
        <p class="mt-2">
            <button onclick="showDiv('list-archived')" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-800 transition duration-300" style="background:   #0D409E";>
    View Archived
</button>

        </p>
    </div>

    <!-- Monthly Reports Card -->
    <div class="card bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
        <span class="text-4xl text-blue-500"><i class="fa fa-chart-line"></i></span>
        <h5 class="mt-4 text-lg font-semibold text-gray-800">Monthly Reports</h5>
        <p class="mt-2">
           <button onclick="showDiv('monthly-reports')" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-800 transition duration-300" style="background:   #0D409E";>
    View Monthly Reports
</button>

        </p>
    </div>
</div>

    </section>
    <?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

    <!-- Add Doctor -->
    <section id="add-doctor" class="section hidden">
      <h4 class="text-xl font-semibold mb-4">Add Doctor</h4>
      <div class="bg-white p-6 rounded shadow-md">
        <form method="post" action="">
          <div class="mb-4">
            <label for="doctor" class="block text-gray-700">Doctor Name:</label>
            <input type="text" name="doctor" class="form-input mt-1 block w-full" required>
          </div>
          <div class="mb-4">
            <label for="special" class="block text-gray-700">Specialization:</label>
            <select name="special" class="form-select mt-1 block w-full" required>
              <option value="" disabled selected>Select Specialization</option>
              <option value="Internal Medicine Cardiology">Internal Medicine Cardiology</option>
              <option value="Internal Medicine Rheumatology">Internal Medicine Rheumatology</option>
              <option value="Internal Medicine Nephrology">Internal Medicine Nephrology</option>
              <option value="Neurology">Neurology</option>
              <option value="Ob-gyne">Ob-gyne</option>
              <option value="Pediatrics">Pediatrics</option>
              <option value="E.N.T">E.N.T</option>
              <option value="Dermatology">Dermatology</option>
            </select>
          </div>
          <div class="mb-4">
            <label for="demail" class="block text-gray-700">Email ID:</label>
            <input type="email" name="demail" class="form-input mt-1 block w-full" required>
          </div>
          <div class="mb-4">
            <label for="dpassword" class="block text-gray-700">Password:</label>
            <input type="password" name="dpassword" id="dpassword" class="form-input mt-1 block w-full" required>
          </div>
          <div class="mb-4">
            <label for="cdpassword" class="block text-gray-700">Confirm Password:</label>
            <input type="password" name="cdpassword" id="cdpassword" class="form-input mt-1 block w-full" required>
            <span id='message'></span>
          </div>
          <button type="submit" name="docsub" class="btn-blue">Add Doctor</button>
        </form>
      </div>
    </section>

    <!-- Doctor List -->
    <section id="list-doc" class="section hidden">
      <h4 class="text-xl font-semibold mb-4">Doctor List</h4>
      <div class="table-container">
        <table class="table-auto w-full">
          <thead>
            <tr>
              <th class="px-4 py-2">ID</th>
              <th class="px-4 py-2">Doctor Name</th>
              <th class="px-4 py-2">Specialization</th>
              <th class="px-4 py-2">Email</th>
              <th class="px-4 py-2">Password</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $query = "SELECT * FROM doctb";
            $result = mysqli_query($con, $query);
            while ($row = mysqli_fetch_array($result)) {
              $id = isset($row['id']) ? $row['id'] : '';
              $username = isset($row['username']) ? $row['username'] : '';
              $spec = isset($row['spec']) ? $row['spec'] : '';
              $email = isset($row['email']) ? $row['email'] : '';
              $password = isset($row['password']) ? $row['password'] : '';
              $status = isset($row['status']) ? $row['status'] : '';

              if ($status == 'archived') {
                $buttonLabel = "Unarchive";
                $newStatus = 'active';
              } else {
                $buttonLabel = "Archive";
                $newStatus = 'archived';
              }

              echo "<tr>
                      <td class='border px-4 py-2'>$id</td>
                      <td class='border px-4 py-2'>$username</td>
                      <td class='border px-4 py-2'>$spec</td>
                      <td class='border px-4 py-2'>$email</td>
                      <td class='border px-4 py-2'>$password</td>
                      <td class='border px-4 py-2'>$status</td>
                      <td class='border px-4 py-2'>
                        <form action='admin-panel1.php' method='POST'>
                          <input type='hidden' name='demail' value='$email'>
                          <input type='hidden' name='new_status' value='$newStatus'>
                          <button type='submit' name='change_status' class='btn btn-blue'>$buttonLabel</button>
                        </form>
                      </td>
                    </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Patient List -->
    <section id="list-pat" class="section hidden">
      <h4 class="text-xl font-semibold mb-4">Patient List</h4>
      <div class="table-container">
        <table class="table-auto w-full">
          <thead>
            <tr>
              <th class="px-4 py-2">Patient ID</th>
              <th class="px-4 py-2">First Name</th>
              <th class="px-4 py-2">Last Name</th>
              <th class="px-4 py-2">Gender</th>
              <th class="px-4 py-2">Email</th>
              <th class="px-4 py-2">Contact</th>
              <th class="px-4 py-2">Doctor Name</th>
              <th class="px-4 py-2">Appointment Date</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            // Modify the query to only select confirmed appointments
            $query = "SELECT * FROM appointmenttb WHERE doctorStatus = 2"; 
            $result = mysqli_query($con, $query);
            while ($row = mysqli_fetch_array($result)) {
              $pid = isset($row['pid']) ? $row['pid'] : '';
              $fname = isset($row['fname']) ? $row['fname'] : '';
              $lname = isset($row['lname']) ? $row['lname'] : '';
              $gender = isset($row['gender']) ? $row['gender'] : '';
              $email = isset($row['email']) ? $row['email'] : '';
              $contact = isset($row['contact']) ? $row['contact'] : '';
              $doctor = isset($row['doctor']) ? $row['doctor'] : '';
              $appdate = isset($row['appdate']) ? $row['appdate'] : '';
              echo "<tr>
                      <td class='border px-4 py-2'>$pid</td>
                      <td class='border px-4 py-2'>$fname</td>
                      <td class='border px-4 py-2'>$lname</td>
                      <td class='border px-4 py-2'>$gender</td>
                      <td class='border px-4 py-2'>$email</td>
                      <td class='border px-4 py-2'>$contact</td>
                      <td class='border px-4 py-2'>$doctor</td>
                      <td class='border px-4 py-2'>$appdate</td>
                    </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>


    <!-- Archived Doctor List -->
    <section id="list-archived" class="section hidden">
      <h4 class="text-xl font-semibold mb-4">Archived Doctor List</h4>
      <div class="table-container">
        <table class="table-auto w-full">
          <thead>
            <tr>
              <th class="px-4 py-2">ID</th>
              <th class="px-4 py-2">Doctor Name</th>
              <th class="px-4 py-2">Specialization</th>
              <th class="px-4 py-2">Email</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $query = "SELECT * FROM doctb WHERE status = 'archived'";
            $result = mysqli_query($con, $query);
            while ($row = mysqli_fetch_array($result)) {
              $id = isset($row['id']) ? $row['id'] : '';
              $username = isset($row['username']) ? $row['username'] : '';
              $spec = isset($row['spec']) ? $row['spec'] : '';
              $email = isset($row['email']) ? $row['email'] : '';
              echo "<tr>
                      <td class='border px-4 py-2'>$id</td>
                      <td class='border px-4 py-2'>$username</td>
                      <td class='border px-4 py-2'>$spec</td>
                      <td class='border px-4 py-2'>$email</td>
                    </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Appointment Details -->
   <section id="list-app" class="section hidden">
      <h4 class="text-xl font-semibold mb-4">Appointment Details</h4>
      <div class="table-container">
        <table class="table-auto w-full">
          <thead>
            <tr>
              <th class="px-4 py-2">Patient ID</th>
              <th class="px-4 py-2">First Name</th>
              <th class="px-4 py-2">Last Name</th>
              <th class="px-4 py-2">Age</th>
              <th class="px-4 py-2">Gender</th>
              <th class="px-4 py-2">Email</th>
              <th class="px-4 py-2">Contact</th>
              <th class="px-4 py-2">Address</th>
              <th class="px-4 py-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php 
          

            $query = "SELECT p.pid, p.fname, p.lname, p.age, p.gender, p.email, p.contact, p.address, a.userStatus, a.doctorStatus 
                      FROM patreg p 
                      LEFT JOIN appointmenttb a ON p.pid = a.pid";
            $result = mysqli_query($con, $query);

            $patientsData = [];
            while ($row = mysqli_fetch_assoc($result)) {
              $patientsData[] = $row;
              $pid = isset($row['pid']) ? $row['pid'] : '';
              $fname = isset($row['fname']) ? $row['fname'] : '';
              $lname = isset($row['lname']) ? $row['lname'] : '';
              $age = isset($row['age']) ? $row['age'] : '';
              $gender = isset($row['gender']) ? $row['gender'] : '';
              $email = isset($row['email']) ? $row['email'] : '';
              $contact = isset($row['contact']) ? $row['contact'] : '';
              $address = isset($row['address']) ? $row['address'] : '';
              $userStatus = isset($row['userStatus']) ? $row['userStatus'] : '';
              $doctorStatus = isset($row['doctorStatus']) ? $row['doctorStatus'] : '';

              $status = '';
              if (($userStatus == 1) && ($doctorStatus == 1)) {
                $status = "<strong>Pending</strong>";
              } elseif (($userStatus == 0) && ($doctorStatus == 1)) {
                $status = "<strong>Cancelled by Patient</strong>";
              } elseif (($userStatus == 1) && ($doctorStatus == 0)) {
                $status = "<strong>Cancelled by Doctor</strong>";
              } elseif (($userStatus == 2) && ($doctorStatus == 2)) {
                $status = "<strong>Confirmed</strong>";
              }

              // Example for Appointment Details



              echo "<tr>
                      <td class='border px-4 py-2'>$pid</td>
                      <td class='border px-4 py-2'>$fname</td>
                      <td class='border px-4 py-2'>$lname</td>
                      <td class='border px-4 py-2'>$age</td>
                      <td class='border px-4 py-2'>$gender</td>
                      <td class='border px-4 py-2'>$email</td>
                      <td class='border px-4 py-2'>$contact</td>
                      <td class='border px-4 py-2'>$address</td>
                      <td class='border px-4 py-2'>$status</td>
                    </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>
      

    <!-- Monthly Reports -->
    <section id="monthly-reports" class="section hidden">
      <h4 class="text-xl font-semibold mb-4">Monthly Reports</h4>
      <div class="bg-white p-6 rounded shadow-md">
        <form method="post" action="">
          <input type="hidden" name="active_section" value="monthly-reports">
          <div class="mb-4">
            <label for="month" class="block text-gray-700">Select Month:</label>
            <select name="month" class="form-select mt-1 block w-full" required>
              <option value="01">January</option>
              <option value="02">February</option>
              <option value="03">March</option>
              <option value="04">April</option>
              <option value="05">May</option>
              <option value="06">June</option>
              <option value="07">July</option>
              <option value="08">August</option>
              <option value="09">September</option>
              <option value="10">October</option>
              <option value="11">November</option>
              <option value="12">December</option>
            </select>
          </div>
          <div class="mb-4">
            <label for="year" class="block text-gray-700">Select Year:</label>
            <select name="year" class="form-select mt-1 block w-full" required>
              <?php
                $currentYear = date('Y');
                for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                  echo "<option value=\"$i\">$i</option>";
                }
              ?>
            </select>
          </div>
          <button type="submit" name="generate_report" class="btn-blue">Generate Report</button>
        </form>

        <?php
        if (isset($_POST['generate_report'])) {
            $month = $_POST['month'];
            $year = $_POST['year'];

            // Fetch data from the database
            $query = "SELECT appointmenttb.*, patreg.fname, patreg.lname, patreg.gender, patreg.email, patreg.contact, appointmenttb.userStatus, appointmenttb.doctorStatus 
                      FROM appointmenttb 
                      JOIN patreg ON appointmenttb.pid = patreg.pid 
                      WHERE MONTH(appointmenttb.appdate) = ? AND YEAR(appointmenttb.appdate) = ?";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $month, $year);
            $stmt->execute();
            $result = $stmt->get_result();

            $statusCounts = [
                'Pending' => 0,
                'Cancelled by Patient' => 0,
                'Cancelled by Doctor' => 0,
                'Confirmed' => 0
            ];

             if ($result->num_rows > 0) {
                // Add a header for the report
                echo "<h5 class='text-lg font-semibold mt-6'>Report for " . date('F', mktime(0, 0, 0, $month, 10)) . " $year</h5>";
                echo "<table class='table-auto w-full mt-6'>
                        <thead>
                          <tr>
                            <th class='px-4 py-2'>Patient ID</th>
                            <th class='px-4 py-2'>First Name</th>
                            <th class='px-4 py-2'>Last Name</th>
                            <th class='px-4 py-2'>Gender</th>
                            <th class='px-4 py-2'>Email</th>
                            <th class='px-4 py-2'>Contact</th>
                            <th class='px-4 py-2'>Doctor Name</th>
                            <th class='px-4 py-2'>Appointment Date</th>
                            <th class='px-4 py-2'>Status</th>
                          </tr>
                        </thead>
                        <tbody>";

                while ($row = $result->fetch_assoc()) {
                    $status = '';
                    if (($row['userStatus'] == 1) && ($row['doctorStatus'] == 1)) {
                      $status = "Pending";
                      $statusCounts['Pending']++;
                    } elseif (($row['userStatus'] == 0) && ($row['doctorStatus'] == 1)) {
                      $status = "Cancelled by Patient";
                      $statusCounts['Cancelled by Patient']++;
                    } elseif (($row['userStatus'] == 1) && ($row['doctorStatus'] == 0)) {
                      $status = "Cancelled by Doctor";
                      $statusCounts['Cancelled by Doctor']++;
                    } elseif (($row['userStatus'] == 2) && ($row['doctorStatus'] == 2)) {
                      $status = "Confirmed";
                      $statusCounts['Confirmed']++;
                    }

                    echo "<tr>
                            <td class='border px-4 py-2'>{$row['pid']}</td>
                            <td class='border px-4 py-2'>{$row['fname']}</td>
                            <td class='border px-4 py-2'>{$row['lname']}</td>
                            <td class='border px-4 py-2'>{$row['gender']}</td>
                            <td class='border px-4 py-2'>{$row['email']}</td>
                            <td class='border px-4 py-2'>{$row['contact']}</td>
                            <td class='border px-4 py-2'>{$row['doctor']}</td>
                            <td class='border px-4 py-2'>{$row['appdate']}</td>
                            <td class='border px-4 py-2'>$status</td>
                          </tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p class='mt-6 text-red-500'>No appointments found for the selected month and year.</p>";
            }

            echo "<script>
                    var statusData = " . json_encode($statusCounts) . ";
                  </script>";
        }
        ?>

        <!-- Chart container -->
        <div class="mt-8">
          <canvas id="statusChart" width="400" height="200"></canvas>
        </div>
      </div>
    </section>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Chart.js logic -->
    <script>
      if (typeof statusData !== 'undefined') {
        var ctx = document.getElementById('statusChart').getContext('2d');
        var statusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Cancelled by Patient', 'Cancelled by Doctor', 'Confirmed'],
                datasets: [{
                    label: 'Number of Appointments',
                    data: [statusData['Pending'], statusData['Cancelled by Patient'], statusData['Cancelled by Doctor'], statusData['Confirmed']],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
      }
    </script>

  </main>
</div>

<script>
      // Function to show the section based on the hash
      function showDiv(divId) {
          const divs = document.querySelectorAll('.section');
          divs.forEach(div => {
              div.classList.add('hidden');
          });

          const targetDiv = document.getElementById(divId);
          if (targetDiv) {
              targetDiv.classList.remove('hidden');
          } else {
              console.warn(`Div with ID ${divId} not found.`);
          }
      }


      // Function to handle URL hash changes and show the correct section
      function handleHashChange() {
        const hash = window.location.hash.substring(1); // Remove '#' from hash
        if (hash) {
          showDiv(hash);  // Show the corresponding section
        }
      }

      // On page load, check if a hash is present in the URL and show the correct section
      window.onload = function() {
        handleHashChange();
      };

      // Listen for hash changes
      window.onhashchange = handleHashChange;
    </script>


<script>
document.getElementById('dpassword').onkeyup = function() {
  var pass = document.getElementById('dpassword').value;
  var confirmPass = document.getElementById('cdpassword').value;
  if (pass === confirmPass) {
    document.getElementById('message').style.color = 'green';
    document.getElementById('message').innerHTML = 'Passwords match';
  } else {
    document.getElementById('message').style.color = 'red';
    document.getElementById('message').innerHTML = 'Passwords do not match';
  }
};

document.getElementById('cdpassword').onkeyup = function() {
  var pass = document.getElementById('dpassword').value;
  var confirmPass = document.getElementById('cdpassword').value;
  if (pass === confirmPass) {
    document.getElementById('message').style.color = 'green';
    document.getElementById('message').innerHTML = 'Passwords match';
  } else {
    document.getElementById('message').style.color = 'red';
    document.getElementById('message').innerHTML = 'Passwords do not match';
  }
};
</script>

</body>
</html>