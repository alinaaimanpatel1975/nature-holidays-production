<?php

if (PHP_SAPI !== 'cli') {
    exit("CLI access only.\n");
}

require_once __DIR__ . '/../app/Core/Database.php';

echo "====================================\n";
echo "  NATURE HOLIDAYS - ADMIN SETUP\n";
echo "====================================\n\n";

echo "Administrator name: ";
$name = trim((string) fgets(STDIN));

echo "Administrator email: ";
$email = strtolower(trim((string) fgets(STDIN)));

echo "Password (minimum 12 characters): ";
$password = trim((string) fgets(STDIN));

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("\nInvalid name or email.\n");
}

if (strlen($password) < 12) {
    exit("\nPassword must be at least 12 characters.\n");
}

try {
    $pdo = Database::connect();

    $role = $pdo->prepare(
        "SELECT id FROM roles WHERE name = 'Super Admin' LIMIT 1"
    );

    $role->execute();
    $roleId = $role->fetchColumn();

    if (!$roleId) {
        exit("Super Admin role not found. Run migrations first.\n");
    }

    $existing = $pdo->prepare(
        "SELECT id FROM users WHERE email = ? LIMIT 1"
    );

    $existing->execute([$email]);

    if ($existing->fetch()) {
        exit("An account with this email already exists.\n");
    }

    $stmt = $pdo->prepare("
        INSERT INTO users (
            role_id,
            name,
            email,
            password,
            status
        )
        VALUES (?, ?, ?, ?, 'active')
    ");

    $stmt->execute([
        $roleId,
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
    ]);

    echo "\nSuper Admin account created successfully!\n";
    echo "You can now sign in to Nature Holidays.\n";

} catch (Throwable $error) {
    fwrite(STDERR, "\nERROR: " . $error->getMessage() . "\n");
    exit(1);
}