<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please log in to view your apps.');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$purchased_apps = getUserPurchasedApps($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Apps - Prady Tec AppMarket</title>
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
                <li class="breadcrumb-item active">My Apps</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-mobile-alt me-2"></i>My Apps</h2>
            <a href="apps.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Browse More Apps
            </a>
        </div>

        <?php if (empty($purchased_apps)): ?>
            <div class="text-center py-5">
                <i class="fas fa-mobile-alt fa-5x text-muted mb-4"></i>
                <h3 class="text-muted">No apps purchased yet</h3>
                <p class="text-muted mb-4">Start building your app collection by browsing our catalog.</p>
                <a href="apps.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Browse Apps
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($purchased_apps as $app): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card app-card h-100">
                            <div class="card-img-top bg-light p-4 text-center">
                                <i class="fas fa-mobile-alt fa-4x text-muted"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($app['name']); ?></h5>
                                <p class="card-text text-muted small">by <?php echo htmlspecialchars($app['developer']); ?></p>
                                <p class="card-text"><?php echo htmlspecialchars(substr($app['description'], 0, 100)) . '...'; ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($app['category_name']); ?></span>
                                    <div class="rating">
                                        <?php echo generateStarRating($app['rating']); ?>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <small class="text-muted">Version <?php echo htmlspecialchars($app['version']); ?></small>
                                    <small class="text-muted"><?php echo formatFileSize($app['file_size']); ?></small>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Purchased: <?php echo date('M d, Y', strtotime($app['purchased_at'])); ?></small>
                                    <span class="badge bg-success"><?php echo strtoupper($app['file_type']); ?></span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-grid gap-2">
                                    <a href="download.php?id=<?php echo $app['id']; ?>" class="btn btn-success">
                                        <i class="fas fa-download me-2"></i>Download
                                    </a>
                                    <a href="app-details.php?id=<?php echo $app['id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
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
