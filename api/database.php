<?php
require_once __DIR__ . '/config.php';

class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS leads (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name       VARCHAR(255)  NOT NULL,
                phone      VARCHAR(50)   NOT NULL,
                email      VARCHAR(255)  DEFAULT NULL,
                message    TEXT          NOT NULL,
                source     VARCHAR(100)  DEFAULT 'Сайт',
                ip         VARCHAR(45)   DEFAULT NULL,
                created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function saveLead(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO leads (name, phone, email, message, source, ip)
            VALUES (:name, :phone, :email, :message, :source, :ip)
        ");
        $stmt->execute([
            ':name'    => $data['name'],
            ':phone'   => $data['phone'],
            ':email'   => $data['email'] ?? null,
            ':message' => $data['message'],
            ':source'  => $data['source'] ?? 'Сайт',
            ':ip'      => $data['ip'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getAllLeads(): array
    {
        return $this->pdo
            ->query("SELECT * FROM leads ORDER BY created_at DESC")
            ->fetchAll();
    }
}
