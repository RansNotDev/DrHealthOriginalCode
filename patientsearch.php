<!DOCTYPE html>
<html>
<head>
    <title>Patient Details</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
            color: #343a40; /* Dark gray text color */
            font-family: 'Arial', sans-serif; /* Default font */
            padding-top: 50px;
        }
        .container-fluid {
            margin-top: 50px;
        }
        .card-body {
            background-color: white; /* Blue background for card body */
            color: #ffffff; /* White text color for card body */
        }
        .table {
            margin-bottom: 0; /* Remove bottom margin from table */
        }
        .table th {
            background-color: black; /* Blue background color for table headers */
            color: #ffffff; /* White text color for table headers */
        }
        .table td {
            color: #000000; /* Black text color for table cells */
        }
        .btn-light {
            color:  #0D409E;
            background-color:  #0D409E;
            border-color:  #0D409E;
        }
        .btn-light:hover {
            color:  #0D409E;
            background-color:  #0D409E;
            border-color:  #0D409E;
        }
    </style>
</head>
<body>
<?php
include("newfunc.php");
if(isset($_POST['patient_search_submit']))
{
    $pid=$_POST['patient_id']; // Change to use the pid provided by the user
    $query = "SELECT * FROM patreg WHERE pid='$pid'";
    $result = mysqli_query($con,$query);
    $row=mysqli_fetch_array($result);
    if(empty($row)) {
        echo "<script> alert('No entries found! Please enter valid details'); window.location.href = 'admin-panel1.php#list-doc';</script>";
    }
    else {
        echo "<div class='container-fluid'>
        <div class='card'>
        <div class='card-body'>
        <table class='table table-hover'>
          <thead>
            <tr>
              <th scope='col'>First Name</th>
              <th scope='col'>Last Name</th>
              <th scope='col'>Email</th>
              <th scope='col'>Contact</th>
              <th scope='col'>Address</th>
              <th scope='col'>Password</th>
            </tr>
          </thead>
          <tbody>";

        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $contact = $row['contact'];
        $address = $row['address'];
        $password = $row['password'];

        echo "<tr>
              <td>$fname</td>
              <td>$lname</td>
              <td>$email</td>
              <td>$contact</td>
              <td>$address</td>
              <td>$password</td>
            </tr>";

        echo "</tbody></table><center><a href='admin-panel1.php' class='btn btn-light'>Back to dashboard</a></div></center></div></div></div>";
    }
}
?>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script> 
</body>
</html>