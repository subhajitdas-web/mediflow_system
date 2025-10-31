<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$db = new Database();
$conn = $db->connect();

// Fetch data
$stmt = $conn->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT * FROM appointments");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="hospital_data_' . date('Y-m-d') . '.csv"');
header('Cache-Control: max-age=0');

// Create file pointer
$output = fopen('php://output', 'w');

// Users data
fputcsv($output, ['--- USERS DATA ---']);
fputcsv($output, ['ID', 'Username', 'Role', 'Full Name', 'Email', 'Created At']);
foreach ($users as $user) {
    fputcsv($output, [
        $user['id'],
        $user['username'],
        $user['role'],
        $user['full_name'],
        $user['email'],
        $user['created_at']
    ]);
}

// Appointments data
fputcsv($output, []);
fputcsv($output, ['--- APPOINTMENTS DATA ---']);
fputcsv($output, ['ID', 'Patient ID', 'Doctor ID', 'Date', 'Time Slot', 'Token', 'Status', 'Created At']);
foreach ($appointments as $appt) {
    fputcsv($output, [
        $appt['id'],
        $appt['patient_id'],
        $appt['doctor_id'],
        $appt['appointment_date'],
        $appt['time_slot'],
        $appt['token'],
        $appt['status'],
        $appt['created_at']
    ]);
}

fclose($output);
exit;
?>
