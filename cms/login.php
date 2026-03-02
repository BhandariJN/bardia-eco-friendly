<?php
/**
 * CMS Login Page
 */

require_once __DIR__ . '/../includes/config.php';

session_start();

// Already logged in → redirect
if (!empty($_SESSION['cms_user_id'])) {
    header('Location: packages.php');
    exit;
}

$error   = '';
$success = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['cms_user_id']  = $user['id'];
            $_SESSION['cms_username'] = $user['username'];
            header('Location: packages.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Bardiya Eco CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Inter',sans-serif;background:#f0f4f0;min-height:100vh;display:flex;align-items:center;justify-content:center;}
        .login-wrap{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.1);padding:40px 36px;width:min(400px,94vw);}
        .login-brand{text-align:center;margin-bottom:24px;}
        .login-brand .icon{font-size:2.4rem;}
        .login-brand h1{font-size:1.3rem;color:#1b5e20;margin-top:6px;}
        .login-brand p{font-size:.82rem;color:#6b7280;margin-top:2px;}
        .alert-error{background:#ffebee;border:1px solid #ef9a9a;color:#b71c1c;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.88rem;}
        .form-group{margin-bottom:16px;}
        .form-group label{display:block;font-size:.85rem;font-weight:500;margin-bottom:5px;}
        .form-group input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-family:inherit;font-size:.9rem;}
        .form-group input:focus{outline:none;border-color:#2e7d32;}
        .btn-login{width:100%;padding:11px;background:#2e7d32;color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:.95rem;font-weight:600;cursor:pointer;margin-top:4px;}
        .btn-login:hover{background:#1b5e20;}
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">
        <div class="icon">🌿</div>
        <h1>Bardiya Eco CMS</h1>
        <p>Sign in to manage your content</p>
    </div>
    <?php if ($success): ?>
        <div style="background:#e8f5e9;border:1px solid #a5d6a7;color:#1b5e20;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.88rem;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autocomplete="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
            <div style="text-align:right;margin-top:4px;">
                <a href="forgot-password.php" style="font-size:.78rem;color:#2e7d32;text-decoration:none;">Forgot Password?</a>
            </div>
        </div>
        <button type="submit" class="btn-login">Sign In</button>
    </form>
</div>
</body>
</html>
