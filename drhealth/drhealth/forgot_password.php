
<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'myhmsdb');

// Handle email submission (show new password form)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if the email exists in the patreg table
    $result = $conn->query("SELECT * FROM patreg WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $showNewPasswordForm = true;
    } else {
        echo "<script>
                alert('No account found with that email.');
                window.location.href = 'forgot_password.php';
              </script>";
    }
}

// Check if the new password form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        $updateQuery = $conn->query("UPDATE patreg SET password = '$new_password', cpassword = '$confirm_password' WHERE email = '$email'");

        if ($updateQuery) {
            echo "<script>
                    alert('Your password has been updated successfully!');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('There was an error updating your password.');
                    window.location.href = 'forgot_password.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('The new password and confirmation password do not match.');
                window.location.href = 'forgot_password.php';
              </script>";
    }
}

$conn->close();
?>

<!-- Password Recovery Page -->
<div class="container">
    <div class="form-card">
        <h2>Password Recovery</h2>
        <?php if (!isset($showNewPasswordForm)) { ?>
            <p>Enter your email address to reset your password.</p>
            <form method="POST" action="forgot_password.php" id="emailForm">
                <label for="email">Email Address</label>
                <input type="email" name="email" placeholder="e.g., example@domain.com" required>
                <button type="submit" class="btn">Submit</button>
            </form>
        <?php } else { ?>
            <p>Enter your new password below.</p>
            <form method="POST" action="forgot_password.php" id="newPasswordForm">
                <input type="hidden" name="email" value="<?php echo $email; ?>">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                <button type="submit" class="btn">Reset Password</button>
            </form>
        <?php } ?>
    </div>
</div>

<!-- Enhanced CSS Styles -->
<style>
    body {
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(135deg, #4caf50, #2196f3);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        color: #fff;
    }

    .container {
        width: 100%;
        max-width: 400px;
        padding: 20px;
        animation: fadeIn 1s ease;
    }

    .form-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        text-align: center;
        color: #333;
    }

    .form-card h2 {
        margin-bottom: 15px;
        font-size: 28px;
        font-weight: 700;
        color: #4caf50;
    }

    .form-card p {
        margin-bottom: 20px;
        font-size: 16px;
        color: #555;
    }

    label {
        font-size: 14px;
        margin-bottom: 8px;
        display: block;
        text-align: left;
        color: #333;
        font-weight: 600;
    }

    input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }

    input:focus {
        border-color: #4caf50;
        outline: none;
    }

    button {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: #4caf50;
        color: white;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    button:hover {
        background: #388e3c;
    }

    .form-card .btn {
        background: #2196f3;
    }

    .form-card .btn:hover {
        background: #1e88e5;
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 20px;
        }

        h2 {
            font-size: 24px;
        }

        button {
            font-size: 16px;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
