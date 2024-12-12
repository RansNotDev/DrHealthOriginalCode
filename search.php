<?php
session_start();
$con=mysqli_connect("localhost","root","","myhmsdb");
if(isset($_POST['search_submit'])){
  $pid=$_POST['pid'];
  $docname = $_SESSION['dname'];
  $query="SELECT a.pid, a.fname, a.lname, p.age, a.gender, a.email, a.contact, a.appdate, p.address 
  FROM appointmenttb AS a 
  INNER JOIN patreg AS p ON a.pid = p.pid 
  WHERE a.pid='$pid' AND a.doctor='$docname';";
 $result=mysqli_query($con,$query);
 echo '<!DOCTYPE html>
 <html lang="en">
 <head>
     <!-- Required meta tags -->
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
 
     <!-- Bootstrap CSS -->
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
     <title>Patient Results</title>
	<link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
     <style>
        body {
            background-color: white; /* Change this color as desired */
            color: black;
            text-align: center;
            padding-top: 50px;
        }
        .container {
            text-align: left;
        }
        .container h3 {
            margin-bottom: 20px;
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
     <div class="container mt-5">
         <div class="row">
             <div class="col">
                 <h3 class="text-center mb-4">Patient Results</h3>
                 <table class="table table-hover">
                     <thead class="thead-dark">
                         <tr>
                             <th>Patient ID</th>
                             <th>First Name</th>
                             <th>Last Name</th>
                             <th>Age</th>
                             <th>Gender</th>
                             <th>Email</th>
                             <th>Contact</th>
                             <th>Address</th>
                             <th>Appointment Date</th>
                         </tr>
                     </thead>
                     <tbody>
  ';
  while($row=mysqli_fetch_array($result)){
    $pid=$row['pid'];
    $fname=$row['fname'];
    $lname=$row['lname'];
    $age=$row['age'];
    $gender=$row['gender'];
    $email=$row['email'];
    $contact=$row['contact'];
    $address=$row['address'];
    $appdate=$row['appdate'];
    echo '<tr>
      <td>'.$pid.'</td>
      <td>'.$fname.'</td>
      <td>'.$lname.'</td>
      <td>'.$age.'</td>
      <td>'.$gender.'</td>
      <td>'.$email.'</td>
      <td>'.$contact.'</td>
      <td>'.$address.'</td>
      <td>'.$appdate.'</td>
    </tr>';
  }
echo '</tbody>
</table>
<div class="text-center">
    <a href="doctor-panel.php" class="btn btn-secondary">Go Back</a>
</div>
</div>
</div>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>';
}

?>