<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admin only.";
    header("Location: login.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

// Fetch data
$stmt = $conn->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT * FROM appointments");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT COUNT(*) as total FROM appointments");
$total_appointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$role_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0b5ed7;
            --light: #f8f9fa;
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
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-align: center;
            padding: 2rem 1.5rem;
            position: relative;
            overflow: hidden;
            border-radius: 20px 20px 0 0;
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
            font-size: 2rem;
            position: relative;
            z-index: 1;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .stat-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .btn-danger {
            border-radius: 50px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }
        .btn-export {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }
        .alert {
            border-radius: 12px;
            margin-bottom: 1.5rem;
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
                <span class="nav-link">Admin: <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container flex-grow-1 py-5">
        <div class="dashboard-card">
            <div class="card-header">
                <h1>Admin Dashboard</h1>
            </div>
            <div class="p-4 p-md-5">
                <!-- Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Export Button -->
                <div class="mb-4 text-end">
                    <a href="export_data.php" class="btn btn-export">
                        <i class="fas fa-file-excel me-2"></i>Download All Data (Excel)
                    </a>
                </div>

                <!-- Stats -->
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                            <h3 class="mb-0"><?php echo $total_appointments; ?></h3>
                            <p class="text-muted mb-0">Total Appointments</p>
                        </div>
                    </div>
                    <?php foreach ($role_counts as $rc): ?>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-<?php
                                    echo $rc['role'] == 'admin' ? 'user-shield' :
                                        ($rc['role'] == 'doctor' ? 'user-md' : 'user');
                                ?>"></i>
                            </div>
                            <h3 class="mb-0"><?php echo $rc['count']; ?></h3>
                            <p class="text-muted mb-0"><?php echo ucfirst($rc['role']); ?>s</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- USERS TABLE (DIRECT DELETE LINK) -->
                <h4 class="mb-3"><i class="fas fa-users me-2"></i>Users</h4>
                <div class="table-responsive mb-5">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo (int)$user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $user['role'] == 'admin' ? 'danger' :
                                            ($user['role'] == 'doctor' ? 'info' : 'success');
                                    ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                        <a href="delete_user.php?user_id=<?php echo $user['id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete @<?php echo htmlspecialchars($user['username']); ?> and all data?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Current Admin</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- APPOINTMENTS TABLE -->
                <h4 class="mb-3"><i class="fas fa-clock me-2"></i>Appointments</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>ID</th>
                                <th>Patient ID</th>
                                <th>Doctor ID</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Token</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appt['id']); ?></td>
                                <td><?php echo htmlspecialchars($appt['patient_id']); ?></td>
                                <td><?php echo htmlspecialchars($appt['doctor_id']); ?></td>
                                <td><?php echo htmlspecialchars($appt['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($appt['time_slot']); ?></td>
                                <td><code><?php echo htmlspecialchars($appt['token']); ?></code></td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $appt['status'] == 'completed' ? 'success' :
                                            ($appt['status'] == 'pending' ? 'warning' :
                                            ($appt['status'] == 'called' ? 'info' : 'secondary'));
                                    ?>">
                                        <?php echo ucfirst($appt['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
