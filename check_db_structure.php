<?php
require_once 'Core/SingletonDatabase.php';
$db = SingletonDatabase::getInstance()->getConnection();

echo "=== USERS TABLE STRUCTURE ===\n";
$stmt = $db->query("DESCRIBE users");
while ($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== THERAPISTS TABLE STRUCTURE ===\n";
$stmt = $db->query("DESCRIBE therapists");
while ($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
