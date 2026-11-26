<?php

return [

    // 1. Roles
    "CREATE TABLE IF NOT EXISTS roles (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(80) NOT NULL UNIQUE,
        description VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 2. Permissions
    "CREATE TABLE IF NOT EXISTS permissions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description VARCHAR(255) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 3. Role permissions
    "CREATE TABLE IF NOT EXISTS role_permissions (
        role_id BIGINT UNSIGNED NOT NULL,
        permission_id BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY (role_id, permission_id),
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 4. Users
    "CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        role_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(30) NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        email_verified_at DATETIME NULL,
        last_login_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_users_role (role_id),
        FOREIGN KEY (role_id) REFERENCES roles(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 5. Customers
    "CREATE TABLE IF NOT EXISTS customers (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NULL UNIQUE,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(30) NULL,
        alternate_phone VARCHAR(30) NULL,
        address TEXT NULL,
        city VARCHAR(100) NULL,
        state VARCHAR(100) NULL,
        country VARCHAR(100) DEFAULT 'India',
        source VARCHAR(100) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_customers_email (email),
        INDEX idx_customers_phone (phone),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 6. Service categories — editable by administrators
    "CREATE TABLE IF NOT EXISTS service_categories (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(180) NOT NULL UNIQUE,
        description TEXT NULL,
        parent_id BIGINT UNSIGNED NULL,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        sort_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES service_categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 7. Destinations
    "CREATE TABLE IF NOT EXISTS destinations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(180) NOT NULL UNIQUE,
        country VARCHAR(100) DEFAULT 'India',
        state VARCHAR(100) NULL,
        description TEXT NULL,
        image VARCHAR(255) NULL,
        is_featured BOOLEAN NOT NULL DEFAULT FALSE,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_destinations_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 8. Packages
    "CREATE TABLE IF NOT EXISTS packages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_id BIGINT UNSIGNED NULL,
        name VARCHAR(200) NOT NULL,
        slug VARCHAR(220) NOT NULL UNIQUE,
        short_description VARCHAR(500) NULL,
        description LONGTEXT NULL,
        duration_days SMALLINT UNSIGNED NULL,
        duration_nights SMALLINT UNSIGNED NULL,
        indicative_price DECIMAL(12,2) NULL,
        currency CHAR(3) NOT NULL DEFAULT 'INR',
        image VARCHAR(255) NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'draft',
        is_featured BOOLEAN NOT NULL DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_packages_status (status),
        INDEX idx_packages_category (category_id),
        FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 9. Package destinations
    "CREATE TABLE IF NOT EXISTS package_destinations (
        package_id BIGINT UNSIGNED NOT NULL,
        destination_id BIGINT UNSIGNED NOT NULL,
        PRIMARY KEY (package_id, destination_id),
        FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
        FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 10. Enquiries / leads
    "CREATE TABLE IF NOT EXISTS enquiries (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_id BIGINT UNSIGNED NULL,
        assigned_to BIGINT UNSIGNED NULL,
        enquiry_number VARCHAR(40) NOT NULL UNIQUE,
        subject VARCHAR(200) NULL,
        travel_start_date DATE NULL,
        travel_end_date DATE NULL,
        traveller_count SMALLINT UNSIGNED NULL,
        budget DECIMAL(12,2) NULL,
        currency CHAR(3) NOT NULL DEFAULT 'INR',
        source VARCHAR(100) NULL,
        priority VARCHAR(30) NOT NULL DEFAULT 'normal',
        status VARCHAR(40) NOT NULL DEFAULT 'new',
        message TEXT NULL,
        internal_notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_enquiries_status (status),
        INDEX idx_enquiries_assigned (assigned_to),
        INDEX idx_enquiries_customer (customer_id),
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 11. Services requested in an enquiry
    "CREATE TABLE IF NOT EXISTS enquiry_services (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        enquiry_id BIGINT UNSIGNED NOT NULL,
        service_category_id BIGINT UNSIGNED NULL,
        description TEXT NULL,
        quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_enquiry_services_enquiry (enquiry_id),
        FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE CASCADE,
        FOREIGN KEY (service_category_id) REFERENCES service_categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 12. Follow-ups
    "CREATE TABLE IF NOT EXISTS followups (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        enquiry_id BIGINT UNSIGNED NOT NULL,
        assigned_to BIGINT UNSIGNED NULL,
        created_by BIGINT UNSIGNED NULL,
        due_at DATETIME NOT NULL,
        completed_at DATETIME NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'call',
        notes TEXT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_followups_due (due_at, status),
        FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Initial role records
    "INSERT IGNORE INTO roles (name, description) VALUES
        ('Super Admin', 'Full system access'),
        ('Administrator', 'Administrative access'),
        ('Sales Executive', 'Customer enquiries and sales'),
        ('CRM Manager', 'Customer relationship management'),
        ('Operations Manager', 'Booking and operations management'),
        ('Operations Staff', 'Assigned operational tasks'),
        ('Finance Manager', 'Financial management'),
        ('Finance Staff', 'Assigned finance tasks'),
        ('Content Manager', 'Website content management'),
        ('Customer', 'Customer portal access')"
];