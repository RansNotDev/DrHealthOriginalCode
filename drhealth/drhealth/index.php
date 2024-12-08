<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D.R. Health Medical and Diagnostic Center</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans|Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: whitesmoke;
            font-family: 'Poppins', sans-serif;
            background-image: url('images/3.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .navbar {
            background-color: #0D409E;
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
        }

        .navbar-brand img {
            height: 60px;
            width: auto;
        }

        .navbar-nav .nav-item .nav-link {
            color: white;
            font-family: 'Poppins', sans-serif;
        }

        .navbar-nav .nav-item .nav-link i {
            margin-right: 5px;
        }

        .form-container {
            margin-top: 1000px;
            /* Adjusted to lower the position further */
            background: linear-gradient(135deg, #ffffff, #e0f7fa);
            border-radius: 10px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            margin: 10% auto;
            transition: all 0.3s ease;
        }

        .form-container:hover {
            box-shadow: 0px 0px 30px 0px rgba(0, 0, 0, 0.2);
        }

        .form-heading {
            text-align: center;
            margin-bottom: 20px;
            color: #17a2b8;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .form-control {
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #17a2b8;
            box-shadow: 0 0 8px rgba(23, 162, 184, 0.2);
        }

        .btn-primary {
            background: #0D409E;
            border: none;
            border-radius: 1rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-primary:hover {
            background: #0D409E;
            box-shadow: 0 0 10px rgba(23, 162, 184, 0.5);
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }

        .footer a {
            color: #17a2b8;
            text-decoration: none;
            font-weight: bold;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        #message {
            display: block;
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
            color: red;
        }

        .nav-tabs .nav-link.active {
            background-color: #0D409E;
            color: white;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .form-group label {
            font-weight: 600;
            color: #0D409E;
        }
    </style>
    <script>
        function toggleForm(formType) {
            var loginTab = document.getElementById('loginTab');
            var registerTab = document.getElementById('registerTab');

            if (formType === 'login') {
                document.getElementById('patient-login-form').style.display = 'block';
                document.getElementById('patient-register-form').style.display = 'none';
                loginTab.classList.add('active');
                registerTab.classList.remove('active');
            } else {
                document.getElementById('patient-login-form').style.display = 'none';
                document.getElementById('patient-register-form').style.display = 'block';
                registerTab.classList.add('active');
                loginTab.classList.remove('active');
            }
        }

        function sendOtp() {
            var fname = document.getElementById('fname').value;
            var lname = document.getElementById('lname').value;
            var age = document.getElementById('age').value;

            var city = document.getElementById('city').value;
            var municipality = document.getElementById('municipality').value;
            var barangay = document.getElementById('barangay').value;

            var gender = document.querySelector('input[name="gender"]:checked').value;
            var email = document.getElementById('register-email').value;
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('cpassword').value;
            var contact = document.getElementById('contact').value;

            if (password !== confirmPassword) {
                document.getElementById('message').style.display = 'block';
                return;
            }

            fetch('store_user_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    fname: fname,
                    lname: lname,
                    age: age,
                    barangay: barangay,
                    municipality: municipality,
                    city: city, 
                    gender: gender,
                    email: email,
                    password: password,
                    confirm_password: confirmPassword,
                    contact: contact
                })
            }).then(() => {
                fetch('send_otp.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: email
                        })
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#otpModal').modal('show');
                        } else {
                            alert('Failed to send OTP. Please try again.');
                        }
                    });
            });
        }


        function verifyOtp() {
            var otp = document.getElementById('otpInput').value;

            console.log("Verifying OTP...");

            // Verify OTP via AJAX
            fetch('verify_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        otp: otp
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("OTP verified successfully. Inserting data into the database...");

                        // Insert data into the database after OTP verification
                        fetch('func2.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: "patsub1=1"
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    console.log("Data inserted successfully. Redirecting to patient-panel.php.");
                                    window.location.href = "patient-panel.php"; // Redirect to patient panel
                                } else {
                                    console.log("Error inserting data:", result.message);
                                    alert("Error inserting data. Please try again.");
                                }
                            });
                    } else {
                        console.log("Invalid OTP.");
                        document.getElementById('otpError').style.display = 'block'; // Show error message
                    }
                });
        }


        window.onload = function() {
            toggleForm('login'); // Default to the login form when the page loads
        };

        // Alpha-Only Validation for First Name and Last Name
        function alphaOnly(event) {
            var key = event.keyCode;
            return ((key >= 65 && key <= 90) || key == 8 || key == 32);
        }
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand js-scroll-trigger" href="home.html">
                <img src="images/logo.png" alt="Logo">
                <h4 class="d-inline align-middle">D.R. HEALTH MEDICAL AND DIAGNOSTIC CENTER</h4>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="home.html">
                            <i class="fa fa-home"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="about.html">
                            <i class="fa fa-info-circle"></i>About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.html" style="color: white;">
                            <i class="fa fa-stethoscope"></i> Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-scroll-trigger" href="index.php">
                            <i class="fa fa-sign-in-alt"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container form-container">
        <ul class="nav nav-tabs" id="formTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="login" aria-selected="true">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="register-tab" data-toggle="tab" href="#register" role="tab" aria-controls="register" aria-selected="false">Register</a>
            </li>
        </ul>
        <div class="tab-content" id="formTabsContent">
            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                <div class="login-content">
                    <h3 class="form-heading">Login</h3>
                    <form method="POST" action="func.php">
                        <div class="form-group">
                            <label for="email"><i class="fa fa-envelope"></i> Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"></span>
                                </div>
                                <input type="text" id="email" name="email" class="form-control" placeholder="Enter email ID" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password2"><i class="fa fa-lock"></i> Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"></span>
                                </div>
                                <input type="password" id="password2" name="password2" class="form-control" placeholder="Enter password" required />
                            </div>
                        </div>


                        <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                        <div class="form-group">
                            <input type="submit" id="inputbtn" name="patsub" value="LOGIN" class="btn btn-primary btn-block" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                <div class="register-content">
                    <h3 class="form-heading">Register</h3>
                    <form id="registration-form" method="POST" action="func2.php" onsubmit="return checkPasswordLength();">
                        <div class="form-group">
                            <label for="fname"><i class="fa fa-user"></i> First Name</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="First Name"
                                id="fname"
                                name="fname"
                                oninput="restrictSpecialChars(this)"
                                required />
                        </div>
                        <div class="form-group">
                            <label for="lname"><i class="fa fa-user"></i> Last Name</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Last Name"
                                id="lname"
                                name="lname"
                                oninput="restrictSpecialChars(this)"
                                required />
                        </div>
                        <div class="form-group">
                            <label for="age"><i class="fa fa-calendar-alt"></i> Age</label>
                            <input
                                type="number"
                                class="form-control"
                                placeholder="Age"
                                id="age"
                                name="age"
                                min="1"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                required />
                        </div>

                        <!-- Address Fields in One Row -->
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="city"><i class="fa fa-map-marker-alt"></i> City</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="City"
                                    id="city"
                                    name="city"
                                    oninput="restrictSpecialChars(this)"
                                    required />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="municipality"><i class="fa fa-map-marker-alt"></i> Municipality</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Municipality"
                                    id="municipality"
                                    name="municipality"
                                    oninput="restrictSpecialChars(this)"
                                    required />
                            </div>
                            <div class="form-group col-md-4">
                                <label for="barangay"><i class="fa fa-map-marker-alt"></i> Barangay</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Barangay"
                                    id="barangay"
                                    name="barangay"
                                    oninput="restrictSpecialChars(this)"
                                    required />
                            </div>
                        </div>

                        <!-- Password Field with validation -->
                        <div class="form-group">
                            <label for="password"><i class="fa fa-lock"></i> Password</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control"
                                    placeholder="Password"
                                    id="password"
                                    name="password"
                                    onkeyup="checkPasswordMatch();"
                                    required />
                                <div class="input-group-append">
                                    <button
                                        class="btn btn-outline-secondary"
                                        type="button"
                                        onclick="togglePasswordVisibility('password', 'toggleIcon')">
                                        <i id="toggleIcon" class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label for="cpassword"><i class="fa fa-lock"></i> Confirm Password</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control"
                                    placeholder="Confirm Password"
                                    id="cpassword"
                                    name="cpassword"
                                    onkeyup="checkPasswordMatch();"
                                    required />
                                <div class="input-group-append">
                                    <button
                                        class="btn btn-outline-secondary"
                                        type="button"
                                        onclick="togglePasswordVisibility('cpassword', 'toggleIconConfirm')">
                                        <i id="toggleIconConfirm" class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="message" class="form-text mt-1"></div>
                        </div>

                        <div class="form-group">
                            <label for="contact"><i class="fa fa-phone"></i> Contact No.</label>
                            <input
                                type="tel"
                                minlength="11"
                                maxlength="11"
                                id="contact"
                                name="contact"
                                class="form-control"
                                placeholder="Contact No."
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                required />
                        </div>
                        <div class="form-group">
                            <label for="register-email"><i class="fa fa-envelope"></i> Email</label>
                            <input
                                type="email"
                                class="form-control"
                                placeholder="Email"
                                id="register-email"
                                name="email"
                                required />
                        </div>
                        <div class="form-group">
                            <label><i class="fa fa-venus-mars"></i> Gender</label>
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
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary btn-block" onclick="sendOtp()">Send OTP</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    </div>

    <!-- OTP Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otpModalLabel">Verify OTP</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Please enter the OTP sent to your email:</p>
                    <input type="text" id="otpInput" class="form-control" placeholder="Enter OTP" maxlength="6" />
                    <div id="otpError" style="color: red; display: none;">Invalid OTP. Please try again.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="verifyOtp()">Verify OTP</button>
                </div>
            </div>
        </div>
    </div>


    <div class="footer">
        <p>&copy; 2024 D.R. Health Medical and Diagnostic Center. All rights reserved.</p>
        <a href="privacy.html">Privacy Policy</a> | <a href="terms.html">Terms of Service</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $(".toggle-password").click(function() {
                var target = $(this).data("target");
                $(this).find("i").toggleClass("fa-eye fa-eye-slash");
                var input = $(target);
                if (input.attr("type") === "password") {
                    input.attr("type", "text");
                } else {
                    input.attr("type", "password");
                }
            });
        });

        function validateRegistrationForm() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("cpassword").value;
            const message = document.getElementById("message");

            if (password !== confirmPassword) {
                message.textContent = "Passwords do not match.";
                return false;
            }
            message.textContent = "";
            return true;
        }

        function alphaOnly(event) {
            const charCode = event.charCode || event.keyCode;
            if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
                event.preventDefault();
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('cpassword').value;
            const messageElement = document.getElementById('message');



            // Check if passwords match
            if (password && confirmPassword) {
                if (password !== confirmPassword) {
                    messageElement.innerHTML = '<i class="fa fa-exclamation-circle text-danger"></i> Passwords do not match!';
                    messageElement.style.color = 'red';
                } else {
                    messageElement.innerHTML = '<i class="fa fa-check-circle text-success"></i> Passwords match.';
                    messageElement.style.color = 'green';
                }
            } else {
                messageElement.innerHTML = ''; // Clear message when no input
            }
        }


        // Additional function to check password length on form submission
        function checkPasswordLength() {
            const password = document.getElementById('password').value;
            const passwordHint = document.getElementById('passwordHint');

            if (password.length < 8) {
                passwordHint.innerHTML = 'Password must be at least 8 characters long.';
                passwordHint.style.display = 'block';
                return false;
            } else {
                passwordHint.style.display = 'none';
            }
            return checkPasswordMatch();
        }

        function togglePasswordVisibility(passwordFieldId, iconId) {
            const passwordField = document.getElementById(passwordFieldId);
            const icon = document.getElementById(iconId);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Restrict special characters in names and address-related fields
        function restrictSpecialChars(input) {
            input.value = input.value.replace(/[^a-zA-Z\s]/g, '');
        }
    </script>
</body>

</html>