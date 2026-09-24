<?php
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = get_cams_pdo();
$clientId = isset($_GET['client_id']) ? (int) $_GET['client_id'] : 0;

$client = null;
$cases = [];
$historySummary = [];

if ($clientId > 0) {
    $clientStmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id LIMIT 1");
    $clientStmt->execute([':id' => $clientId]);
    $client = $clientStmt->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        $caseStmt = $pdo->prepare("SELECT * FROM concerns WHERE client_id = :client_id ORDER BY created_at DESC, id DESC LIMIT 10");
        $caseStmt->execute([':client_id' => $clientId]);
        $cases = $caseStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cases as $caseRow) {
            $statusStmt = $pdo->prepare("SELECT * FROM status_history WHERE concern_id = :concern_id ORDER BY changed_at DESC, id DESC LIMIT 5");
            $statusStmt->execute([':concern_id' => $caseRow['id'] ?? 0]);
            $statusRows = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
            $historySummary[] = [
                'case' => $caseRow,
                'status_history' => $statusRows
            ];
        }
    }
}

function safeValue($value) {
    return $value === null || $value === '' ? 'N/A' : htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$fullName = $client ? trim(implode(' ', array_filter([
    $client['first_name'] ?? '',
    $client['middle_name'] ?? '',
    $client['last_name'] ?? '',
    $client['suffix'] ?? ''
], fn($part) => $part !== ''))) : 'Client';
$subject = $cases[0]['subject'] ?? 'No recent case';
$status = $cases[0]['status'] ?? 'Open';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CRM On Demand | Client History</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      background: #dfe7f4;
      font-family: Arial, Helvetica, sans-serif;
      color: #2b2f36;
    }
    .crm-shell {
      width: 100%; min-height: 100vh; background: #dfe7f4;
    }
    .topbar {
      height: 52px;
      background: linear-gradient(#f5f5f5, #dfdfdf);
      border-bottom: 1px solid #bcc8d8;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 12px;
      font-size: 12px;
    }
    .oracle-brand {
      display: flex; align-items: center; font-weight: 700; color: #1e2a54; letter-spacing: 0.02em;
    }
    .oracle-mark {
      width: 26px; height: 26px; background: #d11c22; color: #fff; display: flex;
      align-items: center; justify-content: center; border-radius: 3px; margin-right: 8px; font-size: 18px; line-height: 1;
    }
    .crm-word { font-weight: 700; color: #2d3d69; }
    .top-links { display: flex; align-items: center; gap: 12px; color: #4b5e80; }
    .layout { display: flex; min-height: calc(100vh - 52px); }
    .sidebar { width: 220px; background: #dfeaf6; border-right: 1px solid #bfcfe0; }
    .nav-section { padding: 10px 8px 0; }
    .nav-item { display: flex; align-items: center; gap: 8px; padding: 8px 10px; margin: 2px 0; border-radius: 4px; color: #1f2f46; font-size: 12px; font-weight: 600; }
    .nav-item i { width: 16px; text-align: center; color: #41628d; }
    .nav-item.active { background: #c9d9ef; border: 1px solid #9db7d6; }
    .nav-item.small { padding-left: 26px; font-weight: 500; }
    .main { flex: 1; background: #edf2f8; }
    .main-strip {
      background: linear-gradient(#edf2f7, #e5edf6);
      border-bottom: 1px solid #cad8eb;
      padding: 12px 14px;
      display: flex; align-items: center; justify-content: space-between;
      gap: 12px;
    }
    .breadcrumb-row { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #45607f; font-weight: 600; }
    .page-title { font-size: 18px; font-weight: 700; color: #1e2f53; }
    .action-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 10px 14px 0; }
    .btn-mini { border: 1px solid #a9b8d0; background: linear-gradient(#f4f8ff, #dfeaf8); color: #224065; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: 600; line-height: 1.2; }
    .btn-mini.primary { background: linear-gradient(#edf6ff, #d0e1f7); }
    .content { padding: 10px 14px 20px; }
    .lead-detail { background: #edf4fb; border: 1px solid #cbd8eb; border-radius: 3px; }
    .detail-header { background: linear-gradient(#edf7ff, #dfeefa); border-bottom: 1px solid #c8d9ee; padding: 8px 12px; font-size: 13px; font-weight: 700; color: #3c5575; }
    .detail-body { background: #fff; padding: 12px; }
    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; }
    .field-row { display: flex; align-items: center; min-height: 32px; border-bottom: 1px solid #e7eef8; }
    .field-row:last-child { border-bottom: none; }
    .field-label { width: 160px; font-size: 12px; color: #536d8d; font-weight: 700; padding-right: 10px; text-align: right; }
    .field-value { flex: 1; font-size: 12px; color: #1e2d3d; background: #fff; border-bottom: 1px solid #d6e1ef; padding: 0 4px 2px; min-height: 22px; }
    .panel { margin-top: 14px; background: #f3f7fb; border: 1px solid #cedbeb; border-radius: 3px; }
    .panel-title { background: linear-gradient(#edf6ff, #e1ebf9); border-bottom: 1px solid #c8d9ee; padding: 8px 12px; font-size: 13px; font-weight: 700; color: #3a4e6b; }
    .panel-body { background: #fff; padding: 10px 12px; }
    .mini-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .mini-table th { background: #eaf1f9; color: #2b405f; font-weight: 700; border-bottom: 1px solid #d4e0f0; padding: 7px 8px; text-align: left; }
    .mini-table td { border-bottom: 1px solid #edf1f6; padding: 7px 8px; color: #273650; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; background: #dbeafc; color: #33536b; font-size: 11px; font-weight: 700; }
    .status-open { background: #dfeef9; color: #224a6b; }
    .status-closed { background: #dfe8d8; color: #285b31; }
    @media (max-width: 980px) { .layout { display: block; } .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #bfcfe0; } .field-grid { grid-template-columns: 1fr; } }
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
          <div class="nav-item" style="margin-top:8px;"><i class="fas fa-plus"></i> Create</div>
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
            <span class="page-title"><i class="fas fa-user"></i> Client History</span>
            <span>|</span>
            <span>Client Detail</span>
          </div>
        </div>

        <div class="action-bar">
          <button class="btn-mini primary" type="button" onclick="window.history.back();">Back to Search</button>
          <button id="btn_edit_client" class="btn-mini" type="button" data-client-id="<?= htmlspecialchars($clientId, ENT_QUOTES) ?>">Edit</button>
          <a href="create_client2.php?existing_client_id=<?= htmlspecialchars($clientId, ENT_QUOTES) ?>" class="btn-mini" role="button">New Case</a>
          <button class="btn-mini" type="button" onclick="window.print();">Print</button>
        </div>

        <div class="content">
          <?php if (!$client): ?>
            <div class="panel">
              <div class="panel-title">Client Record Not Found</div>
              <div class="panel-body">
                <p class="mb-0">No client record was found for this client ID.</p>
              </div>
            </div>
          <?php else: ?>
            <div class="lead-detail">
              <div class="detail-header">Lead Detail: <?= safeValue($fullName) ?> | <?= safeValue($subject) ?></div>
              <div class="detail-body">
                <div class="field-grid">
                  <div>
                    <div class="field-row"><div class="field-label">Client ID</div><div class="field-value"><?= safeValue($client['id'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">First Name</div><div class="field-value"><?= safeValue($client['first_name'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Middle Name</div><div class="field-value"><?= safeValue($client['middle_name'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Last Name</div><div class="field-value"><?= safeValue($client['last_name'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Suffix</div><div class="field-value"><?= safeValue($client['suffix'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Contact Number</div><div class="field-value"><?= safeValue($client['contact_no'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Gender</div><div class="field-value"><?= safeValue($client['sex'] ?? '') ?></div></div>
                  </div>
                  <div>
                    <div class="field-row"><div class="field-label">Email</div><div class="field-value"><?= safeValue($client['email'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Date of Birth</div><div class="field-value"><?= safeValue($client['dob'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Address</div><div class="field-value"><?= safeValue($client['address1'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Province</div><div class="field-value"><?= safeValue($client['province_code'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">City</div><div class="field-value"><?= safeValue($client['city_code'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Barangay</div><div class="field-value"><?= safeValue($client['barangay_code'] ?? '') ?></div></div>
                    <div class="field-row"><div class="field-label">Current Case Status</div><div class="field-value"><span class="badge status-open"><?= safeValue($status) ?></span></div></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="panel">
              <div class="panel-title">Case History</div>
              <div class="panel-body">
                <table class="mini-table">
                  <thead>
                    <tr>
                      <th>Ticket</th>
                      <th>Subject</th>
                      <th>Status</th>
                      <th>Created</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($cases)): ?>
                      <tr><td colspan="4">No case history found for this client.</td></tr>
                    <?php else: ?>
                      <?php foreach ($cases as $caseRow): ?>
                        <tr>
                          <td><?= safeValue($caseRow['ticket_number'] ?? '') ?></td>
                          <td><?= safeValue($caseRow['subject'] ?? '') ?></td>
                          <td><span class="badge <?= strtolower((string) ($caseRow['status'] ?? 'Open')) === 'closed' ? 'status-closed' : 'status-open' ?>"><?= safeValue($caseRow['status'] ?? 'Open') ?></span></td>
                          <td><?= safeValue($caseRow['created_at'] ?? '') ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="panel">
              <div class="panel-title">Recent Status Timeline</div>
              <div class="panel-body">
                <?php if (empty($historySummary)): ?>
                  <p class="mb-0">No status updates available.</p>
                <?php else: ?>
                  <table class="mini-table">
                    <thead>
                      <tr>
                        <th>Case</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Changed</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($historySummary as $entry): ?>
                        <?php foreach (($entry['status_history'] ?? []) as $statusEntry): ?>
                          <tr>
                            <td><?= safeValue($entry['case']['ticket_number'] ?? '') ?></td>
                            <td><?= safeValue($statusEntry['status'] ?? '') ?></td>
                            <td><?= safeValue($statusEntry['remarks'] ?? '') ?></td>
                            <td><?= safeValue($statusEntry['changed_at'] ?? '') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Edit client popup modal (simplified AJAX form) -->
  <div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Edit Client</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div id="editClientAlert"></div>
          <form id="editClientForm">
            <input type="hidden" name="client_id" id="client_id_field" value="">
            <div class="form-group">
              <label for="first_name_field">First Name</label>
              <input class="form-control" name="first_name" id="first_name_field">
            </div>
            <div class="form-group">
              <label for="last_name_field">Last Name</label>
              <input class="form-control" name="last_name" id="last_name_field">
            </div>
            <div class="form-group">
              <label for="contact_no_field">Contact Number</label>
              <input class="form-control" name="contact_no" id="contact_no_field">
            </div>
            <div class="form-group">
              <label for="email_field">Email</label>
              <input class="form-control" name="email" id="email_field" type="email">
            </div>
            <div class="form-group">
              <label for="address1_field">Address</label>
              <input class="form-control" name="address1" id="address1_field">
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="region_code_field">Region</label>
                <input class="form-control" name="region_code" id="region_code_field">
              </div>
              <div class="form-group col-md-4">
                <label for="province_code_field">Province</label>
                <input class="form-control" name="province_code" id="province_code_field">
              </div>
              <div class="form-group col-md-4">
                <label for="city_code_field">Municipality / City</label>
                <input class="form-control" name="city_code" id="city_code_field">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="barangay_code_field">Barangay</label>
                <input class="form-control" name="barangay_code" id="barangay_code_field">
              </div>
              <div class="form-group col-md-6">
                <div class="form-check" style="margin-top:32px;">
                  <input class="form-check-input" type="checkbox" name="is_ofw" id="is_ofw_field" value="1">
                  <label class="form-check-label" for="is_ofw_field">Is OFW</label>
                </div>
              </div>
            </div>

            <hr>
            <h6>OFW Information</h6>
            <div class="form-group">
              <label for="ofw_name_field">OFW Full Name</label>
              <input class="form-control" name="ofw_name" id="ofw_name_field">
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="ofw_country_field">Country</label>
                <input class="form-control" name="country" id="ofw_country_field">
              </div>
              <div class="form-group col-md-4">
                <label for="ofw_employment_type_field">Employment Type</label>
                <input class="form-control" name="employment_type" id="ofw_employment_type_field">
              </div>
              <div class="form-group col-md-4">
                <label for="ofw_relationship_field">Relationship</label>
                <input class="form-control" name="relationship" id="ofw_relationship_field">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
          <button type="button" id="saveEditClientBtn" class="btn btn-primary btn-sm">Save</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(function(){
      var $btn = $('#btn_edit_client');
      if (!$btn.length) return;

      $btn.on('click', function(){
        var id = $(this).data('client-id') || '<?= htmlspecialchars($clientId, ENT_QUOTES) ?>';
        $('#editClientForm')[0].reset();
        console.log('Edit button clicked for client id=', id);

        // validate id
        if (!id || parseInt(id) <= 0) {
          $('#editClientAlert').html('<div class="alert alert-warning">No client selected for editing.</div>');
          $('#editClientModal').modal('show');
          return;
        }

        // show modal immediately so user sees response
        $('#editClientAlert').html('<div class="text-center">Loading...</div>');
        $('#editClientModal').modal('show');

        // fetch with timeout to avoid indefinite loading
        var controller = new AbortController();
        var timeout = setTimeout(function(){ controller.abort(); $('#editClientAlert').html('<div class="alert alert-warning">Load timed out.</div>'); }, 8000);

        fetch('../auth/load_client_info.php?client_id=' + encodeURIComponent(id) + '&debug=1', { signal: controller.signal })
          .then(function(r){
            clearTimeout(timeout);
            return r.text().then(function(txt){
              // try parse JSON, otherwise show raw text for debugging
              try {
                var data = JSON.parse(txt);
                return { ok: true, data: data };
              } catch (e) {
                return { ok: false, text: txt };
              }
            });
          })
          .then(function(result){
            if (!result.ok) {
              console.error('Non-JSON response from load_client_info:', result.text);
              $('#editClientAlert').html('<div class="alert alert-danger">Server response:\n<pre style="white-space:pre-wrap">'+escapeHtml(result.text.substring(0,2000))+'</pre></div>');
              return;
            }
            var data = result.data;
            console.log('load_client_info response:', data);
            if (!data || !data.client){
              var pretty = '';
              try { pretty = JSON.stringify(data, null, 2); } catch(e){ pretty = String(data); }
              $('#editClientAlert').html('<div class="alert alert-danger">Failed to load client data (no client in JSON). See response below:<pre style="white-space:pre-wrap">'+escapeHtml(pretty)+'</pre></div>');
              console.error('load_client_info returned no client', data);
              return;
            }
            var c = data.client;
            $('#client_id_field').val(c.id || '');
            $('#first_name_field').val(c.first_name || '');
            $('#last_name_field').val(c.last_name || '');
            $('#contact_no_field').val(c.contact_no || '');
            $('#email_field').val(c.email || '');
            $('#address1_field').val(c.address1 || '');
            $('#region_code_field').val(c.region_code || '');
            $('#province_code_field').val(c.province_code || '');
            $('#city_code_field').val(c.city_code || '');
            $('#barangay_code_field').val(c.barangay_code || '');
            if (parseInt(c.is_ofw || 0) === 1) $('#is_ofw_field').prop('checked', true); else $('#is_ofw_field').prop('checked', false);

            // OFW fields (may be absent)
            $('#ofw_name_field').val(c.ofw_name || '');
            $('#ofw_country_field').val(c.country || '');
            $('#ofw_employment_type_field').val(c.employment_type || '');
            $('#ofw_relationship_field').val(c.relationship || '');
            $('#editClientAlert').html('');
          })
          .catch(function(err){
            console.error('Error loading client:', err);
            if (err.name === 'AbortError') {
              $('#editClientAlert').html('<div class="alert alert-warning">Request aborted (timeout).</div>');
            } else {
              $('#editClientAlert').html('<div class="alert alert-danger">Failed to load client data.</div>');
            }
          });
      });

      $('#saveEditClientBtn').on('click', function(){
        var form = document.getElementById('editClientForm');
        if (!form) return;
        var fd = new FormData(form);
        fd.append('action','update_client');
        $('#saveEditClientBtn').prop('disabled', true).text('Saving...');

        fetch('edit_client.php', { method: 'POST', body: fd })
          .then(r => r.json())
          .then(resp => {
            $('#saveEditClientBtn').prop('disabled', false).text('Save');
            if (resp.ok){
              $('#editClientModal').modal('hide');
              location.reload();
              return;
            }
            $('#editClientAlert').html('<div class="alert alert-danger">'+(resp.message||'Update failed')+'</div>');
          }).catch(()=>{
            $('#saveEditClientBtn').prop('disabled', false).text('Save');
            $('#editClientAlert').html('<div class="alert alert-danger">Request failed.</div>');
          });
      });
    });
  </script>
</body>
</html>
