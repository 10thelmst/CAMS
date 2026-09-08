<?php
// C:\xampp\htdocs\cams\Employee\create_client.php
require_once __DIR__ . '/../auth/auth_check.php'; // Session & Auth Guard
require_once __DIR__ . '/../config/database.php'; // Database Connection ($pdo)

$message = '';
$message_type = '';
$title = 'Create Client';
$active_page = 'create_client';
ob_start();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_client_case') {
    try {
        $pdo->beginTransaction();

        $client_id = !empty($_POST['existing_client_id']) ? (int)$_POST['existing_client_id'] : null;
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');
        $full_name = trim(implode(' ', array_filter([$first_name, $middle_name, $last_name, $suffix], fn($value) => $value !== '')));

        // 1. Save or Update Client Information
        if (!$client_id) {
            $stmt = $pdo->prepare("
                INSERT INTO clients (first_name, middle_name, last_name, suffix, contact_no, email, sex, dob, is_ofw, address1, region_code, province_code, city_code, barangay_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $first_name,
                $middle_name,
                $last_name,
                $suffix,
                trim($_POST['contact_no']),
                trim($_POST['email']),
                $_POST['sex'] ?? null,
                !empty($_POST['dob']) ? $_POST['dob'] : null,
                isset($_POST['is_ofw']) ? 1 : 0,
                trim($_POST['address1']),
                $_POST['region_code'] ?? '05',
                $_POST['province_code'] ?? null,
                !empty($_POST['city_code']) ? $_POST['city_code'] : null,
                !empty($_POST['barangay_code']) ? $_POST['barangay_code'] : null
            ]);
            $client_id = $pdo->lastInsertId();
        }

        // 2. Save OFW Information
        $is_ofw = isset($_POST['is_ofw']) ? 1 : 0;
        $ofw_first_name = trim($_POST['ofw_first_name'] ?? '');
        $ofw_middle_name = trim($_POST['ofw_middle_name'] ?? '');
        $ofw_last_name = trim($_POST['ofw_last_name'] ?? '');
        $ofw_suffix = trim($_POST['ofw_suffix'] ?? '');
        $ofw_full_name = trim(implode(' ', array_filter([$ofw_first_name, $ofw_middle_name, $ofw_last_name, $ofw_suffix], fn($value) => $value !== '')));
        $ofw_name = $is_ofw ? $full_name : $ofw_full_name;
        $relationship = $is_ofw ? 'Self' : trim($_POST['relationship']);

        $stmt = $pdo->prepare("
            INSERT INTO ofw_information (client_id, ofw_first_name, ofw_middle_name, ofw_last_name, ofw_suffix, ofw_name, country, employment_type, relationship)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $client_id,
            $ofw_first_name,
            $ofw_middle_name,
            $ofw_last_name,
            $ofw_suffix,
            $ofw_name,
            trim($_POST['country']),
            $_POST['employment_type'],
            $relationship
        ]);

        // 3. Generate Ticket Number (e.g., PACD-2026-000123)
        $year = date('Y');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM concerns WHERE YEAR(created_at) = ?");
        $stmt->execute([$year]);
        $count = $stmt->fetchColumn() + 1;
        $ticket_number = sprintf("PACD-%s-%06d", $year, $count);

        // 4. Save Initial Concern / Case Record
        $stmt = $pdo->prepare("
            INSERT INTO concerns (ticket_number, client_id, contact_type, subject, category, description, status, current_program, created_by)
            VALUES (?, ?, ?, ?, ?, ?, 'Open', ?, ?)
        ");
        $stmt->execute([
            $ticket_number,
            $client_id,
            $_POST['contact_type'],
            trim($_POST['subject']),
            $_POST['category'],
            trim($_POST['description']),
            $_POST['program_in_charge'],
            $_SESSION['user_id']
        ]);
        $concern_id = $pdo->lastInsertId();

        // 5. Initial Action Taken (if provided)
        if (!empty($_POST['initial_action'])) {
            $stmt = $pdo->prepare("INSERT INTO action_history (concern_id, action_taken, performed_by) VALUES (?, ?, ?)");
            $stmt->execute([$concern_id, trim($_POST['initial_action']), $_SESSION['user_id']]);
        }

        // 6. Log Initial Status History
        $stmt = $pdo->prepare("INSERT INTO status_history (concern_id, status, remarks, changed_by) VALUES (?, 'Open', 'Initial case intake', ?)");
        $stmt->execute([$concern_id, $_SESSION['user_id']]);

        $pdo->commit();
        $message = "Case successfully filed! Ticket Number: <strong>{$ticket_number}</strong>";
        $message_type = "success";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Failed to save record: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>OFW CAMS | Client Intake & New Case</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      background: #dfe8f1;
      font-family: Arial, Helvetica, sans-serif;
      color: #333;
    }
    .crm-shell { width: 100%; min-height: 100vh; background: #dfe8f1; }
    .topbar {
      height: 52px; background: linear-gradient(#f6f6f6, #e1e1e1); border-bottom: 1px solid #b6c1d1;
      display: flex; align-items: center; justify-content: space-between; padding: 0 12px 0 10px; font-size: 12px;
    }
    .oracle-brand { display: flex; align-items: center; font-weight: 700; color: #1f2f5c; letter-spacing: 0.02em; font-size: 13px; }
    .oracle-mark { background: #e51b22; color: #fff; font-size: 20px; font-weight: 700; padding: 4px 7px 3px; margin-right: 8px; line-height: 1; border-radius: 3px; }
    .crm-word { font-weight: 700; color: #2d3b69; margin-left: 2px; }
    .top-links { display: flex; align-items: center; gap: 14px; font-size: 12px; color: #485a78; }
    .top-links span { white-space: nowrap; }
    .layout { display: flex; min-height: calc(100vh - 52px); }
    .sidebar { width: 220px; background: #dfeaf6; border-right: 1px solid #b9c7d8; padding: 0; }
    .nav-section { padding: 8px 8px 0; }
    .nav-item { display: flex; align-items: center; gap: 8px; padding: 8px 10px; margin: 3px 0; border-radius: 4px; color: #1f2b40; font-size: 12px; font-weight: 600; }
    .nav-item i { width: 16px; text-align: center; color: #3d5271; }
    .nav-item.active { background: #c8d8ee; border: 1px solid #9fb8d9; font-weight: 700; }
    .nav-item.small { padding-left: 26px; font-weight: 500; }
    .main { flex: 1; background: #edf2f8; padding: 0; }
    .main-strip { background: linear-gradient(#edf2f7, #e6edf6); border-bottom: 1px solid #c7d3e3; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .breadcrumb-row { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #3f536f; font-weight: 600; }
    .page-title { display: flex; align-items: center; gap: 8px; font-size: 18px; font-weight: 700; color: #1d2d52; margin: 0; }
    .action-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 10px; padding: 0 14px; }
    .btn-mini { border: 1px solid #a9b8d0; background: linear-gradient(#f4f8ff, #dfeaf8); color: #224065; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: 600; line-height: 1.2; cursor: pointer; }
    .btn-mini.primary { background: linear-gradient(#edf6ff, #d0e1f7); }
    .panel-wrap { margin: 12px 14px 0; background: #f3f7fb; border: 1px solid #c5d3e3; border-radius: 3px; }
    .panel-title { background: linear-gradient(#edf6ff, #dfeefa); border-bottom: 1px solid #c4d3e8; padding: 8px 10px; font-size: 13px; font-weight: 700; color: #3a4d69; }
    .panel-body { background: #fff; padding: 10px 12px 8px; }
    .crm-form-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px 18px; }
    .crm-form-grid.two-col { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .crm-form-group { margin-bottom: 0.35rem; }
    .crm-form-group label { display: block; margin-bottom: 5px; color: #41596c; font-size: 12px; font-weight: 700; line-height: 1.2; }
    .crm-form-group .text-danger { color: #d62642 !important; }
    .form-control.crm-form-control { display: block; width: 100%; min-height: 38px; border-radius: 4px; border: 1px solid #c7d4e4; background-color: #fff; color: #1e2d3d; padding: 0.55rem 0.7rem; font-size: 13px; box-shadow: none; transition: border-color 0.15s ease, box-shadow 0.15s ease; }
    .form-control.crm-form-control:focus { border-color: #77a9d6; box-shadow: 0 0 0 0.16rem rgba(77, 137, 196, 0.12); outline: none; }
    .crm-form-control[disabled], .crm-form-control[readonly] { background: #f1f5f9; color: #4c5d6f; }
    select.crm-form-control { background-image: linear-gradient(45deg, transparent 50%, #64748b 50%), linear-gradient(135deg, #64748b 50%, transparent 50%); background-position: calc(100% - 16px) calc(50% - 2px), calc(100% - 10px) calc(50% - 2px); background-size: 5px 5px, 5px 5px; background-repeat: no-repeat; appearance: none; -webkit-appearance: none; padding-right: 1.8rem; }
    .crm-divider { border: 0; border-top: 1px solid #dfeaf5; margin: 14px 0 12px; }
    .mini-note { font-size: 12px; color: #64748b; margin: 0 0 10px; }
    .crm-check { display: flex; align-items: center; gap: 8px; margin: 0 0 10px; font-size: 13px; color: #223a5b; font-weight: 700; }
    .crm-check input { width: 16px; height: 16px; margin: 0; }
    .crm-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 8px; }
    .crm-table th { background: #dfeaf6; color: #2a3d59; font-weight: 700; border-bottom: 1px solid #bfd0ea; padding: 7px 8px; text-align: left; }
    .crm-table td { border-bottom: 1px solid #ecf0f6; padding: 7px 8px; background: #fff; color: #2c3a4d; }
    .crm-table tr:nth-child(even) td { background: #fafcff; }
    .crm-badge { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #dff3e8; color: #1f6b45; font-size: 11px; font-weight: 700; letter-spacing: 0.02em; }
    .detail-panel { background: #fff; border: 1px solid #d8e4f0; border-radius: 0; overflow: hidden; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0 18px; }
    .detail-column { width: 100%; }
    .detail-row { display: flex; align-items: center; min-height: 30px; border-bottom: 1px solid #e5edf7; padding: 3px 0; }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { width: 150px; font-size: 12px; color: #4b5f7e; padding-right: 10px; font-weight: 600; text-align: right; }
    .detail-value { flex: 1; min-height: 22px; border-bottom: 1px solid #cdd8eb; font-size: 12px; color: #222; padding: 0 4px 2px 0; }
    .editable-form-field {
      flex: 1;
      min-height: 30px;
      border: none;
      border-radius: 0;
      background: transparent;
      color: #1e2d3d;
      font-size: 12px;
      padding: 0.28rem 0.25rem 0.2rem;
      width: 100%;
      box-shadow: none;
      appearance: none;
      -webkit-appearance: none;
    }
    .editable-form-field:focus {
      outline: none;
      background: #f3f8ff;
      border: none;
      box-shadow: none;
    }
    .search-box { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
    .search-box .crm-form-control { flex: 1; }
    .crm-btn { border: 1px solid #b9c6d7; background: linear-gradient(#f7d76d, #edba3a); color: #3d2d00; border-radius: 4px; font-size: 12px; font-weight: 700; padding: 9px 12px; cursor: pointer; }
    .crm-btn.primary { background: linear-gradient(#d7ebff, #bfe0ff); border-color: #a8c3eb; color: #21476b; }
    .crm-btn.secondary { background: #fff; color: #445b7c; }
    .crm-footer { background: #ebf2fa; border-top: 1px solid #c5d7ee; padding: 10px 12px; display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
    @media (max-width: 980px) { .layout { display: block; } .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #b9c7d8; } .crm-form-grid, .crm-form-grid.two-col { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="crm-shell">
    <div class="topbar">
      <div class="oracle-brand"><div class="oracle-mark">O</div><div>RACLE <span class="crm-word">CRM On Demand</span></div></div>
      <div class="top-links"><span>Training and Support</span><span>|</span><span>Admin</span><span>|</span><span>My Setup</span><span>|</span><span>Deleted Items</span><span>|</span><span>Help</span><span>|</span><span>Sign Out</span></div>
    </div>

    <div class="layout">
      <aside class="sidebar">
        <div class="nav-section">
          <div class="nav-item active"><i class="fas fa-envelope"></i> Message Center</div>
          <div class="nav-item"><i class="fas fa-envelope-open-text"></i> 0 New Messages</div>
          <div class="nav-item"><i class="fas fa-search"></i> Search</div>
        </div>

        <div class="nav-section">
          <div class="nav-item"><i class="fas fa-user-friends"></i> Contacts</div>
          <div class="nav-item small">All</div>
          <div class="nav-item small">Last Name</div>
          <div class="nav-item small">First Name</div>
          <div class="nav-item small">Email</div>
          <div class="nav-item small">Cellular Phone #</div>
          <div class="nav-item small">Contact City</div>
          <div class="nav-item" style="margin-top:10px;"><i class="fas fa-plus"></i> Create</div>
        </div>

        <div class="nav-section">
          <div class="nav-item"><i class="fas fa-user-circle"></i> Account</div>
          <div class="nav-item"><i class="fas fa-calendar-check"></i> Appointment</div>
          <div class="nav-item"><i class="fas fa-address-book"></i> Contact</div>
          <div class="nav-item"><i class="fas fa-dollar-sign"></i> Expense</div>
          <div class="nav-item"><i class="fas fa-file-alt"></i> Lead</div>
          <div class="nav-item"><i class="fas fa-briefcase"></i> Opportunity</div>
          <div class="nav-item"><i class="fas fa-handshake"></i> Service Request</div>
          <div class="nav-item"><i class="fas fa-cube"></i> Solution</div>
          <div class="nav-item"><i class="fas fa-check-square"></i> Task</div>
        </div>
      </aside>

      <main class="main">
        <div class="main-strip">
          <div class="breadcrumb-row">
            <span class="page-title"><i class="fas fa-user-plus"></i> Client Intake &amp; New Case</span>
            <span>|</span>
            <span>Employee / Case Filing</span>
          </div>
        </div>

        <div class="action-bar">
          <button class="btn-mini primary" type="button">New Case</button>
          <button class="btn-mini" type="button">Review</button>
          <button class="btn-mini" type="button">Draft</button>
          <button class="btn-mini" type="button">Quick Save</button>
        </div>

        <?php if ($message): ?>
          <div class="panel-wrap" style="margin-top:12px;">
            <div class="panel-body" style="padding: 12px 14px;">
              <div class="alert alert-<?= $message_type ?> mb-0" role="alert">
                <i class="icon fas <?= $message_type === 'success' ? 'fa-check' : 'fa-ban' ?> mr-2"></i>
                <?= $message ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <div class="panel-wrap" style="margin-top:12px;">
          <div class="panel-title">Step 1: Check Existing Client Record</div>
          <div class="panel-body">
            <p class="mini-note">Search by full name or contact number to prevent duplicate client registrations.</p>
            <div class="search-box">
              <input type="text" id="search_term" class="form-control crm-form-control" placeholder="Type Full Name or Contact Number (e.g. Juan Dela Cruz or 0917...)" autocomplete="off">
              <button class="crm-btn" type="button" id="btn_search"><i class="fas fa-search mr-1"></i> Search</button>
            </div>

            <div id="search_results" class="mt-3" style="display: none;">
              <h6 style="font-size:12px; font-weight:700; color:#3c5372; margin-bottom:8px;">Matching Records Found</h6>
              <div class="table-responsive">
                <table class="crm-table">
                  <thead><tr><th>Full Name</th><th>Contact No.</th><th>OFW Name</th><th>Country</th><th>Active Cases</th><th>Action</th></tr></thead>
                  <tbody id="search_results_body"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <form method="POST" id="client_form">
          <input type="hidden" name="action" value="save_client_case">
          <input type="hidden" name="existing_client_id" id="existing_client_id" value="">

          <div class="panel-wrap">
            <div class="panel-title d-flex justify-content-between align-items-center">
              <span>Step 2: Client Personal Details</span>
              <div>
                <span id="selected_client_badge" class="crm-badge" style="display:none;">Existing Client Selected</span>
                <button type="button" id="btn_reset_client" class="crm-btn secondary" style="display:none; margin-left:8px;"><i class="fas fa-times mr-1"></i> Change Client</button>
              </div>
            </div>
            <div class="panel-body">
              <div class="detail-panel">
                <div class="two-col" style="padding: 8px 10px 0;">
                  <div class="detail-column">
                    <div class="detail-row"><div class="detail-label">Last Name</div><input class="editable-form-field" type="text" name="last_name" id="last_name" data-field="last_name" placeholder="Last Name" required></div>
                    <div class="detail-row"><div class="detail-label">First Name</div><input class="editable-form-field" type="text" name="first_name" id="first_name" data-field="first_name" placeholder="First Name" required></div>
                    <div class="detail-row"><div class="detail-label">Middle Name</div><input class="editable-form-field" type="text" name="middle_name" id="middle_name" data-field="middle_name" placeholder="Middle Name"></div>
                    <div class="detail-row"><div class="detail-label">Suffix</div><select class="editable-form-field" name="suffix" id="suffix" data-field="suffix"><option value="">--</option><option value="Jr.">Jr.</option><option value="Jra.">Jra.</option><option value="Sr.">Sr.</option><option value="II">II</option><option value="III">III</option><option value="IV">IV</option></select></div>
                    <div class="detail-row"><div class="detail-label">Contact Number</div><input class="editable-form-field" type="text" name="contact_no" id="contact_no" data-field="contact_no" placeholder="09XXXXXXXXX" required></div>
                    <div class="detail-row"><div class="detail-label">Gender</div><select class="editable-form-field" name="sex" id="sex" data-field="sex"><option value="">--</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                  </div>
                  <div class="detail-column">
                    <div class="detail-row"><div class="detail-label">Email Address</div><input class="editable-form-field" type="email" name="email" id="email" data-field="email" placeholder="client@example.com"></div>
                    <div class="detail-row"><div class="detail-label">Date of Birth</div><input class="editable-form-field" type="date" name="dob" id="dob" data-field="dob"></div>
                    <div class="detail-row"><div class="detail-label">Address 1</div><input class="editable-form-field" type="text" name="address1" id="address1" data-field="address1" placeholder="House No., Street, Subdivision" required></div>
                    <div class="detail-row"><div class="detail-label">Region</div><select class="editable-form-field" id="region_display" data-field="region_name" disabled><option value="05">Region V (Bicol)</option></select></div>
                    <div class="detail-row"><div class="detail-label">Province</div><select class="editable-form-field" name="province_code" id="province_code" data-field="province_name" required disabled><option value="">-- Select Province --</option></select></div>
                    <div class="detail-row"><div class="detail-label">Town / City</div><select class="editable-form-field" name="city_code" id="city_code" data-field="city_name" disabled><option value="">-- Select Town/City --</option></select></div>
                    <div class="detail-row"><div class="detail-label">Barangay</div><select class="editable-form-field" name="barangay_code" id="barangay_code" data-field="barangay_name" disabled><option value="">-- Select Barangay --</option></select></div>
                  </div>
                </div>
              </div>

              <input type="hidden" name="region_code" value="05">
            </div>
          </div>

          <div class="panel-wrap">
            <div class="panel-title">Step 3: OFW Information</div>
            <div class="panel-body">
              <label class="crm-check"><input type="checkbox" id="is_ofw" name="is_ofw" value="1"><span>Client is the OFW himself/herself</span></label>
              <div class="detail-panel">
                <div class="two-col" style="padding: 8px 10px 0;">
                  <div class="detail-column">
                    <div class="detail-row"><div class="detail-label">OFW Last Name</div><input class="editable-form-field" type="text" name="ofw_last_name" id="ofw_last_name" data-field="ofw_last_name" placeholder="OFW Last Name"></div>
                    <div class="detail-row"><div class="detail-label">OFW First Name</div><input class="editable-form-field" type="text" name="ofw_first_name" id="ofw_first_name" data-field="ofw_first_name" placeholder="OFW First Name"></div>
                    <div class="detail-row"><div class="detail-label">OFW Middle Name</div><input class="editable-form-field" type="text" name="ofw_middle_name" id="ofw_middle_name" data-field="ofw_middle_name" placeholder="OFW Middle Name"></div>
                    <div class="detail-row"><div class="detail-label">Suffix</div><select class="editable-form-field" name="ofw_suffix" id="ofw_suffix" data-field="ofw_suffix"><option value="">--</option><option value="Jr.">Jr.</option><option value="Jra.">Jra.</option><option value="Sr.">Sr.</option><option value="II">II</option><option value="III">III</option><option value="IV">IV</option></select></div>
                  </div>
                  <div class="detail-column">
                    <div class="detail-row"><div class="detail-label">Relationship</div><select class="editable-form-field" name="relationship" id="relationship" data-field="relationship"><option value="">-- Select Relationship --</option><option value="Spouse">Spouse</option><option value="Child">Child</option><option value="Parent">Parent</option><option value="Sibling">Sibling</option><option value="Relative">Relative</option><option value="Representative">Representative</option></select></div>
                    <div class="detail-row"><div class="detail-label">Country</div><input class="editable-form-field" type="text" name="country" id="country" data-field="country" placeholder="e.g. Saudi Arabia, UAE, Singapore"></div>
                    <div class="detail-row"><div class="detail-label">Employment Type</div><select class="editable-form-field" name="employment_type" id="employment_type" data-field="employment_type"><option value="">-- Select Type --</option><option value="Land-based">Land-based</option><option value="Sea-based">Sea-based</option></select></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="panel-wrap">
            <div class="panel-title">Step 4: Concern / Case Details</div>
            <div class="panel-body">
              <div class="crm-form-grid">
                <div class="crm-form-group"><label>Transaction Type <span class="text-danger">*</span></label><select name="contact_type" class="form-control crm-form-control" required><option value="Walk-in">Walk-in</option><option value="Phone">Phone</option><option value="Email">Email</option><option value="Online">Online</option></select></div>
                <div class="crm-form-group"><label>Concern Subject <span class="text-danger">*</span></label><input type="text" name="subject" class="form-control crm-form-control" placeholder="e.g. Unpaid Salary, Repatriation Request" required></div>
                <div class="crm-form-group"><label>Category <span class="text-danger">*</span></label><select name="category" class="form-control crm-form-control" required><option value="">-- Select Category --</option><option value="Salary Claim">Salary Claim / Legal Assistance</option><option value="Repatriation">Repatriation / Medical Evacuation</option><option value="Welfare">Welfare Assistance</option><option value="Scholarship">Scholarship / Education</option><option value="Reintegration">Livelihood / Reintegration</option><option value="Others">Others</option></select></div>
                <div class="crm-form-group"><label>Program-in-Charge <span class="text-danger">*</span></label><select name="program_in_charge" class="form-control crm-form-control" required><option value="PACD">Public Assistance Desk (PACD)</option><option value="Welfare Division">Welfare Division</option><option value="Legal Unit">Legal Unit</option><option value="Reintegration Unit">Reintegration Unit</option></select></div>
              </div>
              <div class="crm-form-group" style="margin-top:14px;"><label>Detailed Description of Concern <span class="text-danger">*</span></label><textarea name="description" class="form-control crm-form-control" rows="4" placeholder="Provide full details of the complaint or request..." required></textarea></div>
              <div class="crm-form-group" style="margin-top:14px;"><label>Initial Action Taken (Optional)</label><textarea name="initial_action" class="form-control crm-form-control" rows="2" placeholder="e.g. Conducted initial interview, endorsed to legal officer..."></textarea></div>
            </div>
            <div class="crm-footer">
              <button type="reset" class="crm-btn secondary" id="btn_reset_form"><i class="fas fa-undo mr-1"></i> Reset Form</button>
              <button type="submit" class="crm-btn primary"><i class="fas fa-save mr-1"></i> Submit Case Record</button>
            </div>
          </div>
        </form>
      </main>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const provinceSelect = document.getElementById('province_code');
      const citySelect = document.getElementById('city_code');
      const barangaySelect = document.getElementById('barangay_code');
      const DEFAULT_REGION_CODE = '05';

      function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
      }

      function loadProvincesForRegionV() {
        fetch(`../auth/ajax_address_json.php?action=get_provinces&region_code=${DEFAULT_REGION_CODE}`)
          .then(res => res.json())
          .then(data => {
            provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
            data.forEach(p => { provinceSelect.innerHTML += `<option value="${p.code}">${p.name}</option>`; });
            provinceSelect.disabled = false;
          })
          .catch(err => console.error('Error fetching provinces:', err));
      }
      loadProvincesForRegionV();

      provinceSelect.addEventListener('change', function () {
        citySelect.innerHTML = '<option value="">-- Select Town/City (Optional) --</option>';
        barangaySelect.innerHTML = '<option value="">-- Select Barangay (Optional) --</option>';
        citySelect.disabled = !this.value;
        barangaySelect.disabled = true;

        if (this.value) {
          fetch(`../auth/ajax_address_json.php?action=get_cities&province_code=${this.value}`)
            .then(res => res.json())
            .then(data => { data.forEach(c => { citySelect.innerHTML += `<option value="${c.code}">${c.name}</option>`; }); });
        }
      });

      citySelect.addEventListener('change', function () {
        barangaySelect.innerHTML = '<option value="">-- Select Barangay (Optional) --</option>';
        barangaySelect.disabled = !this.value;

        if (this.value) {
          fetch(`../auth/ajax_address_json.php?action=get_barangays&city_code=${this.value}`)
            .then(res => res.json())
            .then(data => { data.forEach(b => { barangaySelect.innerHTML += `<option value="${b.code}">${b.name}</option>`; }); });
        }
      });

      const isOfwCheckbox = document.getElementById('is_ofw');
      const relationshipWrapper = document.getElementById('relationship_wrapper');
      const ofwLastNameInput = document.getElementById('ofw_last_name');
      const ofwFirstNameInput = document.getElementById('ofw_first_name');
      const relationshipSelect = document.getElementById('relationship');

      function syncFieldValues() {
        const regionDisplay = document.getElementById('region_display');
        if (regionDisplay) {
          regionDisplay.value = '05';
        }

        const fields = [
          ['last_name', 'last_name'],
          ['first_name', 'first_name'],
          ['middle_name', 'middle_name'],
          ['suffix', 'suffix'],
          ['contact_no', 'contact_no'],
          ['sex', 'sex'],
          ['email', 'email'],
          ['dob', 'dob'],
          ['address1', 'address1'],
          ['province_code', 'province_name'],
          ['city_code', 'city_name'],
          ['barangay_code', 'barangay_name'],
          ['ofw_last_name', 'ofw_last_name'],
          ['ofw_first_name', 'ofw_first_name'],
          ['ofw_middle_name', 'ofw_middle_name'],
          ['ofw_suffix', 'ofw_suffix'],
          ['relationship', 'relationship'],
          ['country', 'country'],
          ['employment_type', 'employment_type']
        ];

        fields.forEach(([sourceKey, targetKey]) => {
          const source = document.getElementById(sourceKey);
          const target = document.querySelector(`[data-field="${targetKey}"]`);
          if (!source) return;
          if (target && target === source) return;

          let value = source.value || '-';
          if (sourceKey === 'province_code' || sourceKey === 'city_code' || sourceKey === 'barangay_code') {
            value = source.options[source.selectedIndex]?.text || '-';
          }
          if (sourceKey === 'sex' && !source.value) value = '-';

          if (target) {
            if (target.tagName === 'INPUT' || target.tagName === 'SELECT' || target.tagName === 'TEXTAREA') {
              target.value = value === '-' ? '' : value;
            } else {
              target.textContent = value === '' ? '-' : value;
            }
          }
        });
      }

      function toggleOfwFields() {
        if (isOfwCheckbox.checked) {
          relationshipWrapper.style.display = 'none';
          ofwLastNameInput.removeAttribute('required');
          ofwFirstNameInput.removeAttribute('required');
          relationshipSelect.removeAttribute('required');
        } else {
          relationshipWrapper.style.display = 'block';
          ofwLastNameInput.setAttribute('required', 'required');
          ofwFirstNameInput.setAttribute('required', 'required');
          relationshipSelect.setAttribute('required', 'required');
        }
      }

      ['input', 'change'].forEach(eventName => {
        document.addEventListener(eventName, function (e) {
          if (e.target && e.target.id) {
            syncFieldValues();
          }
        });
      });

      isOfwCheckbox.addEventListener('change', toggleOfwFields);
      toggleOfwFields();
      syncFieldValues();

      function splitFullName(fullName) {
        const parts = String(fullName || '').trim().split(/\s+/).filter(Boolean);
        const suffixes = ['Jr.', 'Jra.', 'Sr.', 'II', 'III', 'IV'];
        let suffix = '';

        if (suffixes.includes(parts[parts.length - 1])) {
          suffix = parts[parts.length - 1];
          parts.pop();
        }

        if (parts.length === 0) return { first_name: '', middle_name: '', last_name: '', suffix: '' };
        if (parts.length === 1) return { first_name: parts[0], middle_name: '', last_name: '', suffix };
        if (parts.length === 2) return { first_name: parts[0], middle_name: '', last_name: parts[1], suffix };
        return { first_name: parts[0], middle_name: parts.slice(1, -1).join(' '), last_name: parts[parts.length - 1], suffix };
      }

      const btnSearch = document.getElementById('btn_search');
      const searchTerm = document.getElementById('search_term');
      const searchResults = document.getElementById('search_results');
      const searchResultsBody = document.getElementById('search_results_body');

      function performSearch() {
        const term = searchTerm.value.trim();
        if (term.length < 3) { alert('Please enter at least 3 characters to search.'); return; }
        searchResultsBody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</td></tr>';
        searchResults.style.display = 'block';

        fetch(`../auth/ajax_search_client.php?term=${encodeURIComponent(term)}`)
          .then(res => res.json())
          .then(data => {
            searchResultsBody.innerHTML = '';
            if (data.ok && data.clients.length > 0) {
              data.clients.forEach(c => {
                const fullname = escapeHtml(c.fullname);
                const contact = escapeHtml(c.contact_no || '');
                const email = escapeHtml(c.email || '');
                const sex = escapeHtml(c.sex || '');
                const ofw = escapeHtml(c.ofw_name || 'N/A');
                const country = escapeHtml(c.country || 'N/A');
                const activeConcerns = parseInt(c.active_concerns || 0, 10);

                searchResultsBody.innerHTML += `
                  <tr>
                    <td><strong>${fullname}</strong></td>
                    <td>${contact || 'N/A'}</td>
                    <td>${ofw}</td>
                    <td>${country}</td>
                    <td><span class="crm-badge">${activeConcerns} Open</span></td>
                    <td><button type="button" class="btn btn-xs btn-primary select-client-btn" data-id="${c.id}" data-name="${fullname}" data-contact="${contact}" data-email="${email}" data-sex="${sex}"><i class="fas fa-check-circle"></i> Use Client</button></td>
                  </tr>
                `;
              });
            } else {
              searchResultsBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No matching client records found. Proceed with new registration.</td></tr>';
            }
          })
          .catch(err => {
            console.error('Search error:', err);
            searchResultsBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Error searching records.</td></tr>';
          });
      }

      btnSearch.addEventListener('click', performSearch);
      searchTerm.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          performSearch();
        }
      });

      document.addEventListener('click', function (e) {
        const btn = e.target.closest('.select-client-btn');
        if (btn) {
          document.getElementById('existing_client_id').value = btn.dataset.id;
          const splitName = splitFullName(btn.dataset.name);
          document.getElementById('first_name').value = splitName.first_name;
          document.getElementById('middle_name').value = splitName.middle_name;
          document.getElementById('last_name').value = splitName.last_name;
          document.getElementById('suffix').value = splitName.suffix;
          document.getElementById('first_name').readOnly = true;
          document.getElementById('middle_name').readOnly = true;
          document.getElementById('last_name').readOnly = true;
          document.getElementById('suffix').disabled = true;

          const contactInput = document.getElementById('contact_no');
          contactInput.value = btn.dataset.contact;
          contactInput.readOnly = true;

          if (btn.dataset.email) document.getElementById('email').value = btn.dataset.email;
          if (btn.dataset.sex) document.getElementById('sex').value = btn.dataset.sex;

          document.getElementById('selected_client_badge').style.display = 'inline-block';
          document.getElementById('btn_reset_client').style.display = 'inline-block';
          alert('Existing client selected! Fields locked to prevent accidental alteration.');
        }
      });

      function unlockClientFields() {
        document.getElementById('existing_client_id').value = '';
        document.getElementById('first_name').readOnly = false;
        document.getElementById('middle_name').readOnly = false;
        document.getElementById('last_name').readOnly = false;
        document.getElementById('suffix').disabled = false;
        document.getElementById('contact_no').readOnly = false;
        document.getElementById('selected_client_badge').style.display = 'none';
        document.getElementById('btn_reset_client').style.display = 'none';
      }

      document.getElementById('btn_reset_client').addEventListener('click', unlockClientFields);
      document.getElementById('btn_reset_form').addEventListener('click', unlockClientFields);
    });
  </script>
</body>
</html>
