<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'config/database.php';

$conn = get_cams_connection();

$error_message = '';
$success_message = '';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'New password must be at least 6 characters long.';
    } else {
        // Get current user's password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password and reset password_change_required flag
            $update_stmt = $conn->prepare("UPDATE users SET password = ?, password_change_required = 0 WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                $success_message = 'Password changed successfully!';
                
                // Redirect to login page after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 2000);
                </script>";
            } else {
                $error_message = 'Error updating password: ' . $conn->error;
            }
            
            $update_stmt->close();
        } else {
            $error_message = 'Current password is incorrect.';
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - CAMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-card {
            max-width: 450px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card password-card">
            <div class="card-header text-center py-4">
                <i class="fas fa-lock fa-3x mb-3"></i>
                <h3 class="mb-0">Change Password</h3>
                <p class="mb-0">Please change your password to continue</p>
            </div>
            <div class="card-body p-4">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-key"></i> Current Password
                        </label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i> New Password
                        </label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required autocomplete="new-password" minlength="6">
                        <small class="text-muted">Password must be at least 6 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirm New Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password" minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-save"></i> Change Password
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="index.php" class="text-muted">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
