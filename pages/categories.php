<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$categories = getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Prady Tec AppMarket</title>
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
                <li class="breadcrumb-item active">Categories</li>
            </ol>
        </nav>

        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Browse by Category</h1>
            <p class="lead text-muted">Discover apps organized by category</p>
        </div>

        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card category-card h-100 text-center">
                        <div class="card-body">
                            <i class="<?php echo htmlspecialchars($category['icon']); ?> fa-4x text-primary mb-4"></i>
                            <h4 class="card-title fw-bold"><?php echo htmlspecialchars($category['name']); ?></h4>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
                            <a href="apps.php?category=<?php echo $category['id']; ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right me-2"></i>Explore Apps
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
