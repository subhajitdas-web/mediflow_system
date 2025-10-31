<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediflow System</title>
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

        /* Glassmorphism Welcome Card */
        .welcome-card {
            max-width: 800px;
            width: 100%;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .welcome-card:hover {
            transform: translateY(-5px);
        }

        /* Animated Gradient Header */
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
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
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
            margin-top: 0.75rem;
            font-weight: 400;
        }

        .card-body {
            padding: 3rem;
            background: rgba(255, 255, 255, 0.7);
            text-align: center;
        }

        .card-body p.lead {
            font-size: 1.2rem;
            color: #444;
            margin-bottom: 2rem;
        }

        .btn-cta {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 50px;
            padding: 0.9rem 2.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            margin: 0.5rem;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(13, 110, 253, 0.3);
        }

        .btn-outline-cta {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline-cta:hover {
            background: var(--primary);
            color: white;
        }

        /* Image Section */
        .bottom-image {
            margin-top: 3rem;
            text-align: center;
        }

        .bottom-image img {
            max-width: 100%;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.4s ease;
        }

        .bottom-image img:hover {
            transform: scale(1.02);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-header h1 {
                font-size: 1.8rem;
            }
            .card-body {
                padding: 2rem;
            }
            .btn-cta {
                width: 100%;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar (Same as Login) -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital me-2"></i>Mediflow System
            </a>
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a class="nav-link" href="admin_dashboard.php">Admin</a>
                    <?php elseif ($_SESSION['role'] == 'doctor'): ?>
                        <a class="nav-link" href="doctor_dashboard.php">Doctor</a>
                    <?php else: ?>
                        <a class="nav-link" href="patient_dashboard.php">Patient</a>
                    <?php endif; ?>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                    <!--<a class="nav-link" href="called_patient.php">Called Patient</a>-->
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- upper Image -->
    <div class="container">
		<br>
    </div>
    <div class="container upper-image">
        <img src="hms.jpg" alt="Hospital Building" class="img-fluid">
        <!-- Optional caption -->
        <!-- <p class="mt-3 text-muted">State-of-the-art medical facility</p> -->
    </div>
    
	<!-- Wide Welcome Card Section -->
	<div class="container-fluid flex-grow-1 d-flex align-items-center justify-content-center py-5 px-4 px-md-5">
		<div class="welcome-card w-100" style="max-width: 1000px;">
			<div class="card-header">
				<h1>Welcome to Our Hospital</h1>
				<div class="subtitle">Advanced Healthcare Management System</div>
			</div>
			<div class="card-body">
				<p class="lead">
					Manage appointments, track patient tokens, and access healthcare services seamlessly.
				</p>
				<a href="called_patient.php" class="btn btn-cta text-white">
					View Called Patient
				</a>
				<?php if (!isset($_SESSION['user_id'])): ?>
					<a href="login.php" class="btn btn-outline-cta">
						Login Now
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
