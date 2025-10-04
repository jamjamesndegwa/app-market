<?php
session_start();
require_once '../includes/functions.php';

// Clear all session data
session_unset();
session_destroy();

// Redirect to home page with success message
setFlashMessage('success', 'You have been logged out successfully.');
redirect('../index.php');
?>
