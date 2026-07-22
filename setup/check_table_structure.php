<?php
require_once '../config/database.php';

$conn = get_cams_connection();

echo "<h1>Table Structure Check</h1>";

// Check employee_personal_data structure
echo "<h2>employee_personal_data</h2>";
$result = $conn->query("DESCRIBE employee_personal_data");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check employee_bio_data structure
echo "<h2>employee_bio_data</h2>";
$result = $conn->query("DESCRIBE employee_bio_data");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
