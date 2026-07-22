<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
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
                header('Location: index.php');
        }
    } else {
        header('Location: index.php');
    }
    exit();
}

// Include database configuration
require_once 'config/database.php';

// Create CAMS database connection
$conn = get_cams_connection();

$error_message = '';
$success_message = '';

// Handle authentication status messages
if (isset($_SESSION['logout_message'])) {
    $success_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
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
        $stmt = $conn->prepare("SELECT id, username, email, password, role, password_change_required FROM users WHERE email = ? OR username = ?");
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
                
                // Check if user needs to change password
                if (isset($user['password_change_required']) && $user['password_change_required'] == 1) {
                    header('Location: change_password.php');
                    exit();
                }
                
                // Redirect to index.php
                header('Location: index.php');
                exit();
            } else {
                $error_message = 'Invalid email or password.';
            }
        } else {
            // User not found in CAMS, check OWWA database
            $owwa_conn = get_owwa_connection();
            
            // Try to get user from OWWA database - handle different column structures
            $owwa_query = "SELECT code_name FROM dtr_employeescopy WHERE code_name = ?";
            $owwa_stmt = $owwa_conn->prepare($owwa_query);
            $owwa_stmt->bind_param("s", $email);
            $owwa_stmt->execute();
            $owwa_result = $owwa_stmt->get_result();
            
            if ($owwa_result->num_rows > 0) {
                $owwa_user = $owwa_result->fetch_assoc();
                $username = trim($owwa_user['code_name']);
                
                // Generate email from username
                $owwa_email = $username . '@owwa.gov.ph';
                $fullname = $username;
                
                // Check if password matches default password for first-time login
                if ($password === 'password123') {
                    // Import user to CAMS database
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $default_role = 'employee';
                    $default_status = 'active';
                    $password_change_required = 1;
                    
                    $import_stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, password_change_required) VALUES (?, ?, ?, ?, ?, ?)");
                    $import_stmt->bind_param("sssssi", $username, $owwa_email, $hashed_password, $default_role, $default_status, $password_change_required);
                    
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
                        
                        header('Location: index.php');
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
