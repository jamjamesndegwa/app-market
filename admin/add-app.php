<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../index.php');
}

$error = '';
$success = '';

// Get categories for dropdown
$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $short_description = sanitizeInput($_POST['short_description']);
    $category_id = (int)$_POST['category_id'];
    $developer = sanitizeInput($_POST['developer']);
    $version = sanitizeInput($_POST['version']);
    $price = (float)$_POST['price'];
    $file_type = sanitizeInput($_POST['file_type']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validation
    if (empty($name) || empty($description) || empty($developer) || empty($version) || $price < 0) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle file upload
        $file_path = '';
        $file_size = 0;
        
        if (isset($_FILES['app_file']) && $_FILES['app_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/apps/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['app_file']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['apk', 'aab'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $file_name = uniqid() . '_' . $_FILES['app_file']['name'];
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['app_file']['tmp_name'], $file_path)) {
                    $file_size = filesize($file_path);
                } else {
                    $error = 'Failed to upload file.';
                }
            } else {
                $error = 'Invalid file type. Only APK and AAB files are allowed.';
            }
        }
        
        if (empty($error)) {
            // Insert app into database
            $stmt = $pdo->prepare("
                INSERT INTO apps (name, description, short_description, category_id, developer, version, price, file_path, file_size, file_type, featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$name, $description, $short_description, $category_id, $developer, $version, $price, $file_path, $file_size, $file_type, $featured])) {
                $success = 'App added successfully!';
                // Clear form data
                $_POST = [];
            } else {
                $error = 'Failed to add app. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add App - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-mobile-alt me-2"></i>Admin Panel
                        </h4>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="apps.php">
                                <i class="fas fa-mobile-alt me-2"></i>Apps
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-folder me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-bag me-2"></i>Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reviews.php">
                                <i class="fas fa-star me-2"></i>Reviews
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-home me-2"></i>Back to Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../pages/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add New App</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="apps.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Apps
                        </a>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Basic Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">App Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide the app name.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="developer" class="form-label">Developer *</label>
                                        <input type="text" class="form-control" id="developer" name="developer" 
                                               value="<?php echo isset($_POST['developer']) ? htmlspecialchars($_POST['developer']) : ''; ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide the developer name.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="version" class="form-label">Version *</label>
                                        <input type="text" class="form-control" id="version" name="version" 
                                               value="<?php echo isset($_POST['version']) ? htmlspecialchars($_POST['version']) : ''; ?>" required>
                                        <div class="invalid-feedback">
                                            Please provide the app version.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category *</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a category.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price ($) *</label>
                                        <input type="number" class="form-control" id="price" name="price" 
                                               value="<?php echo isset($_POST['price']) ? $_POST['price'] : '0.00'; ?>" 
                                               step="0.01" min="0" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid price.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="file_type" class="form-label">File Type *</label>
                                        <select class="form-select" id="file_type" name="file_type" required>
                                            <option value="">Select File Type</option>
                                            <option value="apk" <?php echo (isset($_POST['file_type']) && $_POST['file_type'] === 'apk') ? 'selected' : ''; ?>>APK</option>
                                            <option value="aab" <?php echo (isset($_POST['file_type']) && $_POST['file_type'] === 'aab') ? 'selected' : ''; ?>>AAB</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a file type.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="short_description" class="form-label">Short Description</label>
                                        <textarea class="form-control" id="short_description" name="short_description" rows="3" 
                                                  placeholder="Brief description for app listings..."><?php echo isset($_POST['short_description']) ? htmlspecialchars($_POST['short_description']) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Full Description *</label>
                                        <textarea class="form-control" id="description" name="description" rows="6" required
                                                  placeholder="Detailed description of the app..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                        <div class="invalid-feedback">
                                            Please provide a description.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- File Upload -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>File Upload</h5>
                                </div>
                                <div class="card-body">
                                    <div class="file-upload-area" id="file-upload-area">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <h6>Drop your APK/AAB file here</h6>
                                        <p class="text-muted small">or click to browse</p>
                                        <input type="file" class="form-control" id="app_file" name="app_file" 
                                               accept=".apk,.aab" required>
                                    </div>
                                    <div id="file-info" class="mt-3"></div>
                                </div>
                            </div>

                            <!-- Settings -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                               <?php echo (isset($_POST['featured']) && $_POST['featured']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="featured">
                                            Featured App
                                        </label>
                                        <small class="form-text text-muted">Show this app on the homepage</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Add App
                                        </button>
                                        <a href="apps.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
