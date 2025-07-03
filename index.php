<?php
// We need to fetch the employee names to populate the datalist
include 'db_connection.php';
$employees_result = $conn->query("SELECT name FROM employees ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h1>Employee Daily Time Record</h1>
        <h2 id="clock"></h2>
        <p>Enter your Full Name to Time In or Time Out</p>

        <form id="attendance-form">
            <div class="form-group">
                <!-- Use an input with a datalist for autocomplete -->
                <input type="text" id="employee_name" name="employee_name" list="employee-list" placeholder="e.g., John Doe" required>
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
            <button type="submit">SUBMIT</button>
        </form>

        <div id="message"></div>

        <a href="dtr_log.php" class="log-link">View Attendance Log</a>
    </div>

    <script>
        // Real-time Clock in 12-hour format
        function updateClock() {
            const now = new Date();
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            document.getElementById('clock').textContent = now.toLocaleTimeString('en-US', options);
        }
        setInterval(updateClock, 1000);
        updateClock();

        // AJAX Form Submission
        document.getElementById('attendance-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const employeeName = document.getElementById('employee_name').value;
            const messageDiv = document.getElementById('message');

            const formData = new FormData();
            formData.append('employee_name', employeeName);

            fetch('process_attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.textContent = data.message;
                messageDiv.className = data.status === 'success' ? 'message-success' : 'message-error';
                document.getElementById('employee_name').value = '';
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.className = 'message-error';
            });
        });
    </script>

</body>
</html>