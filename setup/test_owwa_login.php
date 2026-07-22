<?php
require_once '../config/database.php';

$username = isset($_GET['username']) ? $_GET['username'] : 'belo_r';

echo "<h1>Test OWWA User Login</h1>";
echo "<p>Testing username: <strong>" . htmlspecialchars($username) . "</strong></p>";

// Check if user exists in CAMS
$cams_conn = get_cams_connection();
$stmt = $cams_conn->prepare("SELECT id, username, email, role, password_change_required FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h3 style='color: green;'>User exists in CAMS database</h3>";
    $user = $result->fetch_assoc();
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Username: " . htmlspecialchars($user['username']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
    echo "<li>Role: " . htmlspecialchars($user['role']) . "</li>";
    echo "<li>Password Change Required: " . ($user['password_change_required'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
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
        $owwa_user = $owwa_result->fetch_assoc();
        echo "<ul>";
        echo "<li>Code Name: " . htmlspecialchars($owwa_user['code_name']) . "</li>";
        echo "</ul>";
        echo "<p>This user should be able to login with password: <code>password123</code></p>";
    } else {
        echo "<h3 style='color: red;'>User does NOT exist in OWWA database</h3>";
        echo "<p>The username <strong>" . htmlspecialchars($username) . "</strong> was not found in either database.</p>";
    }
    
    $owwa_stmt->close();
    $owwa_conn->close();
}

$stmt->close();
$cams_conn->close();

echo "<hr>";
echo "<form method='GET'>";
echo "<label>Test different username: <input type='text' name='username' value='" . htmlspecialchars($username) . "'></label>";
echo "<button type='submit'>Test</button>";
echo "</form>";
?>
