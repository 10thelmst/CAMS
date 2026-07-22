<?php
require_once '../auth/auth_check.php';

// Check if user has employee role
if (!has_role('employee')) {
    header('Location: ../index.php?unauthorized=1');
    exit();
}

require_once '../config/database.php';

$conn = get_cams_connection();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $success_message = '';
    $error_message = '';
    
    $section = isset($_POST['save_section']) ? $_POST['save_section'] : '';
    
    // 1. Save Personal Data
    if ($section == 'personal' || $section == 'all' || $section == '') {
        try {
            $position_desired = trim($_POST['position_desired']);
            $application_date = !empty($_POST['application_date']) ? $_POST['application_date'] : null;
            $first_name = trim($_POST['first_name']);
            $middle_name = trim($_POST['middle_name']);
            $last_name = trim($_POST['last_name']);
            $gender = trim($_POST['gender']);
            $city_address = trim($_POST['city_address']);
            $provincial_address = trim($_POST['provincial_address']);
            $telephone = trim($_POST['telephone']);
            $cellphone = trim($_POST['cellphone']);
            $email = trim($_POST['email']);
            $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
            $birth_place = trim($_POST['birth_place']);
            $civil_status = trim($_POST['civil_status']);
            $citizenship = trim($_POST['citizenship']);
            $height = !empty($_POST['height']) ? $_POST['height'] : null;
            $weight = !empty($_POST['weight']) ? $_POST['weight'] : null;
            $religion = trim($_POST['religion']);
            $spouse_name = trim($_POST['spouse_name']);
            $spouse_occupation = trim($_POST['spouse_occupation']);
            $children_names = trim($_POST['children_names']);
            $children_birth_dates = trim($_POST['children_birth_dates']);
            $father_name = trim($_POST['father_name']);
            $father_occupation = trim($_POST['father_occupation']);
            $mother_name = trim($_POST['mother_name']);
            $mother_occupation = trim($_POST['mother_occupation']);
            $languages = trim($_POST['languages']);
            
            $stmt = $conn->prepare("INSERT INTO employee_personal_data (user_id, position_desired, application_date, first_name, middle_name, last_name, gender, city_address, provincial_address, telephone, cellphone, email, date_of_birth, birth_place, civil_status, citizenship, height, weight, religion, spouse_name, spouse_occupation, children_names, children_birth_dates, father_name, father_occupation, mother_name, mother_occupation, languages) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE position_desired = VALUES(position_desired), application_date = VALUES(application_date), first_name = VALUES(first_name), middle_name = VALUES(middle_name), last_name = VALUES(last_name), gender = VALUES(gender), city_address = VALUES(city_address), provincial_address = VALUES(provincial_address), telephone = VALUES(telephone), cellphone = VALUES(cellphone), email = VALUES(email), date_of_birth = VALUES(date_of_birth), birth_place = VALUES(birth_place), civil_status = VALUES(civil_status), citizenship = VALUES(citizenship), height = VALUES(height), weight = VALUES(weight), religion = VALUES(religion), spouse_name = VALUES(spouse_name), spouse_occupation = VALUES(spouse_occupation), children_names = VALUES(children_names), children_birth_dates = VALUES(children_birth_dates), father_name = VALUES(father_name), father_occupation = VALUES(father_occupation), mother_name = VALUES(mother_name), mother_occupation = VALUES(mother_occupation), languages = VALUES(languages)");
            
            // Corrected to 28 parameters ("i" + 15 "s" + 2 "d" + 10 "s")
            $stmt->bind_param("isssssssssssssssddssssssssss", 
                $_SESSION['user_id'], $position_desired, $application_date, $first_name, $middle_name, $last_name, $gender, $city_address, $provincial_address, $telephone, $cellphone, $email, $date_of_birth, $birth_place, $civil_status, $citizenship, $height, $weight, $religion, $spouse_name, $spouse_occupation, $children_names, $children_birth_dates, $father_name, $father_occupation, $mother_name, $mother_occupation, $languages);
            $stmt->execute();
            $stmt->close();
            $success_message .= "Personal data saved. ";
        } catch (Exception $e) {
            $error_message .= "Personal data error: " . $e->getMessage() . " ";
        }
    }
    
    // 2. Save Government IDs
    if ($section == 'government' || $section == 'all' || $section == '') {
        try {
            $sss_number = isset($_POST['sss_number']) ? trim($_POST['sss_number']) : '';
            $gsis_number = isset($_POST['gsis_number']) ? trim($_POST['gsis_number']) : '';
            $philhealth_number = isset($_POST['philhealth_number']) ? trim($_POST['philhealth_number']) : '';
            $pagibig_number = isset($_POST['pagibig_number']) ? trim($_POST['pagibig_number']) : '';
            $tin_number = isset($_POST['tin_number']) ? trim($_POST['tin_number']) : '';
            $residence_cert_number = isset($_POST['residence_cert_number']) ? trim($_POST['residence_cert_number']) : '';
            $residence_cert_issued_at = isset($_POST['residence_cert_issued_at']) ? trim($_POST['residence_cert_issued_at']) : '';
            $residence_cert_issued_on = !empty($_POST['residence_cert_issued_on']) ? $_POST['residence_cert_issued_on'] : null;
            $nbi_clearance_number = isset($_POST['nbi_clearance_number']) ? trim($_POST['nbi_clearance_number']) : '';
            $nbi_issued_on = !empty($_POST['nbi_issued_on']) ? $_POST['nbi_issued_on'] : null;
            $passport_number = isset($_POST['passport_number']) ? trim($_POST['passport_number']) : '';
            $passport_issued_on = !empty($_POST['passport_issued_on']) ? $_POST['passport_issued_on'] : null;
            $passport_expiry_date = !empty($_POST['passport_expiry_date']) ? $_POST['passport_expiry_date'] : null;
            
            $stmt = $conn->prepare("INSERT INTO employee_bio_data (user_id, sss_number, gsis_number, philhealth_number, pagibig_number, tin_number, residence_cert_number, residence_cert_issued_at, residence_cert_issued_on, nbi_clearance_number, nbi_issued_on, passport_number, passport_issued_on, passport_expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE sss_number = ?, gsis_number = ?, philhealth_number = ?, pagibig_number = ?, tin_number = ?, residence_cert_number = ?, residence_cert_issued_at = ?, residence_cert_issued_on = ?, nbi_clearance_number = ?, nbi_issued_on = ?, passport_number = ?, passport_issued_on = ?, passport_expiry_date = ?");
            
            $stmt->bind_param("isssssssssssssssssssssssssss", $_SESSION['user_id'], $sss_number, $gsis_number, $philhealth_number, $pagibig_number, $tin_number, $residence_cert_number, $residence_cert_issued_at, $residence_cert_issued_on, $nbi_clearance_number, $nbi_issued_on, $passport_number, $passport_issued_on, $passport_expiry_date, $sss_number, $gsis_number, $philhealth_number, $pagibig_number, $tin_number, $residence_cert_number, $residence_cert_issued_at, $residence_cert_issued_on, $nbi_clearance_number, $nbi_issued_on, $passport_number, $passport_issued_on, $passport_expiry_date);
            $stmt->execute();
            $stmt->close();
            $success_message .= "Government IDs saved. ";
        } catch (Exception $e) {
            $error_message .= "Government IDs error: " . $e->getMessage() . " ";
        }
    }
    
    // 3. Save Education Records
    if ($section == 'education' || $section == 'all' || $section == '') {
        try {
            $stmt_del = $conn->prepare("DELETE FROM employee_education WHERE user_id = ?");
            $stmt_del->bind_param("i", $_SESSION['user_id']);
            $stmt_del->execute();
            $stmt_del->close();

            if (isset($_POST['education_level']) && is_array($_POST['education_level'])) {
                $stmt = $conn->prepare("INSERT INTO employee_education (user_id, education_level, school_name, degree_course, year_graduated, special_skills) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($_POST['education_level'] as $index => $level) {
                    if (!empty($level)) {
                        $school_name = isset($_POST['school_name'][$index]) ? trim($_POST['school_name'][$index]) : '';
                        $degree_course = isset($_POST['degree_course'][$index]) ? trim($_POST['degree_course'][$index]) : '';
                        $year_graduated = isset($_POST['year_graduated'][$index]) && !empty($_POST['year_graduated'][$index]) ? (int)$_POST['year_graduated'][$index] : null;
                        $special_skills = isset($_POST['special_skills'][$index]) ? trim($_POST['special_skills'][$index]) : '';
                        
                        // Corrected type definition from "issssi" to "isssis"
                        $stmt->bind_param("isssis", $_SESSION['user_id'], $level, $school_name, $degree_course, $year_graduated, $special_skills);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }
            $success_message .= "Education saved. ";
        } catch (Exception $e) {
            $error_message .= "Education error: " . $e->getMessage() . " ";
        }
    }
    
    // 4. Save Employment Records
    if ($section == 'employment' || $section == 'all' || $section == '') {
        try {
            $stmt_del = $conn->prepare("DELETE FROM employee_employment WHERE user_id = ?");
            $stmt_del->bind_param("i", $_SESSION['user_id']);
            $stmt_del->execute();
            $stmt_del->close();

            if (isset($_POST['company_name']) && is_array($_POST['company_name'])) {
                $stmt = $conn->prepare("INSERT INTO employee_employment (user_id, company_name, position, from_date, `to`, reason_for_leaving) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($_POST['company_name'] as $index => $company) {
                    if (!empty($company)) {
                        $position = isset($_POST['emp_position'][$index]) ? trim($_POST['emp_position'][$index]) : '';
                        $from_date = isset($_POST['from_date'][$index]) && !empty($_POST['from_date'][$index]) ? $_POST['from_date'][$index] : null;
                        $to_date = isset($_POST['to_date'][$index]) && !empty($_POST['to_date'][$index]) ? $_POST['to_date'][$index] : null;
                        $reason_for_leaving = isset($_POST['reason_for_leaving'][$index]) ? trim($_POST['reason_for_leaving'][$index]) : '';
                        $stmt->bind_param("isssss", $_SESSION['user_id'], $company, $position, $from_date, $to_date, $reason_for_leaving);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }
            $success_message .= "Employment saved. ";
        } catch (Exception $e) {
            $error_message .= "Employment error: " . $e->getMessage() . " ";
        }
    }
    
    // 5. Save References
    if ($section == 'references' || $section == 'all' || $section == '') {
        try {
            $stmt_del = $conn->prepare("DELETE FROM employee_references WHERE user_id = ?");
            $stmt_del->bind_param("i", $_SESSION['user_id']);
            $stmt_del->execute();
            $stmt_del->close();

            if (isset($_POST['reference_name']) && is_array($_POST['reference_name'])) {
                $stmt = $conn->prepare("INSERT INTO employee_references (user_id, reference_name, position, company, contact_number) VALUES (?, ?, ?, ?, ?)");
                foreach ($_POST['reference_name'] as $index => $name) {
                    if (!empty($name)) {
                        $ref_position = isset($_POST['ref_position'][$index]) ? trim($_POST['ref_position'][$index]) : '';
                        $ref_company = isset($_POST['ref_company'][$index]) ? trim($_POST['ref_company'][$index]) : '';
                        $contact_number = isset($_POST['contact_number'][$index]) ? trim($_POST['contact_number'][$index]) : '';
                        $stmt->bind_param("issss", $_SESSION['user_id'], $name, $ref_position, $ref_company, $contact_number);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }
            $success_message .= "References saved. ";
        } catch (Exception $e) {
            $error_message .= "References error: " . $e->getMessage() . " ";
        }
    }
    
    // 6. Save Emergency Contacts
    if ($section == 'emergency' || $section == 'all' || $section == '') {
        try {
            $stmt_del = $conn->prepare("DELETE FROM emergency_contacts WHERE user_id = ?");
            $stmt_del->bind_param("i", $_SESSION['user_id']);
            $stmt_del->execute();
            $stmt_del->close();

            if (isset($_POST['emergency_contact_name']) && is_array($_POST['emergency_contact_name'])) {
                $stmt = $conn->prepare("INSERT INTO emergency_contacts (user_id, contact_name, relationship, contact_number, address) VALUES (?, ?, ?, ?, ?)");
                foreach ($_POST['emergency_contact_name'] as $index => $contact_name) {
                    if (!empty($contact_name)) {
                        $relationship = isset($_POST['emergency_relationship'][$index]) ? trim($_POST['emergency_relationship'][$index]) : '';
                        $emergency_contact_number = isset($_POST['emergency_contact_number'][$index]) ? trim($_POST['emergency_contact_number'][$index]) : '';
                        $emergency_address = isset($_POST['emergency_address'][$index]) ? trim($_POST['emergency_address'][$index]) : '';
                        $stmt->bind_param("issss", $_SESSION['user_id'], $contact_name, $relationship, $emergency_contact_number, $emergency_address);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }
            $success_message .= "Emergency contacts saved. ";
        } catch (Exception $e) {
            $error_message .= "Emergency contacts error: " . $e->getMessage() . " ";
        }
    }
    
    $success_message = trim($success_message);
    $error_message = trim($error_message);
}

// Default Data Initialization
$personal_data = array(
    'position_desired' => '', 'application_date' => '', 'first_name' => '', 'middle_name' => '',
    'last_name' => '', 'gender' => '', 'city_address' => '', 'provincial_address' => '',
    'telephone' => '', 'cellphone' => '', 'email' => '', 'date_of_birth' => '',
    'birth_place' => '', 'civil_status' => '', 'citizenship' => '', 'height' => '',
    'weight' => '', 'religion' => '', 'spouse_name' => '', 'spouse_occupation' => '',
    'children_names' => '', 'children_birth_dates' => '', 'father_name' => '',
    'father_occupation' => '', 'mother_name' => '', 'mother_occupation' => '', 'languages' => ''
);

$bio_data = array(
    'sss_number' => '', 'gsis_number' => '', 'philhealth_number' => '', 'pagibig_number' => '',
    'tin_number' => '', 'residence_cert_number' => '', 'residence_cert_issued_at' => '',
    'residence_cert_issued_on' => '', 'nbi_clearance_number' => '', 'nbi_issued_on' => '',
    'passport_number' => '', 'passport_issued_on' => '', 'passport_expiry_date' => ''
);

$education_records = array();
$employment_records = array();
$reference_records = array();
$emergency_contacts = array();

// Fetch personal data
$stmt = $conn->prepare("SELECT * FROM employee_personal_data WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $personal_data = $result->fetch_assoc();
}
$stmt->close();

// Fetch bio data
$stmt = $conn->prepare("SELECT * FROM employee_bio_data WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $bio_data = $result->fetch_assoc();
}
$stmt->close();

// Fetch education records
$stmt = $conn->prepare("SELECT * FROM employee_education WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $education_records[] = $row;
}
$stmt->close();

// Fetch employment records
$stmt = $conn->prepare("SELECT * FROM employee_employment WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $employment_records[] = $row;
}
$stmt->close();

// Fetch reference records
$stmt = $conn->prepare("SELECT * FROM employee_references WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reference_records[] = $row;
}
$stmt->close();

// Fetch emergency contacts
$stmt = $conn->prepare("SELECT * FROM emergency_contacts WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $emergency_contacts[] = $row;
}
$stmt->close();

$conn->close();

$title = 'Employee Bio-Data';
$active_page = 'bio_data';

$content = '
    <div class="content-header">
        <h1>Employee Bio-Data</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Bio-Data</li>
            </ol>
        </nav>
    </div>';

if ($success_message) {
    $content .= '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($success_message) . '</div>';
}

if ($error_message) {
    $content .= '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($error_message) . '</div>';
}

$content .= '
    <form method="POST" action="">
        <input type="hidden" name="save_section" id="save_section" value="">
        
        <!-- Personal Data Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> Personal Data</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Current Position </label>
                            <input type="text" name="position_desired" class="form-control" value="' . htmlspecialchars($personal_data['position_desired']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Employment Date</label>
                            <input type="date" name="application_date" class="form-control" value="' . htmlspecialchars($personal_data['application_date']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" value="' . htmlspecialchars($personal_data['first_name']) . '" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="' . htmlspecialchars($personal_data['middle_name']) . '">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="' . htmlspecialchars($personal_data['last_name']) . '" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="">Select Gender</option>
                                <option value="Male" ' . ($personal_data['gender'] == 'Male' ? 'selected' : '') . '>Male</option>
                                <option value="Female" ' . ($personal_data['gender'] == 'Female' ? 'selected' : '') . '>Female</option>
                                <option value="Other" ' . ($personal_data['gender'] == 'Other' ? 'selected' : '') . '>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Civil Status</label>
                            <select name="civil_status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="Single" ' . ($personal_data['civil_status'] == 'Single' ? 'selected' : '') . '>Single</option>
                                <option value="Married" ' . ($personal_data['civil_status'] == 'Married' ? 'selected' : '') . '>Married</option>
                                <option value="Widowed" ' . ($personal_data['civil_status'] == 'Widowed' ? 'selected' : '') . '>Widowed</option>
                                <option value="Separated" ' . ($personal_data['civil_status'] == 'Separated' ? 'selected' : '') . '>Separated</option>
                                <option value="Divorced" ' . ($personal_data['civil_status'] == 'Divorced' ? 'selected' : '') . '>Divorced</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>City Address</label>
                    <textarea name="city_address" class="form-control" rows="2">' . htmlspecialchars($personal_data['city_address']) . '</textarea>
                </div>
                
                <div class="form-group">
                    <label>Provincial Address</label>
                    <textarea name="provincial_address" class="form-control" rows="2">' . htmlspecialchars($personal_data['provincial_address']) . '</textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Telephone</label>
                            <input type="text" name="telephone" class="form-control" value="' . htmlspecialchars($personal_data['telephone']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cellphone</label>
                            <input type="text" name="cellphone" class="form-control" value="' . htmlspecialchars($personal_data['cellphone']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="' . htmlspecialchars($personal_data['email']) . '">
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="' . htmlspecialchars($personal_data['date_of_birth']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Birth Place</label>
                            <input type="text" name="birth_place" class="form-control" value="' . htmlspecialchars($personal_data['birth_place']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Citizenship</label>
                            <input type="text" name="citizenship" class="form-control" value="' . htmlspecialchars($personal_data['citizenship']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Religion</label>
                            <input type="text" name="religion" class="form-control" value="' . htmlspecialchars($personal_data['religion']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="number" step="0.01" name="height" class="form-control" value="' . htmlspecialchars($personal_data['height']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" step="0.01" name="weight" class="form-control" value="' . htmlspecialchars($personal_data['weight']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Spouse Name</label>
                            <input type="text" name="spouse_name" class="form-control" value="' . htmlspecialchars($personal_data['spouse_name']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Spouse Occupation</label>
                            <input type="text" name="spouse_occupation" class="form-control" value="' . htmlspecialchars($personal_data['spouse_occupation']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Children Names</label>
                    <textarea name="children_names" class="form-control" rows="2" placeholder="Separate with commas">' . htmlspecialchars($personal_data['children_names']) . '</textarea>
                </div>
                
                <div class="form-group">
                    <label>Children Birth Dates</label>
                    <textarea name="children_birth_dates" class="form-control" rows="2" placeholder="Separate with commas (YYYY-MM-DD format)">' . htmlspecialchars($personal_data['children_birth_dates']) . '</textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Father Name</label>
                            <input type="text" name="father_name" class="form-control" value="' . htmlspecialchars($personal_data['father_name']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Father Occupation</label>
                            <input type="text" name="father_occupation" class="form-control" value="' . htmlspecialchars($personal_data['father_occupation']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mother Name</label>
                            <input type="text" name="mother_name" class="form-control" value="' . htmlspecialchars($personal_data['mother_name']) . '">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mother Occupation</label>
                            <input type="text" name="mother_occupation" class="form-control" value="' . htmlspecialchars($personal_data['mother_occupation']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Languages Spoken</label>
                    <textarea name="languages" class="form-control" rows="2" placeholder="Separate with commas">' . htmlspecialchars($personal_data['languages']) . '</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary" onclick="saveSection(\'personal\')"><i class="fas fa-save"></i> Save Personal Data</button>
            </div>
        </div>
        
        <!-- Emergency Contacts Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-phone-alt"></i> Emergency Contacts</h3>
            </div>
            <div class="card-body">
                <div id="emergency-contacts-container">
';

// Add existing emergency contact records
foreach ($emergency_contacts as $emergency) {
    $content .= '
                    <div class="emergency-contact-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Contact Name</label>
                                        <input type="text" name="emergency_contact_name[]" class="form-control" value="' . htmlspecialchars($emergency['contact_name']) . '">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Relationship</label>
                                        <input type="text" name="emergency_relationship[]" class="form-control" value="' . htmlspecialchars($emergency['relationship']) . '">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="text" name="emergency_contact_number[]" class="form-control" value="' . htmlspecialchars($emergency['contact_number']) . '">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="emergency_address[]" class="form-control" rows="2">' . htmlspecialchars($emergency['address']) . '</textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-emergency-contact">Remove</button>
                        </div>
                    </div>';
}

if (empty($emergency_contacts)) {
    $content .= '
                    <div class="emergency-contact-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Contact Name</label>
                                        <input type="text" name="emergency_contact_name[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Relationship</label>
                                        <input type="text" name="emergency_relationship[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="text" name="emergency_contact_number[]" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="emergency_address[]" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-emergency-contact">Remove</button>
                        </div>
                    </div>';
}

$content .= '
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="add-emergency-contact">
                    <i class="fas fa-plus"></i> Add Emergency Contact
                </button>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary" onclick="saveSection(\'emergency\')"><i class="fas fa-save"></i> Save Emergency Contacts</button>
            </div>
        </div>
        
        <!-- Educational Background Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Educational Background</h3>
            </div>
            <div class="card-body">
                <div id="education-container">
';

foreach ($education_records as $edu) {
    $content .= '
                    <div class="education-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Education Level</label>
                                        <select name="education_level[]" class="form-control">
                                            <option value="">Select Level</option>
                                            <option value="Elementary" ' . ($edu['education_level'] == 'Elementary' ? 'selected' : '') . '>Elementary</option>
                                            <option value="High School" ' . ($edu['education_level'] == 'High School' ? 'selected' : '') . '>High School</option>
                                            <option value="College" ' . ($edu['education_level'] == 'College' ? 'selected' : '') . '>College</option>
                                            <option value="Vocational" ' . ($edu['education_level'] == 'Vocational' ? 'selected' : '') . '>Vocational</option>
                                            <option value="Graduate Studies" ' . ($edu['education_level'] == 'Graduate Studies' ? 'selected' : '') . '>Graduate Studies</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>School Name</label>
                                        <input type="text" name="school_name[]" class="form-control" value="' . htmlspecialchars($edu['school_name']) . '">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Year Graduated</label>
                                        <input type="number" name="year_graduated[]" class="form-control" value="' . htmlspecialchars($edu['year_graduated']) . '" min="1900" max="2099">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Degree/Course</label>
                                        <input type="text" name="degree_course[]" class="form-control" value="' . htmlspecialchars($edu['degree_course']) . '">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Special Skills</label>
                                        <input type="text" name="special_skills[]" class="form-control" value="' . htmlspecialchars($edu['special_skills']) . '">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-education">Remove</button>
                        </div>
                    </div>';
}

if (empty($education_records)) {
    $content .= '
                    <div class="education-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Education Level</label>
                                        <select name="education_level[]" class="form-control">
                                            <option value="">Select Level</option>
                                            <option value="Elementary">Elementary</option>
                                            <option value="High School">High School</option>
                                            <option value="College">College</option>
                                            <option value="Vocational">Vocational</option>
                                            <option value="Graduate Studies">Graduate Studies</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>School Name</label>
                                        <input type="text" name="school_name[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Year Graduated</label>
                                        <input type="number" name="year_graduated[]" class="form-control" min="1900" max="2099">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Degree/Course</label>
                                        <input type="text" name="degree_course[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Special Skills</label>
                                        <input type="text" name="special_skills[]" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-education">Remove</button>
                        </div>
                    </div>';
}

$content .= '
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="add-education">
                    <i class="fas fa-plus"></i> Add Education
                </button>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary" onclick="saveSection(\'education\')"><i class="fas fa-save"></i> Save Education</button>
            </div>
        </div>
        
        <!-- Employment Record Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase"></i> Employment Record</h3>
            </div>
            <div class="card-body">
                <div id="employment-container">
';

foreach ($employment_records as $emp) {
    $content .= '
                    <div class="employment-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name</label>
                                        <input type="text" name="company_name[]" class="form-control" value="' . htmlspecialchars($emp['company_name']) . '">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Position</label>
                                        <input type="text" name="emp_position[]" class="form-control" value="' . htmlspecialchars($emp['position']) . '">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>From</label>
                                        <input type="date" name="from_date[]" class="form-control" value="' . htmlspecialchars($emp['from_date']) . '">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>To</label>
                                        <input type="date" name="to_date[]" class="form-control" value="' . htmlspecialchars($emp['to']) . '">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Reason for Leaving</label>
                                        <input type="text" name="reason_for_leaving[]" class="form-control" value="' . htmlspecialchars($emp['reason_for_leaving']) . '">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-employment">Remove</button>
                        </div>
                    </div>';
}

if (empty($employment_records)) {
    $content .= '
                    <div class="employment-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name</label>
                                        <input type="text" name="company_name[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Position</label>
                                        <input type="text" name="emp_position[]" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>From</label>
                                        <input type="date" name="from_date[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>To</label>
                                        <input type="date" name="to_date[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Reason for Leaving</label>
                                        <input type="text" name="reason_for_leaving[]" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-employment">Remove</button>
                        </div>
                    </div>';
}

$content .= '
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="add-employment">
                    <i class="fas fa-plus"></i> Add Employment
                </button>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary" onclick="saveSection(\'employment\')"><i class="fas fa-save"></i> Save Employment</button>
            </div>
        </div>
        
        <!-- Character References Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Character References</h3>
            </div>
            <div class="card-body">
                <div id="references-container">
';

foreach ($reference_records as $ref) {
    $content .= '
                    <div class="reference-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="reference_name[]" class="form-control" value="' . htmlspecialchars($ref['reference_name']) . '">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Position</label>
                                        <input type="text" name="ref_position[]" class="form-control" value="' . htmlspecialchars($ref['position']) . '">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Company</label>
                                        <input type="text" name="ref_company[]" class="form-control" value="' . htmlspecialchars($ref['company']) . '">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="text" name="contact_number[]" class="form-control" value="' . htmlspecialchars($ref['contact_number']) . '">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-reference">Remove</button>
                        </div>
                    </div>';
}

if (empty($reference_records)) {
    $content .= '
                    <div class="reference-item card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="reference_name[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Position</label>
                                        <input type="text" name="ref_position[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Company</label>
                                        <input type="text" name="ref_company[]" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="text" name="contact_number[]" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-reference">Remove</button>
                        </div>
                    </div>';
}

$content .= '
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="add-reference">
                    <i class="fas fa-plus"></i> Add Reference
                </button>
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary" onclick="saveSection(\'references\')"><i class="fas fa-save"></i> Save References</button>
            </div>
        </div>
        
        <!-- Government IDs Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card"></i> Government IDs</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>SSS Number</label>
                            <input type="text" name="sss_number" class="form-control" value="' . htmlspecialchars($bio_data['sss_number']) . '" placeholder="XX-XXXXXXX-X">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>GSIS Number</label>
                            <input type="text" name="gsis_number" class="form-control" value="' . htmlspecialchars($bio_data['gsis_number']) . '" placeholder="XXXXXXXXXXX">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>PhilHealth Number</label>
                            <input type="text" name="philhealth_number" class="form-control" value="' . htmlspecialchars($bio_data['philhealth_number']) . '" placeholder="XX-XXXXXXXXX-X">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>PAG-IBIG Number</label>
                            <input type="text" name="pagibig_number" class="form-control" value="' . htmlspecialchars($bio_data['pagibig_number']) . '" placeholder="XXXXXXXXXXX">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>TIN Number</label>
                            <input type="text" name="tin_number" class="form-control" value="' . htmlspecialchars($bio_data['tin_number']) . '" placeholder="XXX-XXX-XXX-XXX">
                        </div>
                    </div>
                </div>
                
                <hr>
                <h5>Additional Government IDs</h5>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Residence Certificate Number</label>
                            <input type="text" name="residence_cert_number" class="form-control" value="' . htmlspecialchars($bio_data['residence_cert_number']) . '">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Issued At</label>
                            <input type="text" name="residence_cert_issued_at" class="form-control" value="' . htmlspecialchars($bio_data['residence_cert_issued_at']) . '">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Issued On</label>
                            <input type="date" name="residence_cert_issued_on" class="form-control" value="' . htmlspecialchars($bio_data['residence_cert_issued_on']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>NBI Clearance Number</label>
                            <input type="text" name="nbi_clearance_number" class="form-control" value="' . htmlspecialchars($bio_data['nbi_clearance_number']) . '">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>NBI Issued On</label>
                            <input type="date" name="nbi_issued_on" class="form-control" value="' . htmlspecialchars($bio_data['nbi_issued_on']) . '">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Passport Number</label>
                            <input type="text" name="passport_number" class="form-control" value="' . htmlspecialchars($bio_data['passport_number']) . '">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Passport Issued On</label>
                            <input type="date" name="passport_issued_on" class="form-control" value="' . htmlspecialchars($bio_data['passport_issued_on']) . '">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Passport Expiry Date</label>
                            <input type="date" name="passport_expiry_date" class="form-control" value="' . htmlspecialchars($bio_data['passport_expiry_date']) . '">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <!-- Escaped quotes applied below -->
                <button type="button" class="btn btn-primary" onclick="saveSection(\'government\')"><i class="fas fa-save"></i> Save Government IDs</button>
            </div>
        </div>
        
        <div class="form-group mt-3 mb-4">
            <button type="button" class="btn btn-primary btn-lg" onclick="saveSection(\'all\')">
                <i class="fas fa-save"></i> Save All Bio-Data
            </button>
            <a href="dashboard.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </form>
    
    <script>
    // Save Section JS Handler
    function saveSection(sectionName) {
        document.getElementById("save_section").value = sectionName;
        document.forms[0].submit();
    }

    // Add Education
    document.getElementById("add-education").addEventListener("click", function() {
        var container = document.getElementById("education-container");
        var newItem = document.createElement("div");
        newItem.className = "education-item card mb-3";
        newItem.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Education Level</label>
                            <select name="education_level[]" class="form-control">
                                <option value="">Select Level</option>
                                <option value="Elementary">Elementary</option>
                                <option value="High School">High School</option>
                                <option value="College">College</option>
                                <option value="Vocational">Vocational</option>
                                <option value="Graduate Studies">Graduate Studies</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>School Name</label>
                            <input type="text" name="school_name[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Year Graduated</label>
                            <input type="number" name="year_graduated[]" class="form-control" min="1900" max="2099">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Degree/Course</label>
                            <input type="text" name="degree_course[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Special Skills</label>
                            <input type="text" name="special_skills[]" class="form-control">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-education">Remove</button>
            </div>
        `;
        container.appendChild(newItem);
    });
    
    // Remove Education
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("remove-education")) {
            e.target.closest(".education-item").remove();
        }
    });
    
    // Add Employment
    document.getElementById("add-employment").addEventListener("click", function() {
        var container = document.getElementById("employment-container");
        var newItem = document.createElement("div");
        newItem.className = "employment-item card mb-3";
        newItem.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" name="company_name[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="emp_position[]" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>From</label>
                            <input type="date" name="from_date[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>To</label>
                            <input type="date" name="to_date[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Reason for Leaving</label>
                            <input type="text" name="reason_for_leaving[]" class="form-control">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-employment">Remove</button>
            </div>
        `;
        container.appendChild(newItem);
    });
    
    // Remove Employment
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("remove-employment")) {
            e.target.closest(".employment-item").remove();
        }
    });
    
    // Add Reference
    document.getElementById("add-reference").addEventListener("click", function() {
        var container = document.getElementById("references-container");
        var newItem = document.createElement("div");
        newItem.className = "reference-item card mb-3";
        newItem.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="reference_name[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="ref_position[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Company</label>
                            <input type="text" name="ref_company[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number[]" class="form-control">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-reference">Remove</button>
            </div>
        `;
        container.appendChild(newItem);
    });
    
    // Remove Reference
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("remove-reference")) {
            e.target.closest(".reference-item").remove();
        }
    });
    
    // Add Emergency Contact
    document.getElementById("add-emergency-contact").addEventListener("click", function() {
        var container = document.getElementById("emergency-contacts-container");
        var newItem = document.createElement("div");
        newItem.className = "emergency-contact-item card mb-3";
        newItem.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Contact Name</label>
                            <input type="text" name="emergency_contact_name[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Relationship</label>
                            <input type="text" name="emergency_relationship[]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="emergency_contact_number[]" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="emergency_address[]" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-emergency-contact">Remove</button>
            </div>
        `;
        container.appendChild(newItem);
    });
    
    // Remove Emergency Contact
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("remove-emergency-contact")) {
            e.target.closest(".emergency-contact-item").remove();
        }
    });

    function saveSection(section) {
        document.getElementById("save_section").value = section;
        document.querySelector("form").submit();
    }
    </script>
';

require_once 'layout.php';
?>