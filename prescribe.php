<?php
include('func1.php');

$pid = '';
$ID = '';
$appdate = '';
$apptime = '';
$fname = '';
$lname = '';
$doctor = $_SESSION['dname'];

if(isset($_GET['pid']) && isset($_GET['ID']) && isset($_GET['appdate']) && isset($_GET['apptime']) && isset($_GET['fname']) && isset($_GET['lname'])) {
    $pid = $_GET['pid'];
    $ID = $_GET['ID'];
    $fname = $_GET['fname'];
    $lname = $_GET['lname'];
    $appdate = $_GET['appdate'];
    $apptime = $_GET['apptime'];
}

if(isset($_POST['prescribe']) && isset($_POST['pid']) && isset($_POST['ID']) && isset($_POST['appdate']) && isset($_POST['apptime']) && isset($_POST['lname']) && isset($_POST['fname'])){
    $appdate = $_POST['appdate'];
    $apptime = $_POST['apptime'];
    $disease = $_POST['disease']; // Correct column name
    $allergy = $_POST['allergy']; // Correct column name
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $pid = $_POST['pid'];
    $ID = $_POST['ID'];
    $prescription = $_POST['prescription'];
    
    $query = mysqli_query($con, "INSERT INTO prestb (doctor, pid, ID, fname, lname, appdate, apptime, disease, allergy, prescription) VALUES ('$doctor', '$pid', '$ID', '$fname', '$lname', '$appdate', '$apptime', '$disease', '$allergy', '$prescription')");
    if($query) {
        echo "<script>alert('Prescribed successfully!'); window.location.href = 'doctor-panel.php';</script>";
    } else {
        echo "<script>alert('Unable to process your request. Try again!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=IBM+Plex+Sans&display=swap" rel="stylesheet">

    <title>Prescription Form</title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-top: 50px
        }
        .form-group label {
            font-weight: bold;
        }
        .bg-primary {
            background: -webkit-linear-gradient(left, #3931af, #00c6ff);
        }
        .list-group-item.active {
            z-index: 2;
            color: #fff;
            background-color: #342ac1;
            border-color: #007bff;
        }
        .text-primary {
            color: #342ac1!important;
        }
        .btn-primary {
            background-color: #004085;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #003366;
        }
        .btn-back {
            background-color: #004085;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-back:hover {
            background-color: #003366;
        }
        .navbar {
            background-color: #004085;
        }
        .navbar-brand {
            color: white;
            font-weight: bold;
        }
    </style>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <a class="navbar-brand" href="#">
            <img src="images/logo.png" alt="Logo" style="height: 30px; width: auto; margin-right: 10px;">
            D.R. Health Medical and Diagnostic Center
        </a>
    </nav>

    <div class="container">
        <br>
        <h2 class="text-center mb-4" style="font-weight: bold;">Prescription Form</h2>
        <form class="form-group" name="prescribeform" method="post" action="prescribe.php">
            <div class="form-row">
                <div class="col-md-6">
                    <label for="disease">Test Result:</label>
                    <textarea class="form-control" id="disease" name="disease" rows="4" required></textarea>
                </div>
                <div class="col-md-6">
                    <label for="allergy">Findings:</label>
                    <textarea class="form-control" id="allergy" name="allergy" rows="4" required></textarea>
                </div>
            </div>
            <div class="form-group mt-3">
                <label for="prescription">Prescription:</label>
                <textarea class="form-control" id="prescription" name="prescription" rows="6" required></textarea>
            </div>
            <input type="hidden" name="fname" value="<?php echo htmlspecialchars($fname); ?>">
            <input type="hidden" name="lname" value="<?php echo htmlspecialchars($lname); ?>">
            <input type="hidden" name="appdate" value="<?php echo htmlspecialchars($appdate); ?>">
            <input type="hidden" name="apptime" value="<?php echo htmlspecialchars($apptime); ?>">
            <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
            <input type="hidden" name="ID" value="<?php echo htmlspecialchars($ID); ?>">
            <div class="text-center mt-4">
                <button type="submit" name="prescribe" class="btn btn-primary mr-2">Prescribe</button>
                <a href="doctor-panel.php" class="btn btn-back">Back</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b3UgwA02Y2wYjmBz1e9J65B5rHEEuCXZoXc5nQ12l92YLT9ifXEldz3Q7URy27sm" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-hMN4KUV+0jpuL1QkNuz9FCf7BxU6Tfbw4Db2a9P5J5T6G5oSoXKp7QmgEQvM88m" crossorigin="anonymous"></script>
</body>
</html>
