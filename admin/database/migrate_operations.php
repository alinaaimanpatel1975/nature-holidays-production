<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/Core/Database.php';
$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$ddl = [
"CREATE TABLE IF NOT EXISTS suppliers (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(160) NOT NULL, supplier_type VARCHAR(60) NOT NULL DEFAULT 'other',
 contact_name VARCHAR(120) NULL, email VARCHAR(190) NULL, phone VARCHAR(40) NULL,
 city VARCHAR(100) NULL, state VARCHAR(100) NULL, country VARCHAR(100) NOT NULL DEFAULT 'India',
 services TEXT NULL, notes TEXT NULL, status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_suppliers_name(name), INDEX idx_suppliers_status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS quotations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, quotation_number VARCHAR(40) NOT NULL UNIQUE,
 customer_id BIGINT UNSIGNED NULL, enquiry_id BIGINT UNSIGNED NULL, package_id BIGINT UNSIGNED NULL,
 title VARCHAR(190) NOT NULL, currency CHAR(3) NOT NULL DEFAULT 'INR',
 subtotal DECIMAL(12,2) NOT NULL DEFAULT 0, discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
 tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0, total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
 valid_until DATE NULL, status ENUM('draft','sent','accepted','rejected','expired') NOT NULL DEFAULT 'draft',
 notes TEXT NULL, created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_quotations_customer(customer_id), INDEX idx_quotations_status(status),
 CONSTRAINT fk_ops_quote_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
 CONSTRAINT fk_ops_quote_enquiry FOREIGN KEY(enquiry_id) REFERENCES enquiries(id) ON DELETE SET NULL,
 CONSTRAINT fk_ops_quote_package FOREIGN KEY(package_id) REFERENCES packages(id) ON DELETE SET NULL,
 CONSTRAINT fk_ops_quote_user FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS quotation_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, quotation_id BIGINT UNSIGNED NOT NULL,
 description VARCHAR(255) NOT NULL, quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
 unit_price DECIMAL(12,2) NOT NULL DEFAULT 0, line_total DECIMAL(12,2) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_quote_items(quotation_id),
 CONSTRAINT fk_ops_quote_item FOREIGN KEY(quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS bookings (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, booking_reference VARCHAR(40) NOT NULL UNIQUE,
 customer_id BIGINT UNSIGNED NULL, enquiry_id BIGINT UNSIGNED NULL, quotation_id BIGINT UNSIGNED NULL,
 package_id BIGINT UNSIGNED NULL, travel_start_date DATE NULL, travel_end_date DATE NULL,
 traveller_count INT UNSIGNED NOT NULL DEFAULT 1, currency CHAR(3) NOT NULL DEFAULT 'INR',
 total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
 status ENUM('pending','confirmed','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
 internal_notes TEXT NULL, created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_bookings_customer(customer_id), INDEX idx_bookings_status(status),
 CONSTRAINT fk_ops_booking_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL,
 CONSTRAINT fk_ops_booking_enquiry FOREIGN KEY(enquiry_id) REFERENCES enquiries(id) ON DELETE SET NULL,
 CONSTRAINT fk_ops_booking_quote FOREIGN KEY(quotation_id) REFERENCES quotations(id) ON DELETE SET NULL,
 CONSTRAINT fk_ops_booking_package FOREIGN KEY(package_id) REFERENCES packages(id) ON DELETE SET NULL,
 CONSTRAINT fk_ops_booking_user FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS booking_travellers (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, booking_id BIGINT UNSIGNED NOT NULL,
 full_name VARCHAR(160) NOT NULL, age INT UNSIGNED NULL,
 traveller_type VARCHAR(30) NOT NULL DEFAULT 'adult', phone VARCHAR(40) NULL,
 email VARCHAR(190) NULL, identity_note VARCHAR(190) NULL,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_travellers_booking(booking_id),
 CONSTRAINT fk_ops_traveller_booking FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS booking_itinerary_days (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, booking_id BIGINT UNSIGNED NOT NULL,
 day_number INT UNSIGNED NOT NULL, day_date DATE NULL, title VARCHAR(190) NOT NULL,
 description TEXT NULL, supplier_id BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_booking_day(booking_id,day_number),
 CONSTRAINT fk_ops_itinerary_booking FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
 CONSTRAINT fk_ops_itinerary_supplier FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
"CREATE TABLE IF NOT EXISTS payment_transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, booking_id BIGINT UNSIGNED NOT NULL,
 transaction_type ENUM('payment','refund') NOT NULL DEFAULT 'payment',
 amount DECIMAL(12,2) NOT NULL, currency CHAR(3) NOT NULL DEFAULT 'INR',
 payment_method VARCHAR(50) NOT NULL DEFAULT 'bank_transfer',
 transaction_reference VARCHAR(120) NULL, transaction_date DATE NOT NULL,
 status ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'completed',
 notes TEXT NULL, created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_payments_booking(booking_id), INDEX idx_payments_date(transaction_date),
 CONSTRAINT fk_ops_payment_booking FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
 CONSTRAINT fk_ops_payment_user FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

try {
    
    foreach ($ddl as $sql) $pdo->exec($sql);
    $perms = [
      ['operations.view','View operations workspace'],
      ['destinations.manage','Manage destinations'],
      ['suppliers.manage','Manage suppliers'],
      ['quotations.manage','Manage quotations'],
      ['bookings.manage','Manage bookings'],
      ['payments.view','View payment ledger'],
      ['payments.manage','Manage payment ledger']
    ];
    $ins = $pdo->prepare("INSERT IGNORE INTO permissions(name,description) VALUES(?,?)");
    foreach ($perms as $p) $ins->execute($p);
    $role = $pdo->query("SELECT id FROM roles WHERE LOWER(name) IN ('super admin','super_admin','superadmin') LIMIT 1")->fetchColumn();
    if ($role) {
        $ids = $pdo->query("SELECT id FROM permissions WHERE name IN ('operations.view','destinations.manage','suppliers.manage','quotations.manage','bookings.manage','payments.view','payments.manage')")->fetchAll(PDO::FETCH_COLUMN);
        $grant = $pdo->prepare("INSERT IGNORE INTO role_permissions(role_id,permission_id) VALUES(?,?)");
        foreach ($ids as $pid) $grant->execute([(int)$role,(int)$pid]);
    }
    
    echo "Operations migration complete. Existing tables and records were not reset.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Migration failed: ".$e->getMessage().PHP_EOL);
    exit(1);
}
