<?php
require_once '../config/database.php';

echo "<h1>Remove All Users Except Admin and Superadmin</h1>";
echo "<p style='color: red;'><strong>Warning: This action cannot be undone!</strong></p>";

$conn = get_cams_connection();

// First, show current users
echo "<h2>Current Users:</h2>";
$result = $conn->query("SELECT id, username, email, role FROM users ORDER BY role, username");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Action</th></tr>";

$users_to_delete = array();
$users_to_keep = array();

while ($user = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
    
    if ($user['role'] == 'admin' || $user['role'] == 'superadmin') {
        echo "<td style='color: green;'>Keep</td>";
        $users_to_keep[] = $user['id'];
    } else {
        echo "<td style='color: red;'>Delete</td>";
        $users_to_delete[] = $user['id'];
    }
    echo "</tr>";
}
echo "</table>";

echo "<h3>Summary:</h3>";
echo "<p>Users to keep: <strong>" . count($users_to_keep) . "</strong> (admin and superadmin)</p>";
echo "<p>Users to delete: <strong>" . count($users_to_delete) . "</strong> (employees and others)</p>";

if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
    echo "<h2>Deleting users...</h2>";
    
    $deleted_count = 0;
    $error_count = 0;
    
    foreach ($users_to_delete as $user_id) {
        // Delete from user_roles first (foreign key)
        $delete_roles = $conn->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $delete_roles->bind_param("i", $user_id);
        $delete_roles->execute();
        $delete_roles->close();
        
        // Delete from users table
        $delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_user->bind_param("i", $user_id);
        
        if ($delete_user->execute()) {
            $deleted_count++;
            echo "<p style='color: green;'>✓ Deleted user ID: " . $user_id . "</p>";
        } else {
            $error_count++;
            echo "<p style='color: red;'>✗ Error deleting user ID: " . $user_id . " - " . $conn->error . "</p>";
        }
        
        $delete_user->close();
    }
    
    echo "<h3>Deletion Complete:</h3>";
    echo "<p>Successfully deleted: <strong>" . $deleted_count . "</strong> users</p>";
    echo "<p>Errors: <strong>" . $error_count . "</strong></p>";
    
    if ($error_count == 0) {
        echo "<p style='color: green;'><strong>All non-admin users have been removed successfully!</strong></p>";
    }
    
    echo "<a href='../Superadmin/users.php' class='btn btn-primary'>Go to Users Management</a>";
} else {
    echo "<hr>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<button type='submit' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete all non-admin users? This cannot be undone!\")'>";
    echo "<i class='fas fa-trash'></i> Delete All Non-Admin Users";
    echo "</button>";
    echo "</form>";
    echo "<a href='../Superadmin/users.php' class='btn btn-secondary'>Cancel</a>";
}

$conn->close();
?>
