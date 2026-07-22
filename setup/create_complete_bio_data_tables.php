<?php
require_once '../config/database.php';

$conn = get_cams_connection();

$errors = array();
$success = array();

// 1. Create employee_personal_data table (one-to-one)
$sql_personal = "CREATE TABLE IF NOT EXISTS employee_personal_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    position_desired VARCHAR(100),
    application_date DATE,
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    last_name VARCHAR(100),
    gender ENUM('Male', 'Female', 'Other'),
    city_address TEXT,
    provincial_address TEXT,
    telephone VARCHAR(20),
    cellphone VARCHAR(20),
    email VARCHAR(100),
    date_of_birth DATE,
    birth_place VARCHAR(100),
    civil_status ENUM('Single', 'Married', 'Widowed', 'Separated', 'Divorced'),
    citizenship VARCHAR(50),
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    religion VARCHAR(50),
    spouse_name VARCHAR(100),
    spouse_occupation VARCHAR(100),
    children_names TEXT,
    children_birth_dates TEXT,
    father_name VARCHAR(100),
    father_occupation VARCHAR(100),
    mother_name VARCHAR(100),
    mother_occupation VARCHAR(100),
    languages TEXT,
    emergency_contact VARCHAR(100),
    emergency_address TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_personal (user_id)
)";

if ($conn->query($sql_personal)) {
    $success[] = "employee_personal_data table created";
} else {
    $errors[] = "Error creating employee_personal_data: " . $conn->error;
}

// 2. Create employee_education table (one-to-many)
$sql_education = "CREATE TABLE IF NOT EXISTS employee_education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    education_level ENUM('Elementary', 'High School', 'College', 'Vocational', 'Graduate Studies'),
    school_name VARCHAR(200),
    degree_course VARCHAR(200),
    year_graduated YEAR,
    special_skills TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_education)) {
    $success[] = "employee_education table created";
} else {
    $errors[] = "Error creating employee_education: " . $conn->error;
}

// 3. Create employee_employment table (one-to-many)
$sql_employment = "CREATE TABLE IF NOT EXISTS employee_employment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(200),
    position VARCHAR(100),
    from_date DATE,
    `to` DATE,
    reason_for_leaving TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_employment)) {
    $success[] = "employee_employment table created";
} else {
    $errors[] = "Error creating employee_employment: " . $conn->error;
}

// Fix existing table if it has 'to_date' instead of 'to'
$check_to_col = $conn->query("SHOW COLUMNS FROM employee_employment LIKE 'to_date'");
if ($check_to_col->num_rows > 0) {
    $sql_fix_to = "ALTER TABLE employee_employment CHANGE COLUMN to_date `to` DATE";
    if ($conn->query($sql_fix_to)) {
        $success[] = "Fixed 'to_date' column to 'to' in employee_employment";
    } else {
        $errors[] = "Error fixing 'to' column: " . $conn->error;
    }
}

// 4. Create employee_references table (one-to-many)
$sql_references = "CREATE TABLE IF NOT EXISTS employee_references (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reference_name VARCHAR(100),
    position VARCHAR(100),
    company VARCHAR(200),
    contact_number VARCHAR(20),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_references)) {
    $success[] = "employee_references table created";
} else {
    $errors[] = "Error creating employee_references: " . $conn->error;
}

// 5. Update employee_bio_data table to include additional government IDs
$sql_update_bio = "ALTER TABLE employee_bio_data 
    ADD COLUMN IF NOT EXISTS residence_cert_number VARCHAR(50),
    ADD COLUMN IF NOT EXISTS residence_cert_issued_at VARCHAR(100),
    ADD COLUMN IF NOT EXISTS residence_cert_issued_on DATE,
    ADD COLUMN IF NOT EXISTS nbi_clearance_number VARCHAR(50),
    ADD COLUMN IF NOT EXISTS nbi_issued_on DATE,
    ADD COLUMN IF NOT EXISTS passport_number VARCHAR(50),
    ADD COLUMN IF NOT EXISTS passport_issued_on DATE,
    ADD COLUMN IF NOT EXISTS passport_expiry_date DATE";

// Check if columns exist first
$check_columns = $conn->query("SHOW COLUMNS FROM employee_bio_data LIKE 'residence_cert_number'");
if ($check_columns->num_rows == 0) {
    if ($conn->query($sql_update_bio)) {
        $success[] = "employee_bio_data table updated with additional government IDs";
    } else {
        $errors[] = "Error updating employee_bio_data: " . $conn->error;
    }
} else {
    $success[] = "employee_bio_data table already has additional columns";
}

// 6. Create emergency_contacts table (one-to-many)
$sql_emergency = "CREATE TABLE IF NOT EXISTS emergency_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    contact_name VARCHAR(100),
    relationship VARCHAR(50),
    contact_number VARCHAR(20),
    address TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_emergency)) {
    $success[] = "emergency_contacts table created";
} else {
    $errors[] = "Error creating emergency_contacts: " . $conn->error;
}

// 7. Remove emergency_contact and emergency_address from employee_personal_data
$check_emergency_cols = $conn->query("SHOW COLUMNS FROM employee_personal_data LIKE 'emergency_contact'");
if ($check_emergency_cols->num_rows > 0) {
    $sql_remove_emergency = "ALTER TABLE employee_personal_data DROP COLUMN emergency_contact, DROP COLUMN emergency_address";
    if ($conn->query($sql_remove_emergency)) {
        $success[] = "Removed emergency_contact and emergency_address from employee_personal_data";
    } else {
        $errors[] = "Error removing emergency columns: " . $conn->error;
    }
} else {
    $success[] = "Emergency columns already removed from employee_personal_data";
}

$conn->close();

// Display results
echo "<h1>Database Migration Results</h1>";

if (!empty($success)) {
    echo "<h3 style='color: green;'>Success:</h3>";
    echo "<ul>";
    foreach ($success as $s) {
        echo "<li>" . htmlspecialchars($s) . "</li>";
    }
    echo "</ul>";
}

if (!empty($errors)) {
    echo "<h3 style='color: red;'>Errors:</h3>";
    echo "<ul>";
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e) . "</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Database Structure Created:</h3>";
echo "<ul>";
echo "<li><strong>employee_personal_data</strong> - Personal information (one-to-one)</li>";
echo "<li><strong>employee_education</strong> - Educational background (one-to-many)</li>";
echo "<li><strong>employee_employment</strong> - Employment history (one-to-many)</li>";
echo "<li><strong>employee_references</strong> - Character references (one-to-many)</li>";
echo "<li><strong>employee_bio_data</strong> - Government IDs (one-to-one, updated)</li>";
echo "<li><strong>emergency_contacts</strong> - Emergency contacts (one-to-many)</li>";
echo "</ul>";

echo "<a href='../Employee/bio_data.php' class='btn btn-primary'>Go to Bio-Data Page</a>";
?>
