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
      body {
        margin: 0;
        font-family: "Poppins", sans-serif;
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 40px 20px;
        color: white;
      }

      .dashboard-container {
        width: 100%;
        max-width: 1100px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(15px);
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      }

      header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
      }

      h1 {
        background: linear-gradient(135deg, #5c9efb, #428df5, #3663de);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        color: transparent;
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
        padding: 6px 12px;
        background-color: #f44336;
        border-radius: 8px;
        font-weight: bold;
        transition: 0.3s;
      }

      .logout-btn:hover {
        background-color: #d32f2f;
      }

      .controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        gap: 20px;
        flex-wrap: wrap;
      }

      .search-bar {
        flex: 1;
      }

      input[type="text"] {
        width: 100%;
        padding: 10px 14px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        font-size: 14px;
      }

      input::placeholder {
        color: #eee;
      }

      .add-btn {
        background: linear-gradient(135deg, #5c9efb, #428df5);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
      }

      .add-btn:hover {
        background: linear-gradient(135deg, #3663de, #428df5);
        transform: scale(1.05);
      }

      table {
        width: 100%;
        margin-top: 25px;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        overflow: hidden;
      }

      th,
      td {
        padding: 14px 16px;
        text-align: left;
      }

      th {
        background: rgba(255, 255, 255, 0.15);
        font-weight: 600;
      }

      tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.03);
      }

      .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        margin-right: 5px;
      }

      .edit {
        background-color: #4caf50;
        color: white;
      }

      .delete {
        background-color: #f44336;
        color: white;
      }

      .modal {
        display: none;
        position: fixed;
        z-index: 100;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
      }

      .modal-content {
        background-color: #1e2d3b;
        margin: 80px auto;
        padding: 20px;
        border-radius: 10px;
        width: calc(100% - 40px); /* ensures margin on both left and right */
        max-width: 500px;
        color: white;
        box-sizing: border-box;
        position: relative;
      }

      .modal input[type="text"],
      .modal input[type="date"],
      .modal select {
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        box-sizing: border-box;
      }

      .close {
        color: #aaa;
        position: absolute;
        right: 20px;
        top: 10px;
        font-size: 24px;
        cursor: pointer;
      }

      .form-group {
        margin-bottom: 15px;
      }

      label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
      }

      input[type="date"],
      .modal input[type="text"] {
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.15);
        color: white;
      }

      .submit-btn {
        background: linear-gradient(135deg, #5c9efb, #428df5);
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
      }

      .submit-btn:hover {
        background: linear-gradient(135deg, #3663de, #428df5);
      }

      .input-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }

      .input-group input {
        flex: 1;
        min-width: 0;
      }

      .generate-btn {
        background: #616161;
        border: none;
        border-radius: 6px;
        padding: 10px 12px;
        color: white;
        cursor: pointer;
        white-space: nowrap;
      }

      .success-message,
      .error-message {
        margin-top: 15px;
        padding: 10px 16px;
        border-radius: 8px;
        display: none;
        font-weight: bold;
      }

      .success-message {
        background-color: #4caf50;
        color: white;
      }

      .error-message {
        background-color: #f44336;
        color: white;
      }

      @media screen and (max-width: 600px) {
        .controls {
          flex-direction: column;
          align-items: stretch;
        }
      }
    </style>
  </head>
  <body>
    <div class="container">
      <header>
        <h1>CoDeKu Certificate Management</h1>
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
          style="background-color: #f44336"
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
