<?php
include('db.php');
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

    // Extract the start time from the time slot (e.g., '8:00 AM - 9:00 AM' -> '8:00 AM')
    $startTime = explode(' - ', $apptime)[0];

    $cur_date = date("Y-m-d");
    date_default_timezone_set('Asia/Kolkata');
    $cur_time = date("H:i:s");

    // Convert start time to 24-hour format for comparison
    $startTime24 = date("H:i:s", strtotime($startTime));
    $appdate1 = strtotime($appdate);

    // Initialize $result to null
    $result = null;

    if (date("Y-m-d", $appdate1) >= $cur_date) {
        if ((date("Y-m-d", $appdate1) == $cur_date && $startTime24 > $cur_time) || date("Y-m-d", $appdate1) > $cur_date) {
            $check_query = mysqli_query($con, "SELECT apptime FROM appointmenttb WHERE doctor='$doctor' AND appdate='$appdate' AND apptime='$startTime24'");
            if (mysqli_num_rows($check_query) == 0) {
                $query = "INSERT INTO appointmenttb (pid, fname, lname, age, gender, email, contact, doctor, appdate, apptime, userStatus, doctorStatus) 
                          VALUES ('$pid', '$fname', '$lname', '$age', '$gender', '$email', '$contact', '$doctor', '$appdate', '$startTime24', '1', '1')";

                $result = mysqli_query($con, $query);

                if ($result) {
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showMessageModal('Your appointment was successfully booked.');
                            setTimeout(function() {
                                window.location.href = 'patient-panel.php';
                            }, 3000); // Redirect after 3 seconds
                        });
                    </script>";
                } else {
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showMessageModal('Error: " . mysqli_error($con) . "');
                            setTimeout(function() {
                                window.location.href = 'patient-panel.php';
                            }, 3000); // Redirect after 3 seconds
                        });
                    </script>";
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
    // Ensure there's no redirect before the modal is shown
    // header("Location: patient-panel.php");
    // exit();
}

if (isset($_GET['cancel'])) {
    $query = mysqli_query($con, "UPDATE appointmenttb SET userStatus='0' WHERE ID = '" . $_GET['ID'] . "'");
    if ($query) {
        echo "<script>alert('Your appointment was successfully cancelled.');</script>";
    }
    header("Location: patient-panel.php");
    exit();
}


