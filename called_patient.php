
<?php
require 'config/db.php';

$db = new Database();
$conn = $db->connect();

// Fetch the most recent called patient
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="10"> <!-- Auto-refresh every 10 seconds -->
    <title>Currently Called Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
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

        .navbar-brand {
            font-weight: 700;
        }
        .display-box {
            background: linear-gradient(to right, #28a745, #20c997);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .display-box h2 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .display-box p {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital me-2"></i>Mediflow System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">Login</a>
                <a class="nav-link" href="register.php">Register</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Currently Called Patient</h1>
        <div class="display-box">
            <?php if ($called_patient): ?>
                <div>
                    <h2>Token: <?php echo htmlspecialchars($called_patient['token']); ?></h2>
                    <p>Patient: <?php echo htmlspecialchars($called_patient['patient_name']); ?></p>
                    <p>Doctor: <?php echo htmlspecialchars($called_patient['doctor_name']); ?></p>
                </div>
            <?php else: ?>
                <p>No patient is currently being called.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
