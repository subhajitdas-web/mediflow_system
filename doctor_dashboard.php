<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor' || !isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Access denied. Please log in as a doctor.";
    header("Location: login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

// Fetch appointments
$stmt = $conn->prepare("SELECT a.*, u.full_name AS patient_name FROM appointments a JOIN users u ON a.patient_id = u.id WHERE a.doctor_id = ? AND a.status IN ('pending', 'called')");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all doctors for referral dropdown
$stmt = $conn->prepare("SELECT d.*, u.id AS user_id, u.full_name FROM doctors d JOIN users u ON d.user_id = u.id WHERE u.id != ?");
$stmt->execute([$_SESSION['user_id']]);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
$has_other_doctors = !empty($doctors); // Check if other doctors exist
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
    <style>
        /* === NEW HEADER STYLES ONLY === */
        :root {
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
        }

        .navbar {
            background: var(--primary) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        </style>
<body>

    <!-- === NEW HEADER (Only This Part Changed) === -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-hospital me-2"></i>Mediflow System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">Dr. <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Doctor'); ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2 class="text-center">Doctor Dashboard</h2>
				<?php if (isset($_SESSION['message'])): ?>
					<div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
				<?php endif; ?>
				<?php if (isset($_SESSION['error'])): ?>
					<div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
				<?php endif; ?>
        <h3>Patient Queue</h3>
        <?php if (!$has_other_doctors): ?>
            <div class="alert alert-warning">No other doctors available for referrals.</div>
        <?php endif; ?>
        <table class="table table-striped">
            <thead>
                <tr><th>ID</th><th>Patient Name</th><th>Date</th><th>Time</th><th>Token</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr><td colspan="7">No pending or called appointments.</td></tr>
                <?php else: ?>
                    <?php foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appt['id']); ?></td>
                        <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($appt['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($appt['time_slot']); ?></td>
                        <td><?php echo htmlspecialchars($appt['token']); ?></td>
                        <td><?php echo htmlspecialchars($appt['status']); ?></td>
                        <td>
                            <?php if ($appt['status'] == 'pending'): ?>
                                <a href="call_patient.php?id=<?php echo $appt['id']; ?>" class="btn btn-primary btn-sm">Call Patient</a>
                            <?php endif; ?>
                            <a href="complete_appointment.php?id=<?php echo $appt['id']; ?>" class="btn btn-success btn-sm">Complete</a>
                            <?php if ($has_other_doctors): ?>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#referModal<?php echo $appt['id']; ?>">Refer</button>
                            <?php else: ?>
                                <button class="btn btn-warning btn-sm" disabled>Refer</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <!-- Refer Modal -->
                    <?php if ($has_other_doctors): ?>
                    <div class="modal fade" id="referModal<?php echo $appt['id']; ?>" tabindex="-1" aria-labelledby="referModalLabel<?php echo $appt['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="referModalLabel<?php echo $appt['id']; ?>">Refer Patient</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="refer_patient.php">
                                    <div class="modal-body">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Refer to Doctor</label>
                                            <select name="new_doctor_id" class="form-select" required>
                                                <?php foreach ($doctors as $doc): ?>
                                                    <option value="<?php echo $doc['user_id']; ?>"><?php echo htmlspecialchars($doc['full_name'] . ' (' . $doc['specialty'] . ')'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-warning">Refer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
