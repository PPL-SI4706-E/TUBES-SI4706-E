<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::ensureMysqlTestingDatabaseExists();
    }

    private static function ensureMysqlTestingDatabaseExists(): void
    {
        $connection = $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: null;
        $database = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: null;

        if ($connection !== 'mysql' || blank($database)) {
            return;
        }

        if (! str_ends_with($database, '_testing')) {
            throw new \RuntimeException('Database testing MySQL harus berakhiran _testing agar aman untuk RefreshDatabase.');
        }

        if (! preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            throw new \RuntimeException('Nama database testing hanya boleh berisi huruf, angka, dan underscore.');
        }

        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
        $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

        $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Membuat database test otomatis agar demo tidak perlu setup manual.
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
}
