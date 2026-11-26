<?php
declare(strict_types=1);
// CLI-only additive migration. Run from the project root with php.exe.
if(PHP_SAPI!=='cli'){http_response_code(403);exit('CLI only');}
require_once __DIR__.'/../app/Core/Database.php';
$pdo=\Database::connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (setting_key VARCHAR(100) NOT NULL PRIMARY KEY, setting_value TEXT NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$pdo->exec("CREATE TABLE IF NOT EXISTS cms_pages (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, title VARCHAR(180) NOT NULL, slug VARCHAR(180) NOT NULL UNIQUE, body LONGTEXT NULL, status ENUM('draft','published') NOT NULL DEFAULT 'draft', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_cms_status(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Management migration complete. Existing tables and records were not reset.\n";
