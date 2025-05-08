<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page if not logged in
    header('Location: admin-login.html');
    exit;
}

// Include database configuration
require_once 'config.php';

// Function to get all certificates
function getAllCertificates($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM certificates ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return [];
    }
}

// Initialize database connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all certificates
    $certificates = getAllCertificates($conn);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $certificates = [];
    $dbError = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        h1 {
            color: #333;
            margin: 0;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .username {
            margin-right: 15px;
        }
        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
        .controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .search-bar {
            flex-grow: 1;
            max-width: 400px;
            position: relative;
        }
        .search-bar input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .add-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .add-btn:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f8f8;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            margin-right: 10px;
            color: #2196F3;
            font-size: 14px;
        }
        .action-btn.delete {
            color: #f44336;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .input-group {
            display: flex;
            gap: 10px;
        }
        .input-group input {
            flex-grow: 1;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .generate-btn {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
        }
        .generate-btn:hover {
            background-color: #0b7dda;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .error-message {
            color: #a94442;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }
        .success-message {
            color: #3c763d;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }
        .no-records {
            padding: 20px;
            text-align: center;
            font-size: 16px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Certificate Management</h1>
            <div class="user-info">
                <span class="username">Logged in as: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>
        
        <div class="controls">
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search certificates...">
            </div>
            <button class="add-btn" id="add-certificate-btn">Add New Certificate</button>
        </div>
        
        <div class="error-message" id="error-message"></div>
        <div class="success-message" id="success-message"></div>
        
        <table id="certificates-table">
            <thead>
                <tr>
                    <th>Certificate ID</th>
                    <th>Recipient Name</th>
                    <th>Course Name</th>
                    <th>Issue Date</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($certificates)): ?>
                    <tr>
                        <td colspan="6" class="no-records">No certificates found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($certificates as $cert): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cert['certificate_id']); ?></td>
                            <td><?php echo htmlspecialchars($cert['recipient_name']); ?></td>
                            <td><?php echo htmlspecialchars($cert['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($cert['issue_date']); ?></td>
                            <td><?php echo htmlspecialchars($cert['expiry_date'] ?? 'N/A'); ?></td>
                            <td>
                                <button class="action-btn edit" data-id="<?php echo $cert['id']; ?>">Edit</button>
                                <button class="action-btn delete" data-id="<?php echo $cert['id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add/Edit Certificate Modal -->
    <div id="certificate-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modal-title">Add New Certificate</h2>
            <form id="certificate-form">
                <input type="hidden" id="certificate-id" name="id">
                
                <div class="form-group">
                    <label for="certificate-id-input">Certificate ID</label>
                    <div class="input-group">
                        <input type="text" id="certificate-id-input" name="certificate_id" required>
                        <button type="button" id="generate-id-btn" class="generate-btn">Generate Unique ID</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="recipient-name">Recipient Name</label>
                    <input type="text" id="recipient-name" name="recipient_name" required>
                </div>
                
                <div class="form-group">
                    <label for="course-name">Course Name</label>
                    <input type="text" id="course-name" name="course_name" required>
                </div>
                
                <div class="form-group">
                    <label for="issue-date">Issue Date</label>
                    <input type="date" id="issue-date" name="issue_date" required>
                </div>
                
                <div class="form-group">
                    <label for="expiry-date">Expiry Date (Optional)</label>
                    <input type="date" id="expiry-date" name="expiry_date">
                </div>
                
                <button type="submit" class="submit-btn">Save Certificate</button>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this certificate? This action cannot be undone.</p>
            <input type="hidden" id="delete-certificate-id">
            <button id="confirm-delete" class="submit-btn" style="background-color: #f44336;">Delete</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const certificateModal = document.getElementById('certificate-modal');
            const deleteModal = document.getElementById('delete-modal');
            const certificateForm = document.getElementById('certificate-form');
            const addCertificateBtn = document.getElementById('add-certificate-btn');
            const modalTitle = document.getElementById('modal-title');
            const searchInput = document.getElementById('search-input');
            const errorMessage = document.getElementById('error-message');
            const successMessage = document.getElementById('success-message');
            const generateIdBtn = document.getElementById('generate-id-btn');
            
            // Store generated certificate IDs to prevent duplicates within the session
            const generatedIds = new Set();
            
            // Generate unique ID function
            generateIdBtn.addEventListener('click', function() {
                const certificateIdInput = document.getElementById('certificate-id-input');
                let newId;
                do {
                    // Generate a random 20-char alphanumeric string
                    let chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                    newId = '';
                    for (let i = 0; i < 20; i++) {
                        newId += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                } while (generatedIds.has(newId)); // Ensure uniqueness
                
                // Add to generated IDs set
                generatedIds.add(newId);
                
                // Set the value in the input field
                certificateIdInput.value = newId;
            });
            
            // Modal close buttons
            const closeButtons = document.getElementsByClassName('close');
            for (let i = 0; i < closeButtons.length; i++) {
                closeButtons[i].addEventListener('click', function() {
                    certificateModal.style.display = 'none';
                    deleteModal.style.display = 'none';
                });
            }
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === certificateModal) {
                    certificateModal.style.display = 'none';
                }
                if (event.target === deleteModal) {
                    deleteModal.style.display = 'none';
                }
            });
            
            // Add new certificate button
            addCertificateBtn.addEventListener('click', function() {
                // Reset form
                certificateForm.reset();
                document.getElementById('certificate-id').value = '';
                modalTitle.textContent = 'Add New Certificate';
                
                // Set default issue date to today
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('issue-date').value = today;
                
                // Show modal
                certificateModal.style.display = 'block';
            });
            
            // Edit certificate buttons
            const editButtons = document.getElementsByClassName('edit');
            for (let i = 0; i < editButtons.length; i++) {
                editButtons[i].addEventListener('click', function() {
                    const certificateId = this.getAttribute('data-id');
                    
                    // Fetch certificate data
                    fetch('certificate-actions.php?action=get&id=' + certificateId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const certificate = data.certificate;
                                
                                // Populate form
                                document.getElementById('certificate-id').value = certificate.id;
                                document.getElementById('certificate-id-input').value = certificate.certificate_id;
                                document.getElementById('recipient-name').value = certificate.recipient_name;
                                document.getElementById('course-name').value = certificate.course_name;
                                document.getElementById('issue-date').value = certificate.issue_date;
                                document.getElementById('expiry-date').value = certificate.expiry_date || '';
                                
                                // Add to generated IDs set if editing
                                if (certificate.certificate_id) {
                                    generatedIds.add(certificate.certificate_id);
                                }
                                
                                // Update modal title and show
                                modalTitle.textContent = 'Edit Certificate';
                                certificateModal.style.display = 'block';
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showError('An error occurred while fetching certificate data.');
                        });
                });
            }
            
            // Delete certificate buttons
            const deleteButtons = document.getElementsByClassName('delete');
            for (let i = 0; i < deleteButtons.length; i++) {
                deleteButtons[i].addEventListener('click', function() {
                    const certificateId = this.getAttribute('data-id');
                    document.getElementById('delete-certificate-id').value = certificateId;
                    deleteModal.style.display = 'block';
                });
            }
            
            // Confirm delete button
            document.getElementById('confirm-delete').addEventListener('click', function() {
                const certificateId = document.getElementById('delete-certificate-id').value;
                
                // Send delete request
                fetch('certificate-actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete&id=' + encodeURIComponent(certificateId)
                })
                .then(response => response.json())
                .then(data => {
                    // Hide delete modal
                    deleteModal.style.display = 'none';
                    
                    if (data.success) {
                        // Show success message and reload page
                        showSuccess('Certificate deleted successfully.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    deleteModal.style.display = 'none';
                    showError('An error occurred while deleting the certificate.');
                });
            });
            
            // Certificate form submission
            certificateForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const formData = new FormData(certificateForm);
                const id = document.getElementById('certificate-id').value;
                
                // Determine if this is an add or edit operation
                const action = id ? 'edit' : 'add';
                formData.append('action', action);
                
                // Convert FormData to URL encoded string
                const urlEncodedData = new URLSearchParams(formData).toString();
                
                // Send request
                fetch('certificate-actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: urlEncodedData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide modal
                    certificateModal.style.display = 'none';
                    
                    if (data.success) {
                        // Show success message and reload page
                        showSuccess(action === 'add' ? 'Certificate added successfully.' : 'Certificate updated successfully.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    certificateModal.style.display = 'none';
                    showError('An error occurred while saving the certificate.');
                });
            });
            
            // Search functionality
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.getElementById('certificates-table').getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                
                for (let i = 0; i < rows.length; i++) {
                    const certificateId = rows[i].getElementsByTagName('td')[0];
                    const recipientName = rows[i].getElementsByTagName('td')[1];
                    const courseName = rows[i].getElementsByTagName('td')[2];
                    
                    if (certificateId && recipientName && courseName) {
                        const text = certificateId.textContent.toLowerCase() + 
                                     recipientName.textContent.toLowerCase() + 
                                     courseName.textContent.toLowerCase();
                        
                        if (text.indexOf(searchTerm) > -1) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                }
            });
            
            // Add existing certificate IDs to the generated IDs set
            document.querySelectorAll('#certificates-table tbody tr').forEach(row => {
                const certificateIdCell = row.querySelector('td:first-child');
                if (certificateIdCell) {
                    generatedIds.add(certificateIdCell.textContent.trim());
                }
            });
            
            // Helper functions
            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.style.display = 'block';
                successMessage.style.display = 'none';
                
                // Hide after 3 seconds
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 3000);
            }
            
            function showSuccess(message) {
                successMessage.textContent = message;
                successMessage.style.display = 'block';
                errorMessage.style.display = 'none';
                
                // Hide after 3 seconds
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);
            }
        });
    </script>
</body>
</html>