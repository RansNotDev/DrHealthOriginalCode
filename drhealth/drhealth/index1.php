<?php
// include("header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>D.R. Health Medical and Diagnostic Center</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="style2.css">

    <style type="text/css">
        #inputbtn:hover { cursor: pointer; }
        .card {
            background: #f8f9fa;
            border-radius: 5%;
        }
    </style>
    <script>
        function checkPasswordMatch() {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('cpassword').value;
            if (password == confirmPassword) {
                document.getElementById('message').style.color = '#5dd05d';
                document.getElementById('message').innerHTML = 'Matched';
            } else {
                document.getElementById('message').style.color = '#f55252';
                document.getElementById('message').innerHTML = 'Not Matching';
            }
        }

        function alphaOnly(event) {
            var key = event.keyCode;
            return ((key >= 65 && key <= 90) || key == 8 || key == 32);
        }

        function checkPasswordLength() {
            var password = document.getElementById("password").value;
            if (password.length < 6) {
                alert("Password must be at least 6 characters long. Try again!");
                return false;
            }
            return true;
        }

        function toggleForm(formType) {
            if (formType === 'login') {
                document.getElementById('patient-login-form').style.display = 'block';
                document.getElementById('patient-register-form').style.display = 'none';
            } else {
                document.getElementById('patient-login-form').style.display = 'none';
                document.getElementById('patient-register-form').style.display = 'block';
            }
        }

        window.onload = function() {
            toggleForm('login');
        };
    </script>
</head>
<body style="background-color: whitesmoke;">
    <nav class="navbar navbar-expand navbar-dark fixed-top" id="mainNav" style="background-color:#17a2b8;">
        <div class="container">
            <a class="navbar-brand js-scroll-trigger" href="home.html" style="font-family: 'IBM Plex Sans', sans-serif; display: flex; align-items: center;">
                <img src="images/logo.png" alt="Logo" style="height: 60px; margin-right: 5px;">
                <h4>D.R. HEALTH MEDICAL AND DIAGNOSTIC CENTER</h4>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="home.html" style="color: white;">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="about.html" style="color: white;">ABOUT US</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="services.html" style="color: white;">SERVICES</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="index.php" style="color: white;">LOGIN</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid" style="margin-top: 60px; margin-bottom: 60px; color: #34495E;">
        <div class="row">
            <div class="col-md-7" style="padding-left: 180px;">
                <div style="animation: mover 1s infinite alternate;">
                    <img src="images/ambulance.png" alt="" style="width: 20%; padding-left: 40px; margin-top: 250px; margin-left: 45px; margin-bottom: 15px;">
                </div>
                <div style="color: green;">
                    <h4 style="font-family: 'IBM Plex Sans', sans-serif;">We are here for you!</h4>
                </div>
            </div>
            <div class="col-md-4" style="margin-top: 5%; right: 8%">
                <div class="card" style="font-family: 'IBM Plex Sans', sans-serif;">
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="patient" role="tabpanel" aria-labelledby="home-tab">
                                <div id="patient-login-form">
                                    <h3 class="register-heading">Patient Login</h3>
                                    <form method="POST" action="func.php">
                                        <div class="form-group">
                                            <label>Email-ID:</label>
                                            <input type="text" name="email" class="form-control" placeholder="Enter email ID" required/>
                                        </div>
                                        <div class="form-group">
                                            <label>Password:</label>
                                            <input type="password" class="form-control" name="password2" placeholder="Enter password" required/>
                                        </div>
                                        <input type="submit" id="inputbtn" name="patsub" value="Login" class="btn btn-primary">
                                        <a href="forgot_password.php" class="btn btn-link">Forgot Password?</a>
                                        <br>
                                        <a href="javascript:void(0);" onclick="toggleForm('register');">Don't have an account? Register here</a>
                                    </form>
                                </div>
                                <div id="patient-register-form" style="display: none;">
                                    <h3 class="register-heading">Patient Registration</h3>
                                    <form method="post" action="func2.php">
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="First Name" name="fname" onkeydown="return alphaOnly(event);" required/>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="Last Name" name="lname" onkeydown="return alphaOnly(event);" required/>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="Age" name="age" required/>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="Address" id="address" name="address" required/>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control" placeholder="Password" id="password" name="password" onkeyup="checkPasswordMatch();" required/>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control" placeholder="Confirm Password" id="cpassword" name="cpassword" onkeyup="checkPasswordMatch();" required/>
                                            <span id="message"></span>
                                        </div>

                                        <div class="form-group">
                                            <input type="tel" minlength="11" maxlength="11" name="contact" class="form-control" placeholder="Contact No." required/>
                                        </div>
                                        <div class="form-group">
                                            <input type="email" class="form-control" placeholder="Email" name="email" required/>
                                        </div>
                                        <div class="form-group">
                                            <div class="maxl">
                                                <label class="radio inline">
                                                    <input type="radio" name="gender" value="Male" checked>
                                                    <span> Male </span>
                                                </label>
                                                <label class="radio inline">
                                                    <input type="radio" name="gender" value="Female">
                                                    <span> Female </span>
                                                </label>
                                            </div>
                                        <input type="submit" class="btn btn-primary" name="patsub1" onclick="return checkPasswordLength();" value="Register"/>
                                            <br>
                                            <a href="javascript:void(0);" onclick="toggleForm('login');">Already have an account? Login here</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>
