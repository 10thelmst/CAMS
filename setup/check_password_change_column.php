<?php
require_once '../config/database.php';

$conn = get_cams_connection();

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'password_change_required'");

if ($check->num_rows > 0) {
    echo "<h1 style='color: green;'>Column 'password_change_required' exists</h1>";
    
    // Check user's current value
    $username = isset($_GET['username']) ? $_GET['username'] : 'belo_r';
    $stmt = $conn->prepare("SELECT username, password_change_required FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<h3>User: " . htmlspecialchars($user['username']) . "</h3>";
        echo "<p>password_change_required value: <strong>" . $user['password_change_required'] . "</strong></p>";
        
        if ($user['password_change_required'] == 1) {
            echo "<p style='color: green;'>User should be redirected to change password page on login.</p>";
        } else {
            echo "<p style='color: red;'>User will NOT be redirected to change password page.</p>";
            echo "<p><a href='reset_user_password.php?username=" . htmlspecialchars($username) . "'>Reset password and force change</a></p>";
        }
    } else {
        echo "<p>User not found.</p>";
    }
    
    $stmt->close();
} else {
    echo "<h1 style='color: red;'>Column 'password_change_required' does NOT exist</h1>";
    echo "<p><a href='add_password_change_required.php'>Add the column now</a></p>";
}

$conn->close();
?>
