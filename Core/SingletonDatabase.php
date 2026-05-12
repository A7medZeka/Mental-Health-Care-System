<?php
/*
 * Singleton Pattern Implementation for Database Connection
 * Ensures only one database connection exists throughout the application
 */
class SingletonDatabase {
    private static $instance = null;
    private $connection;
    private $host = 'localhost';
    private $dbname = 'holisticmentalhealth';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $this->host, $this->dbname, $this->charset
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log('[DB] Connection failed: ' . $e->getMessage());
            $_SESSION['error_message'] = 'A server error occurred. Please try again later.';
            header('Location: ../Views/Auth/login.php');
            exit();
        }
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Get the singleton instance
     * @return SingletonDatabase
     */
    public static function getInstance(): SingletonDatabase {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the database connection
     * @return PDO
     */
    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * Execute a prepared statement
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function execute(string $sql, array $params = []): PDOStatement {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): void {
        $this->connection->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): void {
        $this->connection->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): void {
        $this->connection->rollback();
    }

    /**
     * Get the last inserted ID
     * @return string
     */
    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }
}
