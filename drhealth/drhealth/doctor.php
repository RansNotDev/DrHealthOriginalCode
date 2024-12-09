<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D.R. Health Medical and Diagnostic Center</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: whitesmoke;
            font-family: 'IBM Plex Sans', sans-serif;
            background-image: url('images/3.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .form-control {
            border-radius: 0.75rem;
        }

        .login-container {
            max-width: 400px;
            margin: 10% auto;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            background-image: url('images/bck1.png');
            background-size: cover;
            background-position: center;
        }

        .register-heading {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .btnRegister {
            margin-top: 20px;
            width: 100%;
            border: none;
            border-radius: 1.5rem;
            padding: 10px;
            background: #0062cc;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btnRegister:hover {
            background: #0056b3;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-password {
            position: absolute;
            top: 70%;
            right: 15px;
            /* Adjusted the right property to align it properly */
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 2;
            border: none;
            background: none;
            padding: 0;
            font-size: 1.2rem;
            color: #0062cc;
        }

        .form-control:focus {
            border-color: #0062cc;
            box-shadow: none;
        }

        .form-group input:hover {
            border-color: #0056b3;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }

        .footer a {
            color: #0062cc;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .form-group i {
            margin-right: 8px;
            color: #0062cc;
            /* Match your theme color */
        }

        /* Responsive adjustments */
        @media (max-width: 767px) {
            .login-container {
                margin-top: 15%;
                padding: 20px;
                max-width: 90%;
                /* Make the container wider on small screens */
            }

            .register-heading {
                font-size: 1.5rem;
                /* Slightly smaller font size */
            }

            .btnRegister {
                padding: 15px;
                font-size: 1.1rem;
                /* Larger button on small screens */
            }

            .error-message {
                font-size: 0.9rem;
                /* Smaller error message text */
            }
        }
    </style>
</head>

<body>
    <div class="container login-container">
        <div class="register-right">
            <h3 class="register-heading">DOCTOR</h3>
            <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
                <div class="error-message">Incorrect username or password.</div>
            <?php endif; ?>
            <form method="post" action="func1.php">
                <div class="form-group">
                    <label for="email3"><i class="fa fa-envelope"></i> Email</label>
                    <input type="email" id="email3" class="form-control" placeholder="Email" name="email3" required />
                </div>
                <div class="form-group password-toggle">
                    <label for="password3"><i class="fa fa-lock"></i> Password</label>
                    <input type="password" id="password3" class="form-control" placeholder="Password" name="password3" required />
                    <span class="toggle-password" onclick="togglePasswordVisibility()">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="submit" class="btnRegister" name="docsub1" value="Login">
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password3");
            var toggleIcon = document.querySelector(".toggle-password i");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>

</html>