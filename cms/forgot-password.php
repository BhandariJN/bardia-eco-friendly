<?php
/**
 * CMS Forgot Password — Step 1
 * Request reset link via email
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php';

session_start();
if (!empty($_SESSION['cms_user_id'])) {
    header('Location: packages.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Email is required.';
    } else {
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            // Generate secure token
            $token  = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
            $stmt->bind_param('ssi', $token, $expiry, $user['id']);
            
            if ($stmt->execute()) {
                // Build reset link
                $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host     = $_SERVER['HTTP_HOST'];
                $base     = dirname($_SERVER['SCRIPT_NAME']);
                $resetUrl = $scheme . '://' . $host . $base . '/reset-password.php?token=' . $token;

                // Send Email
                $body = "
                    <h2>Password Reset Request</h2>
                    <p>Hello {$user['username']},</p>
                    <p>We received a request to reset your password for Bardiya Eco CMS.</p>
                    <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
                    <p><a href='$resetUrl' style='display:inline-block;background:#2e7d32;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;'>Reset Password</a></p>
                    <p>If you didn't request this, you can safely ignore this email.</p>
                ";

                $mailResult = sendEmail($email, $user['username'], 'Password Reset - Bardiya Eco CMS', $body);
                
                if ($mailResult['success']) {
                    $success = 'If an account exists with that email, a reset link has been sent.';
                } else {
                    $error = 'Failed to send email. Please try again later.';
                    // logError('Forgot password email failed: ' . $mailResult['error']);
                }
            } else {
                $error = 'An error occurred. Please try again.';
            }
            $stmt->close();
        } else {
            // Standard security: don't reveal if email exists or not
            $success = 'If an account exists with that email, a reset link has been sent.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — Bardiya Eco CMS</title>
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
        <h1>Forgot Password</h1>
        <p style="font-size:.85rem;color:#666;">Enter your official email to receive a reset link</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="admin@example.com">
        </div>
        <button type="submit" class="btn-login">Send Reset Link</button>
        <a href="login.php" class="back-link">← Back to Login</a>
    </form>
</div>
</body>
</html>
