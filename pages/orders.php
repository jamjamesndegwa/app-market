<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please log in to view your orders.');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user's orders
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Prady Tec AppMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">My Orders</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shopping-bag me-2"></i>My Orders</h2>
        </div>

        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-5x text-muted mb-4"></i>
                <h3 class="text-muted">No orders found</h3>
                <p class="text-muted mb-4">Your order history will appear here.</p>
                <a href="apps.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">Order #<?php echo $order['id']; ?></h5>
                                        <small class="text-muted">Placed on <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'danger'); ?> fs-6">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                        <div class="h5 text-success mb-0 mt-2">$<?php echo number_format($order['total_amount'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="mb-2">
                                            <strong>Items:</strong> <?php echo $order['item_count']; ?> app(s)
                                        </p>
                                        <?php if ($order['payment_method']): ?>
                                            <p class="mb-2">
                                                <strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($order['transaction_id']): ?>
                                            <p class="mb-0">
                                                <strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="d-grid gap-2">
                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-eye me-2"></i>View Details
                                            </a>
                                            <?php if ($order['status'] === 'completed'): ?>
                                                <a href="my-apps.php" class="btn btn-success">
                                                    <i class="fas fa-download me-2"></i>Download Apps
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
