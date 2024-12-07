<?php
session_start();
include 'db.php';
$otpErr = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $verification_code = $_POST['verification_code'];
    $stored_verification_code = $_SESSION['verification_code'];
    
    if ($verification_code == $stored_verification_code) {
 
        $email = $_SESSION['temp_email'];
        $password = $_POST['password'];
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        $query = "UPDATE patreg
                  SET password = '$hashed_password' 
                  WHERE email = '$email'";
        
        // Execute the query
        if (mysqli_query($conn, $query)) {
            session_destroy();
            header("Location: index.php");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    } else {
      
        $otpErr = "Invalid OTP";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Login</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/ruang-admin.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation bar with logo image -->
    <div class="overlay"></div> 
    <nav class="navbar navbar-light ">
        <div class="container1">
            <div class="logo-container">
                <img src="img\logo\logo1.png" alt="Logo" height="90" class="logo">
            </div>
        </div>
        <a href="#" class="login-text">LOGIN</a>
    </nav>
    <h1 class="text-center">Login</h1> <!-- Larger heading -->
    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-sm-12">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h2>Patient</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <input type="password" name="password" class="form-control" placeholder="New Password" required>
                                <input type="text" id="verification_code" name="verification_code"  class="form-control" placeholder="OTP" required> 
                                <small class="text-danger"><?php echo $otpErr; ?></small>
                            </div>
                            <div class="form-group text-center">
                                <input type="submit" value="Reset Password" class="btn btn-primary">
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>
</body>

</html>