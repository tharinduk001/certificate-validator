<?php
// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    exit('Direct access forbidden');
}

// Get the certificate ID from the request
$certificateId = isset($_POST['certificate_id']) ? $_POST['certificate_id'] : '';

// Validate input
if (empty($certificateId)) {
    echo json_encode(['valid' => false, 'message' => 'Certificate ID is required']);
    exit;
}

// Database connection configuration
$host = 'localhost'; // Usually localhost for Hostinger
$dbname = 'u420126502_yaha'; // Replace with your database name
$username = 'u420126502_cjruhunage'; // Replace with your database username
$password = 'G=4l=2ClBQ!F'; // Replace with your database password

try {
    // Create database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT * FROM certificates WHERE certificate_id = :certificate_id LIMIT 1");
    $stmt->bindParam(':certificate_id', $certificateId);
    $stmt->execute();
    
    // Fetch the result
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($certificate) {
        // Certificate found, return the details
        echo json_encode([
            'valid' => true,
            'name' => $certificate['recipient_name'],
            'course' => $certificate['course_name'],
            'issue_date' => $certificate['issue_date'],
            'expiry_date' => $certificate['expiry_date'] ?? null
        ]);
    } else {
        // Certificate not found
        echo json_encode(['valid' => false]);
    }
} catch (PDOException $e) {
    // Log the error (to a file, not to the response)
    error_log("Database Error: " . $e->getMessage());
    
    // Return a generic error message to the client
    echo json_encode(['valid' => false, 'message' => 'A database error occurred']);
}
?>