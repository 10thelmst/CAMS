<?php
require_once '../config/database.php';

$conn = get_cams_connection();

$user_id = 1;

echo "<h1>Debug Test Data Insertion</h1>";

// Test Personal Data Insertion
echo "<h2>Testing Personal Data Insertion</h2>";
$personal_columns = array('user_id', 'position_desired', 'application_date', 'first_name', 'middle_name', 'last_name', 'gender', 'city_address', 'provincial_address', 'telephone', 'cellphone', 'email', 'date_of_birth', 'birth_place', 'civil_status', 'citizenship', 'height', 'weight', 'religion', 'spouse_name', 'spouse_occupation', 'children_names', 'children_birth_dates', 'father_name', 'father_occupation', 'mother_name', 'mother_occupation', 'languages');

// Check actual columns
$result = $conn->query("DESCRIBE employee_personal_data");
$actual_columns = array();
while ($row = $result->fetch_assoc()) {
    $actual_columns[] = $row['Field'];
}

echo "<p><strong>Expected columns (" . count($personal_columns) . "):</strong></p>";
echo "<pre>" . print_r($personal_columns, true) . "</pre>";

echo "<p><strong>Actual columns (" . count($actual_columns) . "):</strong></p>";
echo "<pre>" . print_r($actual_columns, true) . "</pre>";

$missing = array_diff($personal_columns, $actual_columns);
$extra = array_diff($actual_columns, $personal_columns);

if (!empty($missing)) {
    echo "<p style='color: red;'>Missing columns: " . implode(', ', $missing) . "</p>";
}

if (!empty($extra)) {
    echo "<p style='color: orange;'>Extra columns: " . implode(', ', $extra) . "</p>";
}

// Test Government IDs Insertion
echo "<h2>Testing Government IDs Insertion</h2>";
$bio_columns = array('user_id', 'sss_number', 'gsis_number', 'philhealth_number', 'pagibig_number', 'tin_number', 'residence_cert_number', 'residence_cert_issued_at', 'residence_cert_issued_on', 'nbi_clearance_number', 'nbi_issued_on', 'passport_number', 'passport_issued_on', 'passport_expiry_date');

$result = $conn->query("DESCRIBE employee_bio_data");
$actual_bio_columns = array();
while ($row = $result->fetch_assoc()) {
    $actual_bio_columns[] = $row['Field'];
}

echo "<p><strong>Expected columns (" . count($bio_columns) . "):</strong></p>";
echo "<pre>" . print_r($bio_columns, true) . "</pre>";

echo "<p><strong>Actual columns (" . count($actual_bio_columns) . "):</strong></p>";
echo "<pre>" . print_r($actual_bio_columns, true) . "</pre>";

$missing_bio = array_diff($bio_columns, $actual_bio_columns);
$extra_bio = array_diff($actual_bio_columns, $bio_columns);

if (!empty($missing_bio)) {
    echo "<p style='color: red;'>Missing columns: " . implode(', ', $missing_bio) . "</p>";
}

if (!empty($extra_bio)) {
    echo "<p style='color: orange;'>Extra columns: " . implode(', ', $extra_bio) . "</p>";
}

// Test Employment Insertion
echo "<h2>Testing Employment Insertion</h2>";
$emp_columns = array('user_id', 'company_name', 'position', 'from_date', 'to', 'reason_for_leaving');

$result = $conn->query("DESCRIBE employee_employment");
$actual_emp_columns = array();
while ($row = $result->fetch_assoc()) {
    $actual_emp_columns[] = $row['Field'];
}

echo "<p><strong>Expected columns (" . count($emp_columns) . "):</strong></p>";
echo "<pre>" . print_r($emp_columns, true) . "</pre>";

echo "<p><strong>Actual columns (" . count($actual_emp_columns) . "):</strong></p>";
echo "<pre>" . print_r($actual_emp_columns, true) . "</pre>";

$missing_emp = array_diff($emp_columns, $actual_emp_columns);
$extra_emp = array_diff($actual_emp_columns, $emp_columns);

if (!empty($missing_emp)) {
    echo "<p style='color: red;'>Missing columns: " . implode(', ', $missing_emp) . "</p>";
}

if (!empty($extra_emp)) {
    echo "<p style='color: orange;'>Extra columns: " . implode(', ', $extra_emp) . "</p>";
}

$conn->close();
?>
