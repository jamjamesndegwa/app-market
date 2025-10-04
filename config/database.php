<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'prady_tec_appmarket');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
function createTables() {
    global $pdo;
    
    // Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(100) DEFAULT 'fas fa-folder',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Apps table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS apps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            short_description VARCHAR(500),
            category_id INT,
            developer VARCHAR(100) NOT NULL,
            version VARCHAR(20) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            file_path VARCHAR(500),
            file_size BIGINT,
            file_type ENUM('apk', 'aab') NOT NULL,
            icon_path VARCHAR(500),
            screenshots TEXT,
            featured BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            download_count INT DEFAULT 0,
            rating DECIMAL(3,2) DEFAULT 0.00,
            total_ratings INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )
    ");
    
    // Orders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
            transaction_id VARCHAR(200),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Order items table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            app_id INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE
        )
    ");
    
    // Reviews table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            app_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_app_review (app_id, user_id)
        )
    ");
    
    // User downloads table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_downloads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            app_id INT NOT NULL,
            order_id INT,
            download_count INT DEFAULT 1,
            last_downloaded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
            UNIQUE KEY unique_user_app_download (user_id, app_id)
        )
    ");
}

// Initialize database
createTables();

// Insert default categories if they don't exist
function insertDefaultCategories() {
    global $pdo;
    
    $categories = [
        ['name' => 'Games', 'description' => 'Entertainment and gaming applications', 'icon' => 'fas fa-gamepad'],
        ['name' => 'Productivity', 'description' => 'Tools to boost your productivity', 'icon' => 'fas fa-briefcase'],
        ['name' => 'Social', 'description' => 'Social networking and communication apps', 'icon' => 'fas fa-users'],
        ['name' => 'Education', 'description' => 'Learning and educational applications', 'icon' => 'fas fa-graduation-cap'],
        ['name' => 'Utilities', 'description' => 'Utility and system applications', 'icon' => 'fas fa-tools'],
        ['name' => 'Entertainment', 'description' => 'Media and entertainment applications', 'icon' => 'fas fa-film'],
        ['name' => 'Health & Fitness', 'description' => 'Health, fitness, and wellness apps', 'icon' => 'fas fa-heartbeat'],
        ['name' => 'Business', 'description' => 'Business and professional applications', 'icon' => 'fas fa-chart-line']
    ];
    
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, description, icon) VALUES (?, ?, ?)");
        $stmt->execute([$category['name'], $category['description'], $category['icon']]);
    }
}

insertDefaultCategories();
?>
