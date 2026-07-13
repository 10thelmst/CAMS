<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cams';

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create user_roles table
$sql = "CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('admin', 'superadmin', 'employee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role),
    INDEX idx_user_id (user_id),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Table user_roles created successfully or already exists.<br>";
    
    // Migrate existing users to user_roles table
    $migrate_sql = "INSERT INTO user_roles (user_id, role) 
                     SELECT id, role FROM users 
                     WHERE id NOT IN (SELECT DISTINCT user_id FROM user_roles)";
    
    if ($conn->query($migrate_sql) === TRUE) {
        echo "Existing users migrated to user_roles table successfully.<br>";
        echo "Migrated rows: " . $conn->affected_rows . "<br>";
    } else {
        echo "Error migrating existing users: " . $conn->error . "<br>";
    }
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

$conn->close();
echo "<br><a href='../index.php'>Go to login page</a>";
?>
