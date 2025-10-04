<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please log in to download apps.');
    redirect('login.php');
}

$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($app_id <= 0) {
    setFlashMessage('error', 'Invalid app ID.');
    redirect('my-apps.php');
}

$app = getAppById($app_id);
if (!$app) {
    setFlashMessage('error', 'App not found.');
    redirect('my-apps.php');
}

// Check if user has purchased this app
if (!hasUserPurchasedApp($_SESSION['user_id'], $app_id)) {
    setFlashMessage('error', 'You must purchase this app before downloading it.');
    redirect('app-details.php?id=' . $app_id);
}

// Update download count
updateAppDownloadCount($app_id);

// Record user download
$stmt = $pdo->prepare("
    INSERT INTO user_downloads (user_id, app_id, download_count) 
    VALUES (?, ?, 1)
    ON DUPLICATE KEY UPDATE 
    download_count = download_count + 1,
    last_downloaded = CURRENT_TIMESTAMP
");
$stmt->execute([$_SESSION['user_id'], $app_id]);

// For demo purposes, we'll show a download link
// In a real application, you would serve the actual file
$download_url = "uploads/apps/" . $app['file_path'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download <?php echo htmlspecialchars($app['name']); ?> - Prady Tec AppMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-download fa-5x text-success mb-3"></i>
                            <h2 class="text-success">Download Ready!</h2>
                        </div>

                        <div class="mb-4">
                            <h4><?php echo htmlspecialchars($app['name']); ?></h4>
                            <p class="text-muted">by <?php echo htmlspecialchars($app['developer']); ?></p>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="fas fa-mobile-alt fa-2x text-primary mb-2"></i>
                                    <div class="fw-bold"><?php echo strtoupper($app['file_type']); ?></div>
                                    <small class="text-muted">File Type</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="fas fa-hdd fa-2x text-primary mb-2"></i>
                                    <div class="fw-bold"><?php echo formatFileSize($app['file_size']); ?></div>
                                    <small class="text-muted">File Size</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="fas fa-code-branch fa-2x text-primary mb-2"></i>
                                    <div class="fw-bold"><?php echo htmlspecialchars($app['version']); ?></div>
                                    <small class="text-muted">Version</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Download Instructions:</strong>
                            <ul class="mb-0 mt-2 text-start">
                                <li>Click the download button below to start the download</li>
                                <li>For APK files: Enable "Install from unknown sources" in your Android settings</li>
                                <li>For AAB files: Use Google Play Console or Android Studio to install</li>
                                <li>Keep your download link safe - you can re-download anytime from your account</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-block">
                            <a href="<?php echo $download_url; ?>" class="btn btn-success btn-lg" download>
                                <i class="fas fa-download me-2"></i>Download Now
                            </a>
                            <a href="my-apps.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Back to My Apps
                            </a>
                        </div>

                        <div class="mt-4">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                This download is secure and virus-free. Downloaded on <?php echo date('M d, Y H:i'); ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- App Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>App Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Developer:</strong></td>
                                        <td><?php echo htmlspecialchars($app['developer']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Category:</strong></td>
                                        <td><?php echo htmlspecialchars($app['category_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>File Type:</strong></td>
                                        <td><?php echo strtoupper($app['file_type']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Version:</strong></td>
                                        <td><?php echo htmlspecialchars($app['version']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Size:</strong></td>
                                        <td><?php echo formatFileSize($app['file_size']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Downloads:</strong></td>
                                        <td><?php echo number_format($app['download_count']); ?></td>
                                    </tr>
                                </table>
                            </div>
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
