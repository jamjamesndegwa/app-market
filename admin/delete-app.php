<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'])) {
    $app_id = (int)$_POST['app_id'];
    
    try {
        // Get app details first
        $stmt = $pdo->prepare("SELECT * FROM apps WHERE id = ?");
        $stmt->execute([$app_id]);
        $app = $stmt->fetch();
        
        if ($app) {
            // Delete the app file if it exists
            if ($app['file_path'] && file_exists($app['file_path'])) {
                unlink($app['file_path']);
            }
            
            // Delete the app from database
            $stmt = $pdo->prepare("DELETE FROM apps WHERE id = ?");
            $stmt->execute([$app_id]);
            
            setFlashMessage('success', 'App deleted successfully.');
        } else {
            setFlashMessage('error', 'App not found.');
        }
    } catch (Exception $e) {
        setFlashMessage('error', 'Failed to delete app: ' . $e->getMessage());
    }
}

redirect('apps.php');
?>
