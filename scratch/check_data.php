<?php
require 'Core/SingletonDatabase.php';
$db = SingletonDatabase::getInstance()->getConnection();
$stmt = $db->query('SELECT * FROM therapist_matches LIMIT 10');
echo "therapist_matches data:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $db->query('SELECT * FROM therapist_reviews LIMIT 10');
echo "\ntherapist_reviews data:\n";
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
