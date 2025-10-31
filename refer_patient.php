<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor' || !isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: doctor_dashboard.php");
    exit;
}

$appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
$new_doctor_id  = filter_input(INPUT_POST, 'new_doctor_id', FILTER_VALIDATE_INT);

if (!$appointment_id || !$new_doctor_id) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: doctor_dashboard.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

try {
    // Validate: current doctor owns a 'called' appointment
    $stmt = $conn->prepare("
        SELECT id FROM appointments 
        WHERE id = ? AND doctor_id = ? AND status = 'called'
    ");
    $stmt->execute([$appointment_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Appointment not found or not in 'called' state.";
        header("Location: doctor_dashboard.php");
        exit;
    }

    // Validate: new doctor exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'doctor'");
    $stmt->execute([$new_doctor_id]);
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Selected doctor not found.";
        header("Location: doctor_dashboard.php");
        exit;
    }

    // === CRITICAL FIX ===
    // Change status to 'pending' for the NEW doctor
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET doctor_id = ?, status = 'pending' 
        WHERE id = ?
    ");
    $stmt->execute([$new_doctor_id, $appointment_id]);

    // Log referral
    $stmt = $conn->prepare("
        INSERT INTO referrals (appointment_id, from_doctor_id, to_doctor_id, referred_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$appointment_id, $_SESSION['user_id'], $new_doctor_id]);

    // Optional: silent success
    // $_SESSION['message'] = "Patient referred successfully.";

    header("Location: doctor_dashboard.php");
    exit;

} catch (PDOException $e) {
    error_log("Referral Error: " . $e->getMessage());
    header("Location: doctor_dashboard.php");
    exit;
}
?>
