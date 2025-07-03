<?php
// File: process_attendance.php

header('Content-Type: application/json');

include 'db_connection.php';

function send_json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

if ($conn->connect_error) {
    send_json_response('error', "Database Connection Failed: " . $conn->connect_error);
}

if (!isset($_POST['employee_name']) || empty(trim($_POST['employee_name']))) {
    send_json_response('error', 'Employee Name is required.');
}

$employee_name_input = trim($_POST['employee_name']);

date_default_timezone_set('Asia/Manila');
$current_datetime_obj = new DateTime();
$current_date = $current_datetime_obj->format('Y-m-d');
$current_time_db = $current_datetime_obj->format('H:i:s'); // 24-hour for DB
$current_time_display = $current_datetime_obj->format('h:i:s A'); // 12-hour for display

// Check if employee Name exists
$stmt = $conn->prepare("SELECT id, name FROM employees WHERE name = ?");
$stmt->bind_param("s", $employee_name_input);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    $employee_db_id = $employee['id'];
    $employee_name = $employee['name'];

    // 15-MINUTE SUBMISSION RESTRICTION
    $stmt_last_action = $conn->prepare("SELECT log_date, time_in, time_out FROM attendance WHERE employee_id = ? ORDER BY id DESC LIMIT 1");
    $stmt_last_action->bind_param("i", $employee_db_id);
    $stmt_last_action->execute();
    $result_last_action = $stmt_last_action->get_result();
    if ($result_last_action->num_rows > 0) {
        $last_log = $result_last_action->fetch_assoc();
        $last_action_time_str = $last_log['time_out'] ?? $last_log['time_in'];
        $last_action_datetime_obj = new DateTime($last_log['log_date'] . ' ' . $last_action_time_str);
        $diff_minutes = ($current_datetime_obj->getTimestamp() - $last_action_datetime_obj->getTimestamp()) / 60;
        if ($diff_minutes < 15) {
            send_json_response('error', "Please wait " . ceil(15 - $diff_minutes) . " more minute(s).");
        }
    }
    $stmt_last_action->close();

    // Check for an existing 'time_in' that hasn't been 'timed_out' today
    $stmt_check = $conn->prepare("SELECT id FROM attendance WHERE employee_id = ? AND log_date = ? AND status = 0");
    $stmt_check->bind_param("is", $employee_db_id, $current_date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Timing OUT
        $attendance_log = $result_check->fetch_assoc();
        $attendance_id = $attendance_log['id'];
        $stmt_update = $conn->prepare("UPDATE attendance SET time_out = ?, status = 1 WHERE id = ?");
        $stmt_update->bind_param("si", $current_time_db, $attendance_id);
        if ($stmt_update->execute()) {
            send_json_response('success', 'Goodbye, ' . htmlspecialchars($employee_name) . '! Timed Out at ' . $current_time_display);
        } else {
            send_json_response('error', 'Error updating Time Out record.');
        }
        $stmt_update->close();
    } else {
        // Timing IN
        $status = 0;
        $stmt_insert = $conn->prepare("INSERT INTO attendance (employee_id, log_date, time_in, status) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("issi", $employee_db_id, $current_date, $current_time_db, $status);
        if ($stmt_insert->execute()) {
            send_json_response('success', 'Welcome, ' . htmlspecialchars($employee_name) . '! Timed In at ' . $current_time_display);
        } else {
            send_json_response('error', 'Error inserting Time In record.');
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
} else {
    send_json_response('error', 'Employee Name (' . htmlspecialchars($employee_name_input) . ') not found.');
}

$stmt->close();
$conn->close();
?>