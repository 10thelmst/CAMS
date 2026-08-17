# CAMS Security & Flaws Workaround Guide
**Date:** August 17, 2026  
**Purpose:** Address critical issues and provide fixes/workarounds for CAMS vulnerabilities

---

## 🔴 CRITICAL ISSUES & FIXES

### Issue 1: Missing Database Configuration File

**Problem:** Code references `config/database.php` but file doesn't exist. Functions `get_cams_connection()` and `get_owwa_connection()` are called throughout the project but undefined.

**Files Affected:**
- `index.php`
- `change_password.php`
- `Employee/create_client.php`
- All Superadmin files

**Workaround:** Create the config file with proper connection functions

**File:** `config/database.php`
```php
<?php
/**
 * Database Configuration for CAMS
 * Centralized connection management
 */

// CAMS Database - Main Application
define('CAMS_DB_HOST', 'localhost');
define('CAMS_DB_USERNAME', 'root');
define('CAMS_DB_PASSWORD', '');
define('CAMS_DB_NAME', 'cams');

// OWWA Database - External
define('OWWA_DB_HOST', 'localhost');
define('OWWA_DB_USERNAME', 'root');
define('OWWA_DB_PASSWORD', '');
define('OWWA_DB_NAME', 'owwarebs_owwa');

/**
 * Get CAMS Database Connection
 * @return mysqli
 */
function get_cams_connection() {
    $conn = new mysqli(
        CAMS_DB_HOST,
        CAMS_DB_USERNAME,
        CAMS_DB_PASSWORD,
        CAMS_DB_NAME
    );
    
    if ($conn->connect_error) {
        error_log('CAMS DB Error: ' . $conn->connect_error);
        die('Database connection error. Please contact administrator.');
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Get OWWA Database Connection
 * @return mysqli
 */
function get_owwa_connection() {
    $conn = new mysqli(
        OWWA_DB_HOST,
        OWWA_DB_USERNAME,
        OWWA_DB_PASSWORD,
        OWWA_DB_NAME
    );
    
    if ($conn->connect_error) {
        error_log('OWWA DB Error: ' . $conn->connect_error);
        die('OWWA database connection error. Please contact administrator.');
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
```

---

### Issue 2: Hardcoded Database Credentials

**Problem:** Database credentials hardcoded in multiple files (`Superadmin/create_user.php`, `Superadmin/edit_user.php`, `Superadmin/users.php`)

**Current Code (INSECURE):**
```php
$conn = new mysqli('localhost', 'root', '', 'cams');
```

**Workaround:** Replace all instances with centralized configuration

**Search & Replace Pattern:**
```php
// OLD - In Superadmin/create_user.php, edit_user.php, users.php
$conn = new mysqli('localhost', 'root', '', 'cams');

// NEW
require_once '../config/database.php';
$conn = get_cams_connection();
```

---

### Issue 3: Publicly Accessible Setup Scripts

**Problem:** `/setup/` directory contains dangerous scripts like:
- `setup_database.php` - Can reinitialize database
- `reset_user_password.php` - Can reset any password
- `import_owwa_users.php` - Can import unlimited users
- `remove_all_users_except_admin.php` - Can delete all users

**Workaround: Add Authentication Check to All Setup Files**

**File:** `setup/.htaccess` (First defense - Apache)
```apache
# Deny direct access to setup directory
<FilesMatch "\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

**Better Workaround: Add PHP Authentication Check**

Add this at the TOP of each setup file (`setup_database.php`, `reset_user_password.php`, etc.):

```php
<?php
/**
 * SECURITY: Only allow access if user is authenticated admin
 * or from localhost (development)
 */

// Check if accessing from web or CLI
$is_cli = php_sapi_name() === 'cli';
$is_localhost = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === 'localhost');

if (!$is_cli && !$is_localhost) {
    // Check if authenticated as admin
    session_start();
    
    if (empty($_SESSION['user_id']) || empty($_SESSION['roles']) || 
        !in_array('Superadmin', $_SESSION['roles'])) {
        http_response_code(403);
        die('Access Denied. Setup scripts are only accessible to authenticated Superadmins from localhost.');
    }
}

// Continue with setup script...
?>
```

---

### Issue 4: User Enumeration Vulnerability

**Problem:** Login page reveals if user exists

**Current Code (INSECURE):**
```php
if ($result->num_rows == 0) {
    echo "User not found. If this is your first time logging in, use password123";
}
```

**Workaround: Generic Error Messages**

**File:** `index.php` (around line 159)
```php
<?php
// OLD - Information disclosure
if ($result->num_rows == 0) {
    echo "User not found. If this is your first time logging in, use password123";
}

// NEW - Generic message
else {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Login success
    } else {
        $error = "Invalid username/email or password.";
    }
}

// If user not found, use same generic error:
if ($result->num_rows == 0) {
    $error = "Invalid username/email or password."; // Same message as wrong password
}

