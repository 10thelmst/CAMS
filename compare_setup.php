<?php
$site_title = 'CAMS Setup Comparison';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $site_title; ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: #f3f6fb;
      font-family: 'Segoe UI', sans-serif;
    }
    .page-shell {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px 40px;
    }
    .header-box {
      background: linear-gradient(135deg, #0f172a, #1d4ed8);
      color: #fff;
      border-radius: 18px;
      padding: 28px 30px;
      box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
    }
    .header-box h1 {
      margin: 0;
      font-size: 2rem;
      font-weight: 700;
    }
    .header-box p {
      margin: 8px 0 0;
      opacity: 0.9;
    }
    .comparison-table {
      margin-top: 24px;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }
    .comparison-table th {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .current {
      background: #fff8e1;
      border-left: 4px solid #f59e0b;
    }
    .target {
      background: #e0f2fe;
      border-left: 4px solid #0284c7;
    }
    .summary-box {
      border-radius: 16px;
      padding: 20px 22px;
      background: #fff;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
      margin-top: 24px;
    }
    .badge-pill {
      border-radius: 999px;
      font-size: 0.72rem;
      font-weight: 700;
      padding: 7px 12px;
      letter-spacing: 0.02em;
    }
    ul.key-points {
      padding-left: 20px;
      margin-bottom: 0;
    }
    ul.key-points li {
      margin-bottom: 8px;
    }
  </style>
</head>
<body>
  <div class="page-shell">
    <div class="header-box">
      <h1><i class="fas fa-layer-group mr-2"></i>CAMS Setup Comparison</h1>
      <p>Current setup vs CRM-style case management structure</p>
    </div>

    <div class="summary-box">
      <h4 class="mb-3"><i class="fas fa-balance-scale mr-2 text-primary"></i>Quick View</h4>
      <div class="row">
        <div class="col-md-6">
          <span class="badge badge-warning badge-pill">Current Setup</span>
          <ul class="key-points mt-3">
            <li>Clients are stored as personal records.</li>
            <li>Concerns are treated as case tickets.</li>
            <li>Action and status history are split into separate tables.</li>
            <li>Names are partly normalized but not fully consistent.</li>
          </ul>
        </div>
        <div class="col-md-6">
          <span class="badge badge-info badge-pill">Recommended CRM Style</span>
          <ul class="key-points mt-3">
            <li>Clients = master profile records.</li>
            <li>Cases = service requests and workflows.</li>
            <li>Case logs = full timeline and audit trail.</li>
            <li>Names and OFW records are fully normalized.</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="card comparison-table">
      <div class="card-body p-0">
        <table class="table table-bordered mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width: 20%;">Area</th>
              <th style="width: 40%;" class="current">Current Setup</th>
              <th style="width: 40%;" class="target">CRM-style Upgrade</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Core record</strong></td>
              <td class="current">Client + concern files are handled as separate records within the intake flow.</td>
              <td class="target">Client is the master record; each case is a separate service request tied to a client.</td>
            </tr>
            <tr>
              <td><strong>Case handling</strong></td>
              <td class="current">A concern is created after intake and stored as a case-like record.</td>
              <td class="target">A case is a first-class object with status, assignment, priority, and history.</td>
            </tr>
            <tr>
              <td><strong>History</strong></td>
              <td class="current">Status and action updates are stored in multiple tables such as status_history and action_history.</td>
              <td class="target">All updates are recorded in one case log table with event type, actor, remarks, and timestamp.</td>
            </tr>
            <tr>
              <td><strong>Names</strong></td>
              <td class="current">Names are partly normalized but some fields still seem mixed or display assembled at runtime.</td>
              <td class="target">Use last_name, first_name, middle_name, suffix in 3NF across clients and OFW records.</td>
            </tr>
            <tr>
              <td><strong>OFW record</strong></td>
              <td class="current">OFW information is stored as a separate block related to the case/client.</td>
              <td class="target">OFW profile stays separate but still normalized and clearly mapped to the client relationship.</td>
            </tr>
            <tr>
              <td><strong>Flow</strong></td>
              <td class="current">Search client → fill profile → case details.</td>
              <td class="target">Search client → client profile → OFW profile → case intake and lifecycle.</td>
            </tr>
            <tr>
              <td><strong>Issue type</strong></td>
              <td class="current">Operational and workflow oriented.</td>
              <td class="target">CRM-style case management with service tracking and auditability.</td>
            </tr>
            <tr>
              <td><strong>What matters</strong></td>
              <td class="current">Transaction completion and record saving.</td>
              <td class="target">Client continuity, case history, status tracking, and accountability.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="summary-box">
      <h4 class="mb-3"><i class="fas fa-check-circle mr-2 text-success"></i>Recommended target model</h4>
      <div class="row">
        <div class="col-md-4">
          <h6 class="text-primary">1. clients</h6>
          <p class="mb-0">Stores the master person profile.</p>
        </div>
        <div class="col-md-4">
          <h6 class="text-primary">2. cases</h6>
          <p class="mb-0">Stores case details, subject, status, category, program, and assignment.</p>
        </div>
        <div class="col-md-4">
          <h6 class="text-primary">3. case_logs</h6>
          <p class="mb-0">Stores all timeline events such as creation, assignment, follow-up, and closure.</p>
        </div>
      </div>
    </div>

    <div class="summary-box mt-4">
      <h4 class="mb-3"><i class="fas fa-list-check mr-2 text-warning"></i>Practical upgrade actions</h4>
      <ul class="key-points">
        <li>Keep the client search and duplicate check as Step 1.</li>
        <li>Use normalized name fields on both client and OFW forms.</li>
        <li>Move transaction/source channel into the case details block.</li>
        <li>Standardize all updates as case logs instead of scattered action/status tables.</li>
        <li>Keep the app focused on case management rather than overbuilding as a full CRM suite.</li>
      </ul>
    </div>
  </div>
</body>
</html>
