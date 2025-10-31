<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor' || !isset($_SESSION['user_id'])) {
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
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET status = 'completed' 
        WHERE id = ? AND doctor_id = ? AND status = 'called'
    ");
    $stmt->execute([$appointment_id, $_SESSION['user_id']]);

    // SILENT: No message
    header("Location: doctor_dashboard.php");
    exit;

} catch (PDOException $e) {
    error_log("Complete Error: " . $e->getMessage());
    header("Location: doctor_dashboard.php");
    exit;
}
?>
