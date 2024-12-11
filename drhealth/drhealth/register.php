<?php
// Include database connection
include('db.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Get form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Hash the password before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data into the `admintb` table
    $query = "INSERT INTO admintb (username, password) VALUES ('$username', '$hashed_password')";
    
    // Execute query
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";  // Redirect to login page after successful registration
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
</head>
<body>
    <h2>Admin Registration</h2>
    <form method="POST" action="register.php">
        <!-- Username -->
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br><br>

        <!-- Password -->
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>

        <!-- Submit Button -->
        <button type="submit" name="register">Register</button>
    </form>
</body>
</html>
