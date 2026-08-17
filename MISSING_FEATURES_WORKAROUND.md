# CAMS Missing Features Workaround Guide
**Date:** August 17, 2026  
**Purpose:** Identify missing features and provide implementation workarounds

---

## Executive Summary

CAMS has implemented core functionality but is missing **40+ features** across 11 categories. This guide prioritizes what's missing and provides step-by-step workarounds for each.

**Total Effort Estimate:**
- **Phase 1 (Critical):** 40 hours
- **Phase 2 (High Priority):** 60 hours  
- **Phase 3 (Medium):** 50 hours
- **Phase 4 (Polish):** 40 hours
- **Total:** ~190 hours (5 weeks)

---

## CRITICAL MISSING FILES

### 1. Database Configuration File ❌ **BLOCKING**

**Problem:** `config/database.php` is referenced in 30+ files but doesn't exist.

**Impact:** Code will not run properly.

**Workaround:** See [WORKAROUND_FLAWS_AND_FIXES.md](WORKAROUND_FLAWS_AND_FIXES.md#issue-1-missing-database-configuration-file)

---

### 2. Database Schema (database.sql) ❌ **BLOCKING**

**Problem:** No `database.sql` file provided. Referenced tables might not exist.

**Impact:** Tables like `concerns`, `clients`, `action_history` may be missing.

**Required Tables:**
```sql
-- User & Role Management
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    status ENUM('active', 'inactive'),
    password_change_required BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT
);

CREATE TABLE user_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    UNIQUE KEY (user_id, role_id)
);

-- Client Management
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_number VARCHAR(50) UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    barangay VARCHAR(100),
    country VARCHAR(100),
    date_of_birth DATE,
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Case/Concern Management
CREATE TABLE concerns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_number VARCHAR(50) UNIQUE,
    client_id INT NOT NULL,
    contact_type VARCHAR(100),
    subject VARCHAR(255),
    category VARCHAR(100),
    description TEXT,
    status VARCHAR(50) DEFAULT 'Open',
    current_program VARCHAR(100),
    created_by INT NOT NULL,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_client_id (client_id),
    INDEX idx_created_at (created_at)
);

-- Audit & History Tracking
CREATE TABLE action_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    concern_id INT NOT NULL,
    action_taken TEXT,
    performed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (concern_id) REFERENCES concerns(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

CREATE TABLE status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    concern_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    remarks TEXT,
    changed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (concern_id) REFERENCES concerns(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(255),
    entity_type VARCHAR(100),
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
);

-- File Attachments
CREATE TABLE attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    concern_id INT NOT NULL,
    file_name VARCHAR(255),
    file_path VARCHAR(500),
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (concern_id) REFERENCES concerns(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(100),
    title VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
);

CREATE TABLE notification_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    email_on_case_creation BOOLEAN DEFAULT 1,
    email_on_status_change BOOLEAN DEFAULT 1,
    email_on_assignment BOOLEAN DEFAULT 1,
    push_notifications BOOLEAN DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Case Notes & Comments
CREATE TABLE case_notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    concern_id INT NOT NULL,
    note_text TEXT,
    is_internal BOOLEAN DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (concern_id) REFERENCES concerns(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Employee Bio Data
CREATE TABLE employee_bio_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    position VARCHAR(100),
    department VARCHAR(100),
    hire_date DATE,
    phone_number VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Workaround:** Create this file at `/workspaces/CAMS/database.sql` and run:
```bash
mysql -u root -p cams < database.sql
```

---

## CATEGORY 1: COMMUNICATION & NOTIFICATIONS 🚫

### Missing Feature 1.1: Email Notifications System

**What's Missing:** No email sending system for case updates.

**Workaround Step-by-Step:**

**Step 1:** Create email service file

**File:** `lib/EmailService.php`
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = getenv('SMTP_USERNAME');
        $this->mailer->Password = getenv('SMTP_PASSWORD');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->setFrom('noreply@cams.local', 'CAMS System');
    }
    
    public function sendCaseCreatedNotification($employee_email, $case_data) {
        try {
            $this->mailer->addAddress($employee_email);
            $this->mailer->Subject = "New Case Created: " . $case_data['ticket_number'];
            
            $html = "
                <h2>New Case Assigned</h2>
                <p><b>Ticket Number:</b> {$case_data['ticket_number']}</p>
                <p><b>Client:</b> {$case_data['client_name']}</p>
                <p><b>Subject:</b> {$case_data['subject']}</p>
                <p><b>Description:</b><br>" . nl2br($case_data['description']) . "</p>
                <a href='http://localhost/cams/Employee/manage_case.php?id={$case_data['concern_id']}'>
                    View Case
                </a>
            ";
            
            $this->mailer->isHTML(true);
            $this->mailer->Body = $html;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendCaseStatusChanged($employee_email, $old_status, $new_status, $case_data) {
        // Similar structure, different message
        $this->mailer->addAddress($employee_email);
        $this->mailer->Subject = "Case Status Changed: " . $case_data['ticket_number'];
        
        $html = "
            <h2>Case Status Update</h2>
            <p><b>Case:</b> {$case_data['ticket_number']}</p>
            <p><b>Status Changed:</b> $old_status → $new_status</p>
        ";
        
        $this->mailer->isHTML(true);
        $this->mailer->Body = $html;
        
        return $this->mailer->send();
    }
}
?>
```

**Step 2:** Install PHPMailer via Composer
```bash
cd /workspaces/CAMS
composer require phpmailer/phpmailer
```

**Step 3:** Add notification sending in `Employee/create_client.php`
```php
<?php
// After successful case creation
require_once '../lib/EmailService.php';

$emailService = new EmailService();
if ($assigned_to_user_email) {
    $emailService->sendCaseCreatedNotification(
        $assigned_to_user_email,
        [
            'ticket_number' => $ticket_number,
            'client_name' => $client_name,
            'subject' => $subject,
            'description' => $description,
            'concern_id' => $new_case_id
        ]
    );
}
?>
```

---

### Missing Feature 1.2: Notification Center

**Workaround:** Create in-app notification display

**File:** `components/notification_center.php`
```php
<?php
require_once '../config/database.php';

function getUnreadNotifications($user_id) {
    $conn = get_cams_connection();
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? AND is_read = 0 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $notifications;
}

function markAsRead($notification_id) {
    $conn = get_cams_connection();
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

$notifications = getUnreadNotifications($_SESSION['user_id']);
$unread_count = count($notifications);
?>

<!-- Add to dashboard header -->
<div class="dropdown">
    <button class="btn btn-info dropdown-toggle" type="button" id="notificationDropdown">
        Notifications 
        <span class="badge badge-danger"><?php echo $unread_count; ?></span>
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        <?php foreach ($notifications as $notif): ?>
            <a class="dropdown-item" href="#" onclick="markRead(<?php echo $notif['id']; ?>)">
                <small><?php echo htmlspecialchars($notif['title']); ?></small>
                <br>
                <small class="text-muted"><?php echo $notif['created_at']; ?></small>
            </a>
        <?php endforeach; ?>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="../components/all_notifications.php">View All</a>
    </div>
</div>
```

---

## CATEGORY 2: FILE UPLOADS & ATTACHMENTS 🚫

### Missing Feature 2.1: Case File Attachments

**Workaround:**

**File:** `Employee/case_attachments.php`
```php
<?php
session_start();
require_once '../auth/auth_check.php';
require_once '../config/database.php';

$concern_id = $_GET['case_id'] ?? 0;
$conn = get_cams_connection();

// Check if user has access to this case
$stmt = $conn->prepare("SELECT * FROM concerns WHERE id = ?");
$stmt->bind_param("i", $concern_id);
$stmt->execute();
$case = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$case) {
    die("Case not found");
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['attachment'])) {
    $file = $_FILES['attachment'];
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 
                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    // Validate file
    if (!in_array($file['type'], $allowed_types)) {
        $error = "Invalid file type. Allowed: PDF, JPG, PNG, DOC, DOCX";
    } elseif ($file['size'] > 5242880) { // 5MB
        $error = "File too large. Maximum 5MB allowed";
    } else {
        // Generate safe filename
        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $file_path = "../uploads/case_" . $concern_id . "/" . $file_name;
        
        // Create directory if needed
        if (!is_dir("../uploads/case_" . $concern_id)) {
            mkdir("../uploads/case_" . $concern_id, 0755, true);
        }
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Save to database
            $stmt = $conn->prepare("
                INSERT INTO attachments (concern_id, file_name, file_path, file_size, mime_type, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("issisi", $concern_id, $file_name, $file_path, $file['size'], $file['type'], $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = "File uploaded successfully";
            } else {
                $error = "Failed to save file info";
                unlink($file_path);
            }
            $stmt->close();
        } else {
            $error = "Failed to upload file";
        }
    }
}

// Get existing attachments
$stmt = $conn->prepare("SELECT * FROM attachments WHERE concern_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $concern_id);
$stmt->execute();
$attachments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<div class="card">
    <div class="card-header">
        <h5>Case Attachments</h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Upload File (PDF, JPG, PNG, DOC, DOCX - Max 5MB)</label>
                <input type="file" name="attachment" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        
        <hr>
        
        <h6>Files:</h6>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Size</th>
                    <th>Uploaded</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attachments as $att): ?>
                <tr>
                    <td><?php echo htmlspecialchars($att['file_name']); ?></td>
                    <td><?php echo round($att['file_size'] / 1024, 2) . ' KB'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($att['created_at'])); ?></td>
                    <td>
                        <a href="../api/download_attachment.php?id=<?php echo $att['id']; ?>" class="btn btn-sm btn-primary">Download</a>
                        <button class="btn btn-sm btn-danger" onclick="deleteAttachment(<?php echo $att['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
```

**File:** `api/download_attachment.php`
```php
<?php
session_start();
require_once '../auth/auth_check.php';
require_once '../config/database.php';

$attachment_id = $_GET['id'] ?? 0;
$conn = get_cams_connection();

$stmt = $conn->prepare("
    SELECT a.*, c.id as concern_id FROM attachments a 
    JOIN concerns c ON a.concern_id = c.id 
    WHERE a.id = ?
");
$stmt->bind_param("i", $attachment_id);
$stmt->execute();
$attachment = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$attachment || !file_exists($attachment['file_path'])) {
    die("File not found");
}

// Check if user has access
// TODO: Add proper access control

// Download file
header('Content-Type: ' . $attachment['mime_type']);
header('Content-Disposition: attachment; filename="' . $attachment['file_name'] . '"');
readfile($attachment['file_path']);
?>
```

---

## CATEGORY 3: REPORTING & ANALYTICS 🚫

### Missing Feature 3.1: Dashboard Statistics

**Workaround:**

**File:** `Reports/dashboard_stats.php`
```php
<?php
require_once '../config/database.php';

function getDashboardStats($user_id = null) {
    $conn = get_cams_connection();
    
    $stats = [];
    
    // Total cases
    $result = $conn->query("SELECT COUNT(*) as count FROM concerns");
    $stats['total_cases'] = $result->fetch_assoc()['count'];
    
    // Open cases
    $result = $conn->query("SELECT COUNT(*) as count FROM concerns WHERE status = 'Open'");
    $stats['open_cases'] = $result->fetch_assoc()['count'];
    
    // In Progress
    $result = $conn->query("SELECT COUNT(*) as count FROM concerns WHERE status = 'In Progress'");
    $stats['in_progress'] = $result->fetch_assoc()['count'];
    
    // Resolved
    $result = $conn->query("SELECT COUNT(*) as count FROM concerns WHERE status = 'Resolved'");
    $stats['resolved_cases'] = $result->fetch_assoc()['count'];
    
    // Avg resolution time (in days)
    $result = $conn->query("
        SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_days 
        FROM concerns 
        WHERE status = 'Resolved'
    ");
    $stats['avg_resolution_days'] = ceil($result->fetch_assoc()['avg_days'] ?? 0);
    
    // My pending actions (if user_id provided)
    if ($user_id) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM concerns 
            WHERE assigned_to = ? AND status != 'Resolved'
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['my_pending'] = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
    }
    
    $conn->close();
    return $stats;
}

function getCasesByStatus() {
    $conn = get_cams_connection();
    $result = $conn->query("
        SELECT status, COUNT(*) as count 
        FROM concerns 
        GROUP BY status
    ");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['status']] = $row['count'];
    }
    
    $conn->close();
    return $data;
}

function getCasesByEmployee() {
    $conn = get_cams_connection();
    $result = $conn->query("
        SELECT u.username, COUNT(c.id) as case_count 
        FROM users u 
        LEFT JOIN concerns c ON u.id = c.assigned_to 
        GROUP BY u.id, u.username 
        ORDER BY case_count DESC
    ");
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $conn->close();
    return $data;
}
?>
```

**Display in Dashboard:**
```php
<?php
include '../Reports/dashboard_stats.php';
$stats = getDashboardStats($_SESSION['user_id']);
$case_status = getCasesByStatus();
?>

<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-tasks"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Cases</span>
                <span class="info-box-number"><?php echo $stats['total_cases']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-start"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Open Cases</span>
                <span class="info-box-number"><?php echo $stats['open_cases']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Resolved</span>
                <span class="info-box-number"><?php echo $stats['resolved_cases']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-history"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Avg Resolution (days)</span>
                <span class="info-box-number"><?php echo $stats['avg_resolution_days']; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Chart for Case Distribution -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cases by Status</h3>
            </div>
            <div class="card-body">
                <canvas id="caseStatusChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cases by Employee</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Employee</th><th>Cases</th></tr>
                    <?php foreach (getCasesByEmployee() as $emp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($emp['username']); ?></td>
                        <td><?php echo $emp['case_count']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('caseStatusChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($case_status)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($case_status)); ?>,
            backgroundColor: ['#ff6b6b', '#ffd93d', '#52b788', '#4d96ff']
        }]
    }
});
</script>
```

---

## CATEGORY 4: ADVANCED SEARCH & FILTERING 🚫

### Missing Feature 4.1: Advanced Case Search

**File:** `Employee/advanced_search.php`
```php
<?php
session_start();
require_once '../auth/auth_check.php';
require_once '../config/database.php';

$conn = get_cams_connection();

$filters = [
    'status' => $_GET['status'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'assigned_to' => $_GET['assigned_to'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Build query
$query = "SELECT c.*, cl.first_name, cl.last_name, u.username 
          FROM concerns c 
          LEFT JOIN clients cl ON c.client_id = cl.id 
          LEFT JOIN users u ON c.assigned_to = u.id 
          WHERE 1=1";
$params = [];
$types = "";

if ($filters['status']) {
    $query .= " AND c.status = ?";
    $params[] = $filters['status'];
    $types .= "s";
}

if ($filters['date_from']) {
    $query .= " AND c.created_at >= ?";
    $params[] = $filters['date_from'] . " 00:00:00";
    $types .= "s";
}

if ($filters['date_to']) {
    $query .= " AND c.created_at <= ?";
    $params[] = $filters['date_to'] . " 23:59:59";
    $types .= "s";
}

if ($filters['assigned_to']) {
    $query .= " AND c.assigned_to = ?";
    $params[] = $filters['assigned_to'];
    $types .= "i";
}

if ($filters['search']) {
    $query .= " AND (c.ticket_number LIKE ? OR c.subject LIKE ? OR c.description LIKE ? OR cl.first_name LIKE ? OR cl.last_name LIKE ?)";
    $search_term = "%" . $filters['search'] . "%";
    for ($i = 0; $i < 5; $i++) {
        $params[] = $search_term;
        $types .= "s";
    }
}

$query .= " ORDER BY c.created_at DESC LIMIT 50";

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get users for dropdown
$users_result = $conn->query("SELECT id, username FROM users ORDER BY username");
$users = $users_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Advanced Case Search</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <div class="form-group mr-2">
                <label>Status:</label>
                <select name="status" class="form-control ml-2">
                    <option value="">All</option>
                    <option value="Open" <?php echo $filters['status'] === 'Open' ? 'selected' : ''; ?>>Open</option>
                    <option value="In Progress" <?php echo $filters['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Resolved" <?php echo $filters['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>
            
            <div class="form-group mr-2">
                <label>From Date:</label>
                <input type="date" name="date_from" class="form-control ml-2" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
            </div>
            
            <div class="form-group mr-2">
                <label>To Date:</label>
                <input type="date" name="date_to" class="form-control ml-2" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
            </div>
            
            <div class="form-group mr-2">
                <label>Assigned To:</label>
                <select name="assigned_to" class="form-control ml-2">
                    <option value="">Anyone</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo $filters['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group mr-2">
                <label>Search:</label>
                <input type="text" name="search" class="form-control ml-2" placeholder="Ticket #, Subject, Client..." 
                       value="<?php echo htmlspecialchars($filters['search']); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Client</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $case): ?>
                <tr>
                    <td><?php echo htmlspecialchars($case['ticket_number']); ?></td>
                    <td><?php echo htmlspecialchars($case['first_name'] . ' ' . $case['last_name']); ?></td>
                    <td><?php echo htmlspecialchars(substr($case['subject'], 0, 50)); ?></td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $case['status'] === 'Open' ? 'danger' : 
                                 ($case['status'] === 'In Progress' ? 'warning' : 'success');
                        ?>">
                            <?php echo htmlspecialchars($case['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($case['username'] ?? 'Unassigned'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($case['created_at'])); ?></td>
                    <td>
                        <a href="manage_case.php?id=<?php echo $case['id']; ?>" class="btn btn-sm btn-info">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($results)): ?>
        <div class="alert alert-info">No cases found matching your criteria.</div>
        <?php endif; ?>
    </div>
</div>
```

---

## CATEGORY 5: CASE MANAGEMENT ENHANCEMENTS 🚫

### Missing Feature 5.1: Case Assignment

**Enhancement to `Employee/manage_case.php`:**
```php
<?php
// Add this to the case update section

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_to'])) {
    $concern_id = $_POST['concern_id'];
    $assigned_to = $_POST['assign_to'];
    
    $conn = get_cams_connection();
    
    $stmt = $conn->prepare("UPDATE concerns SET assigned_to = ? WHERE id = ?");
    $stmt->bind_param("ii", $assigned_to, $concern_id);
    $stmt->execute();
    $stmt->close();
    
    // Log the action
    $action = "Case assigned to user ID " . $assigned_to;
    log_audit($_SESSION['user_id'], 'ASSIGN_CASE', $concern_id, $action);
    
    $conn->close();
    
    echo "<div class='alert alert-success'>Case assigned successfully</div>";
}
?>

<!-- Assignment Form -->
<div class="card">
    <div class="card-header">
        <h5>Assign Case</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>Assign To:</label>
                <select name="assign_to" class="form-control" required>
                    <option value="">Select Employee</option>
                    <?php 
                    $conn = get_cams_connection();
                    $result = $conn->query("SELECT id, username FROM users WHERE status = 'active' ORDER BY username");
                    while ($user = $result->fetch_assoc()):
                    ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo $case['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </option>
                    <?php endwhile; $conn->close(); ?>
                </select>
            </div>
            <input type="hidden" name="concern_id" value="<?php echo $concern_id; ?>">
            <button type="submit" class="btn btn-primary">Assign</button>
        </form>
    </div>
</div>
```

### Missing Feature 5.2: Case Notes

**File:** `Employee/case_notes.php`
```php
<?php
session_start();
require_once '../auth/auth_check.php';
require_once '../config/database.php';

$concern_id = $_GET['case_id'] ?? 0;
$conn = get_cams_connection();

// Add note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_text'])) {
    $stmt = $conn->prepare("
        INSERT INTO case_notes (concern_id, note_text, is_internal, created_by)
        VALUES (?, ?, ?, ?)
    ");
    
    $is_internal = $_POST['is_internal'] ?? 0;
    $stmt->bind_param("isii", $concern_id, $_POST['note_text'], $is_internal, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    
    echo "<div class='alert alert-success'>Note added</div>";
}

// Get notes
$stmt = $conn->prepare("
    SELECT cn.*, u.username 
    FROM case_notes cn 
    JOIN users u ON cn.created_by = u.id 
    WHERE cn.concern_id = ? 
    ORDER BY cn.created_at DESC
");
$stmt->bind_param("i", $concern_id);
$stmt->execute();
$notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<div class="card">
    <div class="card-header">
        <h5>Case Notes</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="mb-3">
            <div class="form-group">
                <textarea name="note_text" class="form-control" rows="3" placeholder="Add a note..." required></textarea>
            </div>
            <div class="form-check mb-2">
                <input type="checkbox" name="is_internal" value="1" class="form-check-input" id="internalCheck">
                <label class="form-check-label" for="internalCheck">
                    Internal Note (not visible to client)
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Add Note</button>
        </form>
        
        <hr>
        
        <?php foreach ($notes as $note): ?>
        <div class="note mb-3 p-2 border">
            <small class="text-muted">
                <strong><?php echo htmlspecialchars($note['username']); ?></strong> 
                - <?php echo $note['created_at']; ?>
                <?php if ($note['is_internal']): ?>
                <span class="badge badge-warning">Internal</span>
                <?php endif; ?>
            </small>
            <p class="mt-2"><?php echo htmlspecialchars($note['note_text']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
```

---

## CATEGORY 6: AUDIT LOGGING 🚫

### Missing Feature 6.1: Activity Logging

**Add to `config/database.php`:**
```php
<?php
function log_audit($user_id, $action, $entity_id, $details = null) {
    $conn = get_cams_connection();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, entity_id, ip_address, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("isss", $user_id, $action, $entity_id, $ip_address);
    $stmt->execute();
    $stmt->close();
    
    $conn->close();
    
    // Also log to file for backup
    error_log("[AUDIT] User: $user_id | Action: $action | Entity: $entity_id | Details: $details | IP: $ip_address", 0);
}
?>
```

**Use throughout the app:**
```php
<?php
// In create_user.php
log_audit($_SESSION['user_id'], 'CREATE_USER', $new_user_id, "Username: $username, Email: $email");

// In edit_user.php
log_audit($_SESSION['user_id'], 'EDIT_USER', $user_id, "Updated user $username");

// In manage_case.php
log_audit($_SESSION['user_id'], 'UPDATE_CASE', $concern_id, "Status changed to $new_status");
?>
```

**View Audit Logs - `Superadmin/view_logs.php`:**
```php
<?php
session_start();
require_once '../auth/auth_check.php';
if (!in_array('Superadmin', $_SESSION['roles'])) die("Access denied");

require_once '../config/database.php';

$conn = get_cams_connection();
$result = $conn->query("
    SELECT al.*, u.username 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 200
");

$logs = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<table class="table">
    <thead>
        <tr>
            <th>User</th>
            <th>Action</th>
            <th>Entity ID</th>
            <th>IP Address</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?php echo htmlspecialchars($log['username']); ?></td>
            <td><?php echo htmlspecialchars($log['action']); ?></td>
            <td><?php echo $log['entity_id']; ?></td>
            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
            <td><?php echo $log['created_at']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

---

## QUICK CHECKLIST - MISSING FEATURES PRIORITY

### Phase 1: Critical (Must have - 1-2 weeks)
- [ ] Database schema (database.sql)
- [ ] config/database.php with connection functions
- [ ] Basic audit logging
- [ ] Case assignment functionality
- [ ] Case notes/comments

**Estimated Effort:** 40 hours

---

### Phase 2: High Priority (2-3 weeks)
- [ ] Email notifications system
- [ ] File attachments upload
- [ ] Advanced search & filtering
- [ ] Dashboard statistics widgets
- [ ] Notification center

**Estimated Effort:** 60 hours

---

### Phase 3: Medium Priority (3-4 weeks)
- [ ] PDF/Excel export functionality
- [ ] Reports generator
- [ ] User profile management
- [ ] Activity logs viewer
- [ ] System settings page

**Estimated Effort:** 50 hours

---

### Phase 4: Nice-to-Have (2-3 weeks)
- [ ] Mobile responsiveness testing
- [ ] Print-friendly views
- [ ] Two-factor authentication
- [ ] Dark mode theme
- [ ] Performance optimization

**Estimated Effort:** 40 hours

---

## Installation Guide for Dependencies

```bash
cd /workspaces/CAMS

# Install PHPMailer for emails
composer require phpmailer/phpmailer

# Install for PDF generation (optional)
composer require tecnickcom/tcpdf

# Install for Excel export (optional)
composer require phpoffice/phpspreadsheet

# Install for file handling
composer require symfony/http-foundation
```

---

**Last Updated:** August 17, 2026  
**Total Estimated Effort:** ~190 hours (5 weeks)  
**Recommendation:** Start with Phase 1, they are blocking features
