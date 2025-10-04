<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">
            <i class="fas fa-mobile-alt me-2"></i>Prady Tec AppMarket
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/apps.php">Browse Apps</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/categories.php">Categories</a>
                </li>
            </ul>
            
            <!-- Search Form -->
            <form class="d-flex me-3" method="GET" action="../pages/apps.php">
                <input class="form-control form-control-sm" type="search" name="search" placeholder="Search apps..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-light btn-sm ms-2" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="../pages/cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo getCartCount(); ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../pages/dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="../pages/my-apps.php">My Apps</a></li>
                            <li><a class="dropdown-item" href="../pages/orders.php">Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="../admin/index.php">Admin Panel</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="../pages/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php if (getFlashMessage('success')): ?>
    <div class="alert alert-success alert-dismissible fade show m-0" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo getFlashMessage('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (getFlashMessage('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show m-0" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo getFlashMessage('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (getFlashMessage('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show m-0" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo getFlashMessage('warning'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (getFlashMessage('info')): ?>
    <div class="alert alert-info alert-dismissible fade show m-0" role="alert">
        <i class="fas fa-info-circle me-2"></i><?php echo getFlashMessage('info'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
