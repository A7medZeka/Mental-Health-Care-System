<?php
require 'Core/SingletonDatabase.php';
$db = SingletonDatabase::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE therapist_reviews');
echo "therapist_reviews table structure:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $db->query('DESCRIBE therapists');
echo "\ntherapists table structure:\n";
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
