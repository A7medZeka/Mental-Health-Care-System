<?php

require_once __DIR__ . '/SingletonDatabase.php';


function getConnection(): PDO {
    return SingletonDatabase::getInstance()->getConnection();
}
?>
