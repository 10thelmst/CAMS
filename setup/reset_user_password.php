<?php
require_once '../config/database.php';

$username = isset($_GET['username']) ? $_GET['username'] : 'belo_r';

$conn = get_cams_connection();

// Reset password to default and force password change
$default_password = 'password123';
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
$password_change_required = 1;

$stmt = $conn->prepare("UPDATE users SET password = ?, password_change_required = ? WHERE username = ?");
$stmt->bind_param("sis", $hashed_password, $password_change_required, $username);

if ($stmt->execute()) {
    echo "<h1>Success</h1>";
    echo "<p>Password for <strong>" . htmlspecialchars($username) . "</strong> has been reset.</p>";
    echo "<p>New password: <code>password123</code></p>";
    echo "<p>User will be required to change password on next login.</p>";
    echo "<a href='../index.php' class='btn btn-primary'>Go to Login</a>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>Error resetting password: " . $conn->error . "</p>";
}

$stmt->close();
$conn->close();
?>
