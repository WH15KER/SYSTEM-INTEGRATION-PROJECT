<?php
session_start();
require_once 'db.php'; // Assumes $conn is defined in db.php
$roles = [];
$sqlRoles = "SELECT role_id, role_name FROM roles ORDER BY role_name ASC";
$resultRoles = $conn->query($sqlRoles);
if ($resultRoles) {
    while ($row = $resultRoles->fetch_assoc()) {
        $roles[] = $row;
    }
    $resultRoles->free();
}

// Default profile name in case no user is logged in.
$profile_name = 'Guest';

// Check if the user is logged in (assuming user_id is stored in session)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Retrieve the full name from staff_details using the users table
    $sql = "SELECT sd.full_name 
            FROM staff_details sd 
            JOIN users u ON sd.staff_id = u.staff_id 
            WHERE u.user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($full_name);
        if ($stmt->fetch()) {
            $profile_name = $full_name;
        }
        $stmt->close();
    }
}
// ---------------------------
// Get Inventory Data (distinct medicine names + total quantity)
// ---------------------------
$inventory = [];
$sqlInv = "SELECT m.medicine_name, SUM(i.quantity) AS total_quantity
           FROM inventory i
           JOIN medicine m ON i.medicine_id = m.medicine_id
           GROUP BY m.medicine_name
           ORDER BY m.medicine_name ASC";
$resultInv = $conn->query($sqlInv);
if ($resultInv) {
    while ($row = $resultInv->fetch_assoc()) {
        // Save available quantity for each medicine
        $inventory[$row['medicine_name']] = (int)$row['total_quantity'];
    }
    $resultInv->free();
}
$medicine_names = array_keys($inventory);

$message = "";

// ---------------------------
// Process Form Submissions
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'remove_prescription') {
        // DELETE pending prescription
        $prescription_code = $_POST['prescription_id'];
        $stmt = $conn->prepare("DELETE FROM prescriptions WHERE prescription_code = ? AND status = 'Pending'");
        $stmt->bind_param("s", $prescription_code);
        $stmt->execute();
        $stmt->close();
        $message = "Pending prescription removed.";
        
    } elseif ($action === 'force_complete_prescription') {
        // UPDATE: Mark pending prescription as completed
        $prescription_code = $_POST['prescription_id'];
        $stmt = $conn->prepare("UPDATE prescriptions SET status = 'Completed' WHERE prescription_code = ? AND status = 'Pending'");
        $stmt->bind_param("s", $prescription_code);
        $stmt->execute();
        $stmt->close();
        $message = "Prescription $prescription_code completed.";
        
    } elseif ($action === 'submit_prescription') {
        // INSERT new prescription
        $patient_fullname = trim($_POST["patient_name"]);
        $instructions = isset($_POST["instructions"]) ? trim($_POST["instructions"]) : "";
        $medicines = $_POST["medicines"];
        $quantities = $_POST["quantities"];
        $errors = [];
        $valid_medicines = [];
        
        if (empty($patient_fullname)) {
            $errors[] = "Patient name is required.";
        }
        
        // Validate each selected medicine against inventory
        foreach ($medicines as $i => $med) {
            $med = trim($med);
            $qty = (int)$quantities[$i];
            if (empty($med)) {
                $errors[] = "Medicine at row " . ($i + 1) . " is required.";
                continue;
            }
            if (!isset($inventory[$med])) {
                $errors[] = "Medicine '$med' not found in inventory (row " . ($i + 1) . ").";
                continue;
            }
            if ($inventory[$med] <= 0) {
                $errors[] = "Medicine '$med' is out of stock (row " . ($i + 1) . ").";
                continue;
            }
            if ($inventory[$med] < $qty) {
                $errors[] = "Only " . $inventory[$med] . " available for '$med' (row " . ($i + 1) . ").";
                continue;
            }
            $valid_medicines[] = ["medicine" => $med, "quantity" => $qty];
        }
        
        if (!empty($errors)) {
            $message = implode("<br>", $errors);
        } else {
            // Get patient_id from patients table (or insert new if not found)
            $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE full_name = ?");
            $stmt->bind_param("s", $patient_fullname);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $patient_id = $row['patient_id'];
            } else {
                // Insert new patient record
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO patients (full_name, created_at) VALUES (?, NOW())");
                $stmt->bind_param("s", $patient_fullname);
                $stmt->execute();
                $patient_id = $stmt->insert_id;
            }
            $stmt->close();
            
            // Generate unique prescription code
            $prescription_code = "RX-" . str_pad(rand(1, 999), 3, "0", STR_PAD_LEFT);
            // Insert new prescription
            $stmt = $conn->prepare("
                INSERT INTO prescriptions 
                (prescription_code, patient_id, instructions, status, created_at) 
                VALUES (?, ?, ?, 'Pending', NOW())
            ");
            $stmt->bind_param("sis", $prescription_code, $patient_id, $instructions);
            $stmt->execute();
            $prescription_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert each medicine into prescription_medicines
            foreach ($valid_medicines as $m) {
                $med_name = $m['medicine'];
                $qty = $m['quantity'];
                // Lookup medicine_id from medicine table
                $stmt = $conn->prepare("SELECT medicine_id FROM medicine WHERE medicine_name = ?");
                $stmt->bind_param("s", $med_name);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $medicine_id = $row['medicine_id'];
                    // Insert into prescription_medicines
                    $stmt2 = $conn->prepare("INSERT INTO prescription_medicines (prescription_id, medicine_id, quantity) VALUES (?, ?, ?)");
                    $stmt2->bind_param("iii", $prescription_id, $medicine_id, $qty);
                    $stmt2->execute();
                    $stmt2->close();
                }
                $stmt->close();
            }
            
            $message = "Prescription $prescription_code created successfully and added to pending prescriptions.";
        }
    }
}

