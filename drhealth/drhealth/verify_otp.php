<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    $otp = $input['otp'] ?? '';

    if ($otp == $_SESSION['otp']) {
        $_SESSION['otp_verified'] = true;  // Flag for successful OTP verification
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
