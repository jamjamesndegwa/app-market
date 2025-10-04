<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../index.php');
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(a.name LIKE ? OR a.developer LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get apps
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM apps a 
    LEFT JOIN categories c ON a.category_id = c.id 
    $where_clause
    ORDER BY a.created_at DESC 
    LIMIT ? OFFSET ?
");
$params[] = $limit;
$params[] = $offset;
$stmt->execute($params);
$apps = $stmt->fetchAll();

// Get total count
$count_stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM apps a 
    LEFT JOIN categories c ON a.category_id = c.id 
    $where_clause
");
$count_params = array_slice($params, 0, -2); // Remove limit and offset
$count_stmt->execute($count_params);
$total_apps = $count_stmt->fetch()['total'];
$total_pages = ceil($total_apps / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Apps - Admin Panel</title>
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
                            <a class="nav-link active" href="apps.php">
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
                    <h1 class="h2">Manage Apps</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add-app.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New App
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search apps...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <a href="apps.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Apps Table -->
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($apps)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-mobile-alt fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No apps found</h4>
                                <p class="text-muted">Start by adding your first app.</p>
                                <a href="add-app.php" class="btn btn-primary">Add New App</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>App</th>
                                            <th>Developer</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Downloads</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($apps as $app): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-mobile-alt fa-2x text-muted me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($app['name']); ?></h6>
                                                            <small class="text-muted">v<?php echo htmlspecialchars($app['version']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($app['developer']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($app['category_name']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold">$<?php echo number_format($app['price'], 2); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo number_format($app['download_count']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $app['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($app['status']); ?>
                                                    </span>
                                                    <?php if ($app['featured']): ?>
                                                        <span class="badge bg-warning ms-1">Featured</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($app['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../pages/app-details.php?id=<?php echo $app['id']; ?>" 
                                                           class="btn btn-outline-primary" target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-app.php?id=<?php echo $app['id']; ?>" 
                                                           class="btn btn-outline-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-outline-danger" 
                                                                onclick="deleteApp(<?php echo $app['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Apps pagination" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <script>
        function deleteApp(appId) {
            if (confirm('Are you sure you want to delete this app? This action cannot be undone.')) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-app.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'app_id';
                input.value = appId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
