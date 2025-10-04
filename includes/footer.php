<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-mobile-alt me-2"></i>Prady Tec AppMarket
                </h5>
                <p class="text-muted">
                    Your premier destination for premium mobile applications. 
                    Discover, download, and enjoy the latest AAB and APK files 
                    for your Android devices.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="pages/apps.php" class="text-muted text-decoration-none">Browse Apps</a></li>
                    <li class="mb-2"><a href="pages/categories.php" class="text-muted text-decoration-none">Categories</a></li>
                    <li class="mb-2"><a href="pages/featured.php" class="text-muted text-decoration-none">Featured</a></li>
                    <li class="mb-2"><a href="pages/top-rated.php" class="text-muted text-decoration-none">Top Rated</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Support</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="pages/help.php" class="text-muted text-decoration-none">Help Center</a></li>
                    <li class="mb-2"><a href="pages/contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
                    <li class="mb-2"><a href="pages/faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                    <li class="mb-2"><a href="pages/terms.php" class="text-muted text-decoration-none">Terms of Service</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Account</h6>
                <ul class="list-unstyled">
                    <?php if (isLoggedIn()): ?>
                        <li class="mb-2"><a href="pages/dashboard.php" class="text-muted text-decoration-none">Dashboard</a></li>
                        <li class="mb-2"><a href="pages/my-apps.php" class="text-muted text-decoration-none">My Apps</a></li>
                        <li class="mb-2"><a href="pages/orders.php" class="text-muted text-decoration-none">Orders</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a href="pages/login.php" class="text-muted text-decoration-none">Login</a></li>
                        <li class="mb-2"><a href="pages/register.php" class="text-muted text-decoration-none">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Developers</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="pages/developer-program.php" class="text-muted text-decoration-none">Developer Program</a></li>
                    <li class="mb-2"><a href="pages/submit-app.php" class="text-muted text-decoration-none">Submit App</a></li>
                    <li class="mb-2"><a href="pages/guidelines.php" class="text-muted text-decoration-none">Guidelines</a></li>
                    <li class="mb-2"><a href="pages/analytics.php" class="text-muted text-decoration-none">Analytics</a></li>
                </ul>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">
                    &copy; <?php echo date('Y'); ?> Prady Tec AppMarket. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted mb-0">
                    <a href="pages/privacy.php" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="pages/terms.php" class="text-muted text-decoration-none">Terms of Service</a>
                </p>
            </div>
        </div>
    </div>
</footer>
