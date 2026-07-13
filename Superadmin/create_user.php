<?php
require_once '../auth/auth_check.php';

// Check if user has superadmin role
if (!has_role('superadmin')) {
    header('Location: ../index.php?unauthorized=1');
    exit();
}

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cams';

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';
$success_message = '';
$roles = array();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $roles = isset($_POST['roles']) ? $_POST['roles'] : array();
    $status = $_POST['status'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (empty($roles)) {
        $error_message = 'Please select at least one role.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = 'Email address already exists.';
        } else {
            // Hash password
                       $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user (role field will be set to first selected role for backward compatibility)
            $primary_role = $roles[0];
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $primary_role, $status);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $stmt->close();
                
                // Insert all roles into user_roles table
                $roles_stmt = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, ?)");
                foreach ($roles as $role) {
                    $roles_stmt->bind_param("is", $user_id, $role);
                    $roles_stmt->execute();
                }
                $roles_stmt->close();
                
                $success_message = 'User created successfully!';
                // Clear form values
                $username = '';
                $email = '';
                $password = '';
                $confirm_password = '';
                $roles = array();
            } else {
                $error_message = 'Error creating user: ' . $conn->error;
                $stmt->close();
            }
        }
    }
}

$conn->close();

$content = '
    <div class="content-header">
        <h1>Create User</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                <li class="breadcrumb-item active">Create User</li>
            </ol>
        </nav>
    </div>';

if (isset($error_message)) {
    $content .= '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
}

if (isset($success_message)) {
    $content .= '<div class="alert alert-success">' . htmlspecialchars($success_message) . '</div>';
}

$content .= '
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Full Name *</label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           value="' . (isset($username) ? htmlspecialchars($username) : '') . '">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="' . (isset($email) ? htmlspecialchars($email) : '') . '">
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Roles *</label>
                            <div class="checkbox-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="role_admin" name="roles[]" value="admin" class="custom-control-input" ' . (in_array('admin', $roles) ? 'checked' : '') . '>
                                    <label for="role_admin" class="custom-control-label">Admin</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="role_superadmin" name="roles[]" value="superadmin" class="custom-control-input" ' . (in_array('superadmin', $roles) ? 'checked' : '') . '>
                                    <label for="role_superadmin" class="custom-control-label">Superadmin</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="role_employee" name="roles[]" value="employee" class="custom-control-input" ' . (in_array('employee', $roles) ? 'checked' : '') . '>
                                    <label for="role_employee" class="custom-control-label">Employee</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" ' . ((isset($status) && $status == 'active') ? 'selected' : '') . '>Active</option>
                                <option value="inactive" ' . ((isset($status) && $status == 'inactive') ? 'selected' : '') . '>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Create User
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-secondary btn-block" onclick="window.location.href=\'users.php\'">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
';

$title = 'Create User';
$active_page = 'create_user';

require_once 'layout.php';
?>
