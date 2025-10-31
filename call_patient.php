<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor' || !isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

$appointment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$appointment_id) {
    header("Location: doctor_dashboard.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Fetch patient + token + ensure pending
    $stmt = $conn->prepare("
        SELECT a.id, a.token, u.full_name AS patient_name 
        FROM appointments a 
        JOIN users u ON a.patient_id = u.id 
        WHERE a.id = ? AND a.doctor_id = ? AND a.status = 'pending'
    ");
    $stmt->execute([$appointment_id, $_SESSION['user_id']]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appt) {
        $_SESSION['error'] = "Appointment not found or already called.";
        header("Location: doctor_dashboard.php");
        exit;
    }

    // Update status
    $stmt = $conn->prepare("UPDATE appointments SET status = 'called' WHERE id = ?");
    $stmt->execute([$appointment_id]);

    // SHOW CALL MESSAGE
    $_SESSION['message'] = "Patient <strong>" . htmlspecialchars($appt['patient_name']) . 
                          "</strong> (Token: <code>{$appt['token']}</code>) has been called.";

    header("Location: doctor_dashboard.php");
    exit;

} catch (PDOException $e) {
    error_log("Call Error: " . $e->getMessage());
    $_SESSION['error'] = "Database error.";
    header("Location: doctor_dashboard.php");
    exit;
}
?>