echo $error;
?>
```

---

### Issue 5: Database Errors Exposed to Users

**Problem:** Connection errors display sensitive information

**Current Code (INSECURE):**
```php
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Shows DB details
}
```

**Workaround: Log Errors Server-Side**

```php
<?php
if ($conn->connect_error) {
    // Log the error (invisible to user)
    error_log("Database connection failed: " . $conn->connect_error, 0);
    
    // Show generic message to user
    die("A system error occurred. Please try again later or contact support.");
}
?>
```

---

## 🟠 HIGH SEVERITY ISSUES & FIXES

### Issue 6: Missing CSRF Protection

**Problem:** No CSRF tokens on any forms. Attackers can trick users into creating/deleting users.

**Workaround: Implement CSRF Token System**

**Helper Functions to Add to `config/database.php`:**
```php
<?php
/**
 * Generate CSRF Token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
```

**Add to Forms:**
```html
<!-- In Superadmin/create_user.php form -->
<form method="POST">
    <?php
    session_start();
    $csrf_token = generate_csrf_token();
    ?>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    
    <!-- Rest of form fields -->
    <input type="text" name="username" required>
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    
    <button type="submit">Create User</button>
</form>
```

**Validate in Processing:**
```php
<?php
// At top of form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Security validation failed. Please try again.');
    }
    
    // Continue with user creation
}
?>
```

---

### Issue 7: Unprotected Delete Operation

**Problem:** DELETE triggered via GET request with no CSRF token

**Current Code (INSECURE):**
```php
if (isset($_GET['delete'])) {
    // Deletes user - anyone with the link can do this!
}
```

**Workaround: Move to POST with CSRF Token**

**In Superadmin/users.php:**
```php
<?php
// Remove GET-based delete
// if (isset($_GET['delete'])) { ... } // DELETE THIS

// Add POST-based delete with CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Security validation failed.');
    }
    
    $user_id = intval($_POST['delete_user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    echo "User deleted successfully.";
}
?>
```

**In HTML Table:**
```html
<?php foreach ($users as $user): ?>
<tr>
    <td><?php echo htmlspecialchars($user['username']); ?></td>
    <td><?php echo htmlspecialchars($user['email']); ?></td>
    <td>
        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
```

---

### Issue 8: Weak Default Password

**Problem:** Hardcoded default password `"password123"` is weak and easily guessable

**Workaround: Require Password Change on First Login**

**File:** `config/database.php` - Add helper function
```php
<?php
function check_password_change_required($user_id) {
    $conn = get_cams_connection();
    $stmt = $conn->prepare("SELECT password_change_required FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return ($user['password_change_required'] == 1);
}
?>
```

**File:** `auth/auth_check.php` - Add redirect check
```php
<?php
// After successful login, check if password change is required
if (check_password_change_required($_SESSION['user_id'])) {
    header('Location: /cams/change_password.php?force=1');
    exit;
}
?>
```

**Database Schema Addition:**
```sql
ALTER TABLE users ADD COLUMN password_change_required BOOLEAN DEFAULT 1;

-- Set to 1 for all new OWWA imports
UPDATE users SET password_change_required = 1 WHERE id > 0;
```

---

### Issue 9: No Brute Force Protection

**Problem:** Unlimited login attempts allowed. Attackers can guess passwords endlessly.

**Workaround: Implement Login Rate Limiting**

**File:** `config/database.php` - Add function
```php
<?php
function check_login_attempts($identifier) {
    // Get login attempts from session/cache
    $attempts_key = 'login_attempts_' . md5($identifier);
    
    if (!isset($_SESSION[$attempts_key])) {
        $_SESSION[$attempts_key] = 0;
    }
    
    return $_SESSION[$attempts_key];
}

function increment_login_attempts($identifier) {
    $attempts_key = 'login_attempts_' . md5($identifier);
    $_SESSION[$attempts_key] = (isset($_SESSION[$attempts_key]) ? $_SESSION[$attempts_key'] + 1 : 1);
    
    // Reset after 15 minutes
    $_SESSION[$attempts_key . '_time'] = time();
}

function reset_login_attempts($identifier) {
    $attempts_key = 'login_attempts_' . md5($identifier);
    unset($_SESSION[$attempts_key]);
    unset($_SESSION[$attempts_key . '_time']);
}
?>
```

**In Login Form (index.php):**
```php
<?php
session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Check login attempts
$attempts = check_login_attempts($username);
if ($attempts >= 5) {
    die("Too many login attempts. Please try again in 15 minutes.");
}

// Verify login...
if (/* login fails */) {
    increment_login_attempts($username);
    echo "Invalid credentials. (" . ($attempts + 1) . "/5 attempts)";
} else {
    reset_login_attempts($username);
    // Login success...
}
?>
```

---

## 🟡 MEDIUM SEVERITY ISSUES & FIXES

### Issue 10: Weak Password Policy

**Problem:** Minimum 6 characters with no complexity requirements

**Workaround: Enforce Strong Passwords**

**File:** `config/database.php` - Add validation
```php
<?php
function validate_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain number";
    }
    
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors[] = "Password must contain special character (!@#$%^&*)";
    }
    
    return $errors; // Empty array = valid
}
?>
```

**Use in Password Forms:**
```php
<?php
$password = $_POST['password'] ?? '';
$errors = validate_password_strength($password);

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p class='error'>" . htmlspecialchars($error) . "</p>";
    }
} else {
    // Password is strong, hash and save it
    $hashed = password_hash($password, PASSWORD_DEFAULT);
}
?>
```

---

### Issue 11: No Session Regeneration at Login

**Problem:** Session ID not regenerated when user logs in. Allows session fixation attacks.

**Workaround: Regenerate Session on Login**

**In index.php (login processing):**
```php
<?php
session_start();

