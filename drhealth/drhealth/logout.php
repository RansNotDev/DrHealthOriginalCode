<?php
// Start the session
session_start();

// Destroy the session
session_unset(); // Unsets all session variables
session_destroy(); // Destroys the session

// Optionally, you can delete the session cookie if it's set
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect the user to the homepage or login page
header("Location: index.php"); // Change this to your preferred redirect location
exit();
?>
