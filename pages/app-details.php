<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($app_id <= 0) {
    setFlashMessage('error', 'Invalid app ID.');
    redirect('apps.php');
}

$app = getAppById($app_id);
if (!$app) {
    setFlashMessage('error', 'App not found.');
    redirect('apps.php');
}

$reviews = getAppReviews($app_id);
$has_purchased = isLoggedIn() ? hasUserPurchasedApp($_SESSION['user_id'], $app_id) : false;
$user_review = null;

if (isLoggedIn()) {
    // Check if user has already reviewed this app
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE app_id = ? AND user_id = ?");
    $stmt->execute([$app_id, $_SESSION['user_id']]);
    $user_review = $stmt->fetch();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please log in to submit a review.');
        redirect('login.php');
    }
    
    if (!$has_purchased) {
        setFlashMessage('error', 'You must purchase this app before reviewing it.');
        redirect('app-details.php?id=' . $app_id);
    }
    
    $rating = (int)$_POST['rating'];
    $comment = sanitizeInput($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        setFlashMessage('error', 'Please select a valid rating.');
    } else {
        if (addReview($app_id, $_SESSION['user_id'], $rating, $comment)) {
            setFlashMessage('success', 'Review submitted successfully!');
            redirect('app-details.php?id=' . $app_id);
        } else {
            setFlashMessage('error', 'Failed to submit review. Please try again.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($app['name']); ?> - Prady Tec AppMarket</title>
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
                <li class="breadcrumb-item"><a href="apps.php">Apps</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($app['name']); ?></li>
            </ol>
        </nav>

        <!-- App Header -->
        <div class="app-header">
            <div class="row">
                <div class="col-md-3 text-center">
                    <div class="app-icon mx-auto mb-3">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($app['name']); ?></h1>
                    <p class="text-muted mb-2">by <?php echo htmlspecialchars($app['developer']); ?></p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="rating me-3">
                            <?php echo generateStarRating($app['rating']); ?>
                        </div>
                        <span class="badge bg-primary me-2"><?php echo htmlspecialchars($app['category_name']); ?></span>
                        <span class="badge bg-secondary"><?php echo strtoupper($app['file_type']); ?></span>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-bold"><?php echo $app['download_count']; ?></div>
                            <small class="text-muted">Downloads</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold"><?php echo $app['total_ratings']; ?></div>
                            <small class="text-muted">Reviews</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold"><?php echo formatFileSize($app['file_size']); ?></div>
                            <small class="text-muted">Size</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="h2 text-success mb-3">$<?php echo number_format($app['price'], 2); ?></div>
                        
                        <?php if (isLoggedIn()): ?>
                            <?php if ($has_purchased): ?>
                                <a href="download.php?id=<?php echo $app['id']; ?>" class="btn btn-success btn-lg w-100 mb-2">
                                    <i class="fas fa-download me-2"></i>Download
                                </a>
                            <?php else: ?>
                                <button class="btn btn-primary btn-lg w-100 mb-2 add-to-cart" 
                                        data-app-id="<?php echo $app['id']; ?>" 
                                        data-price="<?php echo $app['price']; ?>">
                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-lg w-100 mb-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                            </a>
                        <?php endif; ?>
                        
                        <div class="small text-muted">
                            Version <?php echo htmlspecialchars($app['version']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- App Details -->
            <div class="col-lg-8">
                <!-- Description -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Description</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($app['description'])); ?></p>
                    </div>
                </div>

                <!-- Screenshots -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-images me-2"></i>Screenshots</h5>
                    </div>
                    <div class="card-body">
                        <div class="app-screenshots">
                            <div class="screenshot">
                                <i class="fas fa-image fa-3x"></i>
                                <div class="mt-2">Screenshot 1</div>
                            </div>
                            <div class="screenshot">
                                <i class="fas fa-image fa-3x"></i>
                                <div class="mt-2">Screenshot 2</div>
                            </div>
                            <div class="screenshot">
                                <i class="fas fa-image fa-3x"></i>
                                <div class="mt-2">Screenshot 3</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Reviews (<?php echo $app['total_ratings']; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isLoggedIn() && $has_purchased && !$user_review): ?>
                            <!-- Add Review Form -->
                            <div class="border rounded p-3 mb-4">
                                <h6>Write a Review</h6>
                                <form method="POST">
                                    <input type="hidden" name="action" value="review">
                                    <div class="mb-3">
                                        <label class="form-label">Rating</label>
                                        <div class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star rating-star text-muted" 
                                                   data-rating="<?php echo $i; ?>" 
                                                   data-app-id="<?php echo $app['id']; ?>"
                                                   style="cursor: pointer; font-size: 1.5rem;"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comment" class="form-label">Comment (Optional)</label>
                                        <textarea class="form-control" id="comment" name="comment" rows="3" 
                                                  placeholder="Share your experience with this app..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Review</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Reviews List -->
                        <?php if (empty($reviews)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No reviews yet</h6>
                                <p class="text-muted">Be the first to review this app!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h6>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                    </div>
                                    <?php if ($review['comment']): ?>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- App Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info me-2"></i>App Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Developer:</strong></td>
                                <td><?php echo htmlspecialchars($app['developer']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Version:</strong></td>
                                <td><?php echo htmlspecialchars($app['version']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Size:</strong></td>
                                <td><?php echo formatFileSize($app['file_size']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td><?php echo strtoupper($app['file_type']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Category:</strong></td>
                                <td><?php echo htmlspecialchars($app['category_name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Downloads:</strong></td>
                                <td><?php echo number_format($app['download_count']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Added:</strong></td>
                                <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Related Apps -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-thumbs-up me-2"></i>You Might Also Like</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $related_apps = getAppsByCategory($app['category_id'], 3);
                        $related_apps = array_filter($related_apps, function($related_app) use ($app) {
                            return $related_app['id'] != $app['id'];
                        });
                        ?>
                        <?php if (empty($related_apps)): ?>
                            <p class="text-muted small">No related apps found.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($related_apps, 0, 3) as $related_app): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <i class="fas fa-mobile-alt fa-2x text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="app-details.php?id=<?php echo $related_app['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($related_app['name']); ?>
                                            </a>
                                        </h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><?php echo htmlspecialchars($related_app['developer']); ?></small>
                                            <span class="text-success fw-bold">$<?php echo number_format($related_app['price'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <script>
        // Rating stars interaction
        document.querySelectorAll('.rating-star').forEach(star => {
            star.addEventListener('mouseenter', function() {
                const rating = this.dataset.rating;
                const stars = this.parentElement.querySelectorAll('.rating-star');
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('text-warning');
                        s.classList.remove('text-muted');
                    } else {
                        s.classList.remove('text-warning');
                        s.classList.add('text-muted');
                    }
                });
            });
        });

        document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
            const stars = this.querySelectorAll('.rating-star');
            stars.forEach(star => {
                star.classList.remove('text-warning');
                star.classList.add('text-muted');
            });
        });
    </script>
</body>
</html>
