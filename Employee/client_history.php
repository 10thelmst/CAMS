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
          <button class="btn-mini primary" type="button">Back to Search</button>
          <button class="btn-mini" type="button">Edit</button>
          <button class="btn-mini" type="button">New Case</button>
          <button class="btn-mini" type="button">Print</button>
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
</body>
</html>
