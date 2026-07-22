<?php
// Start session
session_start();

// Set logout message before destroying session
$logout_message = 'You have been successfully logged out.';

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Start new session for logout message
session_start();
$_SESSION['logout_message'] = $logout_message;

// Redirect to login page
header('Location: ../index.php');
exit();
?>
