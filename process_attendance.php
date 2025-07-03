<?php
// File: process_attendance.php

header('Content-Type: application/json');

include 'db_connection.php';

// A single function to send a JSON response and stop the script.
function send_json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// --- PRIMARY CHECKS ---

// 1. Check for Database Connection Error
if ($conn->connect_error) {
  send_json_response('error', "Database Connection Failed: " . $conn->connect_error);
}

// 2. Check if the employee_id was actually sent
if (!isset($_POST['employee_id']) || empty(trim($_POST['employee_id']))) {
    send_json_response('error', 'Employee ID is required.');
}

// --- MAIN LOGIC ---

$employee_id_input = trim($_POST['employee_id']);

// Set timezone to your local timezone (e.g., 'America/New_York', 'Asia/Manila')
date_default_timezone_set('Asia/Manila'); 
$current_datetime_obj = new DateTime();
$current_date = $current_datetime_obj->format('Y-m-d');
$current_time = $current_datetime_obj->format('H:i:s');


// Check if employee ID exists in the 'employees' table
$stmt = $conn->prepare("SELECT id, name FROM employees WHERE employee_id = ?");
if ($stmt === false) {
    send_json_response('error', "Database Prepare Failed (check employees): " . $conn->error);
}
$stmt->bind_param("s", $employee_id_input);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Employee exists, proceed.
    $employee = $result->fetch_assoc();
    $employee_db_id = $employee['id'];
    $employee_name = $employee['name'];

    // ===================================================================
    // NEW: 15-MINUTE SUBMISSION RESTRICTION
    // ===================================================================
    $stmt_last_action = $conn->prepare("SELECT log_date, time_in, time_out FROM attendance WHERE employee_id = ? ORDER BY id DESC LIMIT 1");
    $stmt_last_action->bind_param("i", $employee_db_id);
    $stmt_last_action->execute();
    $result_last_action = $stmt_last_action->get_result();

    if ($result_last_action->num_rows > 0) {
        $last_log = $result_last_action->fetch_assoc();
        
        // The last action is the time_out if it exists, otherwise it's the time_in
        $last_action_time_str = $last_log['time_out'] ?? $last_log['time_in'];
        $last_action_datetime_obj = new DateTime($last_log['log_date'] . ' ' . $last_action_time_str);
        
        // Calculate the difference in seconds
        $diff_seconds = $current_datetime_obj->getTimestamp() - $last_action_datetime_obj->getTimestamp();
        $diff_minutes = $diff_seconds / 60;
        
        if ($diff_minutes < 15) {
            $minutes_remaining = ceil(15 - $diff_minutes);
            send_json_response('error', "Please wait " . $minutes_remaining . " more minute(s) before submitting again.");
        }
    }
    $stmt_last_action->close();
    // ===================================================================
    // END OF NEW CODE BLOCK
    // ===================================================================


    // Check for an existing 'time_in' record for today that has NOT been 'timed_out' yet.
    $stmt_check = $conn->prepare("SELECT id FROM attendance WHERE employee_id = ? AND log_date = ? AND status = 0");
    $stmt_check->bind_param("is", $employee_db_id, $current_date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // RECORD FOUND: This means the employee is timing OUT.
        $attendance_log = $result_check->fetch_assoc();
        $attendance_id = $attendance_log['id'];
        
        $stmt_update = $conn->prepare("UPDATE attendance SET time_out = ?, status = 1 WHERE id = ?");
        $stmt_update->bind_param("si", $current_time, $attendance_id);

        if ($stmt_update->execute()) {
            send_json_response('success', 'Goodbye, ' . htmlspecialchars($employee_name) . '! Timed Out at ' . $current_time);
        } else {
            send_json_response('error', 'Error updating Time Out record.');
        }
        $stmt_update->close();

    } else {
        // NO RECORD FOUND: This means the employee is timing IN.
        $status = 0; // 0 for time_in
        $stmt_insert = $conn->prepare("INSERT INTO attendance (employee_id, log_date, time_in, status) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("issi", $employee_db_id, $current_date, $current_time, $status);
        
        if ($stmt_insert->execute()) {
            send_json_response('success', 'Welcome, ' . htmlspecialchars($employee_name) . '! Timed In at ' . $current_time);
        } else {
            send_json_response('error', 'Error inserting Time In record.');
        }
        $stmt_insert->close();
    }
    $stmt_check->close();

} else {
    // Employee ID was not found in the database.
    send_json_response('error', 'Employee ID (' . htmlspecialchars($employee_id_input) . ') not found.');
}

$stmt->close();
$conn->close();
?>