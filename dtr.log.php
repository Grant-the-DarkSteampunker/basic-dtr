<?php
include 'db_connection.php';
?>
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
        <a href="index.php" class="log-link" style="text-align:left; margin-top:0; margin-bottom: 20px;">← Back to DTR System</a>
        <table>
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // We use a LEFT JOIN to get the employee's name from the employees table
                $sql = "SELECT e.name, e.employee_id, a.log_date, a.time_in, a.time_out 
                        FROM attendance a
                        LEFT JOIN employees e ON a.employee_id = e.id
                        ORDER BY a.log_date DESC, a.time_in DESC";
                
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["employee_id"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["log_date"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["time_in"]) . "</td>";
                        echo "<td>" . ($row["time_out"] ? htmlspecialchars($row["time_out"]) : 'N/A') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>