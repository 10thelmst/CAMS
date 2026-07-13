<?php
// Check users in database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cams';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT id, username, email, role FROM users");

echo "<h2>Users in Database:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role (exact value)</th></tr>";

while ($user = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td><strong>'" . htmlspecialchars($user['role']) . "'</strong></td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><strong>Note:</strong> The role must be exactly 'superadmin' (lowercase) for the redirect to work.</p>";

$conn->close();
?>
