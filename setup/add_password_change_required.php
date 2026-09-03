<?php
require_once '../config/database.php';

$conn = get_cams_connection();

$check = $conn->query("SHOW COLUMNS FROM users LIKE 'password_change_required'");

if ($check && $check->num_rows > 0) {
    $conn->query("ALTER TABLE users MODIFY password_change_required TINYINT(1) NOT NULL DEFAULT 1");
    echo "<h1>Updated</h1>";
    echo "<p>The 'password_change_required' column exists and now defaults to 1.</p>";
    echo "<p>Users will be required to change their password on their next login.</p>";
} else {
    $sql = "ALTER TABLE users ADD COLUMN password_change_required TINYINT(1) NOT NULL DEFAULT 1 AFTER status";
    if ($conn->query($sql)) {
        echo "<h1>Success</h1>";
        echo "<p>The 'password_change_required' column has been added to the users table.</p>";
        echo "<p>Default value: 1 (required on first login)</p>";
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Error adding column: " . $conn->error . "</p>";
    }
}

echo "<a href='../Superadmin/users.php' class='btn btn-primary'>Go to Users Management</a>";

$conn->close();
?>
