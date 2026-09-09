<?php
// C:\xampp\htdocs\cams\Employee\create_client.php
require_once __DIR__ . '/../auth/auth_check.php'; // Session & Auth Guard
require_once __DIR__ . '/../config/database.php'; // Database Connection ($pdo)

$pdo = get_cams_pdo();

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

  <!-- AdminLTE 3 CSS Dependencies -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <style>
    body {
      background: #f3f6fb;
      font-family: 'Source Sans Pro', sans-serif;
    }

    .content-wrapper {
      background: #f3f6fb;
      min-height: 100vh;
    }

    .content-header {
      padding-bottom: 0.5rem;
    }

    .content-header h1 {
      font-size: 2rem;
      font-weight: 700;
      color: #1f2937;
      letter-spacing: -0.02em;
    }

    .card {
      border: 1px solid #e3e8ef;
      border-radius: 18px;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
      overflow: hidden;
      margin-bottom: 1.4rem;
    }

    .card-header {
      background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
      border-bottom: 1px solid #e5edf8;
      padding: 0.9rem 1.25rem;
    }

    .card-title {
      color: #1e293b;
      font-size: 1.12rem;
      font-weight: 700;
      letter-spacing: 0.01em;
    }

    .card-body {
      padding: 1.25rem 1.25rem 1rem;
      background: white;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      font-size: 0.8rem;
      font-weight: 700;
      color: #374151;
      margin-bottom: 0.45rem;
      letter-spacing: 0.01em;
    }

    .form-control {
      display: block;
      width: 100%;
      min-height: 44px;
      border-radius: 12px;
      border: 1px solid #d7dfeb;
      background-color: #fff;
      color: #1f2937;
      box-shadow: none;
      transition: all 0.2s ease-in-out;
      padding: 0.65rem 0.8rem;
    }

    .form-control:focus {
      border-color: #7ab7db;
      box-shadow: 0 0 0 0.18rem rgba(76, 139, 191, 0.16);
    }

    .form-control::placeholder {
      color: #94a3b8;
      opacity: 1;
    }

    select.form-control {
      background-image: linear-gradient(45deg, transparent 50%, #64748b 50%), linear-gradient(135deg, #64748b 50%, transparent 50%);
      background-position: calc(100% - 18px) calc(50% - 2px), calc(100% - 12px) calc(50% - 2px);
      background-size: 6px 6px, 6px 6px;
      background-repeat: no-repeat;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      padding-right: 2rem;
    }

    .input-group .form-control {
      border-radius: 12px 0 0 12px;
    }

    .input-group-append .btn {
      border-radius: 0 12px 12px 0;
      min-height: 44px;
      font-weight: 700;
    }

    .btn {
      border-radius: 12px;
      font-weight: 700;
      padding: 0.6rem 1rem;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
    }

    .btn-primary {
      background: linear-gradient(135deg, #1d73c7 0%, #2a5bd7 100%);
      border-color: transparent;
      box-shadow: 0 8px 18px rgba(29, 115, 199, 0.2);
    }

    .btn-warning {
      background: linear-gradient(135deg, #f6c453 0%, #f0b429 100%);
      border-color: transparent;
      color: #382d04;
      box-shadow: 0 8px 18px rgba(240, 180, 41, 0.2);
    }

    .btn-default {
      background: #fff;
      border-color: #d9e0eb;
      color: #475569;
    }

    .table thead th {
      background: #edf3ff;
      color: #1f2d3d;
      font-size: 0.83rem;
      letter-spacing: 0.01em;
      text-transform: uppercase;
      border-bottom: 1px solid #dfe7f3;
    }

    .table td {
      vertical-align: middle;
      padding: 0.8rem 0.75rem;
    }

    .alert {
      border-radius: 14px;
      border: none;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
      font-size: 0.98rem;
    }

    .breadcrumb {
      background: transparent;
      padding: 0.5rem 0 0;
    }

    .badge {
      border-radius: 999px;
      padding: 0.55rem 0.8rem;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .text-muted {
      color: #64748b !important;
    }

    .icheck-primary label {
      font-weight: 600;
      color: #374151;
    }

    .form-group.clearfix {
      padding: 0.4rem 0 0.2rem;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="content-wrapper ml-0">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><i class="fas fa-user-plus mr-2 text-primary"></i>Client Intake & Case Filing</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">New Client</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">

        <?php if ($message): ?>
          <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <i class="icon fas <?= $message_type === 'success' ? 'fa-check' : 'fa-ban' ?>"></i>
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endif; ?>

        <!-- STEP 1: DUPLICATE SEARCH CARD -->
        <div class="card card-outline card-warning">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search mr-2"></i>Step 1: Check Existing Client Record</h3>
          </div>
          <div class="card-body">
            <p class="text-muted">Search by Full Name or Contact Number to prevent duplicate client registrations.</p>
            <div class="row">
              <div class="col-md-8">
                <div class="input-group">
                  <input type="text" id="search_term" class="form-control" placeholder="Type Full Name or Contact Number (e.g. Juan Dela Cruz or 0917...)" autocomplete="off">
                  <div class="input-group-append">
                    <button class="btn btn-warning" type="button" id="btn_search"><i class="fas fa-search"></i> Search</button>
                  </div>
                </div>
              </div>
            </div>

            <div id="search_results" class="mt-3" style="display: none;">
              <h5 class="text-secondary"><i class="fas fa-list mr-1"></i> Matching Records Found:</h5>
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                  <thead class="thead-light">
                    <tr>
                      <th>Full Name</th>
                      <th>Contact No.</th>
                      <th>OFW Name</th>
                      <th>Country</th>
                      <th>Active Cases</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="search_results_body"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- MAIN INTAKE FORM -->
        <form method="POST" id="client_form">
          <input type="hidden" name="action" value="save_client_case">
          <input type="hidden" name="existing_client_id" id="existing_client_id" value="">

          <!-- STEP 2: CLIENT INFORMATION -->
          <div class="card card-outline card-primary">
            <div class="card-header d-flex align-items-center">
              <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Step 2: Client Personal Details</h3>
              <div class="ml-auto">
                <span id="selected_client_badge" class="badge badge-success p-2 mr-2" style="display: none;">Existing Client Selected</span>
                <button type="button" id="btn_reset_client" class="btn btn-xs btn-outline-danger" style="display: none;"><i class="fas fa-times"></i> Change Client</button>
              </div>
            </div>
            <div class="card-body">
              <div class="row no-gutters align-items-end">
                <div class="col-md-3 pr-2 form-group mb-2">
                  <label>Last Name <span class="text-danger">*</span></label>
                  <input type="text" name="last_name" id="last_name" class="form-control" placeholder="e.g. Santos" required>
                </div>
                <div class="col-md-4 pr-2 form-group mb-2">
                  <label>First Name <span class="text-danger">*</span></label>
                  <input type="text" name="first_name" id="first_name" class="form-control" placeholder="e.g. Juan" required>
                </div>
                <div class="col-md-3 pr-2 form-group mb-2">
                  <label>Middle Name</label>
                  <input type="text" name="middle_name" id="middle_name" class="form-control" placeholder="e.g. Dela Cruz">
                </div>
                <div class="col-md-2 form-group mb-2">
                  <label>Suffix</label>
                  <select name="suffix" id="suffix" class="form-control">
                    <option value="">--</option>
                    <option value="Jr.">Jr.</option>
                    <option value="Jra.">Jra.</option>
                    <option value="Sr.">Sr.</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                    <option value="IV">IV</option>
                  </select>
                </div>
              </div>

              <div class="row no-gutters align-items-end">
                <div class="col-md-4 pr-2 form-group mb-2">
                  <label>Contact Number <span class="text-danger">*</span></label>
                  <input type="text" name="contact_no" id="contact_no" class="form-control" placeholder="09XXXXXXXXX" required>
                </div>
                <div class="col-md-1 pr-2 form-group mb-2">
                  <label>Gender</label>
                  <select name="sex" id="sex" class="form-control">
                    <option value="">--</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
                <div class="col-md-4 pr-2 form-group mb-2">
                  <label>Email Address</label>
                  <input type="email" name="email" id="email" class="form-control" placeholder="client@example.com">
                </div>
                <div class="col-md-3 form-group mb-2">
                  <label>Date of Birth</label>
                  <input type="date" name="dob" id="dob" class="form-control">
                </div>
              </div>

              <hr>

              <!-- PSGC 3NF Address Selection -->
              <h5 class="text-primary"><i class="fas fa-map-marker-alt mr-1"></i> Address (3NF Normalized)</h5>
              <div class="row">
                <div class="col-md-12 form-group">
                  <label>Address 1 (House No., Street, Subdivision) <span class="text-danger">*</span></label>
                  <input type="text" name="address1" id="address1" class="form-control" placeholder="e.g. Unit 4B, Mabini St." required>
                </div>
              </div>
              <div class="row">
                <!-- Region Dropdown (Locked to Region V) -->
                <div class="col-md-3 form-group">
                  <label>Region <span class="text-danger">*</span></label>
                  <input type="hidden" name="region_code" value="05">
                  <select id="region_code_display" class="form-control bg-light" disabled>
                    <option value="05" selected>Region V (Bicol Region)</option>
                  </select>
                </div>

                <!-- Province Dropdown (Required) -->
                <div class="col-md-3 form-group">
                  <label>Province <span class="text-danger">*</span></label>
                  <select name="province_code" id="province_code" class="form-control" required disabled>
                    <option value="">-- Select Province --</option>
                  </select>
                </div>

                <!-- Town / City Dropdown (Optional) -->
                <div class="col-md-3 form-group">
                  <label>Town / City <small class="text-muted">(Optional)</small></label>
                  <select name="city_code" id="city_code" class="form-control" disabled>
                    <option value="">-- Select Town/City --</option>
                  </select>
                </div>

                <!-- Barangay Dropdown (Optional) -->
                <div class="col-md-3 form-group">
                  <label>Barangay <small class="text-muted">(Optional)</small></label>
                  <select name="barangay_code" id="barangay_code" class="form-control" disabled>
                    <option value="">-- Select Barangay --</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- STEP 3: OFW INFORMATION -->
          <div class="card card-outline card-info">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-plane-departure mr-2"></i>Step 3: OFW Information</h3>
            </div>
            <div class="card-body">
              <div class="form-group clearfix mb-3">
                <div class="icheck-primary d-inline">
                  <input type="checkbox" id="is_ofw" name="is_ofw" value="1">
                  <label for="is_ofw" class="font-weight-bold text-dark">
                    Client is the OFW himself/herself
                  </label>
                </div>
              </div>

              <div class="row no-gutters align-items-end" id="ofw_name_wrapper">
                <div class="col-md-3 pr-2 form-group mb-2">
                  <label>OFW Last Name <span class="text-danger">*</span></label>
                  <input type="text" name="ofw_last_name" id="ofw_last_name" class="form-control" placeholder="e.g. Santos">
                </div>
                <div class="col-md-4 pr-2 form-group mb-2">
                  <label>OFW First Name <span class="text-danger">*</span></label>
                  <input type="text" name="ofw_first_name" id="ofw_first_name" class="form-control" placeholder="e.g. Juan">
                </div>
                <div class="col-md-3 pr-2 form-group mb-2">
                  <label>OFW Middle Name</label>
                  <input type="text" name="ofw_middle_name" id="ofw_middle_name" class="form-control" placeholder="e.g. Dela Cruz">
                </div>
                <div class="col-md-2 form-group mb-2">
                  <label>Suffix</label>
                  <select name="ofw_suffix" id="ofw_suffix" class="form-control">
                    <option value="">--</option>
                    <option value="Jr.">Jr.</option>
                    <option value="Jra.">Jra.</option>
                    <option value="Sr.">Sr.</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                    <option value="IV">IV</option>
                  </select>
                </div>
              </div>

              <div class="row no-gutters align-items-end">
                <div class="col-md-6 pr-2 form-group mb-2" id="relationship_wrapper">
                  <label>Client's Relationship to OFW <span class="text-danger">*</span></label>
                  <select name="relationship" id="relationship" class="form-control">
                    <option value="">-- Select Relationship --</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Child">Child</option>
                    <option value="Parent">Parent</option>
                    <option value="Sibling">Sibling</option>
                    <option value="Relative">Relative</option>
                    <option value="Representative">Representative</option>
                  </select>
                </div>
              </div>

              <div class="row no-gutters align-items-end">
                <div class="col-md-6 pr-2 form-group mb-2">
                  <label>Country of Deployment <span class="text-danger">*</span></label>
                  <input type="text" name="country" id="country" class="form-control" placeholder="e.g. Saudi Arabia, UAE, Singapore" required>
                </div>
                <div class="col-md-6 form-group mb-2">
                  <label>Employment Type <span class="text-danger">*</span></label>
                  <select name="employment_type" id="employment_type" class="form-control" required>
                    <option value="">-- Select Type --</option>
                    <option value="Land-based">Land-based</option>
                    <option value="Sea-based">Sea-based</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- STEP 4: CONCERN DETAILS -->
          <div class="card card-outline card-danger">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-file-invoice mr-2"></i>Step 4: Concern / Case Details</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4 form-group">
                  <label>Transaction Type <span class="text-danger">*</span></label>
                  <select name="contact_type" class="form-control" required>
                    <option value="Walk-in">Walk-in</option>
                    <option value="Phone">Phone</option>
                    <option value="Email">Email</option>
                    <option value="Online">Online</option>
                  </select>
                </div>
                <div class="col-md-4 form-group">
                  <label>Concern Subject <span class="text-danger">*</span></label>
                  <input type="text" name="subject" class="form-control" placeholder="e.g. Unpaid Salary, Repatriation Request" required>
                </div>
                <div class="col-md-4 form-group">
                  <label>Category <span class="text-danger">*</span></label>
                  <select name="category" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <option value="Salary Claim">Salary Claim / Legal Assistance</option>
                    <option value="Repatriation">Repatriation / Medical Evacuation</option>
                    <option value="Welfare">Welfare Assistance</option>
                    <option value="Scholarship">Scholarship / Education</option>
                    <option value="Reintegration">Livelihood / Reintegration</option>
                    <option value="Others">Others</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 form-group offset-md-8">
                  <label>Program-in-Charge <span class="text-danger">*</span></label>
                  <select name="program_in_charge" class="form-control" required>
                    <option value="PACD">Public Assistance Desk (PACD)</option>
                    <option value="Welfare Division">Welfare Division</option>
                    <option value="Legal Unit">Legal Unit</option>
                    <option value="Reintegration Unit">Reintegration Unit</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>Detailed Description of Concern <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="4" placeholder="Provide full details of the complaint or request..." required></textarea>
              </div>

              <div class="form-group">
                <label>Initial Action Taken (Optional)</label>
                <textarea name="initial_action" class="form-control" rows="2" placeholder="e.g. Conducted initial interview, endorsed to legal officer..."></textarea>
              </div>
            </div>
            <div class="card-footer bg-white text-right">
              <button type="reset" class="btn btn-default mr-2" id="btn_reset_form"><i class="fas fa-undo"></i> Reset Form</button>
              <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save mr-1"></i> Submit Case Record</button>
            </div>
          </div>
        </form>

      </div>
    </section>
  </div>
</div>

<!-- JS Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const provinceSelect = document.getElementById('province_code');
    const citySelect = document.getElementById('city_code');
    const barangaySelect = document.getElementById('barangay_code');

    const DEFAULT_REGION_CODE = '05';

    // Helper: Escapes HTML strings to prevent attribute/syntax breaking
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // 1. Auto-load Region V Provinces on Page Load
    function loadProvincesForRegionV() {
        fetch(`../auth/ajax_address_json.php?action=get_provinces&region_code=${DEFAULT_REGION_CODE}`)
            .then(res => res.json())
            .then(data => {
                provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
                data.forEach(p => {
                    provinceSelect.innerHTML += `<option value="${p.code}">${p.name}</option>`;
                });
                provinceSelect.disabled = false;
            })
            .catch(err => console.error('Error fetching provinces:', err));
    }
    loadProvincesForRegionV();

    // 2. Province -> City
    provinceSelect.addEventListener('change', function () {
        citySelect.innerHTML = '<option value="">-- Select Town/City (Optional) --</option>';
        barangaySelect.innerHTML = '<option value="">-- Select Barangay (Optional) --</option>';
        
        citySelect.disabled = !this.value;
        barangaySelect.disabled = true;

        if (this.value) {
            fetch(`../auth/ajax_address_json.php?action=get_cities&province_code=${this.value}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(c => {
                        citySelect.innerHTML += `<option value="${c.code}">${c.name}</option>`;
                    });
                });
        }
    });

    // 3. City -> Barangay
    citySelect.addEventListener('change', function () {
        barangaySelect.innerHTML = '<option value="">-- Select Barangay (Optional) --</option>';
        barangaySelect.disabled = !this.value;

        if (this.value) {
            fetch(`../auth/ajax_address_json.php?action=get_barangays&city_code=${this.value}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(b => {
                        barangaySelect.innerHTML += `<option value="${b.code}">${b.name}</option>`;
                    });
                });
        }
    });

    // 4. OFW Toggle Logic
    const isOfwCheckbox = document.getElementById('is_ofw');
    const ofwNameWrapper = document.getElementById('ofw_name_wrapper');
    const relationshipWrapper = document.getElementById('relationship_wrapper');
    const ofwLastNameInput = document.getElementById('ofw_last_name');
    const ofwFirstNameInput = document.getElementById('ofw_first_name');
    const relationshipSelect = document.getElementById('relationship');

    function toggleOfwFields() {
        if (isOfwCheckbox.checked) {
            if (ofwNameWrapper) ofwNameWrapper.style.display = 'none';
            if (relationshipWrapper) relationshipWrapper.style.display = 'none';
            if (ofwLastNameInput) ofwLastNameInput.removeAttribute('required');
            if (ofwFirstNameInput) ofwFirstNameInput.removeAttribute('required');
            if (relationshipSelect) relationshipSelect.removeAttribute('required');
        } else {
            if (ofwNameWrapper) ofwNameWrapper.style.display = 'block';
            if (relationshipWrapper) relationshipWrapper.style.display = 'block';
            if (ofwLastNameInput) ofwLastNameInput.setAttribute('required', 'required');
            if (ofwFirstNameInput) ofwFirstNameInput.setAttribute('required', 'required');
            if (relationshipSelect) relationshipSelect.setAttribute('required', 'required');
        }
    }
    isOfwCheckbox.addEventListener('change', toggleOfwFields);
    toggleOfwFields();

    function splitFullName(fullName) {
        const parts = String(fullName || '').trim().split(/\s+/).filter(Boolean);
        const suffixes = ['Jr.', 'Jra.', 'Sr.', 'II', 'III', 'IV'];
        let suffix = '';
        let lastIndex = parts.length - 1;

        if (suffixes.includes(parts[lastIndex])) {
            suffix = parts[lastIndex];
            parts.pop();
        }

        if (parts.length === 0) return { first_name: '', middle_name: '', last_name: '', suffix: '' };
        if (parts.length === 1) return { first_name: parts[0], middle_name: '', last_name: '', suffix };
        if (parts.length === 2) return { first_name: parts[0], middle_name: '', last_name: parts[1], suffix };
        return {
            first_name: parts[0],
            middle_name: parts.slice(1, -1).join(' '),
            last_name: parts[parts.length - 1],
            suffix
        };
    }

    // 5. Duplicate Search Engine
    const btnSearch = document.getElementById('btn_search');
    const searchTerm = document.getElementById('search_term');
    const searchResults = document.getElementById('search_results');
    const searchResultsBody = document.getElementById('search_results_body');

    function performSearch() {
        const term = searchTerm.value.trim();
        if (term.length < 3) {
            alert('Please enter at least 3 characters to search.');
            return;
        }

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
                                <td><span class="badge badge-${activeConcerns > 0 ? 'warning' : 'success'}">${activeConcerns} Open</span></td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-primary select-client-btn" 
                                        data-id="${c.id}" 
                                        data-name="${fullname}" 
                                        data-contact="${contact}" 
                                        data-email="${email}" 
                                        data-sex="${sex}">
                                        <i class="fas fa-check-circle"></i> Use Client
                                    </button>
                                </td>
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
    searchTerm.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });

    // 6. Select Existing Client Logic
    document.addEventListener('click', function(e) {
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

    // 7. Reset / Unlock Client Selection
    function unlockClientFields() {
        document.getElementById('existing_client_id').value = '';

        document.getElementById('first_name').readOnly = false;
        document.getElementById('middle_name').readOnly = false;
        document.getElementById('last_name').readOnly = false;
        document.getElementById('suffix').disabled = false;
        
        const contactInput = document.getElementById('contact_no');
        contactInput.readOnly = false;

        document.getElementById('selected_client_badge').style.display = 'none';
        document.getElementById('btn_reset_client').style.display = 'none';
    }

    document.getElementById('btn_reset_client').addEventListener('click', unlockClientFields);
    document.getElementById('btn_reset_form').addEventListener('click', unlockClientFields);
});
</script>
<?php
$content = ob_get_clean();
require_once 'layout.php';
?>