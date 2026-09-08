<?php
// Viewing-only page: DB mapping for client intake / case creation flow
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UI Checker - DB Save Mapping</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body {
      background: #edf2f8;
      color: #1f2d3d;
      font-family: Arial, Helvetica, sans-serif;
    }
    .container {
      max-width: 1100px;
      margin: 32px auto;
      padding: 24px;
      background: #fff;
      border: 1px solid #d8e3f0;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(15, 40, 70, 0.06);
    }
    h2 {
      font-size: 28px;
      font-weight: 700;
      color: #243b5a;
      margin-bottom: 20px;
    }
    .step-box {
      border: 1px solid #d7e2ef;
      border-radius: 8px;
      margin-bottom: 18px;
      overflow: hidden;
      background: #f9fbff;
    }
    .step-header {
      background: linear-gradient(#edf5ff, #dfeefa);
      padding: 12px 16px;
      border-bottom: 1px solid #d0dce8;
      font-weight: 700;
      color: #2a4365;
    }
    .step-body {
      padding: 16px;
    }
    .table thead th {
      background: #eaf1f9;
      color: #2d4362;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .table td {
      font-size: 13px;
      vertical-align: top;
    }
    .badge {
      font-size: 11px;
      padding: 6px 8px;
      border-radius: 999px;
    }
    .code {
      background: #f3f6fb;
      border: 1px solid #dfe8f3;
      border-radius: 5px;
      padding: 8px 10px;
      font-family: Consolas, monospace;
      font-size: 12px;
      color: #1f2d3d;
      margin-top: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Client Intake / Case Save Mapping</h2>

    <div class="step-box">
      <div class="step-header">Step 1: Check Existing Client Record</div>
      <div class="step-body">
        <p class="mb-2">This step does not insert data. It searches existing client records to prevent duplicates.</p>
        <span class="badge badge-secondary">Action: Search only</span>
        <div class="code">
          SELECT ... FROM clients<br>
          JOIN ofw_information ...<br>
          JOIN concerns ...
        </div>
      </div>
    </div>

    <div class="step-box">
      <div class="step-header">Step 2: Client Personal Details</div>
      <div class="step-body">
        <p>Saved into the <strong>clients</strong> table.</p>
        <table class="table table-bordered table-sm mb-0">
          <thead>
            <tr>
              <th>Field</th>
              <th>DB Column</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Last Name</td><td>last_name</td></tr>
            <tr><td>First Name</td><td>first_name</td></tr>
            <tr><td>Middle Name</td><td>middle_name</td></tr>
            <tr><td>Suffix</td><td>suffix</td></tr>
            <tr><td>Contact Number</td><td>contact_no</td></tr>
            <tr><td>Email</td><td>email</td></tr>
            <tr><td>Gender</td><td>sex</td></tr>
            <tr><td>Date of Birth</td><td>dob</td></tr>
            <tr><td>Is OFW</td><td>is_ofw</td></tr>
            <tr><td>Address</td><td>address1</td></tr>
            <tr><td>Region</td><td>region_code</td></tr>
            <tr><td>Province</td><td>province_code</td></tr>
            <tr><td>City</td><td>city_code</td></tr>
            <tr><td>Barangay</td><td>barangay_code</td></tr>
          </tbody>
        </table>
        <div class="code">
          INSERT INTO clients (first_name, middle_name, last_name, suffix, contact_no, email, sex, dob, is_ofw, address1, region_code, province_code, city_code, barangay_code)
        </div>
      </div>
    </div>

    <div class="step-box">
      <div class="step-header">Step 3: OFW Information</div>
      <div class="step-body">
        <p>Saved into the <strong>ofw_information</strong> table.</p>
        <table class="table table-bordered table-sm mb-0">
          <thead>
            <tr>
              <th>Field</th>
              <th>DB Column</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Client ID</td><td>client_id</td></tr>
            <tr><td>OFW Last Name</td><td>ofw_last_name</td></tr>
            <tr><td>OFW First Name</td><td>ofw_first_name</td></tr>
            <tr><td>OFW Middle Name</td><td>ofw_middle_name</td></tr>
            <tr><td>Suffix</td><td>ofw_suffix</td></tr>
            <tr><td>OFW Name</td><td>ofw_name</td></tr>
            <tr><td>Country</td><td>country</td></tr>
            <tr><td>Employment Type</td><td>employment_type</td></tr>
            <tr><td>Relationship</td><td>relationship</td></tr>
          </tbody>
        </table>
        <div class="code">
          INSERT INTO ofw_information (client_id, ofw_first_name, ofw_middle_name, ofw_last_name, ofw_suffix, ofw_name, country, employment_type, relationship)
        </div>
      </div>
    </div>

    <div class="step-box">
      <div class="step-header">Step 4: Concern / Case Details</div>
      <div class="step-body">
        <p>Saved into the <strong>concerns</strong> table.</p>
        <table class="table table-bordered table-sm mb-0">
          <thead>
            <tr>
              <th>Field</th>
              <th>DB Column</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Ticket Number</td><td>ticket_number</td></tr>
            <tr><td>Client ID</td><td>client_id</td></tr>
            <tr><td>Transaction Type</td><td>contact_type</td></tr>
            <tr><td>Subject</td><td>subject</td></tr>
            <tr><td>Category</td><td>category</td></tr>
            <tr><td>Description</td><td>description</td></tr>
            <tr><td>Status</td><td>status</td></tr>
            <tr><td>Program In Charge</td><td>current_program</td></tr>
            <tr><td>Created By</td><td>created_by</td></tr>
          </tbody>
        </table>
        <div class="code">
          INSERT INTO concerns (ticket_number, client_id, contact_type, subject, category, description, status, current_program, created_by)
        </div>
      </div>
    </div>

    <div class="step-box">
      <div class="step-header">Step 5: Action Taken Log</div>
      <div class="step-body">
        <p>Saved into the <strong>action_history</strong> table and linked to the case record.</p>
        <table class="table table-bordered table-sm mb-0">
          <thead>
            <tr>
              <th>Field</th>
              <th>DB Column</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Case ID</td><td>concern_id</td></tr>
            <tr><td>Action Taken</td><td>action_taken</td></tr>
            <tr><td>Performed By</td><td>performed_by</td></tr>
            <tr><td>Created By / Logger</td><td>performed_by</td></tr>
          </tbody>
        </table>
        <div class="code">
          INSERT INTO action_history (concern_id, action_taken, performed_by)
        </div>
      </div>
    </div>

    <div class="step-box">
      <div class="step-header">Status Timeline</div>
      <div class="step-body">
        <p>Saved into the <strong>status_history</strong> table as the case’s status log.</p>
        <table class="table table-bordered table-sm mb-0">
          <thead>
            <tr>
              <th>Field</th>
              <th>DB Column</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Case ID</td><td>concern_id</td></tr>
            <tr><td>Status</td><td>status</td></tr>
            <tr><td>Remarks</td><td>remarks</td></tr>
            <tr><td>Changed By</td><td>changed_by</td></tr>
          </tbody>
        </table>
        <div class="code">
          INSERT INTO status_history (concern_id, status, remarks, changed_by)
        </div>
      </div>
    </div>

    <div class="step-box">
      <div class="step-header">Summary</div>
      <div class="step-body">
        <ul class="mb-0">
          <li><strong>Step 2</strong> → clients</li>
          <li><strong>Step 3</strong> → ofw_information</li>
          <li><strong>Step 4</strong> → concerns</li>
          <li><strong>Step 5</strong> → action_history (case-linked action log; who created it is in performed_by)</li>
          <li><strong>Status timeline</strong> → status_history</li>
        </ul>
      </div>
    </div>
  </div>
</body>
</html>
