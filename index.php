<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured apps for homepage
$featured_apps = getFeaturedApps();
$recent_apps = getRecentApps();
$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prady Tec AppMarket - Mobile App Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Prady Tec AppMarket</h1>
                    <p class="lead mb-4">Discover and download premium mobile applications. Get the latest AAB and APK files for Android devices.</p>
                    <div class="d-flex gap-3">
                        <a href="pages/apps.php" class="btn btn-light btn-lg">Browse Apps</a>
                        <a href="pages/register.php" class="btn btn-outline-light btn-lg">Get Started</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-mobile-alt display-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Browse by Category</h2>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                <div class="col-md-3 mb-4">
                    <div class="card category-card h-100 text-center">
                        <div class="card-body">
                            <i class="<?php echo htmlspecialchars($category['icon']); ?> fa-3x text-primary mb-3"></i>
                            <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                            <a href="pages/apps.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">View Apps</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Apps Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Featured Apps</h2>
            <div class="row">
                <?php foreach ($featured_apps as $app): ?>
                <div class="col-md-4 mb-4">
                    <div class="card app-card h-100">
                        <div class="card-img-top bg-light p-4 text-center">
                            <i class="fas fa-mobile-alt fa-4x text-muted"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($app['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($app['description'], 0, 100)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($app['category_name']); ?></span>
                                <span class="h5 text-success mb-0">$<?php echo number_format($app['price'], 2); ?></span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="pages/app-details.php?id=<?php echo $app['id']; ?>" class="btn btn-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Recent Apps Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Recently Added</h2>
            <div class="row">
                <?php foreach ($recent_apps as $app): ?>
                <div class="col-md-3 mb-4">
                    <div class="card app-card h-100">
                        <div class="card-img-top bg-light p-3 text-center">
                            <i class="fas fa-mobile-alt fa-3x text-muted"></i>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($app['name']); ?></h6>
                            <p class="card-text small"><?php echo htmlspecialchars(substr($app['description'], 0, 60)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?php echo date('M d', strtotime($app['created_at'])); ?></small>
                                <span class="h6 text-success mb-0">$<?php echo number_format($app['price'], 2); ?></span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="pages/app-details.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-primary w-100">View</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
