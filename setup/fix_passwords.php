<?php
// Fix passwords in database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cams';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate correct hash for "password123"
$correct_password = 'password123';
$correct_hash = password_hash($correct_password, PASSWORD_DEFAULT);

echo "<h2>Fixing User Passwords</h2>";
echo "<p>Correct hash for 'password123': " . $correct_hash . "</p>";

// Update all users
$stmt = $conn->prepare("UPDATE users SET password = ?");
$stmt->bind_param("s", $correct_hash);

if ($stmt->execute()) {
    echo "<p style='color: green;'><strong>Success!</strong> All passwords updated to 'password123'</p>";
    echo "<p>Affected rows: " . $stmt->affected_rows . "</p>";
} else {
    echo "<p style='color: red;'><strong>Error:</strong> " . $conn->error . "</p>";
}

$stmt->close();
$conn->close();

echo "<p><a href='../index.php'>Test Login</a></p>";
echo "<p><a href='test_db.php'>Check Database</a></p>";
?>
