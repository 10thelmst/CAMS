<?php
// Database setup script
$host = 'localhost';
$username = 'root';
$password = '';

// Connect to MySQL without selecting database
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read the SQL file
$sql_file = __DIR__ . '/../database.sql';
if (!file_exists($sql_file)) {
    die("SQL file not found: " . $sql_file);
}

$sql = file_get_contents($sql_file);

// Split SQL into individual statements
$statements = explode(';', $sql);

$errors = [];
$success_count = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) {
        continue;
    }
    
    // Skip comments
    if (strpos($statement, '--') === 0 || strpos($statement, '/*') === 0) {
        continue;
    }
    
    if ($conn->query($statement)) {
        $success_count++;
    } else {
        $errors[] = $conn->error;
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Setup</h1>
        
        <?php if (empty($errors)): ?>
            <div class="success">
                <strong>Success!</strong> Database setup completed successfully.<br>
                <?php echo $success_count; ?> statements executed.
            </div>
            <div class="info">
                <strong>Test Credentials:</strong><br>
                Email: admin@cams.com<br>
                Password: password123
            </div>
        <?php else: ?>
            <div class="error">
                <strong>Errors occurred:</strong><br>
                <?php foreach ($errors as $error): ?>
                    <?php echo htmlspecialchars($error); ?><br>
                <?php endforeach; ?>
            </div>
            <div class="info">
                <?php echo $success_count; ?> statements executed successfully.
            </div>
        <?php endif; ?>
        
        <a href="../index.php">Go to Login Page</a>
    </div>
</body>
</html>
