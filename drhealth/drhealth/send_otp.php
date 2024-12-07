<?php
session_start();
require 'vendor/autoload.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/SMTP.php';

function send_email($to, $subject, $message) {
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    try {
        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'drhealthclinicc@gmail.com';              // SMTP username
        $mail->Password   = 'zrws odaq pzlk hzen';                  // SMTP password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('drhealthclinicc@gmail.com', 'D.R. Health Medical and Diagnostic Center');    // Set the sender's email address and name
        $mail->addAddress($to);                                     // Add a recipient

        // Content
        $mail->isHTML(true);                                        // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['email'])) {
        echo json_encode(['success' => false, 'error' => 'Email is required.']);
        exit();
    }
 
    $email = $input['email'];
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;

    $subject = 'OTP for Registration';
    $message = "Your OTP for registration is: <b>$otp</b>";

    if (send_email($email, $subject, $message)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Could not send email.']);
    }
}
?>
