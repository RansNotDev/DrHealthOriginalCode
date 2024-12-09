<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);

    // Store individual fields in the session
    $_SESSION['fname'] = $input['fname'];
    $_SESSION['lname'] = $input['lname'];
    $_SESSION['age'] = $input['age'];
    $_SESSION['barangay'] = $input['barangay']; // Store Barangay
    $_SESSION['municipality'] = $input['municipality']; // Store Municipality
    $_SESSION['city'] = $input['city']; // Store City

    // Combine address components and store as address
    $_SESSION['address'] = $input['barangay'] . ", " . $input['municipality'] . ", " . $input['city'];

    $_SESSION['gender'] = $input['gender'];
    $_SESSION['email'] = $input['email'];
    $_SESSION['password'] = $input['password'];  // Store password in session
    $_SESSION['confirm_password'] = $input['confirm_password'];  // Store confirm password in session
    $_SESSION['contact'] = $input['contact'];

    // Send success response back to the client
    echo json_encode(['success' => true]);
}
?>
