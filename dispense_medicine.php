<?php
session_start();
require_once 'db.php'; // Assumes $conn is defined in db.php

// Fetch available roles (for navigation)
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
// Process Internal Dispense (Pending Prescription)
// ---------------------------
$dispense_success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_dispense'])) {
    $prescription_code = $_POST['prescription_id'] ?? '';
    $patient = $_POST['patient'] ?? '';
    // For internal dispense, we now use default values for source and email.
    $source = "Inhouse";
    $email = "";
    
    if (empty($prescription_code) || empty($patient)) {
        die("Missing required form data.");
    }
    
    // Retrieve the pending prescription
    $stmt = $conn->prepare("SELECT p.prescription_id, p.prescription_code, pat.full_name as patient_name 
                             FROM prescriptions p 
                             JOIN patients pat ON p.patient_id = pat.patient_id 
                             WHERE p.prescription_code = ? AND p.status = 'pending' LIMIT 1");
    if (!$stmt) {
        die("Prepare failed (select): " . $conn->error);
    }
    $stmt->bind_param("s", $prescription_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $prescription = $result->fetch_assoc();
    $stmt->close();
    
    if ($prescription) {
        // Update prescription status to 'Completed'
        $stmt = $conn->prepare("UPDATE prescriptions SET status = 'Completed' WHERE prescription_id = ?");
        if (!$stmt) {
            die("Prepare failed (update): " . $conn->error);
        }
        $stmt->bind_param("i", $prescription['prescription_id']);
        $stmt->execute();
        $stmt->close();
        
        // Build details string using the default source.
        $details = "Type: Dispense Medicine | Source: " . $source;
        if (!empty($email)) {
            $details .= " | Email: " . $email;
        }
        
        // Insert transaction record into the database.
        $transaction_code = "T-" . rand(100, 999);
        $today = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("INSERT INTO transactions (prescription_id, transaction_code, patient_name, details, transaction_date) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed (insert transaction): " . $conn->error);
        }
        $stmt->bind_param("issss", $prescription['prescription_id'], $transaction_code, $patient, $details, $today);
        $stmt->execute();
        $stmt->close();
        
        $dispense_success = "Dispense processed successfully for Prescription " . $prescription['prescription_code'] . ".";
    } else {
        $dispense_success = "Error: Prescription not found or already processed.";
    }
}

