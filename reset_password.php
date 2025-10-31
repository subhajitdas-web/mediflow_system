<?php
session_start();
require 'config/db.php';

$db = new Database();
$conn = $db->connect();

$token = $_GET['token'] ?? '';
$error = '';
$message = '';

if (!$token) {
    $error = "Invalid or missing token.";
} else {
    $stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $error = "Invalid or expired reset link.";
    }
}

// Handle New Password
if (isset($_POST['reset_password']) && !$error) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $reset['user_id']]);

        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        $message = "Password updated successfully! <a href='login.php'>Login now</a>.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
            border-radius: 50px;
        }
        .form-control {
            border-radius: 50px;
        }
        .alert {
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <h3 class="text-center mb-4"><i class="fas fa-key text-primary"></i> Reset Password</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="New Password" required minlength="6">
                </div>
                <div class="mb-3">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required minlength="6">
                </div>
                <button type="submit" name="reset_password" class="btn btn-primary w-100">
                    <i class="fas fa-check"></i> Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
