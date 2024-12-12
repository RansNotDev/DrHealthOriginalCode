<?php
session_start();

// Get the JSON data from the request
$inputData = json_decode(file_get_contents('php://input'), true);

if ($inputData) {
    $_SESSION['fname'] = $inputData['fname'];
    $_SESSION['lname'] = $inputData['lname'];
    $_SESSION['age'] = $inputData['age'];
    $_SESSION['gender'] = $inputData['gender'];
    $_SESSION['email'] = $inputData['email'];
    $_SESSION['contact'] = $inputData['contact'];
    $_SESSION['address'] = $inputData['address'];
    $_SESSION['password'] = $inputData['password'];

    // Return the session data as a JSON response for debugging
    echo json_encode($_SESSION);
} else {
    echo json_encode(['error' => 'Failed to save data to session']);
}
?>
