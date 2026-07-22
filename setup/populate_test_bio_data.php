<?php
require_once '../config/database.php';

$conn = get_cams_connection();

// Test user ID (change this to an actual user ID in your system)
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;

echo "<h1>Populate Test Bio-Data</h1>";
echo "<p>Populating test data for User ID: <strong>" . $user_id . "</strong></p>";

try {
    $conn->begin_transaction();
    
    // 1. Insert/Update Personal Data
    echo "<p>Attempting personal data insert...</p>";
    $personal_stmt = $conn->prepare("INSERT INTO employee_personal_data (user_id, position_desired, application_date, first_name, middle_name, last_name, gender, city_address, provincial_address, telephone, cellphone, email, date_of_birth, birth_place, civil_status, citizenship, height, weight, religion, spouse_name, spouse_occupation, children_names, children_birth_dates, father_name, father_occupation, mother_name, mother_occupation, languages) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE position_desired = ?, application_date = ?, first_name = ?, middle_name = ?, last_name = ?, gender = ?, city_address = ?, provincial_address = ?, telephone = ?, cellphone = ?, email = ?, date_of_birth = ?, birth_place = ?, civil_status = ?, citizenship = ?, height = ?, weight = ?, religion = ?, spouse_name = ?, spouse_occupation = ?, children_names = ?, children_birth_dates = ?, father_name = ?, father_occupation = ?, mother_name = ?, mother_occupation = ?, languages = ?");
    
    $personal_stmt->bind_param("isssssssssssssssddssssssssssssssssssssssssss", 
        $user_id, 'Software Engineer', '2026-01-15', 'Juan', 'Santos', 'Dela Cruz', 'Male', '123 Main Street, Manila', '456 Provincial Road, Laguna', '02-8123-4567', '0917-123-4567', 'juan.delacruz@email.com', '1990-05-15', 'Manila', 'Married', 'Filipino', 175.5, 70.2, 'Roman Catholic', 'Maria Santos', 'Nurse', 'Juan Jr., Maria', '2015-03-20, 2018-07-10', 'Pedro Dela Cruz', 'Engineer', 'Elena Santos', 'Teacher', 'Filipino, English',
        'Software Engineer', '2026-01-15', 'Juan', 'Santos', 'Dela Cruz', 'Male', '123 Main Street, Manila', '456 Provincial Road, Laguna', '02-8123-4567', '0917-123-4567', 'juan.delacruz@email.com', '1990-05-15', 'Manila', 'Married', 'Filipino', 175.5, 70.2, 'Roman Catholic', 'Maria Santos', 'Nurse', 'Juan Jr., Maria', '2015-03-20, 2018-07-10', 'Pedro Dela Cruz', 'Engineer', 'Elena Santos', 'Teacher', 'Filipino', 'English');
    
    $personal_stmt->execute();
    $personal_stmt->close();
    echo "<p style='color: green;'>✓ Personal data inserted/updated</p>";
    
    // 2. Insert/Update Government IDs
    echo "<p>Attempting government IDs insert...</p>";
    $bio_stmt = $conn->prepare("INSERT INTO employee_bio_data (user_id, sss_number, gsis_number, philhealth_number, pagibig_number, tin_number, residence_cert_number, residence_cert_issued_at, residence_cert_issued_on, nbi_clearance_number, nbi_issued_on, passport_number, passport_issued_on, passport_expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE sss_number = ?, gsis_number = ?, philhealth_number = ?, pagibig_number = ?, tin_number = ?, residence_cert_number = ?, residence_cert_issued_at = ?, residence_cert_issued_on = ?, nbi_clearance_number = ?, nbi_issued_on = ?, passport_number = ?, passport_issued_on = ?, passport_expiry_date = ?");
    
    $bio_stmt->bind_param("isssssssssssssssssssssssssss", 
        $user_id, '12-3456789-0', '123456789012', '12-345678901-2', '1234567890123', '123-456-789-000', '2021-000001', 'Manila City Hall', '2021-01-15', 'NBI-2021-123456', '2021-02-20', 'E12345678', '2020-03-15', '2030-03-14',
        '12-3456789-0', '123456789012', '12-345678901-2', '1234567890123', '123-456-789-000', '2021-000001', 'Manila City Hall', '2021-01-15', 'NBI-2021-123456', '2021-02-20', 'E12345678', '2020-03-15', '2030-03-14');
    
    $bio_stmt->execute();
    $bio_stmt->close();
    echo "<p style='color: green;'>✓ Government IDs inserted/updated</p>";
    
    // 3. Delete and Insert Education Records
    $conn->query("DELETE FROM employee_education WHERE user_id = " . $user_id);
    
    $education_data = [
        ['Elementary', 'Manila Elementary School', 'Elementary Graduate', 2002, 'Basic computer skills'],
        ['High School', 'Manila High School', 'High School Graduate', 2006, 'Advanced mathematics'],
        ['College', 'University of the Philippines', 'Bachelor of Science in Computer Science', 2010, 'Programming, Database Management']
    ];
    
    $edu_stmt = $conn->prepare("INSERT INTO employee_education (user_id, education_level, school_name, degree_course, year_graduated, special_skills) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($education_data as $edu) {
        $edu_stmt->bind_param("issssi", $user_id, $edu[0], $edu[1], $edu[2], $edu[3], $edu[4]);
        $edu_stmt->execute();
    }
    $edu_stmt->close();
    echo "<p style='color: green;'>✓ Education records inserted (3 records)</p>";
    
    // 4. Delete and Insert Employment Records
    $conn->query("DELETE FROM employee_employment WHERE user_id = " . $user_id);
    
    $employment_data = [
        ['ABC Corporation', 'Junior Developer', '2010-06-01', '2013-05-31', 'Career advancement'],
        ['XYZ Tech Solutions', 'Senior Developer', '2013-06-01', '2018-12-31', 'Better opportunities'],
        ['DEF Innovations', 'Team Lead', '2019-01-01', '2021-12-31', 'Company restructuring']
    ];
    
    $emp_stmt = $conn->prepare("INSERT INTO employee_employment (user_id, company_name, position, from_date, `to`, reason_for_leaving) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($employment_data as $emp) {
        $emp_stmt->bind_param("isssss", $user_id, $emp[0], $emp[1], $emp[2], $emp[3], $emp[4]);
        $emp_stmt->execute();
    }
    $emp_stmt->close();
    echo "<p style='color: green;'>✓ Employment records inserted (3 records)</p>";
    
    // 5. Delete and Insert Reference Records
    $conn->query("DELETE FROM employee_references WHERE user_id = " . $user_id);
    
    $reference_data = [
        ['Jose Reyes', 'Project Manager', 'ABC Corporation', '0918-234-5678'],
        ['Ana Garcia', 'HR Director', 'XYZ Tech Solutions', '0919-345-6789']
    ];
    
    $ref_stmt = $conn->prepare("INSERT INTO employee_references (user_id, reference_name, position, company, contact_number) VALUES (?, ?, ?, ?, ?)");
    foreach ($reference_data as $ref) {
        $ref_stmt->bind_param("issss", $user_id, $ref[0], $ref[1], $ref[2], $ref[3]);
        $ref_stmt->execute();
    }
    $ref_stmt->close();
    echo "<p style='color: green;'>✓ Reference records inserted (2 records)</p>";
    
    // 6. Delete and Insert Emergency Contact Records
    $conn->query("DELETE FROM emergency_contacts WHERE user_id = " . $user_id);
    
    $emergency_data = [
        ['Maria Dela Cruz', 'Spouse', '0917-123-4567', '123 Main Street, Manila'],
        ['Pedro Dela Cruz', 'Father', '0920-456-7890', '456 Provincial Road, Laguna'],
        ['Elena Santos', 'Mother', '0921-567-8901', '456 Provincial Road, Laguna']
    ];
    
    $emergency_stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, contact_name, relationship, contact_number, address) VALUES (?, ?, ?, ?, ?)");
    foreach ($emergency_data as $emergency) {
        $emergency_stmt->bind_param("issss", $user_id, $emergency[0], $emergency[1], $emergency[2], $emergency[3]);
        $emergency_stmt->execute();
    }
    $emergency_stmt->close();
    echo "<p style='color: green;'>✓ Emergency contact records inserted (3 records)</p>";
    
    $conn->commit();
    
    echo "<hr>";
    echo "<h3 style='color: green;'>Test Data Successfully Populated!</h3>";
    echo "<p><strong>Summary:</strong></p>";
    echo "<ul>";
    echo "<li>Personal data: 1 record</li>";
    echo "<li>Government IDs: 1 record</li>";
    echo "<li>Education: 3 records</li>";
    echo "<li>Employment: 3 records</li>";
    echo "<li>References: 2 records</li>";
    echo "<li>Emergency contacts: 3 records</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><a href='../Employee/bio_data.php' class='btn btn-primary'>View Bio-Data Page</a></p>";
    echo "<p><a href='populate_test_bio_data.php?user_id=" . ($user_id + 1) . "' class='btn btn-secondary'>Populate for Next User</a></p>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}

$conn->close();
?>
