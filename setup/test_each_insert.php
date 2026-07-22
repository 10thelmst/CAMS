<?php
require_once '../config/database.php';

$conn = get_cams_connection();

$user_id = 1;

echo "<h1>Test Each Insert Separately</h1>";

// Test 1: Personal Data
echo "<h2>Test 1: Personal Data</h2>";
try {
    $stmt = $conn->prepare("INSERT INTO employee_personal_data (user_id, position_desired, application_date, first_name, middle_name, last_name, gender, city_address, provincial_address, telephone, cellphone, email, date_of_birth, birth_place, civil_status, citizenship, height, weight, religion, spouse_name, spouse_occupation, children_names, children_birth_dates, father_name, father_occupation, mother_name, mother_occupation, languages) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssssssssssddssssssssssssssssss", 
        $user_id, 'Software Engineer', '2026-01-15', 'Juan', 'Santos', 'Dela Cruz', 'Male', '123 Main Street, Manila', '456 Provincial Road, Laguna', '02-8123-4567', '0917-123-4567', 'juan.delacruz@email.com', '1990-05-15', 'Manila', 'Married', 'Filipino', 175.5, 70.2, 'Roman Catholic', 'Maria Santos', 'Nurse', 'Juan Jr., Maria', '2015-03-20, 2018-07-10', 'Pedro Dela Cruz', 'Engineer', 'Elena Santos', 'Teacher', 'Filipino', 'English');
    $stmt->execute();
    $stmt->close();
    echo "<p style='color: green;'>✓ Personal data inserted successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Personal data failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: Government IDs
echo "<h2>Test 2: Government IDs</h2>";
try {
    $stmt = $conn->prepare("INSERT INTO employee_bio_data (user_id, sss_number, gsis_number, philhealth_number, pagibig_number, tin_number, residence_cert_number, residence_cert_issued_at, residence_cert_issued_on, nbi_clearance_number, nbi_issued_on, passport_number, passport_issued_on, passport_expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssssssssss", 
        $user_id, '12-3456789-0', '123456789012', '12-345678901-2', '1234567890123', '123-456-789-000', '2021-000001', 'Manila City Hall', '2021-01-15', 'NBI-2021-123456', '2021-02-20', 'E12345678', '2020-03-15', '2030-03-14');
    $stmt->execute();
    $stmt->close();
    echo "<p style='color: green;'>✓ Government IDs inserted successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Government IDs failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 3: Education
echo "<h2>Test 3: Education</h2>";
try {
    $stmt = $conn->prepare("INSERT INTO employee_education (user_id, education_level, school_name, degree_course, year_graduated, special_skills) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $user_id, 'Elementary', 'Manila Elementary School', 'Elementary Graduate', 2002, 'Basic computer skills');
    $stmt->execute();
    $stmt->close();
    echo "<p style='color: green;'>✓ Education inserted successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Education failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 4: Employment
echo "<h2>Test 4: Employment</h2>";
try {
    $stmt = $conn->prepare("INSERT INTO employee_employment (user_id, company_name, position, from_date, `to`, reason_for_leaving) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, 'ABC Corporation', 'Junior Developer', '2010-06-01', '2013-05-31', 'Career advancement');
    $stmt->execute();
    $stmt->close();
    echo "<p style='color: green;'>✓ Employment inserted successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Employment failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 5: References
echo "<h2>Test 5: References</h2>";
try {
    $stmt = $conn->prepare("INSERT INTO employee_references (user_id, reference_name, position, company, contact_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, 'Jose Reyes', 'Project Manager', 'ABC Corporation', '0918-234-5678');
    $stmt->execute();
    $stmt->close();
    echo "<p style='color: green;'>✓ References inserted successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ References failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 6: Emergency Contacts
echo "<h2>Test 6: Emergency Contacts</h2>";
try {
    $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, contact_name, relationship, contact_number, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, 'Maria Dela Cruz', 'Spouse', '0917-123-4567', '123 Main Street, Manila');
    $stmt->execute();
    $stmt->close();
    echo "<p style='color: green;'>✓ Emergency contacts inserted successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Emergency contacts failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$conn->close();
?>
