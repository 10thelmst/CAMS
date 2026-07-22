<?php
require_once '../config/database.php';

$username = isset($_GET['username']) ? $_GET['username'] : '';

if (empty($username)) {
    echo "<h1>Test User Login Status</h1>";
    echo "<form method='GET'>";
    echo "<label>Username: <input type='text' name='username' required></label>";
    echo "<button type='submit'>Check</button>";
    echo "</form>";
    exit();
}

echo "<h1>Test User Login Status</h1>";
echo "<p>Testing username: <strong>" . htmlspecialchars($username) . "</strong></p>";

$conn = get_cams_connection();

// Check if user exists in CAMS
$stmt = $conn->prepare("SELECT id, username, email, role, password_change_required FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<h3 style='color: green;'>User exists in CAMS database</h3>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Username: " . htmlspecialchars($user['username']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
    echo "<li>Role: " . htmlspecialchars($user['role']) . "</li>";
    echo "<li>Password Change Required: <strong>" . ($user['password_change_required'] ? 'YES (1)' : 'NO (0)') . "</strong></li>";
    echo "</ul>";
    
    if ($user['password_change_required'] == 1) {
        echo "<p style='color: green;'>✓ User SHOULD be redirected to change password page on login.</p>";
    } else {
        echo "<p style='color: red;'>✗ User will NOT be redirected to change password page.</p>";
        echo "<p><a href='reset_user_password.php?username=" . htmlspecialchars($username) . "'>Reset password and force change</a></p>";
    }
} else {
    echo "<h3 style='color: orange;'>User does NOT exist in CAMS database</h3>";
    
    // Check OWWA database
    $owwa_conn = get_owwa_connection();
    $owwa_stmt = $owwa_conn->prepare("SELECT code_name FROM dtr_employeescopy WHERE code_name = ?");
    $owwa_stmt->bind_param("s", $username);
    $owwa_stmt->execute();
    $owwa_result = $owwa_stmt->get_result();
    
    if ($owwa_result->num_rows > 0) {
        echo "<h3 style='color: green;'>User exists in OWWA database</h3>";
        echo "<p>This user will be auto-imported on first login with password_change_required = 1</p>";
        echo "<p>Login with password: <code>password123</code></p>";
    } else {
        echo "<h3 style='color: red;'>User does NOT exist in OWWA database</h3>";
    }
    
    $owwa_stmt->close();
    $owwa_conn->close();
}

$stmt->close();
$conn->close();

echo "<hr>";
echo "<form method='GET'>";
echo "<label>Test different username: <input type='text' name='username' value='" . htmlspecialchars($username) . "'></label>";
echo "<button type='submit'>Check</button>";
echo "</form>";
?>
