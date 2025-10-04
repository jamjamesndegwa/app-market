<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please log in to access your dashboard.');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user's purchased apps
$purchased_apps = getUserPurchasedApps($user_id);

// Get user's recent orders
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();

// Get user's reviews
$stmt = $pdo->prepare("
    SELECT r.*, a.name as app_name 
    FROM reviews r 
    JOIN apps a ON r.app_id = a.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_reviews = $stmt->fetchAll();

// Get user's download statistics
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_downloads, 
           SUM(ud.download_count) as total_download_count
    FROM user_downloads ud 
    WHERE ud.user_id = ?
");
$stmt->execute([$user_id]);
$download_stats = $stmt->fetch();

// Get user's total spent
$stmt = $pdo->prepare("
    SELECT SUM(total_amount) as total_spent 
    FROM orders 
    WHERE user_id = ? AND status = 'completed'
");
$stmt->execute([$user_id]);
$spending_stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prady Tec AppMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                                <p class="mb-0">Manage your apps, orders, and account settings from your dashboard.</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-user-circle fa-5x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3><?php echo count($purchased_apps); ?></h3>
                    <p class="text-muted">Purchased Apps</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-download"></i>
                    <h3><?php echo $download_stats['total_download_count'] ?? 0; ?></h3>
                    <p class="text-muted">Total Downloads</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-shopping-bag"></i>
                    <h3><?php echo count($recent_orders); ?></h3>
                    <p class="text-muted">Total Orders</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>$<?php echo number_format($spending_stats['total_spent'] ?? 0, 2); ?></h3>
                    <p class="text-muted">Total Spent</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- My Apps -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>My Apps</h5>
                        <a href="my-apps.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($purchased_apps)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No apps purchased yet</h6>
                                <p class="text-muted">Start exploring our app collection!</p>
                                <a href="apps.php" class="btn btn-primary">Browse Apps</a>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($purchased_apps, 0, 3) as $app): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <i class="fas fa-mobile-alt fa-2x text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($app['name']); ?></h6>
                                        <small class="text-muted">by <?php echo htmlspecialchars($app['developer']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <a href="download.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Recent Orders</h5>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No orders yet</h6>
                                <p class="text-muted">Your order history will appear here.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-1">Order #<?php echo $order['id']; ?></h6>
                                        <small class="text-muted"><?php echo $order['item_count']; ?> items</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">$<?php echo number_format($order['total_amount'], 2); ?></div>
                                        <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>My Reviews</h5>
                        <a href="reviews.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_reviews)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No reviews yet</h6>
                                <p class="text-muted">Your reviews will appear here.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_reviews as $review): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($review['app_name']); ?></h6>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <?php if ($review['comment']): ?>
                                        <p class="small text-muted mb-0"><?php echo htmlspecialchars(substr($review['comment'], 0, 100)) . '...'; ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="apps.php" class="btn btn-outline-primary">
                                <i class="fas fa-search me-2"></i>Browse Apps
                            </a>
                            <a href="my-apps.php" class="btn btn-outline-success">
                                <i class="fas fa-mobile-alt me-2"></i>My Apps
                            </a>
                            <a href="orders.php" class="btn btn-outline-info">
                                <i class="fas fa-shopping-bag me-2"></i>Order History
                            </a>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
