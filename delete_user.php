<?php
session_start();
require 'config/db.php';

// 1. Admin check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admin only.";
    header("Location: login.php");
    exit;
}

// 2. Validate user_id
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$user_id || $user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: admin_dashboard.php");
    exit;
}

// 3. Prevent self-delete
if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: admin_dashboard.php");
    exit;
}

// 4. DB connection
$db   = new Database();
$conn = $db->connect();

try {
    $conn->beginTransaction();

    // Get username
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception("User not found.");
    }
    $username = $user['username'];

    // Delete in correct order
    $conn->prepare("DELETE FROM referrals WHERE from_doctor_id = ? OR to_doctor_id = ?")->execute([$user_id, $user_id]);
    $conn->prepare("DELETE FROM appointments WHERE patient_id = ? OR doctor_id = ?")->execute([$user_id, $user_id]);
    $conn->prepare("DELETE FROM doctors WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM patients WHERE user_id = ?")->execute([$user_id]);
    $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

    $conn->commit();
    $_SESSION['message'] = "User @$username deleted successfully.";

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Delete error (user $user_id): " . $e->getMessage());
    $_SESSION['error'] = "Delete failed: " . $e->getMessage();
}

header("Location: admin_dashboard.php");
exit;
?>
