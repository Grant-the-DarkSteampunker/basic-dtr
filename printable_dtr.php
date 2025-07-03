<?php
include 'db_connection.php';
$start_date_default = date('Y-m-01');
$end_date_default = date('Y-m-t');
$employee_name_input = isset($_GET['employee_name']) ? htmlspecialchars($_GET['employee_name']) : '';
$start_date_input = isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : $start_date_default;
$end_date_input = isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : $end_date_default;

$employees_result = $conn->query("SELECT name FROM employees ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable DTR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="height: auto; align-items: flex-start;">
    <div class="report-container">
        <h1>Printable Daily Time Record</h1>
        <form action="printable_dtr.php" method="GET" class="dtr-form no-print">
            <div class="form-row">
                <div class="form-group">
                    <label for="employee_name">Employee Name:</label>
                    <input type="text" id="employee_name" name="employee_name" list="employee-list" value="<?php echo $employee_name_input; ?>" placeholder="Select or type a name" required>
                    <datalist id="employee-list">
                        <?php
                        if ($employees_result->num_rows > 0) {
                            while($row = $employees_result->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['name']) . '">';
                            }
                        }
                        ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date_input; ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date_input; ?>" required>
                </div>
            </div>
            <button type="submit">Generate Report</button>
            <a href="dtr_log.php" class="log-link" style="margin-top:10px; text-align:center;">← Back to Full Log</a>
        </form>

        <?php
        if (!empty($employee_name_input)) :
            // Reconnect if needed or use the existing connection
            if ($conn->ping() === false) { include 'db_connection.php'; }

            $employee_db_id = null;
            $stmt_emp = $conn->prepare("SELECT id FROM employees WHERE name = ?");
            $stmt_emp->bind_param("s", $employee_name_input);
            $stmt_emp->execute();
            $result_emp = $stmt_emp->get_result();
            if ($result_emp->num_rows > 0) {
                $employee = $result_emp->fetch_assoc();
                $employee_db_id = $employee['id'];
            }
            $stmt_emp->close();

            if ($employee_db_id === null) :
        ?>
            <div class="message message-error" style="margin-top: 20px;">Employee Name not found.</div>
        <?php else: ?>
            <div id="printable-area">
                <div class="report-header">
                    <h2>Daily Time Record</h2>
                    <p><strong>Employee:</strong> <?php echo htmlspecialchars($employee_name_input); ?></p>
                    <p><strong>Period:</strong> <?php echo $start_date_input; ?> to <?php echo $end_date_input; ?></p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours Worked</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT log_date, time_in, time_out FROM attendance 
                                WHERE employee_id = ? AND log_date BETWEEN ? AND ? 
                                ORDER BY log_date ASC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iss", $employee_db_id, $start_date_input, $end_date_input);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $hours_worked = 'N/A';
                                if (!empty($row['time_in']) && !empty($row['time_out'])) {
                                    $time_in = new DateTime($row['time_in']);
                                    $time_out = new DateTime($row['time_out']);
                                    $interval = $time_in->diff($time_out);
                                    $hours_worked = $interval->format('%h h, %i m');
                                }
                                $time_in_12h = $row["time_in"] ? date("h:i:s A", strtotime($row["time_in"])) : 'N/A';
                                $time_out_12h = $row["time_out"] ? date("h:i:s A", strtotime($row["time_out"])) : 'N/A';

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["log_date"]) . "</td>";
                                echo "<td>" . $time_in_12h . "</td>";
                                echo "<td>" . $time_out_12h . "</td>";
                                echo "<td>" . $hours_worked . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No records found for this employee in this period.</td></tr>";
                        }
                        $stmt->close();
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
            <button onclick="window.print()" class="no-print" style="margin-top:20px;">Print Report</button>
        <?php endif; endif; ?>
    </div>
</body>
</html>