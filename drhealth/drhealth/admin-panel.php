<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('func.php');
include('newfunc.php');
include('navbar.php');

$con = mysqli_connect("localhost", "root", "", "myhmsdb");

$pid = $_SESSION['pid'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$fname = $_SESSION['fname'];
$age = $_SESSION['age'];
$gender = $_SESSION['gender'];
$lname = $_SESSION['lname'];
$contact = $_SESSION['contact'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app-submit'])) {
    $doctor = $_POST['doctor'];
    $appdate = $_POST['appdate'];
    $apptime = $_POST['apptime'];
    $cur_date = date("Y-m-d");
    date_default_timezone_set('Asia/Kolkata');
    $cur_time = date("H:i:s");

    $apptime1 = strtotime($apptime);
    $appdate1 = strtotime($appdate);

    if (date("Y-m-d", $appdate1) >= $cur_date) {
        if ((date("Y-m-d", $appdate1) == $cur_date && date("H:i:s", $apptime1) > $cur_time) || date("Y-m-d", $appdate1) > $cur_date) {
            $check_query = mysqli_query($con, "SELECT apptime FROM appointmenttb WHERE doctor='$doctor' AND appdate='$appdate' AND apptime='$apptime'");
            if (mysqli_num_rows($check_query) == 0) {
                $query = mysqli_query($con, "INSERT INTO appointmenttb (pid, fname, lname, gender, email, contact, doctor, appdate, apptime, userStatus, doctorStatus) VALUES ($pid, '$fname', '$lname', '$gender', '$email', '$contact', '$doctor', '$appdate', '$apptime', '1', '1')");
                if ($query) {
                    echo "<script>alert('Your appointment was successfully booked.');</script>";
                } else {
                    echo "<script>alert('Unable to process your request. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('The doctor is not available at this time or date. Please choose a different time or date.');</script>";
            }
        } else {
            echo "<script>alert('Select a time or date in the future.');</script>";
        }
    } else {
        echo "<script>alert('Select a time or date in the future.');</script>";
    }
    header("Location: admin-panel.php");
    exit();
}

if (isset($_GET['cancel'])) {
    $query = mysqli_query($con, "UPDATE appointmenttb SET userStatus='0' WHERE ID = '" . $_GET['ID'] . "'");
    if ($query) {
        echo "<script>alert('Your appointment was successfully cancelled.');</script>";
    }
    header("Location: admin-panel.php");
    exit();
}

function get_specs() {
    global $con;
    $query = mysqli_query($con, "SELECT username, spec FROM doctb");
    $docarray = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $docarray[] = $row;
    }
    return json_encode($docarray);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D.R. Health Medical and Diagnostic Center</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="./font-awesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'IBM Plex Sans', sans-serif;
        }
        .sidebar {
            background-color: #342ac1;
            color: white;
        }
        .sidebar a {
            color: white;
        }
        .sidebar a.active {
            background-color: #3c50c1;
        }
        #availability-container {
            display: none;
        }
    </style>
    <script>
        let availableDates = [];

        function showDiv(divId) {
            const divs = document.querySelectorAll('.section');
            divs.forEach(div => {
                div.classList.add('hidden');
            });
            document.getElementById(divId).classList.remove('hidden');
        }

        function fetchAvailability(doctor, callback) {
            fetch(`fetch_availability_text.php?doctor=${doctor}`)
                .then(response => response.json())
                .then(data => {
                    callback(data);
                })
                .catch(error => {
                    console.error('Error fetching availability:', error);
                    alert('An error occurred while fetching availability.');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('select-doctor-btn').addEventListener('click', function() {
                const doctor = document.getElementById('doctor').value;
                if (doctor) {
                    document.getElementById('availability-container').style.display = 'block';
                    fetchAvailability(doctor, data => {
                        const availabilityList = document.getElementById('availability-list');
                        const startEndTime = document.getElementById('start-end-time');
                        
                        availabilityList.innerHTML = '';
                        availableDates = data.availability.map(a => a.split(" ")[0]);
                        if (availableDates.length > 0 && availableDates[0] !== 'No availability for this doctor.') {
                            availableDates.forEach(date => {
                                const listItem = document.createElement('li');
                                listItem.textContent = date;
                                availabilityList.appendChild(listItem);
                            });
                            document.getElementById('appdate').disabled = false;
                        } else {
                            const noAvailabilityItem = document.createElement('li');
                            noAvailabilityItem.textContent = 'No availability for this doctor.';
                            availabilityList.appendChild(noAvailabilityItem);
                            document.getElementById('appdate').disabled = true;
                        }
                        
                        startEndTime.textContent = `Doctor's available time: ${data.start_time} to ${data.end_time}`;
                    });
                } else {
                    alert('Please select a doctor first.');
                }
            });

            document.getElementById('appdate').addEventListener('change', function() {
                const selectedDate = this.value;
                if (!availableDates.includes(selectedDate)) {
                    alert('The selected date is not available. Please choose another date.');
                    this.value = '';
                }
            });
        });
    </script>
</head>
<body class="bg-gray-100">

<div class="flex h-screen bg-gray-200">
    <!-- Sidebar -->
    <nav class="sidebar w-64 p-6 flex-shrink-0">
        <h2 class="text-xl font-bold mb-6">D.R. Health</h2>
        <button onclick="showDiv('dashboard')" class="block py-2 px-4 rounded hover:bg-blue-700">Dashboard</button>
        <button onclick="showDiv('book-appointment')" class="block py-2 px-4 rounded hover:bg-blue-700">Book Appointment</button>
        <button onclick="showDiv('appointment-history')" class="block py-2 px-4 rounded hover:bg-blue-700">Appointment History</button>
        <button onclick="showDiv('prescriptions')" class="block py-2 px-4 rounded hover:bg-blue-700">Prescriptions</button>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-6">
        <h3 class="text-2xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($username); ?>!</h3>

        <!-- Dashboard -->
        <section id="dashboard" class="mb-8 section">
            <h4 class="text-xl font-semibold mb-4">Dashboard</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded shadow-md text-center">
                    <span class="text-3xl text-blue-500"><i class="fa fa-calendar"></i></span>
                    <h5 class="mt-4 text-lg font-semibold">Book My Appointment</h5>
                    <p><button onclick="showDiv('book-appointment')" class="text-blue-500 underline">Book Appointment</button></p>
                </div>
                <div class="bg-white p-6 rounded shadow-md text-center">
                    <span class="text-3xl text-blue-500"><i class="fa fa-history"></i></span>
                    <h5 class="mt-4 text-lg font-semibold">My Appointments</h5>
                    <p><button onclick="showDiv('appointment-history')" class="text-blue-500 underline">View Appointment History</button></p>
                </div>
                <div class="bg-white p-6 rounded shadow-md text-center">
                    <span class="text-3xl text-blue-500"><i class="fa fa-file-text"></i></span>
                    <h5 class="mt-4 text-lg font-semibold">Prescriptions</h5>
                    <p><button onclick="showDiv('prescriptions')" class="text-blue-500 underline">View Prescription List</button></p>
                </div>
            </div>
        </section>

        <!-- Book Appointment -->
        <section id="book-appointment" class="mb-8 section hidden">
            <h4 class="text-xl font-semibold mb-4">Book Appointment</h4>
            <div class="bg-white p-6 rounded shadow-md">
                <form method="post" action="">
                    <div class="mb-4">
                        <label for="doctor" class="block text-gray-700">Select Doctor:</label>
                        <select id="doctor" name="doctor" class="form-select block w-full mt-1">
                            <option value="" disabled selected>Select Doctor</option>
                            <?php
                            $result = mysqli_query($con, "SELECT username, spec FROM doctb");
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . htmlspecialchars($row['username']) . "'>" . htmlspecialchars($row['username']) . " (" . htmlspecialchars($row['spec']) . ")</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="button" id="select-doctor-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Select Doctor</button>
                    <div id="availability-container" class="mt-6">
                        <h4 class="text-xl font-semibold mb-4">Doctor's Availability</h4>
                        <ul id="availability-list" class="list-disc pl-5"></ul>
                        <p id="start-end-time" class="mt-4"></p>
                    </div>
                    <div class="mb-4 mt-6">
                        <label for="appdate" class="block text-gray-700">Appointment Date:</label>
                        <input type="date" id="appdate" name="appdate" class="form-input block w-full mt-1" required disabled>
                    </div>
                    <div class="mb-4">
                        <label for="apptime" class="block text-gray-700">Appointment Time:</label>
                        <input type="time" id="apptime" name="apptime" class="form-input block w-full mt-1" required>
                    </div>
                    <button type="submit" name="app-submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Book Appointment</button>
                </form>
            </div>
        </section>

        <!-- Appointment History -->
        <section id="appointment-history" class="mb-8 section hidden">
            <h4 class="text-xl font-semibold mb-4">Appointment History</h4>
            <div class="bg-white p-6 rounded shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left text-gray-700">Doctor</th>
                            <th class="py-2 px-4 text-left text-gray-700">Appointment Date</th>
                            <th class="py-2 px-4 text-left text-gray-700">Appointment Time</th>
                            <th class="py-2 px-4 text-left text-gray-700">Status</th>
                            <th class="py-2 px-4 text-left text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($con, "SELECT * FROM appointmenttb WHERE pid='$pid' ORDER BY appdate DESC, apptime DESC");
                        while ($row = mysqli_fetch_assoc($query)) {
                            $status = $row['userStatus'] == '1' ? 'Active' : 'Cancelled';
                            $cancelButton = $row['userStatus'] == '1' ? "<a href='?cancel=1&ID=" . $row['ID'] . "' class='text-red-500 hover:underline'>Cancel</a>" : '';
                            echo "<tr>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['doctor']) . "</td>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['appdate']) . "</td>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['apptime']) . "</td>
                                <td class='py-2 px-4'>" . htmlspecialchars($status) . "</td>
                                <td class='py-2 px-4'>" . $cancelButton . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Prescriptions -->
        <section id="prescriptions" class="section hidden">
            <h4 class="text-xl font-semibold mb-4">Prescriptions</h4>
            <div class="bg-white p-6 rounded shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left text-gray-700">Prescription ID</th>
                            <th class="py-2 px-4 text-left text-gray-700">Date</th>
                            <th class="py-2 px-4 text-left text-gray-700">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($con, "SELECT * FROM prestb WHERE pid='$pid'");
                        while ($row = mysqli_fetch_assoc($query)) {
                            echo "<tr>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['pid']) . "</td>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['appdate']) . "</td>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['prescription']) . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>
