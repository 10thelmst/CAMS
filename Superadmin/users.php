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

// Handle delete request
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $error_message = 'You cannot delete your own account.';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success_message = 'User deleted successfully.';
        } else {
            $error_message = 'Error deleting user: ' . $conn->error;
        }
        
        $stmt->close();
    }
}

// Get all users with their roles
$result = $conn->query("SELECT u.id, u.username, u.email, u.role, u.status, u.created_at, GROUP_CONCAT(ur.role SEPARATOR ',') as all_roles FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id GROUP BY u.id ORDER BY u.created_at DESC");

$conn->close();

$content = '
    <div class="content-header">
        <h1>Manage Users</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </div>';

if (isset($success_message)) {
    $content .= '<div class="alert alert-success">' . htmlspecialchars($success_message) . '</div>';
}

if (isset($error_message)) {
    $content .= '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
}

$content .= '
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Users</h3>
            <div class="card-tools">
                <a href="../setup/import_owwa_users.php" class="btn btn-info btn-sm" onclick="return confirm('This will import users from OWWA database. Continue?');">
                    <i class="fas fa-sync"></i> Import from OWWA
                </a>
                <a href="create_user.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Create User
                </a>
            </div>
        </div>
        <div class="card-body">
';

if ($result->num_rows > 0) {
    $content .= '
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
    
    while ($user = $result->fetch_assoc()) {
        // Get all roles for this user
        $user_roles = $user['all_roles'] ? explode(',', $user['all_roles']) : array($user['role']);
        
        // Generate role badges
        $role_badges = '';
        foreach ($user_roles as $role) {
            $badge_class = 'badge-primary';
            if ($role == 'superadmin') $badge_class = 'badge-danger';
            elseif ($role == 'employee') $badge_class = 'badge-success';
            $role_badges .= '<span class="badge ' . $badge_class . ' mr-1">' . ucfirst($role) . '</span>';
        }
        
        $status_badge = $user['status'] == 'active' ? 'badge-success' : 'badge-secondary';
        
        $content .= '
                    <tr>
                        <td>' . $user['id'] . '</td>
                        <td>' . htmlspecialchars($user['username']) . '</td>
                        <td>' . htmlspecialchars($user['email']) . '</td>
                        <td>' . $role_badges . '</td>
                        <td><span class="badge ' . $status_badge . '">' . ucfirst($user['status']) . '</span></td>
                        <td>' . date('M d, Y', strtotime($user['created_at'])) . '</td>
                        <td>
                            <a href="edit_user.php?id=' . $user['id'] . '" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>';
        
        if ($user['id'] != $_SESSION['user_id']) {
            $content .= '
                            <a href="users.php?delete=' . $user['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this user?\');">
                                <i class="fas fa-trash"></i> Delete
                            </a>';
        }
        
        $content .= '
                        </td>
                    </tr>';
    }
    
    $content .= '
                </tbody>
            </table>';
} else {
    $content .= '<p>No users found. <a href="create_user.php">Create your first user</a>.</p>';
}

$content .= '
        </div>
    </div>
';

$title = 'Manage Users';
$active_page = 'users';

require_once 'layout.php';
?>
