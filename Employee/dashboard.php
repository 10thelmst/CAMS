<?php
require_once '../auth/auth_check.php';

// Check if user has employee role
if (!has_role('employee')) {
    header('Location: ../index.php?unauthorized=1');
    exit();
}

$title = 'Employee Dashboard';
$active_page = 'dashboard';

$content = '
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Welcome, ' . htmlspecialchars($_SESSION['username']) . '!</h3>
        </div>
        <div class="card-body">
            <p>You are logged in as: <strong>' . htmlspecialchars(implode(', ', array_map('ucfirst', $_SESSION['roles']))) . '</strong></p>
            <p>Email: ' . htmlspecialchars($_SESSION['email']) . '</p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> This is the Employee Dashboard. Use the sidebar menu to navigate.
            </div>
        </div>
    </div>
';

require_once 'layout.php';
?>
