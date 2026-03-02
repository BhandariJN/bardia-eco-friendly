<?php
/**
 * CMS — Profile & Account Management
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error   = '';

$userId = (int) $_SESSION['cms_user_id'];

// ---------- Fetch User Data ----------
$user = [];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ---------- Handle POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $email = sanitize($_POST['email'] ?? '');
        $currentPass = $_POST['current_password'] ?? '';

        if (empty($email) || empty($currentPass)) {
            $error = 'Email and current password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // 1. Verify password before allowing email change
            $stmt = $conn->prepare("SELECT password, email FROM users WHERE id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $userData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (password_verify($currentPass, $userData['password'])) {
                $oldEmail = $userData['email'];
                
                // 2. Perform the update
                $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->bind_param('si', $email, $userId);
                if ($stmt->execute()) {
                    $success = 'Profile updated successfully.';
                    
                    // 3. Send notification to OLD email address (Security Best Practice)
                    if ($oldEmail && $oldEmail !== $email) {
                        require_once __DIR__ . '/../includes/mailer.php';
                        $subject = "Security Alert: Recovery Email Changed";
                        $body = "<h2>Security Notification</h2>
                                <p>Hello <strong>{$user['username']}</strong>,</p>
                                <p>The recovery email address for your Bardiya Eco CMS account was just changed from <strong>$oldEmail</strong> to <strong>$email</strong>.</p>
                                <p>If you did not perform this change, please contact technical support immediately as your account may be compromised.</p>";
                        sendEmail($oldEmail, $user['username'], $subject, $body);
                    }
                    
                    $user['email'] = $email; // Update local data for form display
                } else {
                    $error = 'Failed to update profile: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Current password is incorrect. Email update denied.';
            }
        }

    } elseif ($action === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password']     ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            $error = 'All password fields are required.';
        } elseif ($newPass !== $confirmPass) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPass) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $dbPass = $stmt->get_result()->fetch_assoc()['password'];
            $stmt->close();

            if (password_verify($currentPass, $dbPass)) {
                $hashedPass = password_hash($newPass, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param('si', $hashedPass, $userId);
                if ($stmt->execute()) {
                    // Password changed successfully -> Logout and redirect
                    $_SESSION['success_msg'] = 'Password updated successfully. Please log in with your new credentials.';
                    header('Location: login.php');
                    exit;
                } else {
                    $error = 'Failed to change password: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}
?>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="form-row">
    <!-- Account Information -->
    <div style="flex: 1; min-width: 300px;">
        <div class="card">
            <div class="card-header">
                <h2>Account Settings</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background:#f5f5f5;">
                        <small style="color:var(--muted);">Username cannot be changed.</small>
                    </div>

                    <div class="form-group">
                        <label for="fEmail">Official Email address <span style="color:red">*</span></label>
                        <input type="email" class="form-control" id="fEmail" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        <small style="color:var(--muted);">Used for password recovery.</small>
                    </div>

                    <div class="form-group">
                        <label for="fEmailPass">Verify Current Password <span style="color:red">*</span></label>
                        <input type="password" class="form-control" id="fEmailPass" name="current_password" required>
                        <small style="color:var(--muted);">Required to change official email.</small>
                    </div>

                    <div style="margin-top:20px;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security / Password -->
    <div style="flex: 1; min-width: 300px;">
        <div class="card">
            <div class="card-header">
                <h2>Security</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="fCurrent">Current Password</label>
                        <input type="password" class="form-control" id="fCurrent" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="fNew">New Password</label>
                        <input type="password" class="form-control" id="fNew" name="new_password" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="fConfirm">Confirm New Password</label>
                        <input type="password" class="form-control" id="fConfirm" name="confirm_password" required minlength="8">
                    </div>

                    <div style="margin-top:20px;">
                        <button type="submit" class="btn btn-secondary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
