<?php
require_once '../auth/auth_check.php';

// Check if user has employee role
if (!has_role('employee')) {
    header('Location: ../index.php?unauthorized=1');
    exit();
}

require_once '../config/database.php';
$conn = get_cams_connection();

$title = 'Manage Case';
$active_page = 'manage_case';

// --- AJAX Endpoint: Fetch Case History ---
if (isset($_GET['ajax_fetch_history'])) {
    header('Content-Type: application/json');
    $logbook_id = (int)$_GET['logbook_id'];

    $stmt = $conn->prepare("
        SELECT c.concern, c.category_of_concern, c.action_taken, c.created_at,
               c.handled_by_user_id
        FROM logbook_concerns c
        WHERE c.logbook_id = ?
        ORDER BY c.id DESC
    ");
    $stmt->bind_param("i", $logbook_id);
    $stmt->execute();
    $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode($history);
    exit();
}

$search_query = trim($_GET['search'] ?? '');
$search_results = [];
$success_message = '';

// Handle Concern & Action Taken Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_concern'])) {
    $logbook_id          = (int)$_POST['logbook_id'];
    $concern             = trim($_POST['concern']);
    $category_of_concern = trim($_POST['category_of_concern']);
    $action_taken        = trim($_POST['action_taken']);
    $status              = trim($_POST['status']);
    $handled_by          = $_SESSION['user_id'] ?? 1;

    // 1. Insert Concern & Action
    $stmt = $conn->prepare("INSERT INTO logbook_concerns (logbook_id, concern, category_of_concern, action_taken, handled_by_user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $logbook_id, $concern, $category_of_concern, $action_taken, $handled_by);
    $stmt->execute();
    $stmt->close();

    // 2. Update Master Status
    $stmt = $conn->prepare("UPDATE logbooks SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $logbook_id);
    $stmt->execute();
    $stmt->close();

    $success_message = "Case concern and action taken recorded successfully!";
}

// Search Logic
if (!empty($search_query)) {
    $sql = "SELECT l.id AS logbook_id, l.ticket_number, l.status, l.created_at,
                   p.first_name, p.last_name, p.contact_number, lp.role, lp.relationship_to_ofw
            FROM logbooks l
            JOIN logbook_people lp ON l.id = lp.logbook_id
            JOIN people p ON lp.person_id = p.id
            WHERE l.ticket_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?
            ORDER BY l.id DESC";
    $stmt = $conn->prepare($sql);
    $param = "%{$search_query}%";
    $stmt->bind_param("sss", $param, $param, $param);
    $stmt->execute();
    $search_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$content = '
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-folder-open"></i> Program In-Charge - Search & Case Management</h3>
        </div>
        <div class="card-body">
            ' . ($success_message ? '<div class="alert alert-success">' . $success_message . '</div>' : '') . '

            <form method="GET" class="row mb-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Search by Ticket #, First Name, or Last Name..." value="' . htmlspecialchars($search_query) . '">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Search Case</button>
                </div>
            </form>

            ' . (!empty($search_results) ? '
                <table class="table table-bordered align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>Ticket #</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Date Created</th>
                            <th width="220px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>' : '');

if (!empty($search_results)) {
    foreach ($search_results as $row) {
        $content .= '
                        <tr>
                            <td><strong>' . htmlspecialchars($row['ticket_number']) . '</strong></td>
                            <td>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>
                            <td><span class="badge bg-info">' . htmlspecialchars($row['role']) . '</span></td>
                            <td><span class="badge bg-warning text-dark">' . htmlspecialchars($row['status']) . '</span></td>
                            <td>' . htmlspecialchars($row['created_at']) . '</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info me-1" onclick="viewHistory(' . $row['logbook_id'] . ', \'' . htmlspecialchars($row['ticket_number']) . '\')">
                                    <i class="fas fa-history"></i> History
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="openActionModal(' . $row['logbook_id'] . ', \'' . htmlspecialchars($row['ticket_number']) . '\')">
                                    <i class="fas fa-edit"></i> Action
                                </button>
                            </td>
                        </tr>';
    }
}

$content .= (!empty($search_results) ? '
                    </tbody>
                </table>
            ' : ($search_query ? '<p class="text-muted">No records found matching "' . htmlspecialchars($search_query) . '".</p>' : '<p class="text-muted">Enter a search term to find cases.</p>')) . '
        </div>
    </div>

    <!-- Modal 1: Add New Concern & Action Taken -->
    <div class="modal fade" id="actionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Record Concern & Action Taken (<span id="modalTicketNo"></span>)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="logbook_id" id="modalLogbookId">
                    <input type="hidden" name="save_concern" value="1">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Category of Concern</label>
                        <select name="category_of_concern" class="form-control" required>
                            <option value="Repatriation">Repatriation</option>
                            <option value="Salary Claim">Salary Claim</option>
                            <option value="Medical Assistance">Medical Assistance</option>
                            <option value="Illegal Recruitment">Illegal Recruitment</option>
                            <option value="Financial Assistance">Financial Assistance</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Client / OFW Concern</label>
                        <textarea name="concern" class="form-control" rows="3" placeholder="Specify details of the concern..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Action Taken</label>
                        <textarea name="action_taken" class="form-control" rows="3" placeholder="Specify actions, referrals, or steps taken..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Update Case Status</label>
                        <select name="status" class="form-control">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Action Taken</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: View History -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-history"></i> Case History (<span id="historyTicketNo"></span>)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="historyTimeline">
                        <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading history...</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function openActionModal(logbookId, ticketNumber) {
        document.getElementById("modalLogbookId").value = logbookId;
        document.getElementById("modalTicketNo").innerText = ticketNumber;
        var modal = new bootstrap.Modal(document.getElementById("actionModal"));
        modal.show();
    }

    function viewHistory(logbookId, ticketNumber) {
        document.getElementById("historyTicketNo").innerText = ticketNumber;
        var historyContainer = document.getElementById("historyTimeline");
        historyContainer.innerHTML = \'<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i> Loading history...</div>\';

        var modal = new bootstrap.Modal(document.getElementById("historyModal"));
        modal.show();

        // Fetch History via AJAX
        fetch("manage_case.php?ajax_fetch_history=1&logbook_id=" + logbookId)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    historyContainer.innerHTML = \'<div class="alert alert-secondary">No concerns or actions recorded for this ticket yet.</div>\';
                    return;
                }

                let html = \'<div class="list-group">\';
                data.forEach(item => {
                    html += `
                        <div class="list-group-item list-group-item-action mb-2 rounded border">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <span class="badge bg-primary">${item.category_of_concern}</span>
                                <small class="text-muted"><i class="far fa-clock"></i> ${item.created_at}</small>
                            </div>
                            <p class="mb-1 fw-bold text-dark">Concern:</p>
                            <p class="mb-2 text-secondary bg-light p-2 rounded">${item.concern}</p>
                            <p class="mb-1 fw-bold text-dark">Action Taken:</p>
                            <p class="mb-1 text-success bg-light p-2 rounded">${item.action_taken}</p>
                            <small class="text-muted">Handled by Staff ID: ${item.handled_by_user_id}</small>
                        </div>
                    `;
                });
                html += \'</div>\';
                historyContainer.innerHTML = html;
            })
            .catch(err => {
                historyContainer.innerHTML = \'<div class="alert alert-danger">Failed to load case history.</div>\';
            });
    }
    </script>
';

require_once 'layout.php';
?>