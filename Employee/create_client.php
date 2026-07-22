<?php
// C:\xampp\htdocs\cams\Employee\create_client.php
require_once __DIR__ . '/../auth/auth_check.php'; // Include auth check & DB connection

$message = '';
$message_type = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_client_case') {
    try {
        $pdo->beginTransaction();

        $client_id = !empty($_POST['existing_client_id']) ? (int)$_POST['existing_client_id'] : null;

        // 1. Save or Update Client Information
        if (!$client_id) {
            $stmt = $pdo->prepare("
                INSERT INTO clients (fullname, contact_no, email, sex, dob, is_ofw, address1, region_code, province_code, city_code, barangay_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                trim($_POST['fullname']),
                trim($_POST['contact_no']),
                trim($_POST['email']),
                $_POST['sex'] ?? null,
                !empty($_POST['dob']) ? $_POST['dob'] : null,
                isset($_POST['is_ofw']) ? 1 : 0,
                trim($_POST['address1']),
                $_POST['region_code'] ?? null,
                $_POST['province_code'] ?? null,
                $_POST['city_code'] ?? null,
                $_POST['barangay_code'] ?? null
            ]);
            $client_id = $pdo->lastInsertId();
        }

        // 2. Save OFW Information
        $is_ofw = isset($_POST['is_ofw']) ? 1 : 0;
        $ofw_name = $is_ofw ? trim($_POST['fullname']) : trim($_POST['ofw_name']);
        $relationship = $is_ofw ? 'Self' : trim($_POST['relationship']);

        $stmt = $pdo->prepare("
            INSERT INTO ofw_information (client_id, ofw_name, country, employment_type, relationship)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $client_id,
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
              <span id="selected_client_badge" class="badge badge-success ml-auto p-2" style="display: none;">Existing Client Selected</span>
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
                  <label>Client Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Last Name, First Name, Middle Name" required>
                </div>
                <div class="col-md-4 form-group">
                  <label>Contact Number <span class="text-danger">*</span></label>
                  <input type="text" name="contact_no" id="contact_no" class="form-control" placeholder="09XXXXXXXXX" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 form-group">
                  <label>Email Address</label>
                  <input type="email" name="email" id="email" class="form-control" placeholder="client@example.com">
                </div>
                <div class="col-md-4 form-group">
                  <label>Sex</label>
                  <select name="sex" id="sex" class="form-control">
                    <option value="">-- Select --</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="col-md-4 form-group">
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
                <div class="col-md-3 form-group">
                  <label>Region <span class="text-danger">*</span></label>
                  <select name="region_code" id="region_code" class="form-control" required>
                    <option value="">-- Select Region --</option>
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label>Province <span class="text-danger">*</span></label>
                  <select name="province_code" id="province_code" class="form-control" required disabled>
                    <option value="">-- Select Province --</option>
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label>Town / City <span class="text-danger">*</span></label>
                  <select name="city_code" id="city_code" class="form-control"  disabled>
                    <option value="">-- Select Town/City --</option>
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label>Barangay <span class="text-danger">*</span></label>
                  <select name="barangay_code" id="barangay_code" class="form-control"  disabled>
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

              <div class="row">
                <div class="col-md-6 form-group" id="ofw_name_wrapper">
                  <label>OFW Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="ofw_name" id="ofw_name" class="form-control" placeholder="Last Name, First Name, Middle Name">
                </div>
                <div class="col-md-6 form-group" id="relationship_wrapper">
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

              <div class="row">
                <div class="col-md-6 form-group">
                  <label>Country of Deployment <span class="text-danger">*</span></label>
                  <input type="text" name="country" id="country" class="form-control" placeholder="e.g. Saudi Arabia, UAE, Singapore" required>
                </div>
                <div class="col-md-6 form-group">
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
                <div class="col-md-6 form-group">
                  <label>Concern Subject <span class="text-danger">*</span></label>
                  <input type="text" name="subject" class="form-control" placeholder="e.g. Unpaid Salary, Repatriation Request" required>
                </div>
                <div class="col-md-3 form-group">
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
                <div class="col-md-3 form-group">
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
              <button type="reset" class="btn btn-default mr-2"><i class="fas fa-undo"></i> Reset Form</button>
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
    const regionSelect = document.getElementById('region_code');
    const provinceSelect = document.getElementById('province_code');
    const citySelect = document.getElementById('city_code');
    const barangaySelect = document.getElementById('barangay_code');

    // Region V (Bicol Region) PSGC Code
    const DEFAULT_REGION_CODE = '050000000';

    // 1. Fetch Regions & Set Default
    fetch('../auth/ajax_address_json.php?action=get_regions')
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            regionSelect.innerHTML = '<option value="">-- Select Region --</option>';
            data.forEach(r => {
                const selected = (r.code === DEFAULT_REGION_CODE) ? 'selected' : '';
                regionSelect.innerHTML += `<option value="${r.code}" ${selected}>${r.name}</option>`;
            });

            // Trigger change event automatically if Region V is selected
            if (regionSelect.value) {
                regionSelect.dispatchEvent(new Event('change'));
            }
        });

    // 2. Region -> Province
    regionSelect.addEventListener('change', function () {
        provinceSelect.innerHTML = '<option value="">-- Select Province --</option>';
        citySelect.innerHTML = '<option value="">-- Select Town/City (Optional) --</option>';
        barangaySelect.innerHTML = '<option value="">-- Select Barangay (Optional) --</option>';
        
        provinceSelect.disabled = !this.value;
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        if (this.value) {
            fetch(`../auth/ajax_address_json.php?action=get_provinces&region_code=${this.value}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(p => {
                        provinceSelect.innerHTML += `<option value="${p.code}">${p.name}</option>`;
                    });
                });
        }
    });

    // 3. Province -> City
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

    // 4. City -> Barangay
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
});
    // -------------------------------------------------------------
    // 2. OFW Toggle Logic
    // -------------------------------------------------------------
    const isOfwCheckbox = document.getElementById('is_ofw');
    const ofwNameWrapper = document.getElementById('ofw_name_wrapper');
    const relationshipWrapper = document.getElementById('relationship_wrapper');
    const ofwNameInput = document.getElementById('ofw_name');
    const relationshipSelect = document.getElementById('relationship');

    function toggleOfwFields() {
        if (isOfwCheckbox.checked) {
            ofwNameWrapper.style.display = 'none';
            relationshipWrapper.style.display = 'none';
            ofwNameInput.removeAttribute('required');
            relationshipSelect.removeAttribute('required');
        } else {
            ofwNameWrapper.style.display = 'block';
            relationshipWrapper.style.display = 'block';
            ofwNameInput.setAttribute('required', 'required');
            relationshipSelect.setAttribute('required', 'required');
        }
    }
    isOfwCheckbox.addEventListener('change', toggleOfwFields);
    toggleOfwFields();

    // -------------------------------------------------------------
    // 3. Duplicate Search Engine
    // -------------------------------------------------------------
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

        fetch(`../auth/ajax_search_client.php?term=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(data => {
                searchResultsBody.innerHTML = '';
                if (data.ok && data.clients.length > 0) {
                    data.clients.forEach(c => {
                        searchResultsBody.innerHTML += `
                            <tr>
                                <td><strong>${c.fullname}</strong></td>
                                <td>${c.contact_no || 'N/A'}</td>
                                <td>${c.ofw_name || 'N/A'}</td>
                                <td>${c.country || 'N/A'}</td>
                                <td><span class="badge badge-${c.active_concerns > 0 ? 'warning' : 'success'}">${c.active_concerns} Open</span></td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-primary select-client-btn" 
                                        data-id="${c.id}" 
                                        data-name="${c.fullname}" 
                                        data-contact="${c.contact_no}" 
                                        data-email="${c.email || ''}" 
                                        data-sex="${c.sex || ''}">
                                        <i class="fas fa-check-circle"></i> Use Client
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    searchResults.style.display = 'block';
                } else {
                    searchResultsBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No matching client records found. Proceed with new registration.</td></tr>';
                    searchResults.style.display = 'block';
                }
            });
    }

    btnSearch.addEventListener('click', performSearch);
    searchTerm.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });

    // Select existing client from search table
    document.addEventListener('click', function(e) {
        if (e.target && e.target.closest('.select-client-btn')) {
            const btn = e.target.closest('.select-client-btn');
            document.getElementById('existing_client_id').value = btn.dataset.id;
            document.getElementById('fullname').value = btn.dataset.name;
            document.getElementById('fullname').readOnly = true;
            document.getElementById('contact_no').value = btn.dataset.contact;
            document.getElementById('contact_no').readOnly = true;
            document.getElementById('email').value = btn.dataset.email;
            document.getElementById('sex').value = btn.dataset.sex;
            document.getElementById('selected_client_badge').style.display = 'inline-block';
            alert('Existing client selected! You can now proceed to log their new concern.');
        }
    });
});
</script>
</body>
</html>