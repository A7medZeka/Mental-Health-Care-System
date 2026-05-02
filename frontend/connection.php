<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'holisticmentalhealth');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getConnection(): PDO {
    static $connection = null;

    if ($connection === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('[DB] Connection failed: ' . $e->getMessage());
            // Redirect gracefully; never expose raw errors to the browser
            $_SESSION['error_message'] = 'A server error occurred. Please try again later.';
            header('Location: index.php');
            exit();
        }
    }

    return $connection;
}