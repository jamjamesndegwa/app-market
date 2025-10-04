<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';

// Get apps based on filters
if (!empty($search)) {
    $apps = searchApps($search, $limit, $offset);
    $total_apps = count(searchApps($search, 1000, 0)); // Get total count
} elseif ($category_id > 0) {
    $apps = getAppsByCategory($category_id, $limit, $offset);
    $total_apps = count(getAppsByCategory($category_id, 1000, 0)); // Get total count
} else {
    $apps = getRecentApps($limit);
    $total_apps = count(getRecentApps(1000)); // Get total count
}

// Apply sorting
if ($sort === 'price_low') {
    usort($apps, function($a, $b) { return $a['price'] <=> $b['price']; });
} elseif ($sort === 'price_high') {
    usort($apps, function($a, $b) { return $b['price'] <=> $a['price']; });
} elseif ($sort === 'rating') {
    usort($apps, function($a, $b) { return $b['rating'] <=> $a['rating']; });
} elseif ($sort === 'downloads') {
    usort($apps, function($a, $b) { return $b['download_count'] <=> $a['download_count']; });
}

$categories = getCategories();
$total_pages = ceil($total_apps / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Apps - Prady Tec AppMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                    </div>
                    <div class="card-body">
                        <!-- Categories -->
                        <div class="mb-4">
                            <h6 class="fw-bold">Categories</h6>
                            <div class="list-group list-group-flush">
                                <a href="apps.php" class="list-group-item list-group-item-action <?php echo $category_id == 0 ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                                <?php foreach ($categories as $category): ?>
                                <a href="apps.php?category=<?php echo $category['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                                    <i class="<?php echo htmlspecialchars($category['icon']); ?> me-2"></i>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Sort Options -->
                        <div class="mb-4">
                            <h6 class="fw-bold">Sort By</h6>
                            <form method="GET" id="sortForm">
                                <?php if ($category_id > 0): ?>
                                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                                <?php endif; ?>
                                <?php if (!empty($search)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                                <select name="sort" class="form-select" onchange="document.getElementById('sortForm').submit()">
                                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                    <option value="downloads" <?php echo $sort === 'downloads' ? 'selected' : ''; ?>>Most Downloaded</option>
                                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                </select>
                            </form>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <h6 class="fw-bold">Price Range</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="priceRange" id="priceAll" checked>
                                <label class="form-check-label" for="priceAll">All Prices</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="priceRange" id="priceFree">
                                <label class="form-check-label" for="priceFree">Free</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="priceRange" id="pricePaid">
                                <label class="form-check-label" for="pricePaid">Paid</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">
                            <?php if (!empty($search)): ?>
                                Search Results for "<?php echo htmlspecialchars($search); ?>"
                            <?php elseif ($category_id > 0): ?>
                                <?php 
                                $current_category = array_filter($categories, function($cat) use ($category_id) {
                                    return $cat['id'] == $category_id;
                                });
                                $current_category = reset($current_category);
                                echo htmlspecialchars($current_category['name']); 
                                ?>
                            <?php else: ?>
                                All Apps
                            <?php endif; ?>
                        </h2>
                        <p class="text-muted mb-0"><?php echo $total_apps; ?> apps found</p>
                    </div>
                </div>

                <!-- Apps Grid -->
                <?php if (empty($apps)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No apps found</h4>
                        <p class="text-muted">Try adjusting your search criteria or browse all apps.</p>
                        <a href="apps.php" class="btn btn-primary">Browse All Apps</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($apps as $app): ?>
                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card app-card h-100">
                                <div class="card-img-top bg-light p-4 text-center">
                                    <i class="fas fa-mobile-alt fa-4x text-muted"></i>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($app['name']); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($app['developer']); ?></p>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($app['description'], 0, 100)) . '...'; ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($app['category_name']); ?></span>
                                        <div class="rating">
                                            <?php echo generateStarRating($app['rating']); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 text-success mb-0">$<?php echo number_format($app['price'], 2); ?></span>
                                        <small class="text-muted"><?php echo $app['download_count']; ?> downloads</small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-grid gap-2">
                                        <a href="app-details.php?id=<?php echo $app['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                        <?php if (isLoggedIn() && !hasUserPurchasedApp($_SESSION['user_id'], $app['id'])): ?>
                                            <button class="btn btn-outline-success add-to-cart" 
                                                    data-app-id="<?php echo $app['id']; ?>" 
                                                    data-price="<?php echo $app['price']; ?>">
                                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                            </button>
                                        <?php elseif (isLoggedIn() && hasUserPurchasedApp($_SESSION['user_id'], $app['id'])): ?>
                                            <a href="download.php?id=<?php echo $app['id']; ?>" class="btn btn-success">
                                                <i class="fas fa-download me-2"></i>Download
                                            </a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-outline-primary">
                                                <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Apps pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $category_id > 0 ? '&category=' . $category_id : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $sort !== 'newest' ? '&sort=' . $sort : ''; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id > 0 ? '&category=' . $category_id : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $sort !== 'newest' ? '&sort=' . $sort : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $category_id > 0 ? '&category=' . $category_id : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $sort !== 'newest' ? '&sort=' . $sort : ''; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
