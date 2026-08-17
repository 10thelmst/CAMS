# CAMS Database Migration & Connection Workaround
**Date:** August 17, 2026  
**Purpose:** Connect CAMS to an online MySQL database (PlanetScale recommended)

---

## Step 1: Set Up Online Database

### Using PlanetScale (Recommended)

1. Go to https://planetscale.com
2. Sign up for free account
3. Create new database (e.g., `cams_prod`)
4. Click "Connect" → Select "PHP/MySQLi"
5. Copy connection credentials:
   - **Host**: `aws.connect.psdb.cloud`
   - **Username**: `xxxxx`
   - **Password**: `pscale_pw_xxxxx`
   - **Database**: `cams_prod`

---

## Step 2: Update Database Configuration

### Create/Update `config/database.php`

```php
<?php
// CAMS Database Configuration
// Production (Online) - PlanetScale or similar service

// CAMS Database (Main)
define('CAMS_DB_HOST', 'aws.connect.psdb.cloud');
define('CAMS_DB_USERNAME', 'YOUR_USERNAME');
define('CAMS_DB_PASSWORD', 'YOUR_PASSWORD');
define('CAMS_DB_NAME', 'cams_prod');
define('CAMS_DB_PORT', 3306);
define('CAMS_DB_SSL', true); // PlanetScale requires SSL

// OWWA Database (External Connection)
define('OWWA_DB_HOST', 'localhost');
define('OWWA_DB_USERNAME', 'root');
define('OWWA_DB_PASSWORD', '');
define('OWWA_DB_NAME', 'owwarebs_owwa');
define('OWWA_DB_PORT', 3306);
define('OWWA_DB_SSL', false);

// Connection function with SSL support
function getCAMSConnection() {
    $mysqli = new mysqli(
        CAMS_DB_HOST,
        CAMS_DB_USERNAME,
        CAMS_DB_PASSWORD,
        CAMS_DB_NAME,
        CAMS_DB_PORT
    );

    if ($mysqli->connect_error) {
        die('Database Connection Failed: ' . $mysqli->connect_error);
    }

    // For PlanetScale, enable SSL
    if (CAMS_DB_SSL) {
        $mysqli->query("SET SESSION sql_mode='STRICT_TRANS_TABLES'");
    }

    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

function getOWWAConnection() {
    $mysqli = new mysqli(
        OWWA_DB_HOST,
        OWWA_DB_USERNAME,
        OWWA_DB_PASSWORD,
        OWWA_DB_NAME,
        OWWA_DB_PORT
    );

    if ($mysqli->connect_error) {
        die('OWWA Database Connection Failed: ' . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}
?>
```

---

## Step 3: Migrate Local Database to Online

### Option A: Using MySQL Dump (Command Line)

```bash
# 1. Export your local CAMS database
mysqldump -u root -p cams > cams_backup.sql

# 2. Import to online database
mysql -h aws.connect.psdb.cloud -u YOUR_USERNAME -p cams_prod < cams_backup.sql
```

### Option B: Using phpMyAdmin

1. Open phpMyAdmin for local database
2. Select `cams` database
3. Click "Export" tab
4. Download as `.sql` file
5. Open new phpMyAdmin for online database
6. Click "Import" tab
7. Upload the `.sql` file

### Option C: Using Online Control Panel

1. Access your database service control panel
2. Use their "Import Database" feature
3. Upload your backup file

---

## Step 4: Test Connection

### Create Connection Test File: `setup/test_online_connection.php`

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

echo "<h2>Testing Online Database Connection</h2>";

// Test CAMS connection
try {
    $cams = getCAMSConnection();
    echo "✅ <b>CAMS Connection: SUCCESS</b><br>";
    
    // Check if tables exist
    $result = $cams->query("SHOW TABLES");
    $table_count = $result->num_rows;
    echo "   Tables found: <b>$table_count</b><br>";
    
    $cams->close();
} catch (Exception $e) {
    echo "❌ <b>CAMS Connection: FAILED</b><br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Test OWWA connection (if available)
try {
    $owwa = getOWWAConnection();
    echo "✅ <b>OWWA Connection: SUCCESS</b><br>";
    $owwa->close();
} catch (Exception $e) {
    echo "⚠️ <b>OWWA Connection: FAILED</b><br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

?>
```

**Access:** http://localhost/cams/setup/test_online_connection.php

---

## Step 5: Update All PHP Files

Update files that reference database connection:

### Files to Check:
- `auth/auth_check.php`
- `Superadmin/create_user.php`
- `Superadmin/edit_user.php`
- `Employee/bio_data.php`
- `setup/import_owwa_users.php`

### Pattern to Update:
```php
// OLD
$mysqli = new mysqli('localhost', 'root', '', 'cams');

// NEW
require_once '../config/database.php';
$mysqli = getCAMSConnection();
```

---

## Step 6: SSL Certificate (For PlanetScale)

If you get SSL errors, add this to your connection:

```php
$mysqli->ssl_set(
    null,  // key
    null,  // cert
    '/etc/ssl/certs/ca-certificates.crt',  // ca
    null,  // capath
    null   // cipher
);
```

Or disable SSL verification (not recommended for production):

```php
$mysqli = new mysqli(
    CAMS_DB_HOST,
    CAMS_DB_USERNAME,
    CAMS_DB_PASSWORD,
    CAMS_DB_NAME,
    CAMS_DB_PORT,
    ini_get("mysqli.default_socket")
);
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| "Connection refused" | Check credentials in `config/database.php` |
| "Access denied for user" | Verify username/password from online service |
| "SSL connection error" | Download CA certificate from your DB provider |
| "No tables found" | Run database migration/import scripts |
| "Timeout error" | Database service might be slow; try again |

---

## Quick Checklist

- [ ] Sign up for PlanetScale account
- [ ] Create new database
- [ ] Copy connection credentials
- [ ] Update `config/database.php`
- [ ] Export local database as SQL
- [ ] Import SQL to online database
- [ ] Test connection using `test_online_connection.php`
- [ ] Update all PHP files to use `getCAMSConnection()`
- [ ] Test login & functionality
- [ ] Backup regularly!

---

## Resources

- PlanetScale Docs: https://planetscale.com/docs
- MySQL SSL: https://dev.mysql.com/doc/refman/8.0/en/using-ssl.html
- Alternative Services: Railway, DigitalOcean, AWS RDS

**Last Updated:** August 17, 2026
