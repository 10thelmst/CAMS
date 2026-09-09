<?php
require_once __DIR__ . '/../config/database.php';

$requiredTables = [
    'clients' => [
        'first_name', 'middle_name', 'last_name', 'suffix', 'contact_no', 'email',
        'sex', 'dob', 'is_ofw', 'address1', 'region_code', 'province_code',
        'city_code', 'barangay_code'
    ],
    'ofw_information' => [
        'client_id', 'ofw_first_name', 'ofw_middle_name', 'ofw_last_name',
        'ofw_suffix', 'ofw_name', 'country', 'employment_type', 'relationship'
    ],
    'concerns' => [
        'ticket_number', 'client_id', 'contact_type', 'subject', 'category',
        'description', 'status', 'current_program', 'created_by'
    ],
    'action_history' => [
        'concern_id', 'action_taken', 'performed_by'
    ],
    'status_history' => [
        'concern_id', 'status', 'remarks', 'changed_by'
    ]
];

$issues = [];
$results = [];
$databaseName = '';

try {
    $conn = get_cams_connection();
    $databaseName = $conn->query("SELECT DATABASE() AS db_name")->fetch_assoc()['db_name'] ?? 'unknown';

    $tableCheck = $conn->query("SHOW TABLES");
    $existingTables = [];
    if ($tableCheck) {
        while ($row = $tableCheck->fetch_array()) {
            $existingTables[] = $row[0];
        }
    }

    foreach ($requiredTables as $tableName => $expectedColumns) {
        $row = [
            'table' => $tableName,
            'exists' => in_array($tableName, $existingTables, true),
            'columns' => [],
            'missing_columns' => [],
            'row_count' => 0
        ];

        if ($row['exists']) {
            $columnsResult = $conn->query("SHOW COLUMNS FROM `{$tableName}`");
            $columnNames = [];
            if ($columnsResult) {
                while ($column = $columnsResult->fetch_assoc()) {
                    $columnNames[] = $column['Field'];
                }
            }

            $row['columns'] = $columnNames;
            $row['missing_columns'] = array_values(array_diff($expectedColumns, $columnNames));

            $countResult = $conn->query("SELECT COUNT(*) AS total FROM `{$tableName}`");
            if ($countResult && $countResult->num_rows > 0) {
                $row['row_count'] = (int) $countResult->fetch_assoc()['total'];
            }

            if (!empty($row['missing_columns'])) {
                $issues[] = "Missing columns in {$tableName}: " . implode(', ', $row['missing_columns']);
            }
        } else {
            $issues[] = "Table not found: {$tableName}";
        }

        $results[] = $row;
    }
} catch (Throwable $e) {
    $issues[] = 'Database connection failed: ' . $e->getMessage();
}

$source = @file_get_contents(__DIR__ . '/../Employee/create_client2.php');
$usesPdo = $source !== false && strpos($source, '$pdo->') !== false;
$usesMysqli = $source !== false && strpos($source, '$conn->') !== false;
if ($usesPdo) {
    $issues[] = 'Mismatch detected: Employee/create_client2.php uses PDO methods while config/database.php provides mysqli connections.';
}

$summaryStatus = empty($issues) ? 'OK' : 'ISSUES FOUND';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CAMS DB Connection Check</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body { background: #edf2f8; font-family: Arial, Helvetica, sans-serif; color: #1b2d3f; }
    .container { max-width: 1200px; margin: 30px auto; background: #fff; border: 1px solid #dce7f3; border-radius: 10px; box-shadow: 0 10px 28px rgba(15, 40, 70, 0.08); }
    .header { background: linear-gradient(#edf5ff, #dfeefa); border-bottom: 1px solid #d3dfef; padding: 18px 22px; }
    .header h1 { margin: 0; font-size: 30px; font-weight: 700; color: #223d5a; }
    .body { padding: 22px; }
    .status-box { border-radius: 8px; padding: 14px 16px; font-weight: 700; margin-bottom: 18px; }
    .status-ok { background: #e8f7ee; border: 1px solid #b8e3c6; color: #1e6f47; }
    .status-issue { background: #fff5e8; border: 1px solid #f2d3a0; color: #8f5a00; }
    .table thead th { background: #eaf1f9; color: #2d4461; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; }
    .table td { font-size: 13px; }
    .badge-ok { background: #d8f3e4; color: #1a7246; }
    .badge-bad { background: #ffe0d8; color: #a0391d; }
    .warning { background: #fff7e5; border: 1px solid #f1d59f; color: #875600; padding: 10px 12px; border-radius: 6px; margin-top: 18px; }
    .code { background: #f4f7fb; border: 1px solid #dae4f0; border-radius: 6px; padding: 10px 12px; font-family: Consolas, monospace; font-size: 12px; }
    .mini { font-size: 12px; color: #4b5f7a; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>CAMS - Database Health Check</h1>
    </div>
    <div class="body">
      <div class="status-box <?php echo $summaryStatus === 'OK' ? 'status-ok' : 'status-issue'; ?>">
        Status: <?php echo htmlspecialchars($summaryStatus); ?>
      </div>

      <div class="mb-3">
        <div class="mini">Database target:</div>
        <div class="font-weight-bold"><?php echo htmlspecialchars($databaseName ?: 'not connected'); ?></div>
      </div>

      <?php if (!empty($issues)): ?>
      <div class="warning">
        <strong>Detected issues:</strong>
        <ul class="mb-0 mt-2">
          <?php foreach ($issues as $issue): ?>
            <li><?php echo htmlspecialchars($issue); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="mt-4">
        <table class="table table-bordered table-sm mb-0">
          <thead>
            <tr>
              <th>Table</th>
              <th>Exists</th>
              <th>Row Count</th>
              <th>Missing Columns</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $result): ?>
              <tr>
                <td><?php echo htmlspecialchars($result['table']); ?></td>
                <td>
                  <?php if ($result['exists']): ?>
                    <span class="badge badge-ok">Yes</span>
                  <?php else: ?>
                    <span class="badge badge-bad">No</span>
                  <?php endif; ?>
                </td>
                <td><?php echo (int) $result['row_count']; ?></td>
                <td>
                  <?php if (empty($result['missing_columns'])): ?>
                    <span class="text-success">None</span>
                  <?php else: ?>
                    <span class="text-danger"><?php echo htmlspecialchars(implode(', ', $result['missing_columns'])); ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        <h4 class="mb-2">Current save mapping</h4>
        <div class="code">
Step 2 → clients<br>
Step 3 → ofw_information<br>
Step 4 → concerns<br>
Step 5 → action_history<br>
Status log → status_history
        </div>
      </div>

      <div class="mt-4">
        <h4 class="mb-2">Critical mismatch</h4>
        <div class="code">
Employee/create_client2.php uses PDO methods such as $pdo->beginTransaction(), $pdo->prepare(), and $pdo->lastInsertId().
config/database.php provides mysqli connections with get_cams_connection() and get_owwa_connection().
This must be unified before the form can save successfully.
        </div>
      </div>
    </div>
  </div>
</body>
</html>
