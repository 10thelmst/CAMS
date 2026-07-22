<?php
require_once '../config/database.php';

$conn = get_cams_connection();

// Create employee_bio_data table
$sql = "CREATE TABLE IF NOT EXISTS employee_bio_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sss_number VARCHAR(20),
    gsis_number VARCHAR(20),
    philhealth_number VARCHAR(20),
    pagibig_number VARCHAR(20),
    tin_number VARCHAR(20),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_bio (user_id)
)";

if ($conn->query($sql)) {
    echo "<h1>Success</h1>";
    echo "<p>The 'employee_bio_data' table has been created successfully.</p>";
    echo "<p>Table structure:</p>";
    echo "<ul>";
    echo "<li>id - Primary key</li>";
    echo "<li>user_id - Foreign key to users table</li>";
    echo "<li>sss_number - SSS number</li>";
    echo "<li>gsis_number - GSIS number</li>";
    echo "<li>philhealth_number - PhilHealth number</li>";
    echo "<li>pagibig_number - PAG-IBIG number</li>";
    echo "<li>tin_number - TIN number</li>";
    echo "<li>updated_at - Last update timestamp</li>";
    echo "<li>created_at - Creation timestamp</li>";
    echo "</ul>";
    echo "<a href='../Employee/bio_data.php' class='btn btn-primary'>Go to Bio-Data Page</a>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>Error creating table: " . $conn->error . "</p>";
    // Check if table already exists
    $check = $conn->query("SHOW TABLES LIKE 'employee_bio_data'");
    if ($check->num_rows > 0) {
        echo "<p>The table 'employee_bio_data' already exists.</p>";
    }
}

$conn->close();
?>
