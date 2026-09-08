<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CAMS CRM Case Preview Dummy</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      margin: 0;
      background: #dfe8f1;
      font-family: Arial, Helvetica, sans-serif;
      color: #333;
    }

    .crm-shell {
      width: 100%;
      min-height: 100vh;
      background: #dfe8f1;
    }

    .topbar {
      height: 52px;
      background: linear-gradient(#f6f6f6, #e1e1e1);
      border-bottom: 1px solid #b6c1d1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 12px 0 10px;
      font-size: 12px;
    }

    .oracle-brand {
      display: flex;
      align-items: center;
      font-weight: 700;
      color: #1f2f5c;
      letter-spacing: 0.02em;
      font-size: 13px;
    }

    .oracle-mark {
      background: #e51b22;
      color: #fff;
      font-size: 20px;
      font-weight: 700;
      padding: 4px 7px 3px;
      margin-right: 8px;
      line-height: 1;
      border-radius: 3px;
    }

    .crm-word {
      font-weight: 700;
      color: #2d3b69;
      margin-left: 2px;
    }

    .top-links {
      display: flex;
      align-items: center;
      gap: 14px;
      font-size: 12px;
      color: #485a78;
    }

    .top-links span {
      white-space: nowrap;
    }

    .layout {
      display: flex;
      min-height: calc(100vh - 52px);
    }

    .sidebar {
      width: 220px;
      background: #dfeaf6;
      border-right: 1px solid #b9c7d8;
      padding: 0;
    }

    .nav-section {
      padding: 8px 8px 0;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 10px;
      margin: 3px 0;
      border-radius: 4px;
      color: #1f2b40;
      font-size: 12px;
      font-weight: 600;
    }

    .nav-item i {
      width: 16px;
      text-align: center;
      color: #3d5271;
    }

    .nav-item.active {
      background: #c8d8ee;
      border: 1px solid #9fb8d9;
      font-weight: 700;
    }

    .nav-item.small {
      padding-left: 26px;
      font-weight: 500;
    }

    .main {
      flex: 1;
      background: #edf2f8;
      padding: 0;
    }

    .main-strip {
      background: linear-gradient(#edf2f7, #e6edf6);
      border-bottom: 1px solid #c7d3e3;
      padding: 12px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .breadcrumb-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #3f536f;
      font-weight: 600;
    }

    .page-title {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 18px;
      font-weight: 700;
      color: #1d2d52;
      margin: 0;
    }

    .action-bar {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 10px;
      padding: 0 14px;
    }

    .btn-mini {
      border: 1px solid #a9b8d0;
      background: linear-gradient(#f4f8ff, #dfeaf8);
      color: #224065;
      padding: 5px 10px;
      border-radius: 3px;
      font-size: 12px;
      font-weight: 600;
      line-height: 1.2;
      cursor: pointer;
    }

    .btn-mini.primary {
      background: linear-gradient(#edf6ff, #d0e1f7);
    }

    .panel-wrap {
      margin: 12px 14px 0;
      background: #f3f7fb;
      border: 1px solid #c5d3e3;
      border-radius: 3px;
    }

    .panel-title {
      background: linear-gradient(#edf6ff, #dfeefa);
      border-bottom: 1px solid #c4d3e8;
      padding: 8px 10px;
      font-size: 13px;
      font-weight: 700;
      color: #3a4d69;
    }

    .panel-body {
      background: #fff;
      padding: 10px 12px 8px;
    }

    .two-col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px 20px;
    }

    .field-row {
      display: flex;
      align-items: center;
      min-height: 30px;
      border-bottom: 1px solid #e5edf7;
      padding: 3px 0;
    }

    .field-label {
      width: 150px;
      font-size: 12px;
      color: #4b5f7e;
      padding-right: 10px;
      font-weight: 600;
      text-align: right;
    }

    .field-value {
      flex: 1;
      min-height: 22px;
      border-bottom: 1px solid #cdd8eb;
      font-size: 12px;
      color: #222;
      padding: 0 4px 2px 0;
    }

    .panel-subtitle {
      font-size: 13px;
      font-weight: 700;
      color: #334c75;
      margin: 14px 0 8px;
      padding-top: 8px;
      border-top: 1px solid #dce7f2;
    }

    .table-grid {
      background: #fff;
      border: 1px solid #c9d6e7;
      border-top: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }

    thead th {
      background: #dfeaf6;
      color: #2a3d59;
      font-weight: 700;
      border-bottom: 1px solid #bfd0ea;
      padding: 7px 8px;
      text-align: left;
    }

    tbody td {
      border-bottom: 1px solid #ecf0f6;
      padding: 7px 8px;
      color: #2c3a4d;
      background: #fff;
    }

    tbody tr:nth-child(even) td { background: #fafcff; }

    .mini-link {
      color: #2d5f9e;
      font-weight: 600;
      text-decoration: none;
    }

    .footer-row {
      background: #ebf2fa;
      border-top: 1px solid #c5d7ee;
      padding: 6px 10px;
      font-size: 12px;
      color: #4a617d;
    }

    .section-box {
      background: #fff;
      border: 1px solid #d8e2ee;
      border-radius: 4px;
      padding: 12px;
    }

    .section-head {
      font-size: 13px;
      font-weight: 700;
      color: #2b3f5d;
      margin-bottom: 8px;
    }

    .case-summary {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 12px 18px;
      margin-top: 12px;
      padding: 10px 0 4px;
    }

    .summary-item {
      border-bottom: 1px solid #edf1f7;
      padding-bottom: 8px;
    }

    .summary-label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #657a94;
      margin-bottom: 4px;
    }

    .summary-value {
      font-size: 12px;
      color: #1f2d3d;
      font-weight: 600;
    }

    .timeline-box {
      background: #fff;
      border: 1px solid #d8e2ee;
      border-radius: 4px;
      padding: 12px;
      margin-top: 12px;
    }

    .timeline-item {
      border-left: 2px solid #dfeaf6;
      padding-left: 12px;
      margin-left: 6px;
      position: relative;
      padding-bottom: 10px;
    }

    .timeline-item::before {
      content: "";
      position: absolute;
      left: -6px;
      top: 2px;
      width: 10px;
      height: 10px;
      background: #3c7ed8;
      border-radius: 50%;
      border: 2px solid #fff;
      box-shadow: 0 0 0 2px #d9e9ff;
    }

    .timeline-date {
      font-size: 11px;
      font-weight: 700;
      color: #657a94;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      margin-bottom: 4px;
    }

    .timeline-title {
      font-size: 12px;
      font-weight: 700;
      color: #243b5d;
      margin-bottom: 3px;
    }

    .timeline-text {
      font-size: 12px;
      color: #425779;
      line-height: 1.5;
      margin: 0;
    }

    @media (max-width: 980px) {
      .layout { display: block; }
      .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #b9c7d8; }
      .two-col, .case-summary { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="crm-shell">
    <div class="topbar">
      <div class="oracle-brand">
        <div class="oracle-mark">O</div>
        <div>RACLE <span class="crm-word">CRM On Demand</span></div>
      </div>
      <div class="top-links">
        <span>Training and Support</span>
        <span>|</span>
        <span>Admin</span>
        <span>|</span>
        <span>My Setup</span>
        <span>|</span>
        <span>Deleted Items</span>
        <span>|</span>
        <span>Help</span>
        <span>|</span>
        <span>Sign Out</span>
      </div>
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
            <span class="page-title"><i class="fas fa-user"></i> Client Case Detail</span>
            <span>|</span>
            <span>Back to Client Records</span>
          </div>
        </div>

        <div class="action-bar">
          <button class="btn-mini primary">New Case</button>
          <button class="btn-mini">Edit Client</button>
          <button class="btn-mini">Log A Call</button>
          <button class="btn-mini">Add Note</button>
          <button class="btn-mini">Assign</button>
          <button class="btn-mini">Close Case</button>
        </div>

        <div class="panel-wrap">
          <div class="panel-title">Client Information</div>
          <div class="panel-body">
            <div class="two-col">
              <div>
                <div class="field-row"><div class="field-label">Last Name</div><div class="field-value">Dela Cruz</div></div>
                <div class="field-row"><div class="field-label">First Name</div><div class="field-value">Juan</div></div>
                <div class="field-row"><div class="field-label">Middle Name</div><div class="field-value">Santos</div></div>
                <div class="field-row"><div class="field-label">Suffix</div><div class="field-value">Jr.</div></div>
                <div class="field-row"><div class="field-label">Contact Number</div><div class="field-value">0917-123-4567</div></div>
                <div class="field-row"><div class="field-label">Gender</div><div class="field-value">Male</div></div>
              </div>

              <div>
                <div class="field-row"><div class="field-label">Email Address</div><div class="field-value">juan.delacruz@email.com</div></div>
                <div class="field-row"><div class="field-label">Date of Birth</div><div class="field-value">1990-05-14</div></div>
                <div class="field-row"><div class="field-label">Address 1</div><div class="field-value">Unit 4B, Mabini Street</div></div>
                <div class="field-row"><div class="field-label">Province</div><div class="field-value">Albay</div></div>
                <div class="field-row"><div class="field-label">Town / City</div><div class="field-value">Legazpi City</div></div>
                <div class="field-row"><div class="field-label">Barangay</div><div class="field-value">Em's Barrio</div></div>
              </div>
            </div>
          </div>
        </div>

        <div class="panel-wrap">
          <div class="panel-title">OFW Information</div>
          <div class="panel-body">
            <div class="two-col">
              <div>
                <div class="field-row"><div class="field-label">OFW Last Name</div><div class="field-value">Dela Cruz</div></div>
                <div class="field-row"><div class="field-label">OFW First Name</div><div class="field-value">Juan</div></div>
                <div class="field-row"><div class="field-label">OFW Middle Name</div><div class="field-value">Santos</div></div>
                <div class="field-row"><div class="field-label">Suffix</div><div class="field-value">Jr.</div></div>
              </div>

              <div>
                <div class="field-row"><div class="field-label">Relationship</div><div class="field-value">Self</div></div>
                <div class="field-row"><div class="field-label">Country</div><div class="field-value">Saudi Arabia</div></div>
                <div class="field-row"><div class="field-label">Employment Type</div><div class="field-value">Land-based</div></div>
              </div>
            </div>
          </div>
        </div>

        <div class="panel-wrap">
          <div class="panel-title">Case Details</div>
          <div class="panel-body">
            <div class="case-summary">
              <div class="summary-item">
                <span class="summary-label">Case Number</span>
                <div class="summary-value">PACD-2026-000145</div>
              </div>
              <div class="summary-item">
                <span class="summary-label">Status</span>
                <div class="summary-value">Open</div>
              </div>
              <div class="summary-item">
                <span class="summary-label">Category</span>
                <div class="summary-value">Salary Claim</div>
              </div>
              <div class="summary-item">
                <span class="summary-label">Program</span>
                <div class="summary-value">Public Assistance Desk</div>
              </div>
              <div class="summary-item">
                <span class="summary-label">Source</span>
                <div class="summary-value">Walk-in</div>
              </div>
              <div class="summary-item">
                <span class="summary-label">Assigned To</span>
                <div class="summary-value">Ms. Reyes</div>
              </div>
              <div class="summary-item">
                <span class="summary-label">Priority</span>
                <div class="summary-value">High</div>
              </div>
              <div class="summary-item">
                <span class="summary-label">Follow-up</span>
                <div class="summary-value">Tomorrow</div>
              </div>
            </div>

            <div class="section-head" style="margin-top: 14px;">Case Description</div>
            <div class="section-box">
              Client reported unpaid salary for 4 months while employed in Saudi Arabia. Client requested immediate assistance for documentation, legal coordination, and return support.
            </div>
          </div>
        </div>

        <div class="panel-wrap" style="margin-top: 12px;">
          <div class="panel-title">Case Log / Log A Call</div>
          <div class="panel-body">
            <div class="timeline-box">
              <div class="timeline-item">
                <div class="timeline-date">Sep 08, 2026 • 09:14 AM</div>
                <div class="timeline-title">Initial call received</div>
                <p class="timeline-text">Client called and reported unpaid salary concerns. Initial interview completed and case assigned to PACD.</p>
              </div>
              <div class="timeline-item">
                <div class="timeline-date">Sep 08, 2026 • 11:30 AM</div>
                <div class="timeline-title">Document review</div>
                <p class="timeline-text">Reviewed supporting documents and confirmed the client’s employment details. Requested additional proof of contract and salary breakdown.</p>
              </div>
              <div class="timeline-item">
                <div class="timeline-date">Sep 08, 2026 • 02:05 PM</div>
                <div class="timeline-title">Status update</div>
                <p class="timeline-text">Client was informed that the case is under evaluation. Follow-up scheduled for next working day.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="panel-wrap" style="margin-top: 12px;">
          <div class="panel-title">Completed Activities</div>
          <div class="table-grid">
            <table>
              <thead>
                <tr>
                  <th style="width: 12%;">Date</th>
                  <th style="width: 14%;">Type</th>
                  <th style="width: 46%;">Remarks</th>
                  <th style="width: 14%;">Status</th>
                  <th style="width: 14%;">By</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Sep 08</td>
                  <td>Call</td>
                  <td>Client requested follow-up regarding salary claim.</td>
                  <td>Completed</td>
                  <td>Ms. Reyes</td>
                </tr>
                <tr>
                  <td>Sep 07</td>
                  <td>Action</td>
                  <td>Requested employment contract and supporting documents.</td>
                  <td>Completed</td>
                  <td>Mr. Santos</td>
                </tr>
                <tr>
                  <td>Sep 06</td>
                  <td>Note</td>
                  <td>Client is waiting for acknowledgment from agency representative.</td>
                  <td>Open</td>
                  <td>Ms. Reyes</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="footer-row">
            Top Completed Activities &nbsp; Open Activities &nbsp; Lead Qualifications Scripts &nbsp; Survey Results &nbsp; LinkedIn
          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html>
