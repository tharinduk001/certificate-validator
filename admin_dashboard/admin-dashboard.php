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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Certificate Admin Dashboard</title>
      <style>
      /* Base Styles */
      * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }

      body {
        font-family: "Poppins", sans-serif;
        background: linear-gradient(135deg, #5c1d1c, #992c2c, #e05e3b);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 20px;
        color: white;
      }

      /* Main Container */
      .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
      }

      /* Header Styles */
      header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 20px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
      }

      h1 {
        background: linear-gradient(135deg, #ffae00, #ff7846, #fc6c6e);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 1.8rem;
        margin: 0;
      }

      .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
      }

      .logout-btn {
        color: white;
        text-decoration: none;
        padding: 8px 16px;
        background-color: #ff3019;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
      }

      .logout-btn:hover {
        background-color: #cc2613;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(204, 38, 19, 0.4);
      }

      /* Dashboard Card */
      .dashboard-card {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
      }

      /* Controls Section */
      .controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        gap: 20px;
      }

      .search-bar {
        flex: 1;
        position: relative;
      }

      input[type="text"] {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        font-size: 14px;
        transition: all 0.3s ease;
      }

      input[type="text"]:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
      }

      input::placeholder {
        color: rgba(255, 255, 255, 0.7);
      }

      .add-btn {
        background: linear-gradient(135deg, #ff9966, #ff5e62);
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 94, 98, 0.3);
      }

      .add-btn:hover {
        background: linear-gradient(135deg, #ff5e62, #ff3019);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 94, 98, 0.4);
      }

      /* Table Styles */
      .table-container {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
      }

      table {
        width: 100%;
        border-collapse: collapse;
      }

      th, td {
        padding: 16px;
        text-align: left;
      }

      th {
        background: rgba(255, 255, 255, 0.1);
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        position: relative;
      }

      th:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 1px;
        background: linear-gradient(to right, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.1));
      }

      tr {
        transition: all 0.3s ease;
      }

      tr:hover {
        background: rgba(255, 255, 255, 0.05);
      }

      tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.02);
      }

      /* Action Buttons */
      .action-btn {
        padding: 8px 14px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        margin-right: 8px;
        transition: all 0.3s ease;
      }

      .edit {
        background-color: #ff9966;
        color: white;
      }

      .edit:hover {
        background-color: #ff7846;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 120, 70, 0.4);
      }

      .delete {
        background-color: #ff3019;
        color: white;
      }

      .delete:hover {
        background-color: #cc2613;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(204, 38, 19, 0.4);
      }

      /* Modal Styles */
      .modal {
        display: none;
        position: fixed;
        z-index: 100;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
      }

      .modal-content {
        background: linear-gradient(135deg, #7a2e20, #b34832);
        margin: 80px auto;
        padding: 30px;
        border-radius: 16px;
        width: 90%;
        max-width: 500px;
        color: white;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        position: relative;
      }

      .modal h2 {
        margin-bottom: 20px;
        color: rgba(255, 255, 255, 0.95);
      }

      .close {
        color: rgba(255, 255, 255, 0.8);
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .close:hover {
        color: white;
        transform: rotate(90deg);
      }

      /* Form Styles */
      .form-group {
        margin-bottom: 20px;
      }

      label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
      }

      .input-group {
        display: flex;
        gap: 10px;
      }

      .modal input[type="text"],
      .modal input[type="date"],
      .modal select {
        width: 100%;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        font-size: 14px;
        transition: all 0.3s ease;
      }

      .modal input:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
      }

      .generate-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 8px;
        padding: 12px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        /* white-space: wordwrap; */
      }

      .generate-btn:hover {
        background: rgba(255, 255, 255, 0.3);
      }

      .submit-btn {
        background: linear-gradient(135deg, #ff9966, #ff5e62);
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(255, 94, 98, 0.3);
        margin-top: 10px;
        width: 100%;
      }

      .submit-btn:hover {
        background: linear-gradient(135deg, #ff5e62, #ff3019);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 94, 98, 0.4);
      }

      /* Message Styles */
      .success-message,
      .error-message {
        margin: 20px 0;
        padding: 12px 20px;
        border-radius: 8px;
        display: none;
        font-weight: 500;
        animation: fadeIn 0.5s ease;
      }

      .success-message {
        background-color: rgba(76, 175, 80, 0.2);
        border-left: 4px solid #4CAF50;
        color: #E8F5E9;
      }

      .error-message {
        background-color: rgba(244, 67, 54, 0.2);
        border-left: 4px solid #F44336;
        color: #FFEBEE;
      }

      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
      }

      /* Responsive Styles */
      @media screen and (max-width: 768px) {
        .container {
          padding: 10px;
        }

        header {
          flex-direction: column;
          align-items: flex-start;
          gap: 15px;
          padding: 15px;
        }

        .user-info {
          width: 100%;
          justify-content: space-between;
        }

        .dashboard-card {
          padding: 20px;
        }

        .controls {
          flex-direction: column;
          align-items: stretch;
        }

        .search-bar {
          width: 100%;
          margin-bottom: 15px;
        }

        .add-btn {
          width: 100%;
        }

        /* Table responsive styles */
        table, thead, tbody, th, td, tr {
          display: block;
        }

        table {
          border-radius: 16px;
          overflow: hidden;
        }

        thead tr {
          position: absolute;
          top: -9999px;
          left: -9999px;
        }

        tr {
          margin-bottom: 15px;
          border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        tr:last-child {
          margin-bottom: 0;
          border-bottom: none;
        }

        td {
          border: none;
          position: relative;
          padding: 12px 12px 12px 50%;
          min-height: 40px;
        }

        td:before {
          position: absolute;
          left: 12px;
          width: 45%;
          padding-right: 10px;
          white-space: nowrap;
          content: attr(data-label);
          font-weight: 600;
          color: rgba(255, 255, 255, 0.8);
        }

        .action-btn {
          display: inline-block;
          margin-bottom: 5px;
        }

        .modal-content {
          margin: 60px auto;
          padding: 20px;
          width: 95%;
        }

        .input-group {
          flex-direction: column;
        }

        .generate-btn {
          width: 100%;
        }
      }

      @media screen and (max-width: 480px) {
        h1 {
          font-size: 1.5rem;
        }

        .dashboard-card {
          padding: 15px;
        }

        .modal-content {
          padding: 15px;
        }

        td {
          padding: 10px 10px 10px 45%;
        }

        .action-btn {
          width: 100%;
          margin-right: 0;
          text-align: center;
        }
      }

      /* Visual Enhancements */
      .dashboard-card, header, .table-container {
        border: 1px solid rgba(255, 255, 255, 0.1);
      }

      button, .logout-btn {
        -webkit-tap-highlight-color: transparent;
        transition: transform 0.1s, box-shadow 0.3s;
        position: relative;
        overflow: hidden;
      }

      button:active, .logout-btn:active {
        transform: scale(0.98);
      }

      .no-records {
        text-align: center;
        padding: 30px;
        color: rgba(255, 255, 255, 0.7);
        font-style: italic;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <header>
        <h1>Yaha Wellness Certificate Management</h1>
        <div class="user-info">
          <span class="username"
            >Logged in as:
            <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span
          >
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="controls">
        <div class="search-bar">
          <input
            type="text"
            id="search-input"
            placeholder="Search certificates..."
          />
        </div>
        <button class="add-btn" id="add-certificate-btn">
          Add New Certificate
        </button>
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
            <td>
              <?php echo htmlspecialchars($cert['expiry_date'] ?? 'N/A'); ?>
            </td>
            <td>
              <button
                class="action-btn edit"
                data-id="<?php echo $cert['id']; ?>"
              >
                Edit
              </button>
              <button
                class="action-btn delete"
                data-id="<?php echo $cert['id']; ?>"
              >
                Delete
              </button>
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
          <input type="hidden" id="certificate-id" name="id" />

          <div class="form-group">
            <label for="certificate-id-input">Certificate ID</label>
            <div class="input-group">
              <input
                type="text"
                id="certificate-id-input"
                name="certificate_id"
                required
              />
              <button type="button" id="generate-id-btn" class="generate-btn">
                Generate Unique ID
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="recipient-name">Recipient Name</label>
            <input
              type="text"
              id="recipient-name"
              name="recipient_name"
              required
            />
          </div>

          <div class="form-group">
            <label for="course-name">Course Name</label>
            <input type="text" id="course-name" name="course_name" required />
          </div>

          <div class="form-group">
            <label for="issue-date">Issue Date</label>
            <input type="date" id="issue-date" name="issue_date" required />
          </div>

          <div class="form-group">
            <label for="expiry-date">Expiry Date (Optional)</label>
            <input type="date" id="expiry-date" name="expiry_date" />
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
        <p>
          Are you sure you want to delete this certificate? This action cannot
          be undone.
        </p>
        <input type="hidden" id="delete-certificate-id" />
        <button
          id="confirm-delete"
          class="submit-btn"
          style="background: linear-gradient(135deg, #ff3019, #cc2613)"
        >
          Delete
        </button>
      </div>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Elements
        const certificateModal = document.getElementById("certificate-modal");
        const deleteModal = document.getElementById("delete-modal");
        const certificateForm = document.getElementById("certificate-form");
        const addCertificateBtn = document.getElementById(
          "add-certificate-btn"
        );
        const modalTitle = document.getElementById("modal-title");
        const searchInput = document.getElementById("search-input");
        const errorMessage = document.getElementById("error-message");
        const successMessage = document.getElementById("success-message");
        const generateIdBtn = document.getElementById("generate-id-btn");

        // Store generated certificate IDs to prevent duplicates within the session
        const generatedIds = new Set();

        // Generate unique ID function
        generateIdBtn.addEventListener("click", function () {
          const certificateIdInput = document.getElementById(
            "certificate-id-input"
          );
          let newId;
          do {
            // Generate a random 20-char alphanumeric string
            let chars =
              "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            newId = "";
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
        const closeButtons = document.getElementsByClassName("close");
        for (let i = 0; i < closeButtons.length; i++) {
          closeButtons[i].addEventListener("click", function () {
            certificateModal.style.display = "none";
            deleteModal.style.display = "none";
          });
        }

        // Close modal when clicking outside
        window.addEventListener("click", function (event) {
          if (event.target === certificateModal) {
            certificateModal.style.display = "none";
          }
          if (event.target === deleteModal) {
            deleteModal.style.display = "none";
          }
        });

        // Add new certificate button
        addCertificateBtn.addEventListener("click", function () {
          // Reset form
          certificateForm.reset();
          document.getElementById("certificate-id").value = "";
          modalTitle.textContent = "Add New Certificate";

          // Set default issue date to today
          const today = new Date().toISOString().split("T")[0];
          document.getElementById("issue-date").value = today;

          // Show modal
          certificateModal.style.display = "block";
        });

        // Edit certificate buttons
        const editButtons = document.getElementsByClassName("edit");
        for (let i = 0; i < editButtons.length; i++) {
          editButtons[i].addEventListener("click", function () {
            const certificateId = this.getAttribute("data-id");

            // Fetch certificate data
            fetch("certificate-actions.php?action=get&id=" + certificateId)
              .then((response) => response.json())
              .then((data) => {
                if (data.success) {
                  const certificate = data.certificate;

                  // Populate form
                  document.getElementById("certificate-id").value =
                    certificate.id;
                  document.getElementById("certificate-id-input").value =
                    certificate.certificate_id;
                  document.getElementById("recipient-name").value =
                    certificate.recipient_name;
                  document.getElementById("course-name").value =
                    certificate.course_name;
                  document.getElementById("issue-date").value =
                    certificate.issue_date;
                  document.getElementById("expiry-date").value =
                    certificate.expiry_date || "";

                  // Add to generated IDs set if editing
                  if (certificate.certificate_id) {
                    generatedIds.add(certificate.certificate_id);
                  }

                  // Update modal title and show
                  modalTitle.textContent = "Edit Certificate";
                  certificateModal.style.display = "block";
                } else {
                  showError(data.message);
                }
              })
              .catch((error) => {
                console.error("Error:", error);
                showError("An error occurred while fetching certificate data.");
              });
          });
        }

        // Delete certificate buttons
        const deleteButtons = document.getElementsByClassName("delete");
        for (let i = 0; i < deleteButtons.length; i++) {
          deleteButtons[i].addEventListener("click", function () {
            const certificateId = this.getAttribute("data-id");
            document.getElementById("delete-certificate-id").value =
              certificateId;
            deleteModal.style.display = "block";
          });
        }

        // Confirm delete button
        document
          .getElementById("confirm-delete")
          .addEventListener("click", function () {
            const certificateId = document.getElementById(
              "delete-certificate-id"
            ).value;

            // Send delete request
            fetch("certificate-actions.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
              body: "action=delete&id=" + encodeURIComponent(certificateId),
            })
              .then((response) => response.json())
              .then((data) => {
                // Hide delete modal
                deleteModal.style.display = "none";

                if (data.success) {
                  // Show success message and reload page
                  showSuccess("Certificate deleted successfully.");
                  setTimeout(() => {
                    window.location.reload();
                  }, 1500);
                } else {
                  showError(data.message);
                }
              })
              .catch((error) => {
                console.error("Error:", error);
                deleteModal.style.display = "none";
                showError("An error occurred while deleting the certificate.");
              });
          });

        // Certificate form submission
        certificateForm.addEventListener("submit", function (event) {
          event.preventDefault();

          const formData = new FormData(certificateForm);
          const id = document.getElementById("certificate-id").value;

          // Determine if this is an add or edit operation
          const action = id ? "edit" : "add";
          formData.append("action", action);

          // Convert FormData to URL encoded string
          const urlEncodedData = new URLSearchParams(formData).toString();

          // Send request
          fetch("certificate-actions.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: urlEncodedData,
          })
            .then((response) => response.json())
            .then((data) => {
              // Hide modal
              certificateModal.style.display = "none";

              if (data.success) {
                // Show success message and reload page
                showSuccess(
                  action === "add"
                    ? "Certificate added successfully."
                    : "Certificate updated successfully."
                );
                setTimeout(() => {
                  window.location.reload();
                }, 1500);
              } else {
                showError(data.message);
              }
            })
            .catch((error) => {
              console.error("Error:", error);
              certificateModal.style.display = "none";
              showError("An error occurred while saving the certificate.");
            });
        });

        // Search functionality
        searchInput.addEventListener("keyup", function () {
          const searchTerm = this.value.toLowerCase();
          const rows = document
            .getElementById("certificates-table")
            .getElementsByTagName("tbody")[0]
            .getElementsByTagName("tr");

          for (let i = 0; i < rows.length; i++) {
            const certificateId = rows[i].getElementsByTagName("td")[0];
            const recipientName = rows[i].getElementsByTagName("td")[1];
            const courseName = rows[i].getElementsByTagName("td")[2];

            if (certificateId && recipientName && courseName) {
              const text =
                certificateId.textContent.toLowerCase() +
                recipientName.textContent.toLowerCase() +
                courseName.textContent.toLowerCase();

              if (text.indexOf(searchTerm) > -1) {
                rows[i].style.display = "";
              } else {
                rows[i].style.display = "none";
              }
            }
          }
        });

        // Add existing certificate IDs to the generated IDs set
        document
          .querySelectorAll("#certificates-table tbody tr")
          .forEach((row) => {
            const certificateIdCell = row.querySelector("td:first-child");
            if (certificateIdCell) {
              generatedIds.add(certificateIdCell.textContent.trim());
            }
          });

    // Add data-labels for mobile responsive table
    document.querySelectorAll('#certificates-table tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        const labels = ['Certificate ID', 'Recipient Name', 'Course Name', 'Issue Date', 'Expiry Date', 'Actions'];
        
        cells.forEach((cell, index) => {
            if (index < labels.length) {
                cell.setAttribute('data-label', labels[index]);
            }
        });
    });

        // Helper functions
        function showError(message) {
          errorMessage.textContent = message;
          errorMessage.style.display = "block";
          successMessage.style.display = "none";

          // Hide after 3 seconds
          setTimeout(() => {
            errorMessage.style.display = "none";
          }, 3000);
        }

        function showSuccess(message) {
          successMessage.textContent = message;
          successMessage.style.display = "block";
          errorMessage.style.display = "none";

          // Hide after 3 seconds
          setTimeout(() => {
            successMessage.style.display = "none";
          }, 3000);
        }
      });
    </script>
  </body>
</html>