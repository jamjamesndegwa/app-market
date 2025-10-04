<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please log in to proceed with checkout.');
    redirect('login.php');
}

$cart_items = getCartItems();
$cart_total = getCartTotal();

// Redirect if cart is empty
if (empty($cart_items)) {
    setFlashMessage('error', 'Your cart is empty.');
    redirect('cart.php');
}

$error = '';
$success = '';

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitizeInput($_POST['payment_method']);
    $billing_name = sanitizeInput($_POST['billing_name']);
    $billing_email = sanitizeInput($_POST['billing_email']);
    $billing_address = sanitizeInput($_POST['billing_address']);
    $billing_city = sanitizeInput($_POST['billing_city']);
    $billing_state = sanitizeInput($_POST['billing_state']);
    $billing_zip = sanitizeInput($_POST['billing_zip']);
    $billing_country = sanitizeInput($_POST['billing_country']);
    
    // Validation
    if (empty($billing_name) || empty($billing_email) || empty($billing_address) || 
        empty($billing_city) || empty($billing_state) || empty($billing_zip) || empty($billing_country)) {
        $error = 'Please fill in all billing information fields.';
    } elseif (!validateEmail($billing_email)) {
        $error = 'Please provide a valid email address.';
    } else {
        // Create order
        $order_id = createOrder($_SESSION['user_id'], $cart_items, $cart_total);
        
        if ($order_id) {
            // Update order with billing information
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET payment_method = ?, 
                    billing_name = ?, 
                    billing_email = ?, 
                    billing_address = ?, 
                    billing_city = ?, 
                    billing_state = ?, 
                    billing_zip = ?, 
                    billing_country = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $payment_method, $billing_name, $billing_email, $billing_address,
                $billing_city, $billing_state, $billing_zip, $billing_country, $order_id
            ]);
            
            if ($payment_method === 'paypal') {
                // Redirect to PayPal payment
                redirect("payment.php?order_id=$order_id&method=paypal");
            } elseif ($payment_method === 'stripe') {
                // Redirect to Stripe payment
                redirect("payment.php?order_id=$order_id&method=stripe");
            } else {
                // For demo purposes, mark as completed
                updateOrderStatus($order_id, 'completed', 'completed', 'demo_' . time());
                clearCart();
                setFlashMessage('success', 'Order placed successfully! You can now download your apps.');
                redirect('orders.php');
            }
        } else {
            $error = 'Failed to create order. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Prady Tec AppMarket</title>
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
                <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>

        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <h2 class="mb-4">
                    <i class="fas fa-credit-card me-2"></i>Checkout
                </h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <!-- Payment Method -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" required>
                                        <label class="form-check-label" for="paypal">
                                            <i class="fab fa-paypal me-2"></i>PayPal
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe" required>
                                        <label class="form-check-label" for="stripe">
                                            <i class="fab fa-stripe me-2"></i>Stripe (Credit Card)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="demo" value="demo" required>
                                        <label class="form-check-label" for="demo">
                                            <i class="fas fa-flask me-2"></i>Demo Payment
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Billing Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="billing_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="billing_name" name="billing_name" 
                                           value="<?php echo isset($_POST['billing_name']) ? htmlspecialchars($_POST['billing_name']) : ''; ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide your full name.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="billing_email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="billing_email" name="billing_email" 
                                           value="<?php echo isset($_POST['billing_email']) ? htmlspecialchars($_POST['billing_email']) : ''; ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a valid email address.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="billing_address" class="form-label">Address *</label>
                                <input type="text" class="form-control" id="billing_address" name="billing_address" 
                                       value="<?php echo isset($_POST['billing_address']) ? htmlspecialchars($_POST['billing_address']) : ''; ?>" required>
                                <div class="invalid-feedback">
                                    Please provide your address.
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="billing_city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="billing_city" name="billing_city" 
                                           value="<?php echo isset($_POST['billing_city']) ? htmlspecialchars($_POST['billing_city']) : ''; ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide your city.
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="billing_state" class="form-label">State *</label>
                                    <input type="text" class="form-control" id="billing_state" name="billing_state" 
                                           value="<?php echo isset($_POST['billing_state']) ? htmlspecialchars($_POST['billing_state']) : ''; ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide your state.
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="billing_zip" class="form-label">ZIP Code *</label>
                                    <input type="text" class="form-control" id="billing_zip" name="billing_zip" 
                                           value="<?php echo isset($_POST['billing_zip']) ? htmlspecialchars($_POST['billing_zip']) : ''; ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide your ZIP code.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="billing_country" class="form-label">Country *</label>
                                <select class="form-select" id="billing_country" name="billing_country" required>
                                    <option value="">Select Country</option>
                                    <option value="US" <?php echo (isset($_POST['billing_country']) && $_POST['billing_country'] === 'US') ? 'selected' : ''; ?>>United States</option>
                                    <option value="CA" <?php echo (isset($_POST['billing_country']) && $_POST['billing_country'] === 'CA') ? 'selected' : ''; ?>>Canada</option>
                                    <option value="GB" <?php echo (isset($_POST['billing_country']) && $_POST['billing_country'] === 'GB') ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="AU" <?php echo (isset($_POST['billing_country']) && $_POST['billing_country'] === 'AU') ? 'selected' : ''; ?>>Australia</option>
                                    <option value="DE" <?php echo (isset($_POST['billing_country']) && $_POST['billing_country'] === 'DE') ? 'selected' : ''; ?>>Germany</option>
                                    <option value="FR" <?php echo (isset($_POST['billing_country']) && $_POST['billing_country'] === 'FR') ? 'selected' : ''; ?>>France</option>
                                    <option value="IN" <?php echo (isset($_POST['billing_country']) && $_POST['billing_country'] === 'IN') ? 'selected' : ''; ?>>India</option>
                                    <option value="JP" <?php echo (isset($_POST['billing_country']) && $_POST['billing_country'] === 'JP') ? 'selected' : ''; ?>>Japan</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select your country.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="cart.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Cart
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock me-2"></i>Complete Order
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="fas fa-mobile-alt fa-2x text-muted"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">by <?php echo htmlspecialchars($item['developer']); ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">$<?php echo number_format($item['cart_price'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Processing Fee:</span>
                            <span>$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong>$<?php echo number_format($cart_total, 2); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Security Features -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-shield-alt me-2"></i>Secure Checkout
                        </h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-lock text-success me-2"></i>
                            <small>256-bit SSL encryption</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-credit-card text-success me-2"></i>
                            <small>Secure payment processing</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-undo text-success me-2"></i>
                            <small>30-day money-back guarantee</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-headset text-success me-2"></i>
                            <small>24/7 customer support</small>
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