// ---------------------------
// Retrieve Pending Prescriptions (with optional filters)
// ---------------------------
$patient_filter = $_GET['patient'] ?? "";
$date_filter = $_GET['date'] ?? "";

$sql = "SELECT p.*, pt.full_name as patient_name,
          (SELECT GROUP_CONCAT(CONCAT(m.medicine_name, ' (Qty: ', pm.quantity, ')') SEPARATOR ', ')
           FROM prescription_medicines pm 
           JOIN medicine m ON pm.medicine_id = m.medicine_id 
           WHERE pm.prescription_id = p.prescription_id) AS med_list
        FROM prescriptions p 
        JOIN patients pt ON p.patient_id = pt.patient_id 
        WHERE p.status = 'Pending'";
if (!empty($patient_filter)) {
    $patient_filter = $conn->real_escape_string($patient_filter);
    $sql .= " AND pt.full_name LIKE '%$patient_filter%'";
}
if (!empty($date_filter)) {
    $date_filter = $conn->real_escape_string($date_filter);
    $sql .= " AND DATE(p.created_at) = '$date_filter'";
}

$result = $conn->query($sql);
$pending_prescriptions = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pending_prescriptions[] = $row;
    }
    $result->free();
}

// ---------------------------
// Retrieve Completed Prescriptions (History)
// ---------------------------
$sql = "SELECT p.*, pt.full_name as patient_name,
          (SELECT GROUP_CONCAT(CONCAT(m.medicine_name, ' (Qty: ', pm.quantity, ')') SEPARATOR ', ')
           FROM prescription_medicines pm 
           JOIN medicine m ON pm.medicine_id = m.medicine_id 
           WHERE pm.prescription_id = p.prescription_id) AS med_list
        FROM prescriptions p 
        JOIN patients pt ON p.patient_id = pt.patient_id 
        WHERE p.status = 'Completed' 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
