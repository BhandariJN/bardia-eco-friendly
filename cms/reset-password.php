<?php
/**
 * CMS Forgot Password — Step 2
 * Handle actual password reset using token
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();
if (!empty($_SESSION['cms_user_id'])) {
    header('Location: packages.php');
    exit;
}

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    die('Invalid or missing token.');
}

// Verify token
$stmt = $conn->prepare("SELECT id, username FROM users WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $error = 'Token is invalid or has expired. Please request a new one.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($newPass) || empty($confirmPass)) {
        $error = 'Both fields are required.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'Passwords do not match.';
    } elseif (strlen($newPass) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        $hashedPass = password_hash($newPass, PASSWORD_BCRYPT);
        
        // Update password and clear token
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $stmt->bind_param('si', $hashedPass, $user['id']);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = 'Password reset successfully! You can now log in.';
            header('Location: login.php');
            exit;
        } else {
            $error = 'Failed to update password. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — Bardiya Eco CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Inter',sans-serif;background:#f0f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;}
        .login-wrap{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.1);padding:40px 36px;width:min(400px,94vw);}
        .login-brand{text-align:center;margin-bottom:24px;}
        .login-brand h1{font-size:1.3rem;color:#1b5e20;margin-top:6px;}
        .alert-error{background:#ffebee;border:1px solid #ef9a9a;color:#b71c1c;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.88rem;}
        .alert-success{background:#e8f5e9;border:1px solid #a5d6a7;color:#1b5e20;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.88rem;}
        .form-group{margin-bottom:16px;}
        .form-group label{display:block;font-size:.85rem;font-weight:500;margin-bottom:5px;}
        .form-group input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-family:inherit;font-size:.9rem;}
        .btn-login{width:100%;padding:11px;background:#2e7d32;color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:.95rem;font-weight:600;cursor:pointer;}
        .back-link{display:block;text-align:center;margin-top:16px;font-size:.88rem;color:#6b7280;text-decoration:none;}
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">
        <h1>Set New Password</h1>
        <p style="font-size:.85rem;color:#666;">Choose a strong password for your account</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
        <a href="login.php" class="btn-login" style="display:block;text-align:center;text-decoration:none;">Log In Now</a>
    <?php elseif ($user): ?>
        <form method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="8" autofocus>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            <button type="submit" class="btn-login">Update Password</button>
        </form>
    <?php endif; ?>
    
    <?php if (!$success): ?>
        <a href="login.php" class="back-link">← Cancel</a>
    <?php endif; ?>
</div>
</body>
</html>
