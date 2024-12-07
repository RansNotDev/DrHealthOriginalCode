<?php
include('db.php'); // Ensure this includes the updated db.php

if (isset($_GET['ref'])) {
    $referenceNumber = $_GET['ref'];

    // Use $conn instead of $con
    if ($conn) {
        $query = mysqli_query($conn, "SELECT * FROM appointmenttb WHERE reference_number='$referenceNumber'");
        $appointment = mysqli_fetch_assoc($query);

        if ($appointment) {
            echo "<h2 class='text-2xl font-bold mb-4 text-center'>D.R. HEALTH MEDICAL AND DIAGNOSTIC CENTER</h2>";
            echo "<div class='text-left'>";
            echo "<p class='mb-2'><span class='font-semibold'>Reference Number:</span> " . htmlspecialchars($appointment['reference_number']) . "</p>"; // Display reference number
            echo "<p class='mb-2'><span class='font-semibold'>Patient Name:</span> " . htmlspecialchars($appointment['fname']) . " " . htmlspecialchars($appointment['lname']) . "</p>";
            echo "<p class='mb-2'><span class='font-semibold'>Doctor:</span> " . htmlspecialchars($appointment['doctor']) . "</p>";
            echo "<p class='mb-2'><span class='font-semibold'>Appointment Date:</span> " . htmlspecialchars($appointment['appdate']) . "</p>";
            echo "<p class='mb-2'><span class='font-semibold'>Appointment Time:</span> " . htmlspecialchars($appointment['apptime']) . "</p>";
            echo "</div>";
        } else {
            echo "<p class='text-red-500'>Invalid reference number.</p>";
        }
    } else {
        echo "<p class='text-red-500'>Database connection error.</p>";
    }
} else {
    echo "<p class='text-red-500'>No reference number provided.</p>";
}
?>