// ---------------------------
// Process Manual Dispense
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_manual_dispense'])) {
    $patient = $_POST['patient'] ?? '';
    // Instead of taking source and email from the form, use default values similar to process dispense.
    $source = "Manual Dispense";
    $email = "";  // Not used here
    
    $medicines = $_POST['medicines'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    
    if (empty($patient) || empty($medicines) || empty($quantities)) {
        die("Missing required form data for manual dispense.");
    }
    
    // Begin transaction for atomicity.
    $conn->begin_transaction();
    
    $med_list = [];
    // Array to hold medicine details for transaction_details insertion.
    $medicine_details = [];
    
    // Deduct inventory and build the medicine list string.
    for ($i = 0; $i < count($medicines); $i++) {
        $med_name = trim($medicines[$i]);
        $qty = intval($quantities[$i]);
        if (!empty($med_name) && $qty > 0) {
            $med_list[] = $med_name . " (Qty: " . $qty . ")";
            $stmt = $conn->prepare("SELECT medicine_id FROM medicine WHERE medicine_name = ?");
            if ($stmt) {
                $stmt->bind_param("s", $med_name);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $medicine_id = $row['medicine_id'];
                    // Save details for transaction_details logging.
                    $medicine_details[] = ['medicine_id' => $medicine_id, 'quantity' => $qty];
                    // Deduct inventory.
                    $stmt2 = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE medicine_id = ?");
                    if ($stmt2) {
                        $stmt2->bind_param("ii", $qty, $medicine_id);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                }
                $stmt->close();
            }
        }
    }
    
    if (empty($med_list)) {
        $conn->rollback();
        die("No valid medicines provided.");
    }
    
    $medicines_str = implode(", ", $med_list);
    // Build the details string with type, source, and list of medicines.
    $details = "Type: Dispense Medicine | Source: " . $source . " | Medicines: " . $medicines_str;
    
    // Generate a transaction code and current timestamp.
    $transaction_code = "T-" . rand(100, 999);
    $today = date("Y-m-d H:i:s");
    // For manual dispense, prescription_id is set to 0.
    $manual_id = 0;
    
    // Insert the transaction record.
    $stmt = $conn->prepare("INSERT INTO transactions (prescription_id, transaction_code, patient_name, details, transaction_date) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        $conn->rollback();
        die("Prepare failed (manual dispense insert): " . $conn->error);
    }
    $stmt->bind_param("issss", $manual_id, $transaction_code, $patient, $details, $today);
    $stmt->execute();
    // Get the auto-generated transaction ID.
    $transaction_id = $conn->insert_id;
    $stmt->close();
    
    // Insert each medicine detail into the transaction_details table.
    foreach ($medicine_details as $md) {
        $stmt = $conn->prepare("INSERT INTO transaction_details (transaction_id, medicine_id, quantity, created_at) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $created_at = date("Y-m-d H:i:s");
            $stmt->bind_param("iiis", $transaction_id, $md['medicine_id'], $md['quantity'], $created_at);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Commit the transaction after all queries succeed.
    $conn->commit();
    
    $dispense_success = "Manual dispense processed successfully.";
}

// ---------------------------
// Process External Dispense
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_external_dispense'])) {
    $external_code = $_POST['external_code'] ?? '';
    $patient = $_POST['patient'] ?? '';
    $source = $_POST['source'] ?? '';
    $email = $_POST['email'] ?? '';
    $medicines = $_POST['medicines'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    
    if (empty($external_code) || empty($patient) || empty($source)) {
        die("Missing required form data for external dispense.");
    }
    
    $med_list = [];
    $medicine_details = [];
    for ($i = 0; $i < count($medicines); $i++) {
        $med_name = trim($medicines[$i]);
        $qty = intval($quantities[$i]);
        if (!empty($med_name) && $qty > 0) {
            $med_list[] = $med_name . " (Qty: " . $qty . ")";
            $stmt = $conn->prepare("SELECT medicine_id FROM medicine WHERE medicine_name = ?");
            if ($stmt) {
                $stmt->bind_param("s", $med_name);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $medicine_id = $row['medicine_id'];
                    $medicine_details[] = ['medicine_id' => $medicine_id, 'quantity' => $qty];
                    $stmt2 = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE medicine_id = ?");
                    if ($stmt2) {
                        $stmt2->bind_param("ii", $qty, $medicine_id);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                }
                $stmt->close();
            }
        }
    }
    if (empty($med_list)) {
        die("No valid medicines provided.");
    }
    $medicines_str = implode(", ", $med_list);
    $details = "Type: Dispense Medicine | Source: " . $source . " | Medicines: " . $medicines_str;
    if (!empty($email)) {
        $details .= " | Email: " . $email;
    }
    $transaction_code = "T-" . rand(100, 999);
    $today = date("Y-m-d H:i:s");
    // For external dispense, set prescription_id to 0 (or handle as needed)
    $external_id = 0;
    $stmt = $conn->prepare("INSERT INTO transactions (prescription_id, transaction_code, patient_name, details, transaction_date) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed (external dispense insert): " . $conn->error);
    }
    $stmt->bind_param("issss", $external_id, $transaction_code, $patient, $details, $today);
    $stmt->execute();
    $transaction_id = $conn->insert_id;
    $stmt->close();
    
    // Log each medicine into transaction_details
    foreach ($medicine_details as $md) {
        $stmt = $conn->prepare("INSERT INTO transaction_details (transaction_id, medicine_id, quantity, created_at) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $created_at = date("Y-m-d H:i:s");
            $stmt->bind_param("iiis", $transaction_id, $md['medicine_id'], $md['quantity'], $created_at);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    $dispense_success = "External dispense processed successfully.";
}

// ---------------------------
// Retrieve Pending Prescriptions (with optional filters)
// ---------------------------
$patient_filter = $_GET['patient'] ?? "";
$date_filter = $_GET['date'] ?? "";
$sql = "SELECT p.*, pat.full_name as patient_name 
        FROM prescriptions p 
        JOIN patients pat ON p.patient_id = pat.patient_id 
        WHERE p.status = 'pending'";
if (!empty($patient_filter)) {
    $patient_filter = $conn->real_escape_string($patient_filter);
    $sql .= " AND pat.full_name LIKE '%$patient_filter%'";
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
// Retrieve Transaction History with Medicine Details
// ---------------------------
$sql = "SELECT t.*, 
               GROUP_CONCAT(CONCAT(m.medicine_name, ' (Qty: ', td.quantity, ')') SEPARATOR ', ') as med_details
        FROM transactions t
        LEFT JOIN transaction_details td ON t.transaction_id = td.transaction_id
        LEFT JOIN medicine m ON td.medicine_id = m.medicine_id
        GROUP BY t.transaction_id
        ORDER BY t.transaction_date DESC";
$result = $conn->query($sql);
$transaction_history = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transaction_history[] = $row;
    }
    $result->free();
}

// Prebuild medicine options for manual & external dispense
$medicine_options = "";
$sqlMed = "SELECT medicine_name FROM medicine ORDER BY medicine_name ASC";
$resultMed = $conn->query($sqlMed);
if ($resultMed) {
    while ($rowMed = $resultMed->fetch_assoc()) {
        $medicine_options .= '<option value="' . htmlspecialchars($rowMed['medicine_name']) . '">' . htmlspecialchars($rowMed['medicine_name']) . '</option>';
    }
    $resultMed->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dispense Medicine</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Global Header -->
  <header class="header">
    <div class="logo">
    <div class="logo">RHU MacArthur</div>
    </div>
    <div class="header-title">RHU MacArthur</div>
  </header>
  
  <div class="global-container">
    <!-- Global Sidebar with Role-Based Navigation -->
    <div class="global-sidebar">
  <div class="profile">
    <div class="profile-logo">
      <img src="logo.png" alt="Logo" class="responsive-logo">
    </div>
    <div class="profile-name"><?php echo htmlspecialchars($profile_name); ?></div>
  </div>
  <div class="nav">
        <?php
          if (!isset($_SESSION['role_id'])) {
              header("Location: login.php");
              exit;
          }
          
          // Look up the current role name using the $roles array.
          $current_role = '';
          foreach ($roles as $r) {
              if ($r['role_id'] == $_SESSION['role_id']) {
                  $current_role = $r['role_name'];
                  break;
              }
          }
          
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
              ["Add Prescription", "make_prescription.php"],
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
          
          if (array_key_exists($current_role, $navItems)) {
              foreach ($navItems[$current_role] as $item) {
                  echo '<button class="nav-button" onclick="window.location.href=\'' . $item[1] . '\'">' . $item[0] . '</button>';
              }
          } else {
              echo '<button class="nav-button" onclick="window.location.href=\'index.php\'">Dashboard</button>';
              echo '<button class="nav-button" onclick="window.location.href=\'about.php\'">About</button>';
          }
        ?>
      </div>
      <div class="logout-sidebar">
    <a href="logout.php">Logout</a>
  </div>
    </div>
    
    <!-- Page Content -->
    <div class="page-content">
      <!-- Top Bar for Dispense Medicine Page -->
      <div class="top-bar">
        <div class="search-area">
          <input type="text" placeholder="Search Prescriptions">
          <button class="search-button">Search</button>
        </div>
        <div class="action-buttons">
          <a href="dispense_medicine.php?action=manual" class="add-item">Dispense Medicine</a>
          <a href="dispense_medicine.php?action=reports" class="reports">Item Reports</a>
        </div>
      </div>
      
      <!-- Success Message Modal -->
      <?php if (!empty($dispense_success)): ?>
        <div class="modal">
          <div class="modal-content">
            <div class="modal-header">
              <h2>Success</h2>
              <a href="dispense_medicine.php" class="close">&times;</a>
            </div>
            <div class="modal-body">
              <p><?php echo $dispense_success; ?></p>
            </div>
          </div>
        </div>
      <?php endif; ?>
      
      <!-- Main Inventory Container -->
      <div class="inventory-container">
        <!-- Left Sidebar: Prescription Filters -->
        <aside class="filter-sidebar">
          <h3>Prescription Filters</h3>
          <form method="GET" action="dispense_medicine.php" class="filter-form">
            <div class="filter-block">
              <label for="patient">Patient Name:</label>
              <input type="text" name="patient" id="patient" placeholder="Enter patient name" value="<?php echo isset($_GET['patient']) ? htmlspecialchars($_GET['patient']) : ''; ?>">
            </div>
            <div class="filter-block">
              <label for="date">Prescription Date:</label>
              <input type="date" name="date" id="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>">
            </div>
            <button type="submit" class="apply-filter">Apply Filter</button>
          </form>
          
          <div class="inventory-stats">
            <h4>Prescription Stats</h4>
            <p><strong>Pending Prescriptions:</strong> <?php echo count($pending_prescriptions); ?></p>
          </div>
        </aside>
        
        <!-- Right Content Area: Pending Prescriptions -->
        <main class="inventory-list">
          <h2>Pending Prescriptions</h2>
          <?php if (count($pending_prescriptions) > 0): ?>
            <?php foreach ($pending_prescriptions as $prescription): ?>
              <div class="inventory-item">
                <div class="item-info">
                  <h3>Prescription <?php echo $prescription['prescription_code']; ?></h3>
                  <span class="category">Patient: <?php echo $prescription['patient_name']; ?></span>
                  <p class="quantity">
                    Instructions: <?php echo $prescription['instructions']; ?>
                  </p>
                </div>
                <div class="date-info">
                  <div class="date-issued">
                    <label>Date:</label>
                    <span><?php echo date("Y-m-d", strtotime($prescription['created_at'])); ?></span>
                  </div>
                </div>
                <div class="item-options">
                  <button class="overview-btn" onclick="window.location.href='dispense_medicine.php?action=process&id=<?php echo $prescription['prescription_code']; ?>'">
                    Process Dispense
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No pending prescriptions.</p>
          <?php endif; ?>
        </main>
      </div>
      
      <!-- Transaction History Section -->
      <div class="transaction-history" style="max-height:200px; overflow-y:auto; margin-top:20px;">
        <h2>Transaction History</h2>
        <?php if (count($transaction_history) > 0): ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Transaction ID</th>
                <th>Prescription ID</th>
                <th>Patient</th>
                <th>Details</th>
                <th>Medicines</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transaction_history as $trans): ?>
                <tr>
                  <td><?php echo htmlspecialchars($trans["transaction_code"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["prescription_id"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["patient_name"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["details"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["med_details"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["transaction_date"]); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No transactions yet.</p>
        <?php endif; ?>
      </div>
      
    </div>
  </div>
  
  <!-- Modal Popup for Internal Dispense -->
  <?php if (isset($_GET['action'])):
    $action = $_GET['action'];
    if ($action == "process" && isset($_GET['id'])):
      $presc_code = $conn->real_escape_string($_GET['id']);
      $stmt = $conn->prepare("SELECT p.*, pat.full_name as patient_name 
                              FROM prescriptions p 
                              JOIN patients pat ON p.patient_id = pat.patient_id 
                              WHERE p.prescription_code = ? AND p.status = 'pending' LIMIT 1");
      if (!$stmt) {
          die("Prepare failed (process modal): " . $conn->error);
      }
      $stmt->bind_param("s", $presc_code);
      $stmt->execute();
      $result = $stmt->get_result();
      $process_prescription = $result ? $result->fetch_assoc() : null;
      $stmt->close();
      if ($process_prescription):
          // Fetch list of medicines for this prescription.
          $stmt2 = $conn->prepare("SELECT pm.*, m.medicine_name FROM prescription_medicines pm JOIN medicine m ON pm.medicine_id = m.medicine_id WHERE pm.prescription_id = ?");
          $stmt2->bind_param("i", $process_prescription['prescription_id']);
          $stmt2->execute();
          $result2 = $stmt2->get_result();
          $medicine_list = [];
          while ($row = $result2->fetch_assoc()) {
              $medicine_list[] = $row;
          }
          $stmt2->close();
  ?>
<div class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Process Dispense for Prescription <?php echo $process_prescription['prescription_code']; ?></h2>
      <a href="dispense_medicine.php" class="close">&times;</a>
    </div>
    <div class="modal-body">
      <form method="POST" action="dispense_medicine.php?action=process&id=<?php echo $process_prescription['prescription_code']; ?>">
        <input type="hidden" name="prescription_id" value="<?php echo $process_prescription['prescription_code']; ?>">
        <input type="hidden" name="patient" value="<?php echo $process_prescription['patient_name']; ?>">
        <div class="form-group">
          <label>Patient:</label>
          <span><?php echo $process_prescription['patient_name']; ?></span>
        </div>
        <div class="form-group">
          <label>Medicines:</label>
          <ul>
            <?php foreach ($medicine_list as $med): ?>
              <li><?php echo htmlspecialchars($med['medicine_name'] . " (Qty: " . $med['quantity'] . ")"); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <button type="submit" name="process_dispense" class="modal-submit">Process Dispense</button>
      </form>
    </div>
  </div>
</div>
<?php 
      endif;
    endif;
  endif;
?>
  
<!-- Modal Popup for Manual Dispense -->
<?php if (isset($_GET['action']) && $_GET['action'] == "manual"): ?>
<div class="modal">
  <div class="modal-content">
    <div class="modal-header">
       <h2>Manual Medicine Dispense</h2>
       <a href="dispense_medicine.php" class="close">&times;</a>
    </div>
    <div class="modal-body">
       <form method="POST" action="dispense_medicine.php?action=manual">
          <input type="hidden" name="process_manual_dispense" value="1">
          <div class="form-group">
             <label for="manual-patient">Patient Name:</label>
             <input type="text" name="patient" id="manual-patient" required>
          </div>
          <div class="form-group" id="medicine-rows">
             <label>Medicines:</label>
             <div class="medicine-row">
                <select name="medicines[]" required>
                   <option value="">-- Select Medicine --</option>
                   <?php echo $medicine_options; ?>
                </select>
                <input type="number" name="quantities[]" placeholder="Quantity" required>
             </div>
          </div>
          <button type="button" onclick="addMedicineRow()">Add Another Medicine</button>
          <button type="submit" class="modal-submit">Process Manual Dispense</button>
       </form>
    </div>
  </div>
</div>
<script>
    function addMedicineRow() {
       var container = document.getElementById("medicine-rows");
       var row = document.createElement("div");
       row.className = "medicine-row";
       row.innerHTML = '<select name="medicines[]" required>' +
                       '<option value="">-- Select Medicine --</option>' +
                       '<?php echo $medicine_options; ?>' +
                       '</select>' +
                       '<input type="number" name="quantities[]" placeholder="Quantity" required>' +
                       '<button type="button" onclick="this.parentNode.remove()">Remove</button>';
       container.appendChild(row);
    }
</script>
<?php endif; ?>

<!-- Modal Popup for External Dispense -->
<?php if (isset($_GET['action']) && $_GET['action'] == "external"): ?>
<div class="modal">
  <div class="modal-content">
    <div class="modal-header">
       <h2>External Prescription Dispense</h2>
       <a href="dispense_medicine.php" class="close">&times;</a>
    </div>
    <div class="modal-body">
       <form method="POST" action="dispense_medicine.php?action=external">
          <input type="hidden" name="process_external_dispense" value="1">
          <div class="form-group">
             <label for="external-code">External Prescription Code:</label>
             <input type="text" name="external_code" id="external-code" required>
          </div>
          <div class="form-group">
             <label for="external-patient">Patient Name:</label>
             <input type="text" name="patient" id="external-patient" required>
          </div>
          <div class="form-group">
             <label for="external-source">Source:</label>
             <input type="text" name="source" id="external-source" required placeholder="Enter source">
          </div>
          <div class="form-group" id="external-medicine-rows">
             <label>Medicines:</label>
             <div class="medicine-row">
                <select name="medicines[]" required>
                   <option value="">-- Select Medicine --</option>
                   <?php echo $medicine_options; ?>
                </select>
                <input type="number" name="quantities[]" placeholder="Quantity" required>
             </div>
          </div>
          <button type="button" onclick="addExternalMedicineRow()">Add Another Medicine</button>
          <button type="submit" class="modal-submit">Process External Dispense</button>
       </form>
    </div>
  </div>
</div>
<script>
    function addExternalMedicineRow() {
       var container = document.getElementById("external-medicine-rows");
       var row = document.createElement("div");
       row.className = "medicine-row";
       row.innerHTML = '<select name="medicines[]" required>' +
                       '<option value="">-- Select Medicine --</option>' +
                       '<?php echo $medicine_options; ?>' +
                       '</select>' +
                       '<input type="number" name="quantities[]" placeholder="Quantity" required>' +
                       '<button type="button" onclick="this.parentNode.remove()">Remove</button>';
       container.appendChild(row);
    }
</script>
<?php endif; ?>

</body>
</html>
