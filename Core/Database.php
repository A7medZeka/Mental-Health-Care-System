<?php

require_once __DIR__ . '/SingletonDatabase.php';

/**
 * Legacy database function - now uses Singleton pattern
 * @deprecated Use SingletonDatabase::getInstance()->getConnection() instead
 */
function getConnection(): PDO {
    return SingletonDatabase::getInstance()->getConnection();
}
?>
