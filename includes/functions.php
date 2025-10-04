<?php
// Helper functions for the application

// Get featured apps
function getFeaturedApps($limit = 6) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name 
        FROM apps a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.featured = 1 AND a.status = 'active' 
        ORDER BY a.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Get recent apps
function getRecentApps($limit = 8) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name 
        FROM apps a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.status = 'active' 
        ORDER BY a.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Get categories
function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

// Get apps by category
function getAppsByCategory($category_id, $limit = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name 
        FROM apps a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.category_id = ? AND a.status = 'active' 
        ORDER BY a.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$category_id, $limit, $offset]);
    return $stmt->fetchAll();
}

// Get app by ID
function getAppById($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name 
        FROM apps a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.id = ? AND a.status = 'active'
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Search apps
function searchApps($query, $limit = 20, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name 
        FROM apps a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.status = 'active' AND (
            a.name LIKE ? OR 
            a.description LIKE ? OR 
            a.developer LIKE ?
        )
        ORDER BY a.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
    return $stmt->fetchAll();
}

// Get app reviews
function getAppReviews($app_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, u.username, u.first_name, u.last_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.app_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$app_id, $limit]);
    return $stmt->fetchAll();
}

// Get user by ID
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get user by email
function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Get user by username
function getUserByUsername($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

// Create user
function createUser($data) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, first_name, last_name) 
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $data['username'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['first_name'],
        $data['last_name']
    ]);
}

// Update app download count
function updateAppDownloadCount($app_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE apps SET download_count = download_count + 1 WHERE id = ?");
    return $stmt->execute([$app_id]);
}

// Check if user has purchased app
function hasUserPurchasedApp($user_id, $app_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM order_items oi 
        JOIN orders o ON oi.order_id = o.id 
        WHERE o.user_id = ? AND oi.app_id = ? AND o.status = 'completed'
    ");
    $stmt->execute([$user_id, $app_id]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

// Get user's purchased apps
function getUserPurchasedApps($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT DISTINCT a.*, c.name as category_name, o.created_at as purchased_at
        FROM apps a
        JOIN order_items oi ON a.id = oi.app_id
        JOIN orders o ON oi.order_id = o.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE o.user_id = ? AND o.status = 'completed'
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Create order
function createOrder($user_id, $items, $total_amount) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, status) 
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $total_amount]);
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, app_id, price) 
            VALUES (?, ?, ?)
        ");
        
        foreach ($items as $item) {
            $stmt->execute([$order_id, $item['app_id'], $item['price']]);
        }
        
        $pdo->commit();
        return $order_id;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Update order status
function updateOrderStatus($order_id, $status, $payment_status = null, $transaction_id = null) {
    global $pdo;
    $sql = "UPDATE orders SET status = ?";
    $params = [$status];
    
    if ($payment_status !== null) {
        $sql .= ", payment_status = ?";
        $params[] = $payment_status;
    }
    
    if ($transaction_id !== null) {
        $sql .= ", payment_method = 'online', transaction_id = ?";
        $params[] = $transaction_id;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $order_id;
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Add review
function addReview($app_id, $user_id, $rating, $comment = null) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Add review
        $stmt = $pdo->prepare("
            INSERT INTO reviews (app_id, user_id, rating, comment) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            rating = VALUES(rating), 
            comment = VALUES(comment),
            updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$app_id, $user_id, $rating, $comment]);
        
        // Update app rating
        $stmt = $pdo->prepare("
            UPDATE apps SET 
                rating = (SELECT AVG(rating) FROM reviews WHERE app_id = ?),
                total_ratings = (SELECT COUNT(*) FROM reviews WHERE app_id = ?)
            WHERE id = ?
        ");
        $stmt->execute([$app_id, $app_id, $app_id]);
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Generate star rating HTML
function generateStarRating($rating, $size = 'sm') {
    $html = '<div class="rating">';
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $fullStars) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i == $fullStars + 1 && $hasHalfStar) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-muted"></i>';
        }
    }
    
    $html .= '<span class="ms-1 small text-muted">(' . number_format($rating, 1) . ')</span>';
    $html .= '</div>';
    
    return $html;
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Flash message functions
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

// Get cart count
function getCartCount() {
    if (isset($_SESSION['cart'])) {
        return count($_SESSION['cart']);
    }
    return 0;
}

// Add to cart
function addToCart($app_id, $price) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (!isset($_SESSION['cart'][$app_id])) {
        $_SESSION['cart'][$app_id] = [
            'app_id' => $app_id,
            'price' => $price
        ];
    }
}

// Remove from cart
function removeFromCart($app_id) {
    if (isset($_SESSION['cart'][$app_id])) {
        unset($_SESSION['cart'][$app_id]);
    }
}

// Get cart items
function getCartItems() {
    if (!isset($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    foreach ($_SESSION['cart'] as $item) {
        $app = getAppById($item['app_id']);
        if ($app) {
            $items[] = array_merge($app, ['cart_price' => $item['price']]);
        }
    }
    
    return $items;
}

// Clear cart
function clearCart() {
    unset($_SESSION['cart']);
}

// Get cart total
function getCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $total += $item['cart_price'];
    }
    return $total;
}
?>
