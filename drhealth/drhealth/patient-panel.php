<?php

// Start the session first
session_start();

// Check if the session is valid (i.e., the user is logged in)
if (!isset($_SESSION['email'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit();
}

// Include other necessary files after checking the session
include('db.php');
include('func.php');
include('newfunc.php');
include('navbar.php');

// Your database connection and other logic
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
function get_patient_info($con)
{
    // Get the logged-in patient's email from the session
    $patient_email = $_SESSION['email'];

    // Prepare the SQL query to get patient info
    $query = "SELECT fname, lname, age, contact, address FROM patreg WHERE email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $patient_email);  // Bind the patient's email
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if patient data is found
    if ($result->num_rows > 0) {
        $patient_info = $result->fetch_assoc();

        // Split the address into province, city, barangay
        $address_parts = explode(',', $patient_info['address']);
        $patient_info['province'] = trim($address_parts[0] ?? '');
        $patient_info['city'] = trim($address_parts[1] ?? '');
        $patient_info['barangay'] = trim($address_parts[2] ?? '');

        return $patient_info;
    } else {
        return false;  // Return false if no data is found
    }
}

// Get patient information from the database
$patient_info = get_patient_info($con);

// If patient info is found, populate form fields
if ($patient_info) {
    $first_name = htmlspecialchars($patient_info['fname']);
    $last_name = htmlspecialchars($patient_info['lname']);
    $age = htmlspecialchars($patient_info['age']);
    $contact = htmlspecialchars($patient_info['contact']);
    $province = htmlspecialchars($patient_info['province']);
    $city = htmlspecialchars($patient_info['city']);
    $barangay = htmlspecialchars($patient_info['barangay']);
} else {
    // Handle the case where no patient info is found (optional)
    echo "Patient information not found.";
}

function update_patient_info($con, $first_name, $last_name, $age, $contact_no, $new_password = null)
{
    // Get the logged-in patient's email from the session
    $patient_email = $_SESSION['email'];

    if (!empty($new_password)) {
        // If a new password is provided, update it along with other details
        $query = "UPDATE patreg SET fname = ?, lname = ?, age = ?, contact = ?, password = ? WHERE email = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ssssss", $first_name, $last_name, $age, $contact_no, $new_password, $patient_email);
    } else {
        // If no new password is provided, exclude the password field
        $query = "UPDATE patreg SET fname = ?, lname = ?, age = ?, contact = ? WHERE email = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("sssss", $first_name, $last_name, $age, $contact_no, $patient_email);
    }

    // Execute the query
    if ($stmt->execute()) {
        // If the update is successful, update the session with the new information
        $_SESSION['fname'] = $first_name;
        $_SESSION['lname'] = $last_name;
        $_SESSION['age'] = $age;
        $_SESSION['contact'] = $contact_no;
    } else {
        // Handle failure
        echo "Error updating profile: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    // Collect form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $contact_no = "+639" . $_POST['contact_no'];  // Prepend +639 to the contact number
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Combine address
    $address = $province . ', ' . $city . ', ' . $barangay;

    if (!empty($new_password)) {
        // Validate the new password and confirm password
        if ($new_password !== $confirm_password) {
            $_SESSION['alert_message'] = "New password and confirm password do not match.";
        } else {
            // Fetch the current hashed password from the database
            $stmt = $con->prepare("SELECT password FROM patreg WHERE email = ?");
            $stmt->bind_param("s", $_SESSION['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stored_password = $row['password'];

            // Verify current password
            if (!password_verify($current_password, $stored_password)) {
                $_SESSION['alert_message'] = "Current password is incorrect.";
            } else {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update patient info in database
                $stmt = $con->prepare("UPDATE patreg SET fname = ?, lname = ?, age = ?, contact = ?, address = ?, password = ? WHERE email = ?");
                $stmt->bind_param("sssssss", $first_name, $last_name, $age, $contact_no, $address, $hashed_password, $_SESSION['email']);
                $stmt->execute();
                $_SESSION['alert_message'] = "Patient information updated successfully.";
            }
        }
    } else {
        // Update patient info without changing the password
        $stmt = $con->prepare("UPDATE patreg SET fname = ?, lname = ?, age = ?, contact = ?, address = ? WHERE email = ?");
        $stmt->bind_param("ssssss", $first_name, $last_name, $age, $contact_no, $address, $_SESSION['email']);
        $stmt->execute();
        $_SESSION['alert_message'] = "Patient information updated successfully.";
    }

    // Redirect to the same page to show the alert message
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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
            <button onclick="showDiv('profile')" class="block py-2 px-4 rounded hover:bg-blue-700">
                <i class="fas fa-user"></i> Profile
            </button>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6">
            <h3 class="text-2xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($username); ?>!</h3>

            <section id="dashboard" class="mb-8 section">
                <h4 class="text-xl font-semibold mb-4">Dashboard</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Book My Appointment Card -->
                    <div class="card text-center">
                        <span class="text-3xl text-blue-500"><i class="fa fa-calendar"></i></span>
                        <h5 class="mt-4">Book My Appointment</h5>
                        <p><button onclick="showDiv('book-appointment')" class="btn-blue">Book Appointment</button></p>
                    </div>

                    <!-- My Appointments Card -->
                    <div class="card text-center">
                        <span class="text-3xl text-blue-500"><i class="fa fa-history"></i></span>
                        <h5 class="mt-4">My Appointments</h5>
                        <p><button onclick="showDiv('appointment-history')" class="btn-blue">View Appointment History</button></p>
                    </div>

                    <!-- Prescriptions Card -->
                    <div class="card text-center">
                        <span class="text-3xl text-blue-500"><i class="fa fa-file-text"></i></span>
                        <h5 class="mt-4">Prescriptions</h5>
                        <p><button onclick="showDiv('prescriptions')" class="btn-blue">View Prescription List</button></p>
                    </div>

                    <!-- Profile Card -->
                    <div class="card text-center">
                        <span class="text-3xl text-blue-500"><i class="fa fa-user"></i></span>
                        <h5 class="mt-4">My Profile</h5>
                        <p><button onclick="showDiv('profile')" class="btn-blue">View Profile</button></p>
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
                        <button type="button" id="select-doctor-btn"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Select Doctor</button>
                        <div id="availability-container" class="mt-6">
                            <h4 class="text-xl font-semibold mb-4">Doctor's Availability</h4>
                            <ul id="availability-list" class="list-disc pl-5"></ul>
                            <p id="start-end-time" class="mt-4"></p>
                        </div>
                        <div class="mb-4 mt-6">
                            <label for="appdate" class="block text-gray-700">Appointment Date:</label>
                            <input type="date" id="appdate" name="appdate" class="form-input block w-full mt-1" required
                                disabled>
                        </div>
                        <div class="mb-4">
                            <label for="apptime" class="block text-gray-700">Appointment Time:</label>
                            <select id="apptime" name="apptime" class="form-select block w-full mt-1" required disabled>
                                <option value="" disabled selected>Select a time slot</option>
                            </select>
                        </div>

                        <button type="submit" name="app-submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Book Appointment</button>
                    </form>
                </div>
            </section>

            <!-- Appointment History -->
            <section id="appointment-history" class="section hidden mt-8">
                <h4 class="text-xl font-semibold mb-4">Appointment History</h4>

                <!-- Search and Filter -->
                <div class="flex mb-4">
                    <form method="GET" class="flex space-x-4" onsubmit="showDiv('appointment-history');">
                        <!-- Search Input -->
                        <div class="flex items-center">
                            <input type="text" name="search" placeholder="Search Doctor, Appointment Date, or Status"
                                class="form-input px-4 py-2 border rounded-md w-full md:w-auto"
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
                        </div>

                        <!-- Status Filter Dropdown -->
                        <div class="flex items-center">
                            <select name="status" class="form-select px-4 py-2 border rounded-md">
                                <option value="">All</option>
                                <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo isset($_GET['status']) && $_GET['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Search</button>
                    </form>
                </div>

                <!-- Appointment History Table -->
                <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                    <table class="table-auto w-full min-w-max divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-700">Doctor</th>
                                <th class="px-4 py-2 text-left text-gray-700">Appointment Date</th>
                                <th class="px-4 py-2 text-left text-gray-700">Appointment Time</th>
                                <th class="px-4 py-2 text-left text-gray-700">Status</th>
                                <th class="px-4 py-2 text-left text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            // Capture search and status filter
                            $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
                            $statusFilter = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';

                            // Query to fetch appointment history with search and status filter functionality
                            $query = "SELECT * FROM appointmenttb WHERE pid='$pid'";

                            // Add search condition
                            if ($search) {
                                $query .= " AND (doctor LIKE '%$search%' OR appdate LIKE '%$search%' OR apptime LIKE '%$search%')";
                            }

                            // Add status filter condition
                            if ($statusFilter) {
                                if ($statusFilter == 'pending') {
                                    $query .= " AND userStatus = '1' AND doctorStatus = '1'";
                                } elseif ($statusFilter == 'confirmed') {
                                    $query .= " AND userStatus = '2' AND doctorStatus = '2'";
                                } elseif ($statusFilter == 'cancelled') {
                                    $query .= " AND (userStatus = '0' OR doctorStatus = '0')";
                                }
                            }

                            $query .= " ORDER BY appdate DESC, apptime DESC";

                            // Execute the query
                            $result = mysqli_query($con, $query);

                            // Loop through and display the results
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status = '';
                                $actionButton = '';

                                // Determine status and action button
                                if ($row['userStatus'] == '0' || $row['doctorStatus'] == '0') {
                                    $status = 'Cancelled';
                                    $actionButton = 'Cancelled';
                                } elseif ($row['userStatus'] == '1' && $row['doctorStatus'] == '1') {
                                    $status = 'Pending';
                                    $actionButton = "<a href='?cancel=1&ID=" . $row['ID'] . "' class='text-red-500 hover:underline'>Cancel</a>";
                                } elseif ($row['userStatus'] == '2' && $row['doctorStatus'] == '2') {
                                    // Check for a prescription
                                    $prescriptionQuery = mysqli_query($con, "SELECT * FROM prestb WHERE pid='$pid' AND appdate='" . $row['appdate'] . "' AND apptime='" . $row['apptime'] . "'");
                                    $status = (mysqli_num_rows($prescriptionQuery) > 0) ? '<i class="fas fa-check-circle text-green-500"></i>' : 'Confirmed';
                                    $referenceNumber = $row['reference_number']; // Assuming this column exists
                                    $actionButton = "<button onclick=\"showQueue('$referenceNumber')\" class='bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>View Queue</button>";
                                }

                                echo "<tr>
            <td class='px-4 py-2 whitespace-nowrap'>" . htmlspecialchars($row['doctor']) . "</td>
            <td class='px-4 py-2 whitespace-nowrap'>" . htmlspecialchars($row['appdate']) . "</td>
            <td class='px-4 py-2 whitespace-nowrap'>" . htmlspecialchars($row['apptime']) . "</td>
            <td class='px-4 py-2 whitespace-nowrap'>$status</td>
            <td class='px-4 py-2 whitespace-nowrap'>$actionButton</td>
          </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>


            <!-- Modal Structure -->
            <div id="queueModal"
                class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative">
                    <!-- Modal Close Button -->
                    <button id="closeModal"
                        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>

                    <!-- Modal Content -->
                    <div id="queueContent">
                        <!-- Queue details will be dynamically inserted here -->
                    </div>

                    <!-- Print and Close Buttons at the Bottom -->
                    <div class="flex justify-between mt-6">
                        <button id="printButton"
                            class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">Print</button>
                        <button id="closeModalBottom"
                            class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Close</button>
                    </div>
                </div>
            </div>

            <!-- Prescriptions -->
            <section id="prescriptions" class="section hidden mt-8">
                <h4 class="text-xl font-semibold mb-4">Prescriptions</h4>

                <!-- Search Bar -->
                <div class="flex mb-4">
                    <form method="GET" class="flex space-x-4">
                        <!-- Search Input -->
                        <div class="flex items-center">
                            <input type="text" name="search_prescription"
                                placeholder="Search Patient ID, Date, Test Results, or Findings"
                                class="form-input px-4 py-2 border rounded-md"
                                value="<?php echo isset($_GET['search_prescription']) ? htmlspecialchars($_GET['search_prescription']) : ''; ?>" />
                        </div>

                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Search</button>
                        <!-- Add a hidden input to maintain the active section -->
                        <input type="hidden" name="section" value="prescriptions">
                    </form>
                </div>

                <!-- Responsive Table Wrapper -->
                <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                    <table class="table-auto w-full min-w-max divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-700">Patient ID</th>
                                <th class="px-4 py-2 text-left text-gray-700">Date</th>
                                <th class="px-4 py-2 text-left text-gray-700">Test Results</th>
                                <th class="px-4 py-2 text-left text-gray-700">Findings</th>
                                <th class="px-4 py-2 text-left text-gray-700">Prescription</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            // Capture the search term
                            $search = isset($_GET['search_prescription']) ? mysqli_real_escape_string($con, $_GET['search_prescription']) : '';

                            // Modify query to include search functionality
                            $query = "SELECT * FROM prestb WHERE pid='$pid'";
                            if ($search) {
                                $query .= " AND (pid LIKE '%$search%' OR appdate LIKE '%$search%' OR disease LIKE '%$search%' OR allergy LIKE '%$search%' OR prescription LIKE '%$search%')";
                            }

                            // Execute the query
                            $result = mysqli_query($con, $query);

                            // Display results
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>
            <td class='px-4 py-2 whitespace-nowrap'>" . htmlspecialchars($row['pid']) . "</td>
            <td class='px-4 py-2 whitespace-nowrap'>" . htmlspecialchars($row['appdate']) . "</td>
            <td class='px-4 py-2 whitespace-nowrap'>" . htmlspecialchars($row['disease']) . "</td>
            <td class='px-4 py-2 whitespace-nowrap'>" . htmlspecialchars($row['allergy']) . "</td>
            <td class='px-4 py-2 whitespace-nowrap'>" . htmlspecialchars($row['prescription']) . "</td>
          </tr>";
                            }

                            // Display a message if no records found
                            if (mysqli_num_rows($result) == 0) {
                                echo "<tr>
            <td colspan='5' class='text-center px-4 py-4 text-gray-500'>No prescriptions found.</td>
          </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php
            if (isset($_SESSION['alert_message'])) {
                // Output the alert message in JavaScript
                echo "<script>alert('" . $_SESSION['alert_message'] . "');</script>";

                // Optionally, clear the alert message after it's shown to prevent it from showing again
                unset($_SESSION['alert_message']);
            }
            ?>

            <section id="profile" class="section hidden mt-8">
                <form method="post" action="">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-input mt-1 block w-full p-2 border rounded" placeholder="First Name" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" value="<?= $first_name ?? ''; ?>" required>
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Last Name" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" value="<?= $last_name ?? ''; ?>" required>
                        </div>

                        <!-- Age -->
                        <div>
                            <label for="age" class="block text-sm font-medium text-gray-700">Age</label>
                            <input type="number" name="age" id="age" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Age" min="0" value="<?= $age ?? ''; ?>" required>
                        </div>

                        <!-- Contact No. with +639 Prefix -->
                        <div>
                            <label for="contact_no" class="block text-sm font-medium text-gray-700">Contact No.</label>
                            <div class="flex items-center mt-1">
                                <!-- Display +639 prefix centered vertically -->
                                <span class="text-gray-700 mr-2 flex items-center">(+639)</span> <!-- Center-align prefix -->
                                <!-- Input field for phone number -->
                                <input type="tel" id="contact_no" name="contact_no" class="form-input mt-1 block w-full p-2 border rounded-md" placeholder="Enter Contact No." required pattern="\d{9}" title="Enter a valid 9-digit phone number (without +639)" value="<?= substr($contact ?? '', 4); ?>" minlength="9" maxlength="9" />
                            </div>
                        </div>

                        <!-- Address Fields -->
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">Province</label>
                            <input type="text" name="province" id="province" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Province" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" value="<?= $province ?? ''; ?>" required>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="city" id="city" class="form-input mt-1 block w-full p-2 border rounded" placeholder="City" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" value="<?= $city ?? ''; ?>" required>
                        </div>

                        <div>
                            <label for="barangay" class="block text-sm font-medium text-gray-700">Barangay</label>
                            <input type="text" name="barangay" id="barangay" class="form-input mt-1 block w-full p-2 border rounded" placeholder="Barangay" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" value="<?= $barangay ?? ''; ?>" required>
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
            </section>


            <!-- Success/Failure Modal -->
            <div id="messageModal"
                class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative">
                    <!-- Modal Close Button -->
                    <button id="closeMessageModal"
                        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>

                    <!-- Modal Content -->
                    <div id="messageContent" class="text-center">
                        <!-- Success/Failure message will be dynamically inserted here -->
                    </div>

                    <!-- Close Button at the Bottom -->
                    <div class="flex justify-center mt-6">
                        <button id="closeModalButton"
                            class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Close</button>
                    </div>
                </div>
            </div>


</body>

</html>