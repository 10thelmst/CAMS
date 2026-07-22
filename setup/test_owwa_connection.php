<?php
require_once '../config/database.php';

echo "<h1>OWWA Database Connection Test</h1>";

try {
    $owwa_conn = get_owwa_connection();
    echo "<p style='color: green;'>✓ Connected to OWWA database successfully</p>";
    
    // Check if table exists
    $table_check = $owwa_conn->query("SHOW TABLES LIKE 'dtr_employeescopy'");
    if ($table_check->num_rows > 0) {
        echo "<p style='color: green;'>✓ Table 'dtr_employeescopy' exists</p>";
        
        // Check table structure
        $columns = $owwa_conn->query("DESCRIBE dtr_employeescopy");
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1'><tr><th>Column</th><th>Type</th></tr>";
        while ($col = $columns->fetch_assoc()) {
            echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
        }
        echo "</table>";
        
        // Show sample data
        $sample = $owwa_conn->query("SELECT * FROM dtr_employeescopy LIMIT 5");
        echo "<h3>Sample Data (first 5 rows):</h3>";
        echo "<table border='1'>";
        if ($sample->num_rows > 0) {
            echo "<tr>";
            foreach ($sample->fetch_fields() as $field) {
                echo "<th>" . $field->name . "</th>";
            }
            echo "</tr>";
            $sample->data_seek(0);
            while ($row = $sample->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='10'>No data found</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Table 'dtr_employeescopy' does not exist</p>";
        echo "<p>Available tables:</p>";
        $tables = $owwa_conn->query("SHOW TABLES");
        echo "<ul>";
        while ($table = $tables->fetch_array()) {
            echo "<li>" . $table[0] . "</li>";
        }
        echo "</ul>";
    }
    
    $owwa_conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
