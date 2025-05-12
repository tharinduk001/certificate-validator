<?php
// Start session to manage login state
session_start();

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden');
}

// Get username and password from request
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Basic validation
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

// In a real application, you would store these credentials in a database
// For simplicity, we're using hardcoded values
// IMPORTANT: Change these values before deploying to production!
$validUsername = 'CJRuhunage';
$validPasswordHash = password_hash('YWA#1', PASSWORD_DEFAULT); // Use password_hash when setting up

// Check if the credentials are valid
if ($username === $validUsername && password_verify($password, $validPasswordHash)) {
    // Credentials are valid, set session variables
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    
    // Return success response
    echo json_encode(['success' => true]);
} else {
    // Invalid credentials
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}
?>
