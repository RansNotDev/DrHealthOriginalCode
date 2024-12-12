<?php
// registration_confirmation.php
session_start();
if (isset($_SESSION['fname']) && isset($_SESSION['lname'])) {
    echo "<h1>Registration Successful</h1>";
    echo "<p>Welcome, " . $_SESSION['fname'] . " " . $_SESSION['lname'] . ".</p>";
    echo "<p>Your details have been saved successfully.</p>";
} else {
    echo "No session data found. Please register first.";
}
?>
