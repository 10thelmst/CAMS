<?php
require_once '../config/database.php';

// Get database connections
$cams_conn = get_cams_connection();
$owwa_conn = get_owwa_connection();

$imported_count = 0;
$updated_count = 0;
$error_count = 0;
$errors = array();

// Fetch users from OWWA database
$owwa_query = "SELECT code_name FROM dtr_employeescopy WHERE code_name IS NOT NULL AND code_name != ''";
$owwa_result = $owwa_conn->query($owwa_query);

if ($owwa_result) {
    while ($owwa_user = $owwa_result->fetch_assoc()) {
        $username = trim($owwa_user['code_name']);
        
        // Skip if username is empty
        if (empty($username)) {
            continue;
        }
        
        // Generate email from username
        $email = $username . '@owwa.gov.ph';
        
        // Check if user already exists in CAMS
        $check_stmt = $cams_conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // User exists, update if needed
            $existing_user = $check_result->fetch_assoc();
            $user_id = $existing_user['id'];
            
            // Update username and email if they changed
            $update_stmt = $cams_conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $username, $email, $user_id);
            
            if ($update_stmt->execute()) {
                $updated_count++;
            } else {
                $errors[] = "Error updating user $username: " . $cams_conn->error;
                $error_count++;
            }
            
            $update_stmt->close();
        } else {
            // User doesn't exist, create new user with default password
            $default_password = 'password123';
            $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
            $default_role = 'employee';
            $default_status = 'active';
            $password_change_required = 1;
            
            // Insert new user
            $insert_stmt = $cams_conn->prepare("INSERT INTO users (username, email, password, role, status, password_change_required) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssi", $username, $email, $hashed_password, $default_role, $default_status, $password_change_required);
            
            if ($insert_stmt->execute()) {
                $user_id = $cams_conn->insert_id;
                
                // Insert default role into user_roles table
                $role_stmt = $cams_conn->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, ?)");
                $role_stmt->bind_param("is", $user_id, $default_role);
                $role_stmt->execute();
                $role_stmt->close();
                
                $imported_count++;
            } else {
                $errors[] = "Error importing user $username: " . $cams_conn->error;
                $error_count++;
            }
            
            $insert_stmt->close();
        }
        
        $check_stmt->close();
    }
} else {
    $errors[] = "Error fetching users from OWWA database: " . $owwa_conn->error;
    $error_count++;
}

$cams_conn->close();
$owwa_conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import OWWA Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3>Import OWWA Users</h3>
            </div>
            <div class="card-body">
                <?php if ($error_count > 0): ?>
                    <div class="alert alert-danger">
                        <strong>Errors occurred:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <h4>Import Summary</h4>
                    <ul>
                        <li><strong>New users imported:</strong> <?php echo $imported_count; ?></li>
                        <li><strong>Existing users updated:</strong> <?php echo $updated_count; ?></li>
                        <li><strong>Errors:</strong> <?php echo $error_count; ?></li>
                    </ul>
                    <p><strong>Note:</strong> All imported users have default password: <code>password123</code></p>
                </div>
                
                <a href="../Superadmin/users.php" class="btn btn-primary">Go to Users Management</a>
                <a href="import_owwa_users.php" class="btn btn-secondary">Run Import Again</a>
            </div>
        </div>
    </div>
</body>
</html>
