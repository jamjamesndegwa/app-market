<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please log in to view order details.');
    redirect('login.php');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if ($order_id <= 0) {
    setFlashMessage('error', 'Invalid order ID.');
    redirect('orders.php');
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Order not found.');
    redirect('orders.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, a.name as app_name, a.developer, a.version, a.file_type, a.price as app_price
    FROM order_items oi 
    JOIN apps a ON oi.app_id = a.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['id']; ?> - Prady Tec AppMarket</title>
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
                <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                <li class="breadcrumb-item active">Order #<?php echo $order['id']; ?></li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-receipt me-2"></i>Order #<?php echo $order['id']; ?></h2>
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>

        <div class="row">
            <!-- Order Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Order Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Order ID:</strong></td>
                                        <td>#<?php echo $order['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Order Date:</strong></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Status:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : ($order['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <?php if ($order['payment_method']): ?>
                                    <tr>
                                        <td><strong>Payment Method:</strong></td>
                                        <td><?php echo ucfirst($order['payment_method']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($order['transaction_id']): ?>
                                    <tr>
                                        <td><strong>Transaction ID:</strong></td>
                                        <td><?php echo htmlspecialchars($order['transaction_id']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Total Amount:</strong></td>
                                        <td class="h5 text-success mb-0">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($order_items as $item): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="me-3">
                                    <i class="fas fa-mobile-alt fa-3x text-muted"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['app_name']); ?></h6>
                                    <p class="text-muted mb-1">by <?php echo htmlspecialchars($item['developer']); ?></p>
                                    <small class="text-muted">Version <?php echo htmlspecialchars($item['version']); ?> • <?php echo strtoupper($item['file_type']); ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">$<?php echo number_format($item['price'], 2); ?></div>
                                    <?php if ($order['status'] === 'completed'): ?>
                                        <a href="download.php?id=<?php echo $item['app_id']; ?>" class="btn btn-sm btn-success mt-1">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Billing Information -->
                <?php if ($order['billing_name']): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Billing Information</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong><?php echo htmlspecialchars($order['billing_name']); ?></strong></p>
                        <?php if ($order['billing_email']): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($order['billing_email']); ?></p>
                        <?php endif; ?>
                        <?php if ($order['billing_address']): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($order['billing_address']); ?></p>
                        <?php endif; ?>
                        <?php if ($order['billing_city'] && $order['billing_state'] && $order['billing_zip']): ?>
                            <p class="mb-1"><?php echo htmlspecialchars($order['billing_city'] . ', ' . $order['billing_state'] . ' ' . $order['billing_zip']); ?></p>
                        <?php endif; ?>
                        <?php if ($order['billing_country']): ?>
                            <p class="mb-0"><?php echo htmlspecialchars($order['billing_country']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($order['status'] === 'completed'): ?>
                                <a href="my-apps.php" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Download All Apps
                                </a>
                            <?php endif; ?>
                            <a href="orders.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>View All Orders
                            </a>
                            <a href="apps.php" class="btn btn-outline-secondary">
                                <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
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
