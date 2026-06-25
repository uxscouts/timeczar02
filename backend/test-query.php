<?php
header('Content-Type: application/json');

class DatabaseQueryTester {
    private $pdo;

    public function __construct() {
        $host     = getenv('DB_HOST') ?: 'mysql';
        $db       = getenv('DB_DATABASE') ?: 'my_database';
        $user     = getenv('DB_USERNAME') ?: 'dev_user';
        $pass     = getenv('DB_PASSWORD') ?: 'dev_password';
        $port     = getenv('DB_PORT') ?: '3306';
        
        $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, 
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Connection failed", "debug" => $e->getMessage()]);
            exit;
        }
    }

    public function runSupercalifragilisticTest() {
        try {
            // 1. Create a secure users table if it doesn't exist
            $createTableSQL = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;";
            $this->pdo->exec($createTableSQL);

            // 2. Insert a sample user safely using a Prepared Statement (SQL Injection Protection)
            // We use standard PASSWORD_DEFAULT which applies secure, modern bcrypt hashing
            $mockUsername = 'coding_at_work_dev';
            $mockPassword = 'SuperSecurePassword123!';
            $hashedPassword = password_hash($mockPassword, PASSWORD_DEFAULT);

            $insertSQL = "INSERT IGNORE INTO users (username, password_hash) VALUES (:username, :password)";
            $stmt = $this->pdo->prepare($insertSQL);
            $stmt->execute([
                ':username' => $mockUsername,
                ':password' => $hashedPassword
            ]);

            // 3. Query the data back out to prove it exists
            $selectSQL = "SELECT id, username, password_hash, created_at FROM users WHERE username = :username";
            $queryStmt = $this->pdo->prepare($selectSQL);
            $queryStmt->execute([':username' => $mockUsername]);
            $userRecord = $queryStmt->fetch();

            // 4. Output the victorious results
            echo json_encode([
                "status" => "success",
                "message" => "Table created, data inserted, and queried flawlessly!",
                "queried_data" => $userRecord
            ], JSON_PRETTY_PRINT);

        } catch (\PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database operations failed", "debug" => $e->getMessage()]);
        }
    }
}

$tester = new DatabaseQueryTester();
$tester->runSupercalifragilisticTest();
