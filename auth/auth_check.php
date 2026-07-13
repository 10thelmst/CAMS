<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    // Destroy any existing session data
    session_unset();
    session_destroy();
    
    // Redirect to login page
    header('Location: ../index.php');
    exit();
}

// Optional: Check session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: ../index.php?timeout=1');
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Optional: Regenerate session ID periodically for security
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 600) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Function to check if user has specific role
function has_role($required_role) {
    // Check if user has roles array (new system) or single role (old system)
    if (isset($_SESSION['roles']) && is_array($_SESSION['roles'])) {
        return in_array($required_role, $_SESSION['roles']);
    }
    // Fallback to old single role system for backward compatibility
    return isset($_SESSION['role']) && $_SESSION['role'] === $required_role;
}

// Function to check if user has any of the specified roles
function has_any_role($roles) {
    // Check if user has roles array (new system) or single role (old system)
    if (isset($_SESSION['roles']) && is_array($_SESSION['roles'])) {
        return !empty(array_intersect($_SESSION['roles'], $roles));
    }
    // Fallback to old single role system for backward compatibility
    if (!isset($_SESSION['role'])) {
        return false;
    }
    return in_array($_SESSION['role'], $roles);
}

// Function to get current user role (primary role)
function get_user_role() {
    // Return primary role from roles array if available
    if (isset($_SESSION['roles']) && is_array($_SESSION['roles']) && !empty($_SESSION['roles'])) {
        return $_SESSION['roles'][0];
    }
    // Fallback to old single role system
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Function to get all user roles
function get_user_roles() {
    // Return roles array if available
    if (isset($_SESSION['roles']) && is_array($_SESSION['roles'])) {
        return $_SESSION['roles'];
    }
    // Fallback to old single role system - convert to array
    if (isset($_SESSION['role'])) {
        return array($_SESSION['role']);
    }
    return array();
}

// Function to get current user info
function get_user_info() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => get_user_role(),
        'roles' => get_user_roles()
    ];
}
?>
