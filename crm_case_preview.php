<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CAMS CRM Preview</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: #f3f6fb;
      font-family: 'Segoe UI', sans-serif;
    }

    .wrapper-shell {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px 60px;
    }

    .page-header {
      background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
      color: #fff;
      border-radius: 18px;
      padding: 24px 28px;
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
      margin-bottom: 20px;
    }

    .page-header h1 {
      margin: 0;
      font-weight: 700;
      font-size: 2rem;
    }

    .page-header p {
      margin: 6px 0 0;
      opacity: 0.9;
    }

    .card {
      border: 1px solid #e3e8ef;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
      margin-bottom: 22px;
    }

    .card-header {
      background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
      border-bottom: 1px solid #e6eef9;
      padding: 0.9rem 1.2rem;
    }

    .card-title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #1f2937;
      letter-spacing: 0.01em;
    }

    .card-body {
      padding: 1.25rem 1.25rem 1rem;
      background: #fff;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.45rem;
      font-size: 0.8rem;
      font-weight: 700;
      color: #374151;
      letter-spacing: 0.01em;
    }

    .form-control {
      min-height: 44px;
      border-radius: 12px;
      border: 1px solid #d8e1ee;
      padding: 0.7rem 0.8rem;
      box-shadow: none;
      background: #fff;
      color: #1f2937;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-control:focus {
      border-color: #7ab7db;
      box-shadow: 0 0 0 0.18rem rgba(92, 155, 208, 0.18);
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

    textarea.form-control {
      min-height: 120px;
    }

    .btn {
      border-radius: 12px;
      font-weight: 700;
      padding: 0.7rem 1.1rem;
    }

    .btn-primary {
      background: linear-gradient(135deg, #1d73c7 0%, #2b5fe9 100%);
      border-color: transparent;
      box-shadow: 0 10px 18px rgba(29, 115, 199, 0.2);
    }

    .btn-warning {
      background: linear-gradient(135deg, #f7c75d 0%, #e6a623 100%);
      border-color: transparent;
      color: #382d04;
      box-shadow: 0 10px 18px rgba(230, 166, 35, 0.18);
    }

    .btn-default {
      background: #fff;
      border: 1px solid #d7dfeb;
      color: #475569;
    }

    .badge {
      border-radius: 999px;
      padding: 0.5rem 0.8rem;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .text-muted {
      color: #64748b !important;
    }

    .table thead th {
      background: #edf3ff;
      color: #1f2d3d;
      font-size: 0.8rem;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .table td { vertical-align: middle; }

    .search-box .input-group .form-control {
      border-radius: 12px 0 0 12px;
    }

    .search-box .input-group-append .btn {
      border-radius: 0 12px 12px 0;
      min-height: 44px;
    }

    .section-caption {
      font-size: 0.72rem;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #64748b;
      font-weight: 700;
      margin-bottom: 0.6rem;
      display: block;
    }
  </style>
</head>
<body>
  <div class="wrapper-shell">
    <div class="page-header">
      <h1><i class="fas fa-diagram-project mr-2"></i>CRM-Style Case Intake Preview</h1>
      <p>Alternative design based on clients, cases, and case logs</p>
    </div>

    <form>
      <div class="card card-outline card-warning">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-search mr-2"></i>Step 1: Check Existing Client Record</h3>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">Search by full name or contact number before creating a new client profile.</p>
          <div class="row">
            <div class="col-md-8 search-box">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Type Full Name or Contact Number" autocomplete="off">
                <div class="input-group-append">
                  <button type="button" class="btn btn-warning"><i class="fas fa-search"></i> Search</button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <div class="table-responsive">
              <table class="table table-bordered table-hover table-striped mb-0">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Contact</th>
                    <th>OFW</th>
                    <th>Country</th>
                    <th>Active Cases</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Juan Dela Cruz</td>
                    <td>0917-123-4567</td>
                    <td>Juan Dela Cruz</td>
                    <td>Saudi Arabia</td>
                    <td>2</td>
                    <td><button type="button" class="btn btn-xs btn-primary">Use Client</button></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="card card-outline card-primary">
        <div class="card-header d-flex align-items-center">
          <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Step 2: Client Personal Details</h3>
          <div class="ml-auto">
            <span class="badge badge-success">Existing Client Selected</span>
          </div>
        </div>
        <div class="card-body">
          <div class="row no-gutters align-items-end">
            <div class="col-md-3 pr-2 form-group mb-2">
              <label>Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="e.g. Santos">
            </div>
            <div class="col-md-4 pr-2 form-group mb-2">
              <label>First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="e.g. Juan">
            </div>
            <div class="col-md-3 pr-2 form-group mb-2">
              <label>Middle Name</label>
              <input type="text" class="form-control" placeholder="e.g. Dela Cruz">
            </div>
            <div class="col-md-2 form-group mb-2">
              <label>Suffix</label>
              <select class="form-control">
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
              <input type="text" class="form-control" placeholder="09XXXXXXXXX">
            </div>
            <div class="col-md-1 pr-2 form-group mb-2">
              <label>Gender</label>
              <select class="form-control">
                <option value="">--</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="col-md-4 pr-2 form-group mb-2">
              <label>Email Address</label>
              <input type="email" class="form-control" placeholder="client@example.com">
            </div>
            <div class="col-md-3 form-group mb-2">
              <label>Date of Birth</label>
              <input type="date" class="form-control">
            </div>
          </div>

          <hr>

          <div class="section-caption"><i class="fas fa-map-marker-alt mr-1"></i> Address</div>
          <div class="row">
            <div class="col-md-12 form-group">
              <label>Address 1 <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="Unit 4B, Mabini Street">
            </div>
          </div>

          <div class="row">
            <div class="col-md-3 form-group">
              <label>Region</label>
              <select class="form-control">
                <option>Region V (Bicol Region)</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Province <span class="text-danger">*</span></label>
              <select class="form-control">
                <option>Albay</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Town / City</label>
              <select class="form-control">
                <option>Legazpi City</option>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Barangay</label>
              <select class="form-control">
                <option>Em's Barrio</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="card card-outline card-info">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-plane-departure mr-2"></i>Step 3: OFW Information</h3>
        </div>
        <div class="card-body">
          <div class="form-group clearfix mb-3">
            <div class="icheck-primary d-inline">
              <input type="checkbox" id="is_ofw" checked>
              <label for="is_ofw" class="font-weight-bold text-dark">Client is the OFW himself/herself</label>
            </div>
          </div>

          <div class="row no-gutters align-items-end">
            <div class="col-md-3 pr-2 form-group mb-2">
              <label>OFW Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="e.g. Santos">
            </div>
            <div class="col-md-4 pr-2 form-group mb-2">
              <label>OFW First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="e.g. Juan">
            </div>
            <div class="col-md-3 pr-2 form-group mb-2">
              <label>OFW Middle Name</label>
              <input type="text" class="form-control" placeholder="e.g. Dela Cruz">
            </div>
            <div class="col-md-2 form-group mb-2">
              <label>Suffix</label>
              <select class="form-control">
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
            <div class="col-md-6 pr-2 form-group mb-2">
              <label>Client's Relationship to OFW <span class="text-danger">*</span></label>
              <select class="form-control">
                <option>-- Select Relationship --</option>
                <option>Spouse</option>
                <option>Child</option>
                <option>Parent</option>
                <option>Sibling</option>
                <option>Representative</option>
              </select>
            </div>
          </div>

          <div class="row no-gutters align-items-end">
            <div class="col-md-6 pr-2 form-group mb-2">
              <label>Country of Deployment <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="e.g. Saudi Arabia, UAE, Singapore">
            </div>
            <div class="col-md-6 form-group mb-2">
              <label>Employment Type <span class="text-danger">*</span></label>
              <select class="form-control">
                <option>-- Select Type --</option>
                <option>Land-based</option>
                <option>Sea-based</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="card card-outline card-danger">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-file-invoice mr-2"></i>Step 4: Case / Concern Details</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 form-group">
              <label>Source Channel <span class="text-danger">*</span></label>
              <select class="form-control">
                <option>Walk-in</option>
                <option>Phone</option>
                <option>Email</option>
                <option>Online</option>
              </select>
            </div>
            <div class="col-md-4 form-group">
              <label>Concern Subject <span class="text-danger">*</span></label>
              <input type="text" class="form-control" placeholder="e.g. Unpaid Salary, Repatriation Request">
            </div>
            <div class="col-md-4 form-group">
              <label>Category <span class="text-danger">*</span></label>
              <select class="form-control">
                <option>-- Select Category --</option>
                <option>Salary Claim / Legal Assistance</option>
                <option>Repatriation / Medical Evacuation</option>
                <option>Welfare Assistance</option>
                <option>Scholarship / Education</option>
                <option>Livelihood / Reintegration</option>
                <option>Others</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 form-group offset-md-8">
              <label>Program-in-Charge <span class="text-danger">*</span></label>
              <select class="form-control">
                <option>Public Assistance Desk (PACD)</option>
                <option>Welfare Division</option>
                <option>Legal Unit</option>
                <option>Reintegration Unit</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Detailed Description of Concern <span class="text-danger">*</span></label>
            <textarea class="form-control" placeholder="Provide full details of the complaint or request..."></textarea>
          </div>

          <div class="form-group">
            <label>Initial Action Taken</label>
            <textarea class="form-control" placeholder="e.g. Conducted initial interview, endorsed to legal officer..."></textarea>
          </div>
        </div>
        <div class="card-footer bg-white text-right">
          <button type="reset" class="btn btn-default mr-2"><i class="fas fa-undo"></i> Reset Form</button>
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save mr-1"></i> Submit Case Record</button>
        </div>
      </div>
    </form>
  </div>
</body>
</html>
