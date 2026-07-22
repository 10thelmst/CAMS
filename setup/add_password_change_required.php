<?php
require_once '../config/database.php';

$conn = get_cams_connection();

// Add password_change_required column to users table
$sql = "ALTER TABLE users ADD COLUMN password_change_required TINYINT(1) DEFAULT 0 AFTER status";

if ($conn->query($sql)) {
    echo "<h1>Success</h1>";
    echo "<p>The 'password_change_required' column has been added to the users table.</p>";
    echo "<p>Default value: 0 (not required)</p>";
    echo "<p>Set to 1 for users who need to change their password on first login.</p>";
    echo "<a href='../Superadmin/users.php' class='btn btn-primary'>Go to Users Management</a>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>Error adding column: " . $conn->error . "</p>";
    // Check if column already exists
    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'password_change_required'");
    if ($check->num_rows > 0) {
        echo "<p>The column 'password_change_required' already exists in the users table.</p>";
    }
}

$conn->close();
?>
