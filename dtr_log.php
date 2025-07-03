<?php include 'db_connection.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR Log</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="height: auto; align-items: flex-start;">

    <div class="log-container">
        <h1>Attendance Log</h1>
        <a href="printable_dtr.php" class="log-link" style="text-align:left; margin-top:0; margin-bottom: 20px;">Generate Printable Report →</a>
        <a href="index.php" class="log-link" style="text-align:left; margin-top:0; margin-bottom: 20px;">← Back to DTR System</a>
        <table>
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // We use a LEFT JOIN to get the employee's name
                $sql = "SELECT e.name, a.log_date, a.time_in, a.time_out 
                        FROM attendance a
                        LEFT JOIN employees e ON a.employee_id = e.id
                        ORDER BY a.id DESC";
                
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Format times to 12-hour format
                        $time_in_12h = $row["time_in"] ? date("h:i:s A", strtotime($row["time_in"])) : 'N/A';
                        $time_out_12h = $row["time_out"] ? date("h:i:s A", strtotime($row["time_out"])) : 'N/A';
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["log_date"]) . "</td>";
                        echo "<td>" . $time_in_12h . "</td>";
                        echo "<td>" . $time_out_12h . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No records found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>