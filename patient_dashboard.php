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

// Fetch patient's appointments
$stmt = $conn->prepare("
    SELECT a.*, u.full_name AS doctor_name 
    FROM appointments a 
    JOIN users u ON a.doctor_id = u.id 
    WHERE a.patient_id = ? 
    ORDER BY a.appointment_date DESC, a.time_slot
");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all doctors for booking
$stmt = $conn->query("
    SELECT d.*, u.id AS user_id, u.full_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id
");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --success: #198754;
            --warning: #ffc107;
            --info: #0dcaf0;
            --gray: #6c757d;
        }

        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: var(--primary) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            overflow: hidden;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-align: center;
            padding: 2.5rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .card-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2.2rem;
            position: relative;
            z-index: 1;
        }

        .card-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.7);
        }

        .table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .table thead {
            background: var(--primary);
            color: white;
        }

        .badge {
            font-size: 0.85rem;
        }

        .alert {
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .no-appointments {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            font-style: italic;
        }

        .booking-form {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .time-slot-container {
            position: relative;
            width: fit-content;
        }

        .time-slot-container .bi-clock {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.3s;
        }

        .time-slot-container:hover .bi-clock {
            opacity: 1;
        }

        .form-control[type="time"] {
            padding-right: 35px;
        }

        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-hospital me-2"></i>Mediflow System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Patient'); ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Card -->
    <div class="container flex-grow-1 py-4">
        <div class="dashboard-card">
            <div class="card-header">
                <h1>Patient Dashboard</h1>
                <div class="subtitle">View Appointments & Book New</div>
            </div>
            <div class="card-body">

                <!-- Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Appointments Section -->
                <div class="section-title">
                    <i class="fas fa-calendar-check"></i> Your Appointments
                </div>

                <?php if (empty($appointments)): ?>
                    <div class="no-appointments">
                        <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i><br>
                        No appointments booked yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Token</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appt): ?>
                                <tr>
                                    <td><strong><?php echo $appt['id']; ?></strong></td>
                                    <td><i class="fas fa-user-md me-1"></i> <?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($appt['appointment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($appt['time_slot']); ?></td>
                                    <td><code class="bg-light px-2 py-1 rounded"><?php echo $appt['token']; ?></code></td>
                                    <td>
                                        <span class="badge bg-<?php
                                            echo $appt['status'] === 'pending' ? 'warning' :
                                                 ($appt['status'] === 'called' ? 'info' :
                                                 ($appt['status'] === 'completed' ? 'success' : 'secondary'));
                                        ?>">
                                            <?php echo ucfirst($appt['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <hr class="my-5">

                <!-- Booking Form -->
                <div class="section-title">
                    <i class="fas fa-plus-circle"></i> Book New Appointment
                </div>

                <form method="POST" action="book_appointment.php" class="booking-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Doctor</label>
                            <select name="doctor_id" class="form-select" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doc): ?>
                                    <option value="<?php echo $doc['user_id']; ?>">
                                        <?php echo htmlspecialchars($doc['full_name'] . ' - ' . $doc['specialty']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" name="appointment_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Time Slot</label>
                            <div class="time-slot-container">
                                <input type="time" name="time_slot" class="form-control" required>
                                <i class="bi bi-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Focus time input on clock icon click
        document.querySelector('.bi-clock').addEventListener('click', function() {
            this.previousElementSibling.focus();
        });
    </script>
</body>
</html>