if (isset($_GET['doctorCancel'])) {
    $query = mysqli_query($con, "UPDATE appointmenttb SET doctorStatus='0', userStatus='0' WHERE ID = '" . $_GET['ID'] . "'");
    if ($query) {
        echo "<script>alert('The appointment was successfully cancelled by the doctor.');</script>";
    }
    header("Location: doctor-panel.php");
    exit();
}
function get_specs()
{
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="./font-awesome/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <script>
        // Define the showMessageModal function
        function showMessageModal(message) {
            document.getElementById('messageContent').innerHTML = `<p>${message}</p>`;
            document.getElementById('messageModal').classList.remove('hidden');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const closeMessageModal = document.getElementById('closeMessageModal');
            const closeModalButton = document.getElementById('closeModalButton');

            closeMessageModal.addEventListener('click', function() {
                document.getElementById('messageModal').classList.add('hidden');
            });

            closeModalButton.addEventListener('click', function() {
                document.getElementById('messageModal').classList.add('hidden');
            });
        });
    </script>
    <style>
        nav {
            background-color: #0D409E;
            /* Updated navigation bar color */
            color: var(--white);
            /* Ensure text remains readable */
        }

        :root {
            --pastel-blue: #4a6fa5;
            --pastel-green: #88b04b;
            --pastel-purple: #6a4ca5;
            --pastel-orange: #e6955e;
            --pastel-gray: #c8c8c8;
            --dark-gray: #2e2e2e;
            --white: #FAF8F6;
            --hover-gray: #f1f1f1;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-dark: rgba(0, 0, 0, 0.2);
        }

        body {
            background-color: var(--white);
            font-family: 'Roboto', sans-serif;
            color: var(--dark-gray);
            line-height: 1.6;
        }

        /* Sidebar */
        .sidebar {
            background-color: #0D409E;
            color: var(--white);
            border-radius: 15px;
            padding: 1rem;
            box-shadow: 0 4px 12px var(--shadow-dark);
        }

        .sidebar button:hover {
            background-color: var(--pastel-green);
            transition: background-color 0.3s ease-in-out;
        }

        .sidebar a {
            color: var(--white);
            padding: 0.75rem 1rem;
            display: block;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: background-color 0.3s;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background-color: #3c50c1;
        }

        /* Cards */
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

        /* Buttons */
        .btn-blue {
            background-color: #0D409E;
            color: var(--white);
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-blue:hover {
            background-color: var(--pastel-purple);
            transform: translateY(-3px);
        }

        /* Input and Select styling */
        .form-input,
        .form-select {
            background-color: var(--white);
            border: 1px solid var(--pastel-gray);
            padding: 0.75rem;
            border-radius: 10px;
            width: 100%;
            margin-bottom: 1rem;
            transition: box-shadow 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            box-shadow: 0 0 8px #0D409E;
            outline: none;
        }

        /* Table Headers */
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background-color: #0D409E;
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


        /* Modal */
        #queueModal {
            z-index: 1000;
            background-color: rgba(0, 0, 0, 0.4);
        }

        #queueModal .bg-white {
            border-radius: 12px;
            padding: 20px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 10px 20px var(--shadow-dark);
        }

        #queueModal h2 {
            font-size: 1.75rem;
            color: #2c7aed;
            margin-bottom: 1rem;
        }

        #queueContent p {
            margin-bottom: 15px;
            line-height: 1.5;
            font-size: 1rem;
            color: var(--dark-gray);
        }

        /* Close button styling */
        #closeModal,
        #closeModalBottom {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-gray);
            transition: background-color 0.3s ease;
        }

        #closeModal:hover,
        #closeModalBottom:hover {
            background-color: var(--hover-gray);
            color: var(--dark-gray);
        }

        #closeModalBottom {
            background-color: #2c7aed;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
        }

        /* Responsive improvements */
        @media (max-width: 640px) {
            #queueModal .bg-white {
                width: 90%;
            }
        }

        /* Print styles */
        @media print {

            body,
            html {
                width: 100%;
                height: 100%;
                overflow: hidden;
                margin: 0;
                padding: 0;
            }

            @page {
                size: 90mm 100mm;
                margin: auto;
            }

            .sidebar,
            nav,
            button,
            .hidden,
            #availability-container,
            .section.hidden {
                display: none;
            }

            #queueModal,
            #queueModal * {
                visibility: visible;
                overflow: visible;
                width: 100%;
            }

            #queueModal .bg-white {
                border-radius: 0;
                padding: 0;
                margin: 0;
                width: 100%;
                box-shadow: none;
                font-family: 'Courier New', Courier, monospace;
            }

            #queueModal h2 {
                font-size: 1.25rem;
                color: #000;
                text-align: center;
                border-bottom: 1px dashed #000;
                padding-bottom: 5mm;
                margin-bottom: 5mm;
            }

            #queueContent p {
                margin-bottom: 4mm;
                border-bottom: 1px dashed #000;
                padding-bottom: 2mm;
            }

            #queueContent .total {
                font-weight: bold;
                font-size: 1rem;
                border-top: 1px dashed #000;
                padding-top: 5mm;
            }

            html,
            body,
            #queueModal {
                page-break-inside: avoid;
                page-break-before: avoid;
                page-break-after: avoid;
            }

            #closeModal,
            #closeModalBottom,
            #printButton {
                display: none;
            }
        }

        /* Hide sidebar on mobile view */
        @media (max-width: 767px) {
            .sidebar {
                display: none;
            }
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

        function fetchAvailability(doctor, appdate, callback) {
            console.log(`Fetching availability for doctor: ${doctor} on date: ${appdate}`);
            fetch(`fetch_availability_text.php?doctor=${doctor}&appdate=${appdate}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Availability data received:', data);
                    callback(data);
                })
                .catch(error => {
                    console.error('Error fetching availability:', error);
                    alert('An error occurred while fetching availability.');
                });
        }

        function generateTimeSlots(startTime, endTime) {
            console.log(`Generating time slots from ${startTime} to ${endTime}`);

            let timeSlots = [];

            // Convert the start and end times to 24-hour format strings
            let [startHour, startMinutes] = convertTo24Hour(startTime).split(':').map(Number);
            let [endHour, endMinutes] = convertTo24Hour(endTime).split(':').map(Number);

            console.log(`Converted start time: ${startHour}:${startMinutes}`);
            console.log(`Converted end time: ${endHour}:${endMinutes}`);

            // Generate 30-minute time slots
            while (startHour < endHour || (startHour === endHour && startMinutes < endMinutes)) {
                let nextMinutes = startMinutes + 30;
                let nextHour = startHour;

                if (nextMinutes >= 60) {
                    nextMinutes -= 60;
                    nextHour += 1;
                }

                if (nextHour > endHour || (nextHour === endHour && nextMinutes > endMinutes)) {
                    break; // Stop generating slots if the next slot exceeds end time
                }

                let slotStart = formatTime(startHour, startMinutes);
                let slotEnd = formatTime(nextHour, nextMinutes);

                console.log(`Adding time slot: ${slotStart} - ${slotEnd}`);
                timeSlots.push(`${slotStart} - ${slotEnd}`);

                startHour = nextHour;
                startMinutes = nextMinutes;
            }

            console.log('Generated time slots:', timeSlots);
            return timeSlots;
        }

        function convertTo24Hour(timeStr) {
            const [time, modifier] = timeStr.split(' ');
            let [hours, minutes] = time.split(':');

            if (modifier === 'PM' && hours !== '12') {
                hours = parseInt(hours, 10) + 12;
            } else if (modifier === 'AM' && hours === '12') {
                hours = '00';
            }

            return `${hours}:${minutes}`;
        }

        function formatTime(hour, minutes) {
            let period = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12; // Convert to 12-hour format
            minutes = String(minutes).padStart(2, '0');
            return `${hour}:${minutes} ${period}`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Event listener for selecting a doctor
            document.getElementById('select-doctor-btn').addEventListener('click', function() {
                const selectedDoctor = document.getElementById('doctor').value;
                const selectedDate = document.getElementById('appdate').value;
                if (selectedDoctor) {
                    document.getElementById('availability-container').style.display = 'block';

                    // Fetch the availability of the selected doctor
                    fetchAvailability(selectedDoctor, selectedDate, data => {
                        const availabilityList = document.getElementById('availability-list');
                        const startEndTime = document.getElementById('start-end-time');
                        const apptimeSelect = document.getElementById('apptime');

                        // Clear previous entries
                        availabilityList.innerHTML = '';
                        apptimeSelect.innerHTML = '';

                        // Parse the available dates
                        availableDates = data.availability.map(a => a.split(" ")[0]);

                        if (availableDates.length > 0 && availableDates[0] !== 'No availability for this doctor.') {
                            // Populate the availability list
                            availableDates.forEach(date => {
                                const listItem = document.createElement('li');
                                listItem.textContent = date;
                                availabilityList.appendChild(listItem);
                            });

                            // Enable the appointment date input
                            document.getElementById('appdate').disabled = false;
                        } else {
                            // No availability found for the doctor
                            const noAvailabilityItem = document.createElement('li');
                            noAvailabilityItem.textContent = 'No availability for this doctor.';
                            availabilityList.appendChild(noAvailabilityItem);
                            document.getElementById('appdate').disabled = true;
                        }

                        // Show the doctor's available time range
                        startEndTime.textContent = `Doctor's available time: ${data.start_time} to ${data.end_time}`;

                        // Generate and populate time slots in the dropdown
                        const timeSlots = generateTimeSlots(data.start_time, data.end_time);
                        const bookedTimes = data.booked_times || [];

                        if (timeSlots.length > 0) {
                            timeSlots.forEach(slot => {
                                const option = document.createElement('option');
                                option.value = slot;
                                option.textContent = slot;

                                // Disable the option if it's already booked
                                if (bookedTimes.includes(slot.split(' - ')[0])) {
                                    option.disabled = true;
                                    option.textContent += ' (Booked)';
                                }

                                apptimeSelect.appendChild(option);
                            });
                            apptimeSelect.disabled = false; // Enable the time selection dropdown
                        } else {
                            // If no time slots are generated, disable the time dropdown
                            apptimeSelect.disabled = true;
                        }
                        console.log('Time slots populated in dropdown:', apptimeSelect.innerHTML);
                    });
                } else {
                    alert('Please select a doctor first.');
                }
            });

            // Event listener for selecting a date
            // ... (rest of the code)

            // Set the min attribute for the appointment date to disable past dates
            document.addEventListener("DOMContentLoaded", function() {
                const dateInput = document.getElementById("appdate");
                const today = new Date().toISOString().split("T")[0]; // Get today's date in YYYY-MM-DD format
                dateInput.setAttribute("min", today); // Set the min attribute to today's date
            });

            document.getElementById('appdate').addEventListener('change', function() {
                const selectedDoctor = document.getElementById('doctor').value;
                const selectedDate = this.value;

                if (selectedDate) {
                    const today = new Date().toISOString().split("T")[0];

                    // Check if the selected date is in the past
                    if (selectedDate < today) {
                        alert('You cannot select a past date. Please choose a valid date.');
                        this.value = ""; // Clear the selected date
                        return;
                    }

                    // Check if the selected date is available
                    if (availableDates.includes(selectedDate)) {
                        // Fetch availability for the selected date
                        fetchAvailability(selectedDoctor, selectedDate, data => {
                            const apptimeSelect = document.getElementById('apptime');
                            apptimeSelect.innerHTML = ''; // Clear previous options

                            // Generate and populate time slots
                            const timeSlots = generateTimeSlots(data.start_time, data.end_time);
                            const bookedTimes = data.booked_times || [];

                            if (timeSlots.length > 0) {
                                timeSlots.forEach(slot => {
                                    const option = document.createElement('option');
                                    option.value = slot;
                                    option.textContent = slot;

                                    // Disable the option if it's already booked
                                    if (bookedTimes.includes(slot.split(' - ')[0])) {
                                        option.disabled = true;
                                        option.textContent += ' (Booked)';
                                    }

                                    apptimeSelect.appendChild(option);
                                });
                                apptimeSelect.disabled = false; // Enable the time selection dropdown
                                // Enable the "Book Appointment" button
                                document.querySelector('button[name="app-submit"]').disabled = false;
                            } else {
                                apptimeSelect.disabled = true;
                                // Disable the "Book Appointment" button
                                document.querySelector('button[name="app-submit"]').disabled = true;
                            }
                            console.log('Time slots populated in dropdown:', apptimeSelect.innerHTML);
                        });
                    } else {
                        // If the date is not in availableDates, disable the time dropdown,
                        // and disable the "Book Appointment" button
                        document.getElementById('apptime').disabled = true;
                        document.querySelector('button[name="app-submit"]').disabled = true; // Disable the button
                        alert('The doctor is not available on this date. Please choose another date.');
                    }
                } else {
                    alert('Please select a date.');
                }
            });

        });

        function showQueue(referenceNumber) {
            // Display the modal
            document.getElementById('queueModal').classList.remove('hidden');

            // Fetch queue details using AJAX
            fetch(`fetch_queue.php?ref=${referenceNumber}`)
                .then(response => response.text())
                .then(data => {
                    // Insert the fetched data into the modal content area
                    document.getElementById('queueContent').innerHTML = data;
                })
                .catch(error => {
                    console.error('Error fetching queue details:', error);
                    document.getElementById('queueContent').innerHTML = '<p>Error fetching queue details. Please try again later.</p>';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Close the modal when the close button is clicked
            const closeModalButton = document.getElementById('closeModal');
            if (closeModalButton) {
                closeModalButton.addEventListener('click', function() {
                    document.getElementById('queueModal').classList.add('hidden');
                });
            }

            // Close the modal when the bottom close button is clicked
            const closeModalBottomButton = document.getElementById('closeModalBottom');
            if (closeModalBottomButton) {
                closeModalBottomButton.addEventListener('click', function() {
                    document.getElementById('queueModal').classList.add('hidden');
                });
            }

            // Print the modal content when the print button is clicked
            const printButton = document.getElementById('printButton');
            if (printButton) {
                printButton.addEventListener('click', function() {
                    window.print();
                });
            }

            // Optional: Close the modal if the user clicks outside of it
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('queueModal');
                if (event.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        });
    </script>



</head>

<body>

    <div class="flex h-screen bg-gray-200">

        <!-- Sidebar -->
        <nav class="sidebar mt-4 space-y-6 w-64 p-6 flex-shrink-0 ml-4">
            <div class="text-center text-2xl font-bold">D.R. Health Medical and Diagnostic Center</div>

            <button onclick="showDiv('dashboard')" class="block py-2 px-4 rounded hover:bg-blue-700">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </button>
            <button onclick="showDiv('book-appointment')" class="block py-2 px-4 rounded hover:bg-blue-700">
                <i class="fas fa-calendar-plus"></i> Book Appointment
            </button>
            <button onclick="showDiv('appointment-history')" class="block py-2 px-4 rounded hover:bg-blue-700">
                <i class="fas fa-history"></i> Appointment History
            </button>
            <button onclick="showDiv('prescriptions')" class="block py-2 px-4 rounded hover:bg-blue-700">
                <i class="fas fa-file-prescription"></i> Prescriptions
            </button>
        </nav>



        <!-- Main Content -->
        <main class="flex-1 p-6">
            <h3 class="text-2xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($username); ?>!</h3>

            <!-- Dashboard -->
            <section id="dashboard" class="mb-8 section">
                <h4 class="text-xl font-semibold mb-4">Dashboard</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="card text-center">
                        <span class="text-3xl text-blue-500"><i class="fa fa-calendar"></i></span>
                        <h5 class="mt-4">Book My Appointment</h5>
                        <p><button onclick="showDiv('book-appointment')" class="btn-blue">Book Appointment</button></p>
                    </div>
                    <div class="card text-center">
                        <span class="text-3xl text-blue-500"><i class="fa fa-history"></i></span>
                        <h5 class="mt-4">My Appointments</h5>
                        <p><button onclick="showDiv('appointment-history')" class="btn-blue">View Appointment History</button></p>
                    </div>
                    <div class="card text-center">
                        <span class="text-3xl text-blue-500"><i class="fa fa-file-text"></i></span>
                        <h5 class="mt-4">Prescriptions</h5>
                        <p><button onclick="showDiv('prescriptions')" class="btn-blue">View Prescription List</button></p>
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
                                // Updated query to only select doctors whose status is not 'archived'
                                $result = mysqli_query($con, "SELECT username, spec FROM doctb WHERE status != 'archived'");
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
                            <select id="apptime" name="apptime" class="form-select block w-full mt-1" required disabled>
                                <option value="" disabled selected>Select a time slot</option>
                            </select>
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
                                $status = '';
                                $actionButton = '';

                                // Check if the appointment is cancelled by either the user or the doctor
                                if ($row['userStatus'] == '0' || $row['doctorStatus'] == '0') {
                                    $status = 'Cancelled';
                                    $actionButton = "Cancelled"; // No clickable action for cancelled appointments
                                } elseif ($row['userStatus'] == '1' && $row['doctorStatus'] == '1') {
                                    $status = 'Pending';
                                    $actionButton = "<a href='?cancel=1&ID=" . $row['ID'] . "' class='text-red-500 hover:underline'>Cancel</a>";
                                } elseif ($row['userStatus'] == '2' && $row['doctorStatus'] == '2') {
                                    // Check if a prescription exists for this appointment
                                    $prescriptionQuery = mysqli_query($con, "SELECT * FROM prestb WHERE pid='$pid' AND appdate='" . $row['appdate'] . "' AND apptime='" . $row['apptime'] . "'");
                                    if (mysqli_num_rows($prescriptionQuery) > 0) {
                                        $status = '<i class="fas fa-check-circle text-green-500"></i>'; // Only the icon, no text
                                    } else {
                                        $status = 'Confirmed';
                                    }
                                    // Generate the Show Queue button with an event listener to trigger the modal
                                    $referenceNumber = $row['reference_number']; // Assuming this column exists
                                    $actionButton = "<button onclick=\"showQueue('$referenceNumber')\" class='bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>View Queue</button>";
                                }

                                echo "<tr>
                        <td class='py-2 px-4'>" . htmlspecialchars($row['doctor']) . "</td>
                        <td class='py-2 px-4'>" . htmlspecialchars($row['appdate']) . "</td>
                        <td class='py-2 px-4'>" . htmlspecialchars($row['apptime']) . "</td>
                        <td class='py-2 px-4'>" . $status . "</td>
                        <td class='py-2 px-4'>" . $actionButton . "</td>
                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Modal Structure -->
            <div id="queueModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative">
                    <!-- Modal Close Button -->
                    <button id="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>

                    <!-- Modal Content -->
                    <div id="queueContent">
                        <!-- Queue details will be dynamically inserted here -->
                    </div>

                    <!-- Print and Close Buttons at the Bottom -->
                    <div class="flex justify-between mt-6">
                        <button id="printButton" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">Print</button>
                        <button id="closeModalBottom" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Close</button>
                    </div>
                </div>
            </div>



            <!-- Prescriptions -->
            <section id="prescriptions" class="section hidden">
                <h4 class="text-xl font-semibold mb-4">Prescriptions</h4>
                <div class="bg-white p-6 rounded shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 text-left text-gray-700">Patient ID</th>
                                <th class="py-2 px-4 text-left text-gray-700">Date</th>
                                <th class="py-2 px-4 text-left text-gray-700">Test Results</th>
                                <th class="py-2 px-4 text-left text-gray-700">Findings</th>
                                <th class="py-2 px-4 text-left text-gray-700">Prescription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($con, "SELECT * FROM prestb WHERE pid='$pid'");
                            while ($row = mysqli_fetch_assoc($query)) {
                                echo "<tr>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['pid']) . "</td>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['appdate']) . "</td>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['disease']) . "</td>
                                <td class='py-2 px-4'>" . htmlspecialchars($row['allergy']) . "</td>
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


    <!-- Success/Failure Modal -->
    <div id="messageModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative">
            <!-- Modal Close Button -->
            <button id="closeMessageModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>

            <!-- Modal Content -->
            <div id="messageContent" class="text-center">
                <!-- Success/Failure message will be dynamically inserted here -->
            </div>

            <!-- Close Button at the Bottom -->
            <div class="flex justify-center mt-6">
                <button id="closeModalButton" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Close</button>
            </div>
        </div>
    </div>


</body>

</html>