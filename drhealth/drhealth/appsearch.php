<!DOCTYPE html>
<html>
<head>
    <title>Patient Details</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
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
            background-color:white; /* Dark blue background for card body */
            color: #ffffff; /* White text color for card body */
        }
        .table {
            margin-bottom: 0; /* Remove bottom margin from table */
        }
        .table th {
            background-color: black; /* Dark blue background color for table headers */
            color: #ffffff; /* White text color for table headers */
        }
        .table td {
            color: #000000; /* Black text color for table cells */
        }
        .btn-light {
            color: #212529;
            background-color: #f8f9fa;
            border-color: #f8f9fa;
        }
        .btn-light:hover {
            color: #212529;
            background-color: #e2e6ea;
            border-color: #dae0e5;
        }
    </style>
</head>
<body>
<?php
include("newfunc.php");
if(isset($_POST['app_search_submit']))
{
    $pid=$_POST['app_id']; // Use pid as input when searching
    $query = "SELECT * FROM appointmenttb WHERE pid='$pid';";
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
              <th scope='col'>Patient ID</th>
              <th scope='col'>First Name</th>
              <th scope='col'>Last Name</th>
              <th scope='col'>Email</th>
              <th scope='col'>Contact</th>
              <th scope='col'>Doctor Name</th>
              <th scope='col'>Appointment Date</th>
              <th scope='col'>Appointment Time</th>
              <th scope='col'>Appointment Status</th>
            </tr>
          </thead>
          <tbody>";
        $pid = $row['pid'];
        $fname = $row['fname'];
        $lname = $row['lname'];
        $email = $row['email'];
        $contact = $row['contact'];
        $doctor = $row['doctor'];
        $appdate= $row['appdate'];
        $apptime = $row['apptime'];
        if(($row['userStatus']==1) && ($row['doctorStatus']==1))  
                    {
                      $appstatus = "Active";
                    }
                    if(($row['userStatus']==0) && ($row['doctorStatus']==1))  
                    {
                      $appstatus = "Cancelled by You";
                    }

                    if(($row['userStatus']==1) && ($row['doctorStatus']==0))  
                    {
                      $appstatus = "Cancelled by Doctor";
                    }
        echo "<tr>
            <td>$pid</td>
            <td>$fname</td>
            <td>$lname</td>
            <td>$email</td>
            <td>$contact</td>
            <td>$doctor</td>
            <td>$appdate</td>
            <td>$apptime</td>
            <td>$appstatus</td>
          </tr>";

        echo "</tbody></table><center><a href='admin-panel1.php' class='btn btn-light'>Back to your Dashboard</a></div></center></div></div></div>";
    }
}
?>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script> 
</body>
</html>