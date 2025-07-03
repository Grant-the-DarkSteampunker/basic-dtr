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
        <p>Enter your Employee ID to Time In or Time Out</p>

        <form id="attendance-form">
            <div class="form-group">
                <input type="text" id="employee_id" name="employee_id" placeholder="e.g., EMP-001" required>
            </div>
            <button type="submit">SUBMIT</button>
        </form>

        <div id="message"></div>

        <a href="dtr_log.php" class="log-link">View Attendance Log</a>
    </div>

    <script>
        // Real-time Clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        // AJAX Form Submission
        document.getElementById('attendance-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const employeeId = document.getElementById('employee_id').value;
            const messageDiv = document.getElementById('message');

            // Create a FormData object to send data
            const formData = new FormData();
            formData.append('employee_id', employeeId);

            // Use Fetch API to send data to the server
            fetch('process_attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Expect a JSON response
            .then(data => {
                // Display the server's message
                messageDiv.textContent = data.message;
                if (data.status === 'success') {
                    messageDiv.className = 'message-success';
                } else {
                    messageDiv.className = 'message-error';
                }
                // Clear the input field
                document.getElementById('employee_id').value = '';
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