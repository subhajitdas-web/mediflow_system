<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'config/db.php';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    $_SESSION['error'] = "Database connection failed.";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password are required.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($user && password_verify($password, $user['password'])) {
				$_SESSION['user_id'] = $user['id'];
				$_SESSION['role'] = $user['role'];
				$_SESSION['full_name'] = $user['full_name'];
				$_SESSION['message'] = "Login successful! Welcome back.";

				// Role-based redirect
				switch ($user['role']) {
					case 'admin':
						header("Location: admin_dashboard.php");
						break;
					case 'doctor':
						header("Location: doctor_dashboard.php");
						break;
					case 'patient':
						header("Location: patient_dashboard.php");
						break;
					default:
						header("Location: index.php");
				}
				exit;
			} else {
                $_SESSION['error'] = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $_SESSION['error'] = "Database error occurred.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mediflow System</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Glassmorphism Card */
        .login-card {
            max-width: 420px;
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

        .login-card:hover {
            transform: translateY(-5px);
        }

        /* Animated Gradient Header */
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-align: center;
            padding: 2rem 1.5rem;
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

        .card-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.75rem;
            position: relative;
            z-index: 1;
        }

        .card-header .subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-top: 0.5rem;
            font-weight: 400;
        }

        .card-body {
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.7);
        }

        /* Floating Labels */
        .form-floating {
            margin-bottom: 1.2rem;
        }

        .form-floating > .form-control {
            border: none;
            border-bottom: 2px solid #ced4da;
            border-radius: 0;
            padding: 1rem 0.75rem 0.3rem;
            background: transparent;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus {
            box-shadow: none;
            border-bottom-color: var(--primary);
        }

        .form-floating > label {
            color: var(--gray);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary);
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }

        /* Password Toggle */
        .password-toggle {
            cursor: pointer;
            color: var(--gray);
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        /* Submit Button */
        .btn-login {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }

        .alert {
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
        }

        .register-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .card-body {
                padding: 2rem 1.5rem;
            }
            .card-header h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-hospital me-2"></i>Mediflow System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i>Register</a>
            </div>
        </div>
    </nav>

    <!-- Professional Login Card -->
    <div class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
        <div class="login-card">
            <div class="card-header">
                <h3>Login</h3>
                <div class="subtitle">Hospital Management Portal</div>
            </div>
            <div class="card-body">

                <!-- Success Message -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Login Form with Floating Labels -->
                <form method="POST">
                    <div class="form-floating">
                        <input type="text" name="username" class="form-control" id="username" placeholder="Username" required autofocus>
                        <label for="username">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                    </div>

                    <div class="form-floating position-relative">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login Securely
                    </button>
                </form>

                <div class="register-link">
                    New here? <a href="register.php">Create an account</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap + Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
