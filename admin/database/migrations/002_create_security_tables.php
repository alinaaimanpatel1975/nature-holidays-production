<?php

return [
    "CREATE TABLE IF NOT EXISTS audit_logs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL,
        action VARCHAR(120) NOT NULL,
        entity_type VARCHAR(100) NULL,
        entity_id BIGINT UNSIGNED NULL,
        ip_address VARCHAR(45) NULL,
        details JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_audit_user (user_id),
        INDEX idx_audit_entity (entity_type, entity_id),
        INDEX idx_audit_created (created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS auth_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE,
        purpose VARCHAR(40) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_auth_tokens_user (user_id),
        INDEX idx_auth_tokens_expiry (expires_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "INSERT IGNORE INTO permissions (name, description) VALUES
        ('dashboard.view', 'View the admin dashboard'),
        ('customers.view', 'View customers'),
        ('customers.manage', 'Create and update customers'),
        ('enquiries.view', 'View enquiries'),
        ('enquiries.manage', 'Create and update enquiries'),
        ('followups.manage', 'Manage enquiry follow-ups'),
        ('packages.manage', 'Manage travel packages'),
        ('destinations.manage', 'Manage destinations'),
        ('quotations.manage', 'Manage quotations'),
        ('bookings.manage', 'Manage bookings'),
        ('payments.view', 'View payments'),
        ('payments.manage', 'Manage payments'),
        ('suppliers.manage', 'Manage suppliers'),
        ('cms.manage', 'Manage website content'),
        ('reports.view', 'View business reports'),
        ('settings.manage', 'Manage system settings'),
        ('audit.view', 'View audit logs')",

    "INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT r.id, p.id
        FROM roles r
        CROSS JOIN permissions p
        WHERE r.name = 'Super Admin'",

    "INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT r.id, p.id
        FROM roles r
        CROSS JOIN permissions p
        WHERE r.name = 'Administrator'
        AND p.name <> 'settings.manage'",

    "INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT r.id, p.id
        FROM roles r
        CROSS JOIN permissions p
        WHERE
            (r.name = 'Sales Executive' AND p.name IN (
                'dashboard.view', 'customers.view', 'customers.manage',
                'enquiries.view', 'enquiries.manage', 'followups.manage',
                'quotations.manage'
            ))
            OR
            (r.name = 'CRM Manager' AND p.name IN (
                'dashboard.view', 'customers.view', 'customers.manage',
                'enquiries.view', 'enquiries.manage', 'followups.manage',
                'reports.view'
            ))
            OR
            (r.name = 'Operations Manager' AND p.name IN (
                'dashboard.view', 'bookings.manage', 'suppliers.manage',
                'customers.view', 'reports.view'
            ))
            OR
            (r.name = 'Operations Staff' AND p.name IN (
                'dashboard.view', 'bookings.manage'
            ))
            OR
            (r.name = 'Finance Manager' AND p.name IN (
                'dashboard.view', 'payments.view', 'payments.manage',
                'reports.view'
            ))
            OR
            (r.name = 'Finance Staff' AND p.name IN (
                'dashboard.view', 'payments.view'
            ))
            OR
            (r.name = 'Content Manager' AND p.name IN (
                'dashboard.view', 'packages.manage', 'destinations.manage',
                'cms.manage'
            ))"
];