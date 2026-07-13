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

// Get user ID from URL
if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$user_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $roles = isset($_POST['roles']) ? $_POST['roles'] : array();
    $status = $_POST['status'];
    $new_password = $_POST['new_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($roles) || empty($status)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = 'Email address already exists for another user.';
        } else {
            // Update user
            $primary_role = $roles[0];
            if (!empty($new_password)) {
                // Update with new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $username, $email, $primary_role, $status, $hashed_password, $user_id);
            } else {
                // Update without password change
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $primary_role, $status, $user_id);
            }
            
            if ($stmt->execute()) {
                // Delete existing roles for this user
                $delete_stmt = $conn->prepare("DELETE FROM user_roles WHERE user_id = ?");
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
                $delete_stmt->close();
                
                // Insert new roles
                $insert_stmt = $conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, ?)");
                foreach ($roles as $role) {
                    $insert_stmt->bind_param("is", $user_id, $role);
                    $insert_stmt->execute();
                }
                $insert_stmt->close();
                
                $success_message = 'User updated successfully!';
            } else {
                $error_message = 'Error updating user: ' . $conn->error;
            }
            
            $stmt->close();
        }
    }
}

// Get current user data
$stmt = $conn->prepare("SELECT id, username, email, role, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $error_message = 'User not found.';
    $user = null;
    $user_roles = array();
    $stmt->close();
} else {
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Get user's roles from user_roles table
    $roles_stmt = $conn->prepare("SELECT role FROM user_roles WHERE user_id = ?");
    $roles_stmt->bind_param("i", $user_id);
    $roles_stmt->execute();
    $roles_result = $roles_stmt->get_result();
    $user_roles = array();
    while ($row = $roles_result->fetch_assoc()) {
        $user_roles[] = $row['role'];
    }
    $roles_stmt->close();
}
$conn->close();

$content = '
    <div class="content-header">
        <h1>Edit User</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                <li class="breadcrumb-item active">Edit User</li>
            </ol>
        </nav>
    </div>';

if (isset($error_message)) {
    $content .= '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
}

if (isset($success_message)) {
    $content .= '<div class="alert alert-success">' . htmlspecialchars($success_message) . '</div>';
}

if ($user) {
    $content .= '
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit User Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Full Name *</label>
                    <input type="text" id="username" name="username" class="form-control" required 
                           value="' . htmlspecialchars($user['username']) . '">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="' . htmlspecialchars($user['email']) . '">
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Roles *</label>
                            <div class="checkbox-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="role_admin" name="roles[]" value="admin" class="custom-control-input" ' . (in_array('admin', $user_roles) ? 'checked' : '') . '>
                                    <label for="role_admin" class="custom-control-label">Admin</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="role_superadmin" name="roles[]" value="superadmin" class="custom-control-input" ' . (in_array('superadmin', $user_roles) ? 'checked' : '') . '>
                                    <label for="role_superadmin" class="custom-control-label">Superadmin</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="role_employee" name="roles[]" value="employee" class="custom-control-input" ' . (in_array('employee', $user_roles) ? 'checked' : '') . '>
                                    <label for="role_employee" class="custom-control-label">Employee</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" ' . (($user['status'] == 'active') ? 'selected' : '') . '>Active</option>
                                <option value="inactive" ' . (($user['status'] == 'inactive') ? 'selected' : '') . '>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" minlength="6">
                    <small>Minimum 6 characters. Leave empty to keep current password.</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Update User
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
    </div>';
} else {
    $content .= '
    <div class="card">
        <div class="card-body">
            <div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>
            <a href="users.php" class="btn btn-primary">Back to Users</a>
        </div>
    </div>';
}

$title = 'Edit User';
$active_page = 'users';

require_once 'layout.php';
?>