// ... username/password validation ...

if ($login_successful) {
    // IMPORTANT: Regenerate session to prevent fixation
    session_regenerate_id(true); // true = delete old session file
    
    // Set user info
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['roles'] = $roles;
    
    header('Location: appropriate_dashboard.php');
    exit;
}
?>
```

---

### Issue 12: Session Cookies Missing Security Flags

**Problem:** Session cookies missing HTTPOnly and Secure flags. XSS attacks can steal cookies.

**Workaround: Set Security Flags**

**Add to top of every file using sessions (or create a session bootstrap file):**
```php
<?php
// Set cookie parameters BEFORE session_start()
ini_set('session.cookie_httponly', 1);      // Prevent JavaScript access
ini_set('session.cookie_secure', 0);        // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
ini_set('session.use_strict_mode', 1);      // Reject invalid session IDs

session_start();
?>
```

**Or in `php.ini`:**
```ini
session.cookie_httponly = 1
session.cookie_samesite = Strict
session.use_strict_mode = 1
```

---

### Issue 13: No Audit Logging

**Problem:** No record of admin actions. Can't detect unauthorized changes.

**Workaround: Add Simple Audit Log**

**Database Schema:**
```sql
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(255),
    target_user_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Helper Function (config/database.php):**
```php
<?php
function log_audit($user_id, $action, $target_user_id = null, $details = null) {
    $conn = get_cams_connection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $conn->prepare(
        "INSERT INTO audit_log (user_id, action, target_user_id, details, ip_address) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isiss", $user_id, $action, $target_user_id, $details, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
?>
```

**Usage in Superadmin/create_user.php:**
```php
<?php
// After successful user creation
log_audit(
    $_SESSION['user_id'],
    'CREATE_USER',
    $new_user_id,
    "Username: $username, Email: $email"
);
?>
```

---

### Issue 14: PDO vs MySQLi Inconsistency

**Problem:** Mixed database libraries. Employee/create_client.php uses PDO, others use MySQLi.

**Workaround: Standardize on MySQLi**

**In Employee/create_client.php:**
Replace PDO code:
```php
// OLD - PDO
$pdo = new PDO('mysql:host=localhost;dbname=cams', 'root', '');
$stmt = $pdo->prepare("INSERT INTO ...");
```

With MySQLi:
```php
// NEW - MySQLi
require_once '../config/database.php';
$conn = get_cams_connection();
$stmt = $conn->prepare("INSERT INTO ...");
```

---

## Quick Fix Checklist

### IMMEDIATE (Today)
- [ ] Create `config/database.php` with connection functions
- [ ] Add authentication check to all `/setup/*.php` files
- [ ] Replace generic error messages in login form
- [ ] Add CSRF token generation functions

### THIS WEEK
- [ ] Add CSRF tokens to all forms
- [ ] Move DELETE operations to POST with CSRF
- [ ] Implement login rate limiting
- [ ] Regenerate session ID on login
- [ ] Set session cookie security flags

### THIS MONTH
- [ ] Enforce strong password policy
- [ ] Implement audit logging
- [ ] Standardize on MySQLi
- [ ] Add password change requirement on first login
- [ ] Move setup directory outside web root

### ONGOING
- [ ] Regular security audits
- [ ] Keep dependencies updated
- [ ] Monitor audit logs
- [ ] Update documentation

---

## Testing Checklist

```bash
# Test 1: Database Connection
- [ ] Can user login successfully?
- [ ] Can create new user?
- [ ] Can edit user?
- [ ] Can delete user?

# Test 2: Security
- [ ] CSRF tokens present in forms?
- [ ] Can't access setup files directly?
- [ ] Login error message generic?
- [ ] Session regenerates on login?

# Test 3: Password Policy
- [ ] Weak passwords rejected?
- [ ] Strong passwords accepted?
- [ ] Password change required on first login?

# Test 4: Rate Limiting
- [ ] 5+ failed attempts blocks login?
- [ ] Lockout message displays?
```

---

**Last Updated:** August 17, 2026
**Next Review:** After implementing all CRITICAL fixes
