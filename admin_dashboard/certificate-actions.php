<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Include database configuration
require_once 'config.php';

// Initialize database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

// Handle GET requests (fetch certificate data)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM certificates WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($certificate) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'certificate' => $certificate]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Certificate not found']);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    
    exit;
}

// Handle POST requests (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Set header for all responses
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'add':
            // Validate required fields
            if (empty($_POST['certificate_id']) || empty($_POST['recipient_name']) || 
                empty($_POST['course_name']) || empty($_POST['issue_date'])) {
                echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
                exit;
            }
            
            // Check if certificate ID already exists
            try {
                $stmt = $conn->prepare("SELECT id FROM certificates WHERE certificate_id = :certificate_id LIMIT 1");
                $stmt->bindParam(':certificate_id', $_POST['certificate_id']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Certificate ID already exists']);
                    exit;
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error']);
                exit;
            }
            
            // Insert new certificate
            try {
                $sql = "INSERT INTO certificates (certificate_id, recipient_name, course_name, issue_date, expiry_date) 
                        VALUES (:certificate_id, :recipient_name, :course_name, :issue_date, :expiry_date)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':certificate_id', $_POST['certificate_id']);
                $stmt->bindParam(':recipient_name', $_POST['recipient_name']);
                $stmt->bindParam(':course_name', $_POST['course_name']);
                $stmt->bindParam(':issue_date', $_POST['issue_date']);
                
                // Handle optional expiry date
                $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
                $stmt->bindParam(':expiry_date', $expiryDate);
                
                $stmt->execute();
                
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error adding certificate: ' . $e->getMessage()]);
            }
            break;
            
        case 'edit':
            // Validate required fields
            if (empty($_POST['id']) || empty($_POST['certificate_id']) || empty($_POST['recipient_name']) || 
                empty($_POST['course_name']) || empty($_POST['issue_date'])) {
                echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
                exit;
            }
            
            // Check if certificate ID already exists (for another certificate)
            try {
                $stmt = $conn->prepare("SELECT id FROM certificates WHERE certificate_id = :certificate_id AND id != :id LIMIT 1");
                $stmt->bindParam(':certificate_id', $_POST['certificate_id']);
                $stmt->bindParam(':id', $_POST['id']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Certificate ID already exists for another certificate']);
                    exit;
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error']);
                exit;
            }
            
            // Update certificate
            try {
                $sql = "UPDATE certificates SET 
                        certificate_id = :certificate_id,
                        recipient_name = :recipient_name,
                        course_name = :course_name,
                        issue_date = :issue_date,
                        expiry_date = :expiry_date
                        WHERE id = :id";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':certificate_id', $_POST['certificate_id']);
                $stmt->bindParam(':recipient_name', $_POST['recipient_name']);
                $stmt->bindParam(':course_name', $_POST['course_name']);
                $stmt->bindParam(':issue_date', $_POST['issue_date']);
                
                // Handle optional expiry date
                $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
                $stmt->bindParam(':expiry_date', $expiryDate);
                
                $stmt->bindParam(':id', $_POST['id']);
                
                $stmt->execute();
                
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error updating certificate: ' . $e->getMessage()]);
            }
            break;
            
        case 'delete':
            // Validate required fields
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'Certificate ID is required']);
                exit;
            }
            
            // Delete certificate
            try {
                $stmt = $conn->prepare("DELETE FROM certificates WHERE id = :id");
                $stmt->bindParam(':id', $_POST['id']);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Certificate not found']);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error deleting certificate: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    exit;
}

// Handle invalid requests
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>