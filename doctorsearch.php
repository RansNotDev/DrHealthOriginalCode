<!DOCTYPE html>
<html>
<head>
    <title>Doctor's Profile</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
            font-family: Arial, sans-serif; /* Default font family */
        }

        .card-body {
            background-color:white; /* Primary color for cards */
            color: #ffffff; /* Text color for cards */
        }

        .table {
            background-color: #ffffff; /* White background for tables */
        }

        th {
            background-color: #007bff; /* Primary color for table header */
            color: #ffffff; /* Text color for table header */
        }

        td {
            color: #000000; /* Text color for table cells */
        }
    </style>
</head>
<body>
<?php
include("newfunc.php");
if(isset($_POST['doctor_search_submit']))
{
      $id=$_POST['doctor_id']; // Change to use the ID provided by the user
      $query = "SELECT * FROM doctb WHERE id='$id'";
      $result = mysqli_query($con,$query);
      $row=mysqli_fetch_array($result);
    if(empty($row)) {
        echo "<script> alert('No entries found!'); window.location.href = 'admin-panel1.php#list-doc';</script>";
    }
    else {
        echo "<div class='container-fluid' style='margin-top:50px;'>
        <div class ='card'>
        <div class='card-body'>
        <table class='table table-hover'>
          <thead>
            <tr>
              <th scope='col'>ID</th>
              <th scope='col'>Name</th>
              <th scope='col'>Email</th>
              <th scope='col'>Specialization</th>
              <th scope='col'>Status</th>
            </tr>
          </thead>
          <tbody>";

        $id = $row['id'];
        $username = $row['username'];
        $email = $row['email'];
        $spec = $row['spec'];
        $status = $row['status'];

        echo "<tr>
              <td>$id</td>
              <td>$username</td>
              <td>$email</td>
              <td>$spec</td>
              <td>$status</td>
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