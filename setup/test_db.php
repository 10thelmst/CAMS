<?php
// Simple database test
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cams';

// Test connection without database first
$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if database exists
$db_check = $conn->query("SHOW DATABASES LIKE 'cams'");
if ($db_check->num_rows == 0) {
    die("Database 'cams' does not exist. Please run <a href='setup_database.php'>setup_database.php</a> first.");
}

// Select database
$conn->select_db($database);

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows == 0) {
    die("Table 'users' does not exist in database 'cams'. Please run <a href='setup_database.php'>setup_database.php</a> first.");
}

// Check if users exist
$user_check = $conn->query("SELECT COUNT(*) as count FROM users");
$user_count = $user_check->fetch_assoc()['count'];

if ($user_count == 0) {
    die("No users found in database. Please run <a href='setup_database.php'>setup_database.php</a> first.");
}

// List all users
$users = $conn->query("SELECT id, username, email, role, status FROM users");

echo "<h2>Database Connection Successful!</h2>";
echo "<p>Total users: " . $user_count . "</p>";
echo "<h3>Users in database:</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";

while ($user = $users->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
    echo "<td>" . htmlspecialchars($user['status']) . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><a href='../index.php'>Go to Login Page</a></p>";

$conn->close();
?>
