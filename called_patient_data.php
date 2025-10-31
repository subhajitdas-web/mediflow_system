<?php
require 'config/db.php';
$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("
    SELECT a.token, u1.full_name AS patient_name, u2.full_name AS doctor_name
    FROM appointments a
    JOIN users u1 ON a.patient_id = u1.id
    JOIN users u2 ON a.doctor_id = u2.id
    WHERE a.status = 'called'
    ORDER BY a.created_at DESC
    LIMIT 1
");
$stmt->execute();
$called_patient = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
if ($called_patient) {
    echo json_encode([
        'success' => true,
        'data' => $called_patient
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No patient is currently being called.'
    ]);
}
?>
