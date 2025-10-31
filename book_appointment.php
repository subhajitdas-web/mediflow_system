<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_SESSION['user_id'];
    $doctor_id = filter_input(INPUT_POST, 'doctor_id', FILTER_SANITIZE_NUMBER_INT);
    $appointment_date = filter_input(INPUT_POST, 'appointment_date', FILTER_SANITIZE_STRING);
    $time_slot = filter_input(INPUT_POST, 'time_slot', FILTER_SANITIZE_STRING);
    $token = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)); // Generate 8-char unique token

    try {
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, time_slot, token) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $doctor_id, $appointment_date, $time_slot, $token]);
        $_SESSION['message'] = "Appointment booked successfully! Your token: $token";
    } catch (PDOException $e) {
        error_log("Booking Error: " . $e->getMessage());
        $_SESSION['error'] = "Error booking appointment.";
    }
    header("Location: patient_dashboard.php");
    exit;
}
?>
