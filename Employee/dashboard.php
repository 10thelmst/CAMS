<?php
require_once '../auth/auth_check.php';

// Check if user has employee role
if (!has_role('employee')) {
    header('Location: ../index.php?unauthorized=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .welcome { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .logout { padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Employee Dashboard</h1>
            <a href="/cams/auth/logout.php" class="logout">Logout</a>
        </div>
        <div class="welcome">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>You are logged in as: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
            <p>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
        </div>
    </div>
</body>
</html>
