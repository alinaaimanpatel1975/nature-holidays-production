<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI access only.');
}

require_once __DIR__ . '/../app/Core/Database.php';

try {
    $pdo = Database::connect();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS schema_migrations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT UNSIGNED NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $files = glob(__DIR__ . '/migrations/*.php');
    sort($files);

    $batch = (int) $pdo->query(
        "SELECT COALESCE(MAX(batch), 0) + 1 FROM schema_migrations"
    )->fetchColumn();

    $executed = 0;

    foreach ($files as $file) {
        $name = basename($file);

        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM schema_migrations WHERE migration = ?"
        );
        $check->execute([$name]);

        if ((int) $check->fetchColumn() > 0) {
            echo "SKIPPED: {$name}\n";
            continue;
        }

        $statements = require $file;

        if (!is_array($statements)) {
            throw new RuntimeException("Invalid migration: {$name}");
        }

        foreach ($statements as $sql) {
            if (!is_string($sql)) {
                throw new RuntimeException("Invalid SQL in {$name}");
            }

            $pdo->exec($sql);
        }

        $record = $pdo->prepare(
            "INSERT INTO schema_migrations (migration, batch) VALUES (?, ?)"
        );

        $record->execute([$name, $batch]);

        echo "SUCCESS: {$name}\n";
        $executed++;
    }

    echo "\nCompleted. {$executed} migration(s) executed.\n";

} catch (Throwable $error) {
    fwrite(STDERR, "\nMIGRATION FAILED: " . $error->getMessage() . "\n");
    exit(1);
}