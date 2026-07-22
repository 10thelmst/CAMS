<?php
require_once '../auth/auth_check.php';

// Check if user has employee role
if (!has_role('employee')) {
    header('Location: ../index.php?unauthorized=1');
    exit();
}

require_once '../config/database.php';
$conn = get_cams_connection();

$title = 'Create Client';
$active_page = 'create_client';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $is_ofw = isset($_POST['is_ofw_complainant']) ? 1 : 0;
    
    // Complainant Details
    $first_name     = trim($_POST['first_name']);
    $middle_name    = trim($_POST['middle_name']);
    $last_name      = trim($_POST['last_name']);
    $address        = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    
    // OFW Job Details (If complainant is OFW, take from complainant form; else take from OFW form)
    $jobsite      = $is_ofw ? trim($_POST['comp_jobsite']) : trim($_POST['ofw_jobsite']);
    $job_category = $is_ofw ? trim($_POST['comp_job_category']) : trim($_POST['ofw_job_category']);

    $conn->begin_transaction();
    try {
        // 1. Save / Insert Complainant into `people`
        $stmt = $conn->prepare("INSERT INTO people (first_name, middle_name, last_name, address, contact_number, is_ofw, jobsite, job_category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiss", $first_name, $middle_name, $last_name, $address, $contact_number, $is_ofw, $jobsite, $job_category);
        $stmt->execute();
        $complainant_id = $stmt->insert_id;
        $stmt->close();

        // 2. Create Central Logbook Entry
        $ticket_number = 'LOG-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $encoded_by = $_SESSION['user_id'] ?? 1; // Fallback to 1 if session user is missing

        $stmt = $conn->prepare("INSERT INTO logbooks (ticket_number, encoded_by_user_id, status) VALUES (?, ?, 'Pending')");
        $stmt->bind_param("si", $ticket_number, $encoded_by);
        $stmt->execute();
        $logbook_id = $stmt->insert_id;
        $stmt->close();

        // 3. Link Complainant in `logbook_people`
        if ($is_ofw) {
            $role = 'Complainant and OFW';
            $relationship = 'Self';
            $stmt = $conn->prepare("INSERT INTO logbook_people (logbook_id, person_id, role, relationship_to_ofw) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $logbook_id, $complainant_id, $role, $relationship);
            $stmt->execute();
            $stmt->close();
        } else {
            // Complainant mapping
            $role = 'Complainant';
            $relationship = trim($_POST['relationship_to_ofw']);
            $stmt = $conn->prepare("INSERT INTO logbook_people (logbook_id, person_id, role, relationship_to_ofw) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $logbook_id, $complainant_id, $role, $relationship);
            $stmt->execute();
            $stmt->close();

            // 4. Save separate OFW info if complainant is not the OFW
            $ofw_name = trim($_POST['ofw_name']);
            if (!empty($ofw_name)) {
                $is_ofw_flag = 1;
                // Split simple name into first/last for demo purposes
                $stmt = $conn->prepare("INSERT INTO people (first_name, last_name, is_ofw, jobsite, job_category) VALUES (?, '', ?, ?, ?)");
                $stmt->bind_param("siss", $ofw_name, $is_ofw_flag, $jobsite, $job_category);
                $stmt->execute();
                $ofw_id = $stmt->insert_id;
                $stmt->close();

                $ofw_role = 'OFW';
                $ofw_rel = 'Self';
                $stmt = $conn->prepare("INSERT INTO logbook_people (logbook_id, person_id, role, relationship_to_ofw) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $logbook_id, $ofw_id, $ofw_role, $ofw_rel);
                $stmt->execute();
                $stmt->close();
            }
        }

        $conn->commit();
        $success_message = "Logbook ticket successfully generated! Ticket #: <strong>{$ticket_number}</strong>";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Failed to create logbook entry: " . $e->getMessage();
    }
}

$content = '
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus"></i> Public Assistance Complaints Desk (PACD) - New Client</h3>
        </div>
        <div class="card-body">
            ' . ($success_message ? '<div class="alert alert-success">' . $success_message . '</div>' : '') . '
            ' . ($error_message ? '<div class="alert alert-danger">' . $error_message . '</div>' : '') . '

            <form method="POST">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_ofw_complainant" name="is_ofw_complainant" onchange="toggleOFWSection()">
                    <label class="form-check-label fw-bold" for="is_ofw_complainant">Complainant is the OFW</label>
                </div>

                <h5>Complainant Basic Information</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control">
                    </div>
                </div>

                <!-- Fields shown if Complainant IS the OFW -->
                <div id="complainant_ofw_fields" class="row mb-3" style="display: none;">
                    <div class="col-md-6">
                        <label>Jobsite / Country</label>
                        <input type="text" name="comp_jobsite" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Job Category</label>
                        <input type="text" name="comp_job_category" class="form-control" placeholder="e.g. Land-based, Sea-based, HSW">
                    </div>
                </div>

                <!-- Fields shown if Complainant IS NOT the OFW -->
                <div id="separate_ofw_section">
                    <hr>
                    <h5>OFW Details</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Name of OFW</label>
                            <input type="text" name="ofw_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Relationship to OFW</label>
                            <select name="relationship_to_ofw" class="form-control">
                                <option value="Spouse">Spouse</option>
                                <option value="Parent">Parent</option>
                                <option value="Child">Child</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Relative">Relative</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Jobsite / Country</label>
                            <input type="text" name="ofw_jobsite" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Job Category</label>
                            <input type="text" name="ofw_job_category" class="form-control" placeholder="e.g. Land-based, Sea-based">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Submit and Generate Ticket</button>
                <a href="manage_case.php" class="btn btn-outline-secondary"><i class="fas fa-folder-open"></i> Go to Case Management</a>
            </form>
        </div>
    </div>

    <script>
    function toggleOFWSection() {
        var isOFW = document.getElementById("is_ofw_complainant").checked;
        document.getElementById("complainant_ofw_fields").style.display = isOFW ? "flex" : "none";
        document.getElementById("separate_ofw_section").style.display = isOFW ? "none" : "block";
    }
    </script>
';

require_once 'layout.php';
?>