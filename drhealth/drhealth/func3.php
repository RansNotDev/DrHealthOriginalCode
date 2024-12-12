<?php
session_start();  // Start the session at the top

// Create connection to the database
$con = mysqli_connect("localhost", "root", "", "myhmsdb");

// Check if the connection was successful
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form was submitted
if (isset($_POST['adsub'])) {
    $username = $_POST['username1'];
    $password = $_POST['password2'];

    // Use prepared statement to prevent SQL injection
    $query = "SELECT * FROM admintb WHERE username = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $username);  // Bind username to the query
    $stmt->execute();
    $result = $stmt->get_result();  // Execute the query and get the result

    // Check if the user exists and verify password
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Verify password hash
        if (password_verify($password, $row['password'])) {  // Verify password hash
            $_SESSION['username'] = $username;  // Set session
            echo "<script>
                    alert('Login successful!');
                    window.location.href = 'admin-panel1.php';
                  </script>";
            exit();  // Ensure no further code runs after redirect
        } else {
            // Invalid password
            echo "<script>
                    alert('Invalid password. Please try again.');
                    window.location.href = 'admin.php';
                  </script>";
            exit();
        }
    } else {
        // Username not found
        echo "<script>
                alert('Username not found. Please try again.');
                window.location.href = 'admin.php';
              </script>";
        exit();
    }
}
if (isset($_POST['update_data'])) {
	$contact = $_POST['contact'];
	$status = $_POST['status'];
	$query = "update appointmenttb set payment='$status' where contact='$contact';";
	$result = mysqli_query($con, $query);
	if ($result)
		header("Location:updated.php");
}




function display_docs()
{
	global $con;
	$query = "select * from doctb";
	$result = mysqli_query($con, $query);
	while ($row = mysqli_fetch_array($result)) {
		$name = $row['name'];
		# echo'<option value="" disabled selected>Select Doctor</option>';
		echo '<option value="' . $name . '">' . $name . '</option>';
	}
}

if (isset($_POST['doc_sub'])) {
	$name = $_POST['name'];
	$query = "insert into doctb(name)values('$name')";
	$result = mysqli_query($con, $query);
	if ($result)
		header("Location:adddoc.php");
}
