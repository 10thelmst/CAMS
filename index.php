<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Create CAMS database connection
$conn = get_cams_connection();

$error_message = '';
$success_message = '';

// Handle authentication status messages
if (isset($_GET['logged_out'])) {
    $success_message = 'You have been successfully logged out.';
}
if (isset($_GET['timeout'])) {
    $error_message = 'Your session has expired due to inactivity. Please log in again.';
}
if (isset($_GET['unauthorized'])) {
    $error_message = 'You are not authorized to access this page.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Get user's roles from user_roles table
                $stmt = $conn->prepare("SELECT role FROM user_roles WHERE user_id = ?");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                $roles_result = $stmt->get_result();
                $user_roles = array();
                while ($row = $roles_result->fetch_assoc()) {
                    $user_roles[] = $row['role'];
                }
                $stmt->close();
                
                // If no roles found in user_roles table, use the role from users table (backward compatibility)
                if (empty($user_roles)) {
                    $user_roles = array($user['role']);
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['roles'] = $user_roles;
                $_SESSION['role'] = $user_roles[0]; // Primary role for backward compatibility
                
                // Redirect based on primary role (first role in array)
                $primary_role = $user_roles[0];
                switch ($primary_role) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'superadmin':
                        header('Location: Superadmin/dashboard.php');
                        break;
                    case 'employee':
                        header('Location: Employee/dashboard.php');
                        break;
                    default:
                        $error_message = 'Invalid user role. Please contact administrator.';
                }
                exit();
            } else {
                $error_message = 'Invalid email or password.';
            }
        } else {
            // User not found in CAMS, check OWWA database
            $owwa_conn = get_owwa_connection();
            $owwa_stmt = $owwa_conn->prepare("SELECT code_name, email, fullname FROM dtr_employeescopy WHERE code_name = ? OR email = ?");
            $owwa_stmt->bind_param("ss", $email, $email);
            $owwa_stmt->execute();
            $owwa_result = $owwa_stmt->get_result();
            
            if ($owwa_result->num_rows > 0) {
                $owwa_user = $owwa_result->fetch_assoc();
                $username = trim($owwa_user['code_name']);
                $owwa_email = trim($owwa_user['email']);
                $fullname = trim($owwa_user['fullname']);
                
                // Use email as username if code_name is empty
                if (empty($username)) {
                    $username = $owwa_email;
                }
                
                // Generate email if not available
                if (empty($owwa_email)) {
                    $owwa_email = $username . '@owwa.gov.ph';
                }
                
                // Check if password matches default password for first-time login
                if ($password === 'password123') {
                    // Import user to CAMS database
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $default_role = 'employee';
                    $default_status = 'active';
                    
                    $import_stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                    $import_stmt->bind_param("sssss", $username, $owwa_email, $hashed_password, $default_role, $default_status);
                    
                    if ($import_stmt->execute()) {
                        $user_id = $conn->insert_id;
                        
                        // Insert default role into user_roles table
                        $role_stmt = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, ?)");
                        $role_stmt->bind_param("is", $user_id, $default_role);
                        $role_stmt->execute();
                        $role_stmt->close();
                        
                        $import_stmt->close();
                        $owwa_stmt->close();
                        $owwa_conn->close();
                        
                        // Auto-login the imported user
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $owwa_email;
                        $_SESSION['roles'] = array($default_role);
                        $_SESSION['role'] = $default_role;
                        
                        header('Location: Employee/dashboard.php');
                        exit();
                    } else {
                        $error_message = 'Error importing user: ' . $conn->error;
                        $import_stmt->close();
                    }
                } else {
                    $error_message = 'User not found. If this is your first time logging in, please use the default password: password123';
                }
                
                $owwa_stmt->close();
                $owwa_conn->close();
            } else {
                $error_message = 'Invalid email or password.';
            }
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Client Assisted Management System</title>
    <link rel="stylesheet" href="assets/css/adminlte.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="card">
            <div class="card-header" style="background: #007bff; color: white; border-bottom: none;">
                <h3 style="margin: 0; font-size: 1.5rem;">CAMS Login</h3>
            </div>
            <div class="card-body">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Username or Email</label>
                        <div class="input-group">
                            <input type="text" id="email" name="email" class="form-control" required autocomplete="username" placeholder="Username or Email">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Password">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>
            </div>
            <div class="card-footer" style="text-align: center; border-top: 1px solid #dee2e6;">
                <a href="forgot_password.php" style="color: #007bff; text-decoration: none;">Forgot Password?</a>
            </div>
        </div>
    </div>
</body>
</html>
