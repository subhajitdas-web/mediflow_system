<?php
session_start();
require 'config/db.php';
$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password']; // Not hashed yet, for validation
    $role = $_POST['role'];
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING) ?: '';

    // === VALIDATE PASSWORD (8 characters) ===
    if (strlen($password) !== 8) {
        $_SESSION['error'] = "Password must be exactly 8 characters.";
        header("Location: register.php");
        exit;
    }

    // === VALIDATE PHONE (10 digits, only for patient) ===
    if ($role == 'patient' && (!preg_match('/^\d{10}$/', $phone))) {
        $_SESSION['error'] = "Phone number must be exactly 10 digits.";
        header("Location: register.php");
        exit;
    }

    // Hash password after validation
    $password = password_hash($password, PASSWORD_BCRYPT);

    try {
        $conn->beginTransaction();

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $role, $full_name, $email]);
        $user_id = $conn->lastInsertId();

        // Role-specific inserts
        if ($role == 'patient') {
            $age = filter_input(INPUT_POST, 'age', FILTER_SANITIZE_NUMBER_INT) ?: 0;
            $gender = $_POST['gender'] ?? 'other';
            $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING) ?: '';
            $stmt = $conn->prepare("INSERT INTO patients (user_id, age, gender, address, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $age, $gender, $address, $phone]);
        } elseif ($role == 'doctor') {
            $specialty = filter_input(INPUT_POST, 'specialty', FILTER_SANITIZE_STRING) ?: '';
            $experience = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_NUMBER_INT) ?: 0;
            $stmt = $conn->prepare("INSERT INTO doctors (user_id, specialty, experience) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $specialty, $experience]);
        }

        $conn->commit();
        $_SESSION['message'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Registration Error: " . $e->getMessage());
        $_SESSION['error'] = "Error: Username already exists or invalid data.";
        header("Location: register.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Mediflow System</title>
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
        .register-card {
            max-width: 500px;
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
        .register-card:hover {
            transform: translateY(-5px);
        }
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
        }
        .card-body {
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.7);
        }
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
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary);
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }
        .password-toggle {
            cursor: pointer;
            color: var(--gray);
        }
        .password-toggle:hover {
            color: var(--primary);
        }
        .btn-register {
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
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }
        .role-fields {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            display: none;
            opacity: 0;
            transition: all 0.4s ease;
        }
        .role-fields.show {
            display: block;
            opacity: 1;
        }
        .alert {
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
        }
        .login-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        .login-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        .is-invalid {
            border-bottom-color: #dc3545 !important;
        }
        .invalid-feedback {
            font-size: 0.85rem;
            color: #dc3545;
        }
        @media (max-width: 576px) {
            .card-body {
                padding: 2rem 1.5rem;
            }
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
                <a class="nav-link" href="login.php">Login</a>
            </div>
        </div>
    </nav>

    <!-- Register Card -->
    <div class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
        <div class="register-card">
            <div class="card-header">
                <h3>Create Account</h3>
                <div class="subtitle">Join Our Healthcare Network</div>
            </div>
            <div class="card-body">
                <!-- Error Message -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Register Form -->
                <form method="POST" id="registerForm" novalidate>
                    <div class="form-floating">
                        <input type="text" name="username" class="form-control" id="username" placeholder="Username" required>
                        <label for="username">Username</label>
                        <div class="invalid-feedback">Username is required.</div>
                    </div>

                    <div class="form-floating position-relative">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required minlength="8" maxlength="8">
                        <label for="password">Password (8 characters)</label>
                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </span>
                        <div class="invalid-feedback">Password must be exactly 8 characters.</div>
                    </div>

                    <div class="form-floating">
                        <input type="text" name="full_name" class="form-control" id="full_name" placeholder="Full Name" required>
                        <label for="full_name">Full Name</label>
                        <div class="invalid-feedback">Full name is required.</div>
                    </div>

                    <div class="form-floating">
                        <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>
                        <label for="email">Email</label>
                        <div class="invalid-feedback">Valid email is required.</div>
                    </div>

                    <div class="form-floating">
                        <select name="role" class="form-select" id="role" required onchange="toggleRoleFields()">
                            <option value="">Select Role</option>
                            <option value="patient">Patient</option>
                            <option value="doctor">Doctor</option>
                            <option value="admin">Admin</option>
                        </select>
                        <label for="role">Role</label>
                        <div class="invalid-feedback">Role is required.</div>
                    </div>

                    <!-- Patient Fields -->
                    <div id="patient_fields" class="role-fields">
                        <div class="form-floating">
                            <input type="number" name="age" class="form-control" id="age" placeholder="Age" min="0">
                            <label for="age">Age</label>
                        </div>
                        <div class="form-floating">
                            <select name="gender" class="form-select" id="gender">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <label for="gender">Gender</label>
                        </div>
                        <div class="form-floating">
                            <textarea name="address" class="form-control" id="address" placeholder="Address" style="height: 80px;"></textarea>
                            <label for="address">Address</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" name="phone" class="form-control" id="phone" placeholder="Phone" pattern="\d{10}" maxlength="10">
                            <label for="phone">Phone (10 digits)</label>
                            <div class="invalid-feedback">Phone must be exactly 10 digits.</div>
                        </div>
                    </div>

                    <!-- Doctor Fields -->
                    <div id="doctor_fields" class="role-fields">
                        <div class="form-floating">
                            <input type="text" name="specialty" class="form-control" id="specialty" placeholder="Specialty">
                            <label for="specialty">Specialty (e.g., Cardiology)</label>
                        </div>
                        <div class="form-floating">
                            <input type="number" name="experience" class="form-control" id="experience" placeholder="Experience" min="0">
                            <label for="experience">Experience (Years)</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-register">
                        Register Now
                    </button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>

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

        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const patientFields = document.getElementById('patient_fields');
            const doctorFields = document.getElementById('doctor_fields');

            patientFields.classList.remove('show');
            doctorFields.classList.remove('show');

            if (role === 'patient') {
                patientFields.classList.add('show');
                document.getElementById('phone').setAttribute('required', 'required');
            } else if (role === 'doctor') {
                doctorFields.classList.add('show');
                document.getElementById('phone').removeAttribute('required');
            } else {
                document.getElementById('phone').removeAttribute('required');
            }
        }

        // Client-side validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const phone = document.getElementById('phone');
            const role = document.getElementById('role').value;

            let isValid = true;

            // Password: Exactly 8 characters
            if (password.value.length !== 8) {
                password.classList.add('is-invalid');
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
            }

            // Phone: Exactly 10 digits (for patient only)
            if (role === 'patient' && !/^\d{10}$/.test(phone.value)) {
                phone.classList.add('is-invalid');
                isValid = false;
            } else {
                phone.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Auto-show fields if page reloads with error
        document.addEventListener('DOMContentLoaded', function() {
            const role = document.getElementById('role').value;
            if (role) toggleRoleFields();
        });
    </script>
</body>
</html>