$prescription_history = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $prescription_history[] = $row;
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Make Prescription</title>
  <link rel="stylesheet" href="style.css">
  <script>
    // Pass inventory data to JavaScript for client-side validation
    var inventory = <?php echo json_encode($inventory); ?>;
    
    // Add a new medicine row dynamically with a <select> for medicine
    function addRow() {
      var container = document.getElementById("medicineRows");
      var row = document.createElement("div");
      row.className = "medicine-row";
      row.innerHTML = `
        <select name="medicines[]" required>
          <option value="">-- Select Medicine --</option>
          <?php foreach ($medicine_names as $med): ?>
            <option value="<?php echo htmlspecialchars($med); ?>">
              <?php echo htmlspecialchars($med); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="quantities[]" placeholder="Quantity" required>
        <button type="button" class="delete-row" onclick="deleteRow(this)">Delete</button>
      `;
      container.appendChild(row);
    }
    
    // Delete a medicine row
    function deleteRow(btn) {
      var row = btn.parentNode;
      row.parentNode.removeChild(row);
    }
    
    // Validate the form on submit: warn if any selected medicine exceeds available quantity
    window.addEventListener('DOMContentLoaded', function() {
      var form = document.getElementById('prescriptionForm');
      form.addEventListener('submit', function(e) {
        var warnings = [];
        var rows = document.querySelectorAll('.medicine-row');
        rows.forEach(function(row) {
          var select = row.querySelector('select[name="medicines[]"]');
          var qtyInput = row.querySelector('input[name="quantities[]"]');
          if (select && qtyInput) {
            var med = select.value.trim();
            var qty = parseInt(qtyInput.value);
            if (inventory.hasOwnProperty(med)) {
              var available = inventory[med];
              if (available <= 0) {
                warnings.push(med + " is out of stock.");
              } else if (qty > available) {
                warnings.push("Only " + available + " available for " + med + ". You entered " + qty + ".");
              }
            } else if (med !== "") {
              warnings.push("Medicine '" + med + "' not found in inventory.");
            }
          }
        });
        if (warnings.length > 0) {
          var msg = "The following issues were found:\n" + warnings.join("\n") + "\n\nDo you want to continue anyway?";
          if (!confirm(msg)) {
            e.preventDefault();
          }
        }
      });
    });
  </script>
