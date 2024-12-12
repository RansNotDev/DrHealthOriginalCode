<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Results</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        body {
    background-color: #f7f9fc; /* Softer, cleaner background */
    font-family: 'Arial', sans-serif;
    color: #343a40;
}
.container {
    margin-top: 50px;
    margin-bottom: 50px;
}
.prescription-details {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.prescription-details:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
}
h2 {
    font-size: 30px;
    color: #495057; /* Darker shade for better contrast */
    font-weight: bold;
    margin-bottom: 25px;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-align: center;
}
.table-responsive {
    margin-top: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table th, table td {
    padding: 15px;
    vertical-align: middle;
}
table th {
    background-color: #e9ecef; /* Subtle background for header */
    color: #343a40; /* Darker text for better visibility */
    font-weight: 600;
}
table td {
    background-color: #ffffff;
    font-weight: 500;
    color: #495057;
}
table tr:nth-child(even) td {
    background-color: #f8f9fa; /* Alternating row color */
}
.btn-back {
    background-color: #007bff;
    border: none;
    padding: 12px 30px;
    border-radius: 30px;
    text-transform: uppercase;
    font-weight: bold;
    margin-top: 25px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}
.btn-back:hover {
    background-color: #0056b3;
    transform: translateY(-3px);
}
.btn-back:focus {
    outline: none;
    box-shadow: 0px 0px 10px rgba(0, 123, 255, 0.5);
}

    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="prescription-details">
                    <?php
                    // Check if appointment ID is set in the URL
                    if(isset($_GET['id'])) {
                        // Get the appointment ID from the URL
                        $patient_id = $_GET['id'];

                        // Connect to the database
                        $con = mysqli_connect("localhost", "root", "", "myhmsdb");

                        // Check connection
                        if (mysqli_connect_errno()) {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                            exit();
                        }

                        // Query to retrieve prescription information based on appointment ID
                        $query = "SELECT p.*, pr.disease, pr.allergy, pr.prescription 
                                  FROM patreg p
                                  INNER JOIN prestb pr ON p.pid = pr.pid
                                  WHERE pr.ID = ?";
                        $stmt = mysqli_prepare($con, $query);
                        mysqli_stmt_bind_param($stmt, "i", $patient_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);

                        // Check if the query was successful
                        if($result) {
                            // Check if prescription information exists
                            if(mysqli_num_rows($result) > 0) {
                                // Fetch prescription details
                                $prescription = mysqli_fetch_assoc($result);
                                ?>
                                <h2 class="text-center">Prescription Details</h2>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th>Patient ID:</th>
                                                <td><?php echo htmlspecialchars($prescription['pid']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>First Name:</th>
                                                <td><?php echo htmlspecialchars($prescription['fname']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Last Name:</th>
                                                <td><?php echo htmlspecialchars($prescription['lname']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Gender:</th>
                                                <td><?php echo htmlspecialchars($prescription['gender']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Test Result:</th>
                                                <td><?php echo htmlspecialchars($prescription['disease']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Findings:</th>
                                                <td><?php echo htmlspecialchars($prescription['allergy']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Prescription:</th>
                                                <td><?php echo htmlspecialchars($prescription['prescription']); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
                            } else {
                                echo "<p class='text-center'>Prescription not found.</p>";
                            }
                        } else {
                            echo "<p class='text-center'>Error: " . mysqli_error($con) . "</p>";
                        }

                        // Close connection
                        mysqli_stmt_close($stmt);
                        mysqli_close($con);
                    } else {
                        echo "<p class='text-center'>Patient ID is not set.</p>";
                    }
                    ?>
                    <div class="text-center">
                        <a href="doctor-panel.php" class="btn btn-back text-white">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
