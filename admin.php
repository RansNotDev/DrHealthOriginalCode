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
            background-image: url('images/h1.jpg');
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
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 2;
            border: none;
            background: none;
            padding: 0;
        }

        .form-group i {
            margin-right: 8px;
            color: #0062cc;
        }

        .form-control:focus {
            border-color: #0062cc;
            box-shadow: none;
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
    </style>
</head>

<body>
    <div class="container login-container">
        <div class="register-right">
            <h3 class="register-heading">ADMIN</h3>
            <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid'): ?>
                <div class="error-message">Incorrect username or password.</div>
            <?php endif; ?>
            <form method="post" action="func3.php" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="username"><i class="fa fa-user"></i> Username</label>
                    <input type="text" class="form-control" id="username" placeholder="User Name" name="username1" required>
                </div>
                <div class="form-group password-toggle">
                    <label for="password"><i class="fa fa-lock"></i> Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" placeholder="Password" name="password2" required>
                        <div class="input-group-append">
                            <span class="toggle-password" onclick="togglePasswordVisibility()">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <input type="submit" class="btnRegister" name="adsub" value="Login">
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
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

        function validateForm() {
            var username = document.getElementById("username").value;
            var password = document.getElementById("password").value;
            if (username === "" || password === "") {
                alert("Both fields are required!");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>