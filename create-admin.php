<?php
/**
 * Script to create an admin user
 * Run this script once to create the first admin user
 * Delete this file after creating the admin user for security
 */

require_once 'config/database.php';

// Check if admin already exists
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$admin_count = $stmt->fetch()['count'];

if ($admin_count > 0) {
    echo "Admin user already exists. Please delete this file for security.\n";
    exit;
}

// Create admin user
$admin_data = [
    'username' => 'admin',
    'email' => 'admin@pradytec.com',
    'password' => 'admin123', // Change this password!
    'first_name' => 'Admin',
    'last_name' => 'User',
    'role' => 'admin'
];

try {
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, first_name, last_name, role) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $admin_data['username'],
        $admin_data['email'],
        password_hash($admin_data['password'], PASSWORD_DEFAULT),
        $admin_data['first_name'],
        $admin_data['last_name'],
        $admin_data['role']
    ]);
    
    echo "Admin user created successfully!\n";
    echo "Username: " . $admin_data['username'] . "\n";
    echo "Email: " . $admin_data['email'] . "\n";
    echo "Password: " . $admin_data['password'] . "\n";
    echo "\nIMPORTANT: Change the password after first login!\n";
    echo "Delete this file (create-admin.php) for security.\n";
    
} catch (Exception $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
}
?>
