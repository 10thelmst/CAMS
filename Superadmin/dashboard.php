<?php
require_once '../auth/auth_check.php';

// Check if user has superadmin role
if (!has_role('superadmin')) {
    header('Location: ../index.php?unauthorized=1');
    exit();
}

$content = '
    <div class="content-header">
        <h1>Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Welcome, ' . htmlspecialchars($_SESSION['username']) . '!</h5>
                    <p class="card-text">You are logged in as: <strong>' . htmlspecialchars($_SESSION['role']) . '</strong></p>
                    <p class="card-text">Email: ' . htmlspecialchars($_SESSION['email']) . '</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="users.php" class="btn btn-success btn-block">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="create_user.php" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i> Create User
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
';

$title = 'Superadmin Dashboard';
$active_page = 'dashboard';

require_once 'layout.php';
?>
