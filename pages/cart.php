<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please log in to view your cart.');
    redirect('login.php');
}

$cart_items = getCartItems();
$cart_total = getCartTotal();

// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $app_id = (int)$_POST['app_id'];
    removeFromCart($app_id);
    setFlashMessage('success', 'Item removed from cart.');
    redirect('cart.php');
}

// Handle update quantity (if needed in future)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    // Future implementation for quantity updates
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Prady Tec AppMarket</title>
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
                <li class="breadcrumb-item active">Shopping Cart</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4">
                    <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                </h2>

                <?php if (empty($cart_items)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Your cart is empty</h4>
                        <p class="text-muted">Start shopping to add apps to your cart.</p>
                        <a href="apps.php" class="btn btn-primary">Browse Apps</a>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body p-0">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center">
                                            <i class="fas fa-mobile-alt fa-3x text-muted"></i>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                            <p class="text-muted mb-1">by <?php echo htmlspecialchars($item['developer']); ?></p>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <span class="h5 text-success mb-0">$<?php echo number_format($item['cart_price'], 2); ?></span>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="app_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                        onclick="return confirm('Remove this item from cart?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($cart_items)): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal (<?php echo count($cart_items); ?> items):</span>
                                <span id="cart-total">$<?php echo number_format($cart_total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax:</span>
                                <span>$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong id="cart-total-final">$<?php echo number_format($cart_total, 2); ?></strong>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="checkout.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                </a>
                                <a href="apps.php" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>Continue Shopping
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <p class="text-muted">No items in cart</p>
                                <a href="apps.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Security Features -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-shield-alt me-2"></i>Secure Shopping
                        </h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-lock text-success me-2"></i>
                            <small>SSL Encrypted</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-undo text-success me-2"></i>
                            <small>30-Day Refund</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-headset text-success me-2"></i>
                            <small>24/7 Support</small>
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