</head>
<body>
  <!-- Global Header -->
  <header class="header">
    <div class="logo">
    <div class="logo">RHU MacArthur</div>
    </div>
    <div class="header-title">RHU MacArthur</div>
  </header>
  
  <div class="global-sidebar">
  <div class="profile">
    <div class="profile-logo">
      <img src="logo.png" alt="Logo" class="responsive-logo">
    </div>
    <div class="profile-name"><?php echo htmlspecialchars($profile_name); ?></div>
  </div>
  <div class="nav">
  <?php
    // Ensure the user is logged in and role_id is set.
    if (!isset($_SESSION['role_id'])) {
        header("Location: login.php");
        exit;
    }
    
    // Look up the current role name using the $roles array fetched from SQL.
    $current_role = '';
    foreach ($roles as $r) {
        if ($r['role_id'] == $_SESSION['role_id']) {
            $current_role = $r['role_name'];
            break;
        }
    }
    
    // Define the navigation items for each role.
    // Format: "Role Name" => [ [Label, URL], ... ]
    $navItems = [
      "Admin" => [
              ["Dashboard", "index.php"],
              ["Manage Inventory", "manage_inventory.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["Manage Accounts", "manage_accounts.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ],
            "Municipal Health Officer" => [
              ["Dashboard", "index.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["Add Prescription", "make_prescription.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ],
            "Health Officer" => [
              ["Dashboard", "index.php"],
              ["Manage Inventory", "manage_inventory.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ],
            "Public Health Nurse" => [
              ["Dashboard", "index.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ],
            "Pharmacist" => [
              ["Dashboard", "index.php"],
              ["Manage Inventory", "manage_inventory.php"],
              ["Dispense Medicine", "dispense_medicine.php"],
              ["View Transactions", "transaction_history.php"],
              ["About", "about.php"]
            ]
    ];
    
    // Output the navigation buttons based on the current role.
    if (array_key_exists($current_role, $navItems)) {
        foreach ($navItems[$current_role] as $item) {
            echo '<button class="nav-button" onclick="window.location.href=\'' . $item[1] . '\'">' . $item[0] . '</button>';
        }
    } else {
        // Fallback for unknown roles.
        echo '<button class="nav-button" onclick="window.location.href=\'index.php\'">Dashboard</button>';
        echo '<button class="nav-button" onclick="window.location.href=\'about.php\'">About</button>';
    }
  ?>
</div>

    </div>
    
    <!-- Main Content -->
    <div class="page-content">
      <div class="page-header">
        <h1>Make Prescription</h1>
      </div>
      
      <?php if (!empty($message)): ?>
      <!-- Message Modal -->
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Notice</h2>
            <a href="make_prescription.php" class="close">&times;</a>
          </div>
          <div class="modal-body">
            <p><?php echo $message; ?></p>
          </div>
        </div>
      </div>
      <?php endif; ?>
      
      <!-- Prescription Creation Form -->
      <div class="inventory-container">
        <main class="inventory-list">
          <h2>Create New Prescription</h2>
          <form method="POST" action="make_prescription.php" id="prescriptionForm">
            <input type="hidden" name="action" value="submit_prescription">
            
            <!-- Patient Name -->
            <div class="form-group">
              <label for="patient_name">Patient Name:</label>
              <input type="text" name="patient_name" id="patient_name" placeholder="Enter patient's full name" required>
            </div>
            
            <!-- Instructions -->
            <div class="form-group">
              <label for="instructions">Instructions:</label>
              <textarea name="instructions" id="instructions" placeholder="Enter any prescription instructions" rows="3"></textarea>
            </div>
            
            <!-- Medicine Rows Container -->
            <div id="medicineRows">
              <div class="medicine-row">
                <select name="medicines[]" required>
                  <option value="">-- Select Medicine --</option>
                  <?php foreach ($medicine_names as $med): ?>
                    <option value="<?php echo htmlspecialchars($med); ?>">
                      <?php echo htmlspecialchars($med); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <input type="number" name="quantities[]" placeholder="Quantity" required>
                <button type="button" class="delete-row" onclick="deleteRow(this)">Delete</button>
              </div>
            </div>
            
            <!-- Button to add more medicine rows -->
            <div class="form-group">
              <button type="button"  onclick="addRow()" class="nav-button">Add Row</button>
            </div>
            
            <button type="submit" class="modal-submit">Add Prescription</button>
          </form>
        </main>
      </div>
      
      <!-- Pending Prescriptions List -->
      <div class="transaction-history" style="max-height:200px; overflow-y:auto; margin-top:20px;">
        <h2>Pending Prescriptions</h2>
        <?php if (!empty($pending_prescriptions)): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Prescription ID</th>
                <th>Patient</th>
                <th>Medicines</th>
                <th>Instructions</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pending_prescriptions as $presc): ?>
                <tr>
                  <td><?php echo htmlspecialchars($presc["prescription_code"]); ?></td>
                  <td><?php echo htmlspecialchars($presc["patient_name"]); ?></td>
                  <td><?php echo htmlspecialchars($presc["med_list"] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($presc["instructions"]); ?></td>
                  <td><?php echo date("Y-m-d", strtotime($presc["created_at"])); ?></td>
                  <td>
                    <!-- Remove Prescription -->
                    <form method="POST" action="make_prescription.php" style="display:inline;">
                      <input type="hidden" name="action" value="remove_prescription">
                      <input type="hidden" name="prescription_id" value="<?php echo htmlspecialchars($presc["prescription_code"]); ?>">
                      <button type="submit" class="overview-btn">Remove</button>
                    </form>
                    <!-- Force Complete Prescription -->
                    <form method="POST" action="make_prescription.php" style="display:inline;">
                      <input type="hidden" name="action" value="force_complete_prescription">
                      <input type="hidden" name="prescription_id" value="<?php echo htmlspecialchars($presc["prescription_code"]); ?>">
                      <button type="submit" class="overview-btn">Complete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No pending prescriptions.</p>
        <?php endif; ?>
      </div>
      
      <!-- Prescription History (Completed) -->
      <div class="transaction-history" style="max-height:200px; overflow-y:auto; margin-top:20px;">
        <h2>Prescription History</h2>
        <?php if (!empty($prescription_history)): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Prescription ID</th>
                <th>Patient</th>
                <th>Medicines</th>
                <th>Instructions</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($prescription_history as $presc): ?>
                <tr>
                  <td><?php echo htmlspecialchars($presc["prescription_code"]); ?></td>
                  <td><?php echo htmlspecialchars($presc["patient_name"]); ?></td>
                  <td><?php echo htmlspecialchars($presc["med_list"] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($presc["instructions"]); ?></td>
                  <td><?php echo date("Y-m-d", strtotime($presc["created_at"])); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No completed prescriptions.</p>
        <?php endif; ?>
      </div>
      
    </div><!-- end page-content -->
  </div><!-- end global-container -->
  
  <!-- Modal Popup for Processing Internal Dispense -->
  <?php if (isset($_GET['action'])):
    $action = $_GET['action'];
    if ($action == "process" && isset($_GET['id'])):
      $presc_code = $conn->real_escape_string($_GET['id']);
      $stmt = $conn->prepare("SELECT p.*, pt.full_name as patient_name 
                              FROM prescriptions p 
                              JOIN patients pt ON p.patient_id = pt.patient_id 
                              WHERE p.prescription_code = ? AND p.status = 'Pending' LIMIT 1");
      if (!$stmt) {
          die("Prepare failed (process modal): " . $conn->error);
      }
      $stmt->bind_param("s", $presc_code);
      $stmt->execute();
      $result = $stmt->get_result();
      $process_prescription = $result ? $result->fetch_assoc() : null;
      $stmt->close();
      if ($process_prescription):
        $instructions = $process_prescription['instructions'];
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Process Dispense for Prescription <?php echo htmlspecialchars($process_prescription['prescription_code']); ?></h2>
            <a href="dispense_medicine.php" class="close">&times;</a>
          </div>
          <div class="modal-body">
            <form method="POST" action="dispense_medicine.php?action=process&id=<?php echo htmlspecialchars($process_prescription['prescription_code']); ?>">
              <input type="hidden" name="prescription_id" value="<?php echo htmlspecialchars($process_prescription['prescription_code']); ?>">
              <input type="hidden" name="patient" value="<?php echo htmlspecialchars($process_prescription['patient_name']); ?>">
              <input type="hidden" name="instructions" value="<?php echo htmlspecialchars($instructions); ?>">
              <div class="form-group">
                <label>Patient:</label>
                <span><?php echo htmlspecialchars($process_prescription['patient_name']); ?></span>
              </div>
              <div class="form-group">
                <label>Instructions:</label>
                <span><?php echo htmlspecialchars($instructions); ?></span>
              </div>
              <div class="form-group">
                <label for="dispense-type">Dispense Type:</label>
                <select name="dispense_type" id="dispense-type" required>
                  <option value="inhouse">Inhouse</option>
                  <option value="e-prescription">E-Prescription</option>
                </select>
              </div>
              <div id="e-prescription-options" style="display:none;">
                <div class="form-group">
                  <label for="email">Email:</label>
                  <input type="email" name="email" id="email" placeholder="Enter email for e-prescription">
                </div>
                <div class="form-group">
                  <button type="button" id="printPrescription">Print Prescription</button>
                </div>
              </div>
              <button type="submit" name="process_dispense" class="modal-submit">Process Dispense</button>
            </form>
          </div>
        </div>
      </div>
      <script>
        document.getElementById('dispense-type').addEventListener('change', function() {
          if (this.value === 'e-prescription') {
            document.getElementById('e-prescription-options').style.display = 'block';
          } else {
            document.getElementById('e-prescription-options').style.display = 'none';
          }
        });
        document.getElementById('printPrescription').addEventListener('click', function() {
          window.print();
        });
      </script>
  <?php 
      endif;
    endif;
  endif;
  ?>
  
</body>
</html>
