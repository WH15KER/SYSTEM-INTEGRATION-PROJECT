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
// ===========================
// Retrieve Distinct Categories from inventory (via medicine join)
// ===========================
$categories = [];
$sql = "SELECT DISTINCT m.category FROM inventory i JOIN medicine m ON i.medicine_id = m.medicine_id ORDER BY m.category ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['category'])) {
            $categories[] = $row['category'];
        }
    }
    $result->free();
}

// ===========================
// Helper Functions
// ===========================

// Compute stock status based on quantity.
if (!function_exists('compute_status')) {
    function compute_status($quantity) {
        $quantity = is_numeric($quantity) ? intval($quantity) : 0;
        if ($quantity <= 0) {
            return "Out of Stock";
        } elseif ($quantity < 10) {
            return "Low Stock";
        } else {
            return "In Stock";
        }
    }
}

// Retrieve a single inventory item by medicine name (via join)
function get_item_by_name($conn, $name) {
    $stmt = $conn->prepare("SELECT i.*, m.medicine_name AS name, m.category 
                            FROM inventory i 
                            JOIN medicine m ON i.medicine_id = m.medicine_id 
                            WHERE m.medicine_name = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
    return $item;
}

// ===========================
// Compute Total Items in Stock
// ===========================
$sqlTotal = "SELECT SUM(quantity) AS totalStock FROM inventory";
$resultTotal = $conn->query($sqlTotal);
$totalStock = 0;
if ($resultTotal) {
    $row = $resultTotal->fetch_assoc();
    $totalStock = !empty($row['totalStock']) ? $row['totalStock'] : 0;
    $resultTotal->free();
}

// ===========================
// Process POST Actions for Deduct, Delete, Mark Out, and Edit
// ===========================
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['name']) && !isset($_POST['generate_report'])) {
    $itemName = trim($_POST['name']); // This is the medicine name.
    if (empty($itemName)) {
        $message = "Error: Item name is required.";
    } else {
        if ($_POST['action'] === 'deduct') {
            if (!isset($_POST['deduct_amount']) || !is_numeric($_POST['deduct_amount']) || intval($_POST['deduct_amount']) <= 0) {
                $message = "Error: Please enter a valid deduction amount.";
            } else {
                $deductAmount = intval($_POST['deduct_amount']);
                // Fetch current quantity using join.
                $stmt = $conn->prepare("SELECT i.quantity 
                                        FROM inventory i 
                                        JOIN medicine m ON i.medicine_id = m.medicine_id 
                                        WHERE m.medicine_name = ?");
                $stmt->bind_param("s", $itemName);
                $stmt->execute();
                $stmt->bind_result($currentQuantity);
                if ($stmt->fetch()) {
                    $stmt->close();
                    // Allow negative inventory by removing max(0, ...)
                    $newQuantity = $currentQuantity - $deductAmount;
                    $newStatus = compute_status($newQuantity);
                    // Update via join.
                    $stmt = $conn->prepare("UPDATE inventory i 
                                            JOIN medicine m ON i.medicine_id = m.medicine_id 
                                            SET i.quantity = ?, i.status = ? 
                                            WHERE m.medicine_name = ?");
                    $stmt->bind_param("iss", $newQuantity, $newStatus, $itemName);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) {
                        $message = "Success: Deducted {$deductAmount} from {$itemName}.";
                    } else {
                        $message = "Error: No rows updated. Please check the item name.";
                    }
                    $stmt->close();
                } else {
                    $stmt->close();
                    $message = "Error: Item not found.";
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            // Delete inventory item via join.
            $stmt = $conn->prepare("DELETE i 
                                    FROM inventory i 
                                    JOIN medicine m ON i.medicine_id = m.medicine_id 
                                    WHERE m.medicine_name = ?");
            $stmt->bind_param("s", $itemName);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $message = "Success: Deleted {$itemName}.";
            } else {
                $message = "Error: Item not found.";
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'markout') {
            // Mark the item as out of stock (set quantity to 0).
            $newStatus = compute_status(0);
            $stmt = $conn->prepare("UPDATE inventory i 
                                    JOIN medicine m ON i.medicine_id = m.medicine_id 
                                    SET i.quantity = 0, i.status = ? 
                                    WHERE m.medicine_name = ?");
            $stmt->bind_param("ss", $newStatus, $itemName);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $message = "Success: Marked {$itemName} as out of stock.";
            } else {
                $message = "Error: Item not found.";
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'edit') {
            // Removed check for negative values so negative inventory is allowed.
            if (!isset($_POST['new_quantity']) || !is_numeric($_POST['new_quantity'])) {
                $message = "Error: Please enter a valid quantity.";
            } else {
                $newQuantity = intval($_POST['new_quantity']);
                $newStatus = compute_status($newQuantity);
                $stmt = $conn->prepare("UPDATE inventory i 
                                        JOIN medicine m ON i.medicine_id = m.medicine_id 
                                        SET i.quantity = ?, i.status = ? 
                                        WHERE m.medicine_name = ?");
                $stmt->bind_param("iss", $newQuantity, $newStatus, $itemName);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $message = "Success: Updated quantity for {$itemName} to {$newQuantity}.";
                } else {
                    $message = "Error: Update failed or no changes made.";
                }
                $stmt->close();
            }
        }
    }
}

// ===========================
// Process the Add Inventory Item form submission
// ===========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_meds'])) {
    $name       = trim($_POST['name']);       // Medicine name
    $category   = trim($_POST['category']);     // Category from form
    $quantity   = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $dateIssued = trim($_POST['dateIssued']);
    $expiryDate = trim($_POST['expiryDate']);
    $errors = [];
    if (empty($name)) {
        $errors[] = "Item name is required.";
    }
    if (empty($category)) {
        $errors[] = "Category is required.";
    }
    if ($quantity <= 0) {
        $errors[] = "Quantity must be a positive number.";
    }
    if (empty($dateIssued)) {
        $errors[] = "Date Issued is required.";
    }
    if (empty($expiryDate)) {
        $errors[] = "Expiry Date is required.";
    }
    if (!empty($errors)) {
        $message = implode("<br>", $errors);
    } else {
        $status = compute_status($quantity);
        // First, check if the medicine exists.
        $stmt = $conn->prepare("SELECT medicine_id FROM medicine WHERE medicine_name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $resultMed = $stmt->get_result();
        if ($row = $resultMed->fetch_assoc()) {
            $medicine_id = $row['medicine_id'];
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO medicine (medicine_name, category) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $category);
            if ($stmt->execute()) {
                $medicine_id = $stmt->insert_id;
            } else {
                $message = "Error inserting medicine: " . $stmt->error;
                $stmt->close();
                goto end_output; // jump to end output section
            }
        }
        $stmt->close();
        // Insert new inventory record using the medicine_id.
        $stmt = $conn->prepare("INSERT INTO inventory (medicine_id, quantity, date_issued, expiry_date, status) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $message = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("iisss", $medicine_id, $quantity, $dateIssued, $expiryDate, $status);
            if ($stmt->execute()) {
                header("Location: manage_inventory.php?message=" . urlencode("Inventory item added successfully."));
                exit;
            } else {
                $message = "Error adding inventory item: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// ===========================
// Process "Generate Report" Form Submission
// ===========================
$reportGenerated = false;
$reportError = "";
$reportSummary = [];
$reportItems = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $r_category    = isset($_POST['report_category']) ? trim($_POST['report_category']) : '';
    $r_stockType   = isset($_POST['report_stock_type']) ? trim($_POST['report_stock_type']) : '';
    $r_stockStatus = isset($_POST['report_stock_status']) ? trim($_POST['report_stock_status']) : '';
    if (empty($r_category)) {
        $r_category = "all";
    }
    // Build query joining inventory and medicine.
    $r_sql = "SELECT i.*, m.medicine_name as name, m.category 
              FROM inventory i JOIN medicine m ON i.medicine_id = m.medicine_id WHERE 1=1";
    $r_params = [];
    $r_types = "";
    if ($r_category !== "all") {
        $r_sql .= " AND m.category = ?";
        $r_params[] = $r_category;
        $r_types .= "s";
    }
    if ($r_stockType === 'expired') {
        $r_sql .= " AND i.expiry_date < CURDATE()";
    } elseif ($r_stockType === 'new') {
        $r_sql .= " AND i.expiry_date >= CURDATE()";
    }
    if (!empty($r_stockStatus) && in_array($r_stockStatus, ['In Stock', 'Low Stock', 'Out of Stock'])) {
        $r_sql .= " AND i.status = ?";
        $r_params[] = $r_stockStatus;
        $r_types .= "s";
    }
    $stmt = $conn->prepare($r_sql);
    if ($stmt) {
        if (!empty($r_params)) {
            $stmt->bind_param($r_types, ...$r_params);
        }
        $stmt->execute();
        $resultReport = $stmt->get_result();
        $totalItems = 0;
        $totalQuantity = 0;
        $statusCounts = [
            "In Stock"     => 0,
            "Low Stock"    => 0,
            "Out of Stock" => 0
        ];
        $reportItems = [];
        while ($row = $resultReport->fetch_assoc()) {
            $reportItems[] = $row;
            $totalItems++;
            $totalQuantity += intval($row['quantity']);
            $status = $row['status'];
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }
        $stmt->close();
        $reportSummary = [
            "Category"         => ($r_category === "all") ? "All Categories" : $r_category,
            "Total Items"      => $totalItems,
            "Total Quantity"   => $totalQuantity,
            "Status Breakdown" => $statusCounts
        ];
        $reportGenerated = true;
    } else {
        $reportError = "Database error: " . $conn->error;
    }
}

// ===========================
// Process Filter Inventory for Display (GET parameters)
// ===========================
$sql = "SELECT i.*, m.medicine_name as name, m.category 
        FROM inventory i 
        JOIN medicine m ON i.medicine_id = m.medicine_id 
        WHERE 1=1";
$params = [];
$types = "";
if (isset($_GET['filter_name']) && trim($_GET['filter_name']) !== "") {
    $sql .= " AND m.medicine_name LIKE ?";
    $params[] = "%" . trim($_GET['filter_name']) . "%";
    $types .= "s";
}
if (isset($_GET['filter_category']) && $_GET['filter_category'] !== "all" && trim($_GET['filter_category']) !== "") {
    $filter_category = trim($_GET['filter_category']);
    $sql .= " AND m.category = ?";
    $params[] = $filter_category;
    $types .= "s";
}
if (isset($_GET['filter_stock_type']) && trim($_GET['filter_stock_type']) !== "") {
    $filter_stock_type = trim($_GET['filter_stock_type']);
    if ($filter_stock_type === 'expired') {
        $sql .= " AND i.expiry_date < CURDATE()";
    } elseif ($filter_stock_type === 'new') {
        $sql .= " AND i.expiry_date >= CURDATE()";
    }
}
if (isset($_GET['filter_stock_status']) && trim($_GET['filter_stock_status']) !== "") {
    $filter_stock_status = trim($_GET['filter_stock_status']);
    $sql .= " AND i.status = ?";
    $params[] = $filter_stock_status;
    $types .= "s";
}
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$filtered_inventory = [];
while ($row = $result->fetch_assoc()) {
    $filtered_inventory[] = $row;
}
$stmt->close();

// ===========================
// Retrieve Transaction History
// ===========================
$sql = "SELECT * FROM transactions ORDER BY transaction_date DESC";
$result = $conn->query($sql);
$transaction_history = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transaction_history[] = $row;
    }
    $result->free();
}
end_output:
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Medicine Inventory</title>
  <link rel="stylesheet" href="style.css">
  <script>
    // Optional: Client-side validation or UI enhancements.
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
  
  <div class="global-container">
    <!-- Global Sidebar -->
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
  <!-- Logout Link in the Sidebar -->
  <div class="logout-sidebar">
    <a href="logout.php">Logout</a>
  </div>
</div>


    </div>
    
    <!-- Page Content -->
    <div class="page-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="search-area">
          <form method="GET" action="manage_inventory.php">
            <input type="text" name="filter_name" placeholder="Search Inventory" value="<?php echo isset($_GET['filter_name']) ? htmlspecialchars($_GET['filter_name']) : ''; ?>">
            <button type="submit" class="search-button">Search</button>
          </form>
        </div>
        <div class="action-buttons">
          <form method="GET" action="manage_inventory.php" style="display:inline;">
            <input type="hidden" name="action" value="add">
            <button type="submit" class="add-item">Add Inventory Item</button>
          </form>
          <form method="GET" action="manage_inventory.php" style="display:inline;">
            <input type="hidden" name="action" value="reports">
            <button type="submit" class="reports">Item Reports</button>
          </form>
        </div>
      </div>
      
      <!-- Inventory Container -->
      <div class="inventory-container">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
          <h3>Inventory Filters</h3>
          <form method="GET" action="manage_inventory.php" class="filter-form">
            <!-- Name Filter -->
            <div class="form-group">
              <label for="filter_name">Name:</label>
              <input type="text" name="filter_name" id="filter_name" placeholder="Enter medicine name" 
                     value="<?php echo isset($_GET['filter_name']) ? htmlspecialchars($_GET['filter_name']) : ''; ?>">
            </div>
            <!-- Category Filter -->
            <div class="form-group">
              <label for="filter_category">Category:</label>
              <select name="filter_category" id="filter_category">
                <option value="all">-- All Categories --</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo htmlspecialchars($cat); ?>"
                    <?php echo (isset($_GET['filter_category']) && $_GET['filter_category'] === $cat) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($cat); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <!-- Stock Type Filter -->
            <div class="form-group">
              <label for="filter_stock_type">Stock Type:</label>
              <select name="filter_stock_type" id="filter_stock_type">
                <option value="">-- All Stock Types --</option>
                <option value="new" <?php echo (isset($_GET['filter_stock_type']) && $_GET['filter_stock_type'] === 'new') ? "selected" : ""; ?>>
                  New Stock (Not Expired)
                </option>
                <option value="expired" <?php echo (isset($_GET['filter_stock_type']) && $_GET['filter_stock_type'] === 'expired') ? "selected" : ""; ?>>
                  Expired Stock
                </option>
              </select>
            </div>
            <!-- Stock Status Filter -->
            <div class="form-group">
              <label for="filter_stock_status">Stock Status:</label>
              <select name="filter_stock_status" id="filter_stock_status">
                <option value="">-- All Statuses --</option>
                <option value="In Stock" <?php echo (isset($_GET['filter_stock_status']) && $_GET['filter_stock_status'] === 'In Stock') ? "selected" : ""; ?>>
                  In Stock
                </option>
                <option value="Low Stock" <?php echo (isset($_GET['filter_stock_status']) && $_GET['filter_stock_status'] === 'Low Stock') ? "selected" : ""; ?>>
                  Low Stock
                </option>
                <option value="Out of Stock" <?php echo (isset($_GET['filter_stock_status']) && $_GET['filter_stock_status'] === 'Out of Stock') ? "selected" : ""; ?>>
                  Out of Stock
                </option>
              </select>
            </div>
            <button type="submit" class="apply-filter">Apply Filter</button>
          </form>
          
          <div class="inventory-stats">
            <h4>Inventory Statistics</h4>
            <p><strong>Items in Stock:</strong> <?php echo $totalStock; ?></p>
          </div>
        </aside>
        
        <!-- Inventory List -->
        <main class="inventory-list">
          <h2>Inventory List</h2>
          <?php if (count($filtered_inventory) > 0): ?>
            <?php foreach ($filtered_inventory as $item): ?>
              <?php $computedStatus = compute_status($item['quantity']); ?>
              <div class="inventory-item">
                <!-- Basic Info -->
                <div class="item-info">
                  <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                  <span class="category"><?php echo htmlspecialchars($item['category']); ?></span>
                  <p class="quantity">Quantity: <?php echo $item['quantity']; ?></p>
                  <p class="status <?php echo strtolower(str_replace(' ', '-', $computedStatus)); ?>">
                    <?php echo $computedStatus; ?>
                  </p>
                </div>
                <!-- Date Info -->
                <div class="date-info">
                  <div class="date-issued">
                    <label>Date Issued:</label>
                    <span><?php echo htmlspecialchars($item['date_issued']); ?></span>
                  </div>
                  <div class="expiry">
                    <label>Expiry Date:</label>
                    <span><?php echo htmlspecialchars($item['expiry_date']); ?></span>
                  </div>
                </div>
                <!-- Item Actions -->
                <div class="item-options">
                  <form method="GET" action="manage_inventory.php" style="display:inline;">
                    <input type="hidden" name="action" value="overview">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
                    <button type="submit" class="overview-btn">Quantity Overview</button>
                  </form>
                  <form method="GET" action="manage_inventory.php" style="display:inline;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
                    <button type="submit" class="options-btn">Edit</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No inventory items found.</p>
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
                <th></th>
                <th>Date</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transaction_history as $trans): ?>
                <tr>
                  <td><?php echo htmlspecialchars($trans["transaction_code"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["prescription_id"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["patient_name"]); ?></td>
                  <td><?php echo htmlspecialchars($trans["details"]); ?></td>
                  <td><?php echo isset($trans["email"]) ? htmlspecialchars($trans["email"]) : ''; ?></td>
                  <td><?php echo htmlspecialchars($trans["transaction_date"]); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No transactions yet.</p>
        <?php endif; ?>
      </div>
      
    </div><!-- end page-content -->
  </div><!-- end global-container -->
  
  <!-- PHP-Driven Modal Popups -->
  <?php if (isset($_GET['action'])):
    $action = $_GET['action'];
    // Add Inventory Item Modal
    if ($action == "add"):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Add Inventory Item</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <form method="POST" action="manage_inventory.php">
              <div class="form-group">
                <label for="add-name">Name:</label>
                <input type="text" name="name" id="add-name" required>
              </div>
              <div class="form-group">
                <label for="add-category">Category:</label>
                <input type="text" name="category" id="add-category" list="existingCategories" placeholder="Enter or select category" required>
                <datalist id="existingCategories">
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>">
                  <?php endforeach; ?>
                </datalist>
              </div>
              <div class="form-group">
                <label for="add-quantity">Quantity:</label>
                <input type="number" name="quantity" id="add-quantity" required>
              </div>
              <div class="form-group">
                <label for="add-dateIssued">Date Issued:</label>
                <input type="date" name="dateIssued" id="add-dateIssued" required>
              </div>
              <div class="form-group">
                <label for="add-expiryDate">Expiry Date:</label>
                <input type="date" name="expiryDate" id="add-expiryDate" required>
              </div>
              <button type="submit" name="add_meds" class="modal-submit">Add Item</button>
            </form>
          </div>
        </div>
      </div>
  <?php
    // Report Modal: Form to generate a report
    elseif ($action == "reports"):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Item Report</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <form method="POST" action="manage_inventory.php">
              <div class="form-group">
                <label for="report-category">Category:</label>
                <select name="report_category" id="report-category">
                  <option value="all">-- All Categories --</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>">
                      <?php echo htmlspecialchars($cat); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="report-stock-type">Stock Type:</label>
                <select name="report_stock_type" id="report-stock-type">
                  <option value="">-- All Stock Types --</option>
                  <option value="new">New Stock (Not Expired)</option>
                  <option value="expired">Expired Stock</option>
                </select>
              </div>
              <div class="form-group">
                <label for="report-stock-status">Stock Status:</label>
                <select name="report_stock_status" id="report-stock-status">
                  <option value="">-- All Statuses --</option>
                  <option value="In Stock">In Stock</option>
                  <option value="Low Stock">Low Stock</option>
                  <option value="Out of Stock">Out of Stock</option>
                </select>
              </div>
              <button type="submit" name="generate_report" class="modal-submit">Generate Report &amp; Print</button>
            </form>
          </div>
        </div>
      </div>
  <?php
    // Edit Inventory Item Modal
    elseif ($action == "edit" && isset($_GET['name'])):
      $item = get_item_by_name($conn, $_GET['name']);
      if ($item):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Edit Inventory Item</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <form method="POST" action="manage_inventory.php">
              <div class="form-group">
                <label>Item Name:</label>
                <span><?php echo htmlspecialchars($item['name']); ?></span>
              </div>
              <div class="form-group">
                <label for="new_quantity">New Quantity:</label>
                <input type="number" name="new_quantity" id="new_quantity" value="<?php echo $item['quantity']; ?>" required>
              </div>
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
              <button type="submit" class="modal-submit">Update Quantity</button>
            </form>
            <form method="POST" action="manage_inventory.php" onsubmit="return confirm('Are you sure you want to delete this item?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="name" value="<?php echo htmlspecialchars($item['name']); ?>">
              <button type="submit" class="modal-submit delete-btn">Delete Item</button>
            </form>
          </div>
        </div>
      </div>
  <?php
      else:
        echo "<p>Item not found.</p>";
      endif;
    // Overview Modal
    elseif ($action == "overview" && isset($_GET['name'])):
      $item = get_item_by_name($conn, $_GET['name']);
      if ($item):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Quantity Overview</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($item['name']); ?></p>
            <p><strong>Current Quantity:</strong> <?php echo $item['quantity']; ?></p>
          </div>
        </div>
      </div>
  <?php
      else:
        echo "<p>Item not found.</p>";
      endif;
    endif;
  endif;
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($message) && !isset($_POST['generate_report'])):
  ?>
      <div class="modal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Action Completed</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <p><?php echo $message; ?></p>
            <form method="GET" action="manage_inventory.php">
              <button type="submit" class="modal-submit">Return to Inventory</button>
            </form>
          </div>
        </div>
      </div>
  <?php
  endif;
  
  if ($reportGenerated === true):
  ?>
      <div class="modal" id="reportModal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Inventory Report Summary</h2>
            <form method="GET" action="manage_inventory.php" style="display:inline;">
              <button type="submit" class="close">&times;</button>
            </form>
          </div>
          <div class="modal-body">
            <?php if (!empty($reportError)): ?>
              <p style="color:red;"><?php echo htmlspecialchars($reportError); ?></p>
            <?php else: ?>
              <p><strong>Category:</strong> <?php echo htmlspecialchars($reportSummary["Category"]); ?></p>
              <p><strong>Total Items Found:</strong> <?php echo $reportSummary["Total Items"]; ?></p>
              <p><strong>Total Quantity:</strong> <?php echo $reportSummary["Total Quantity"]; ?></p>
              <h3>Status Breakdown</h3>
              <table>
                <thead>
                  <tr>
                    <th>Status</th>
                    <th>Count</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($reportSummary["Status Breakdown"] as $status => $count): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($status); ?></td>
                      <td><?php echo $count; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <h3>Detailed Items</h3>
              <table>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Date Issued</th>
                    <th>Expiry Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($reportItems as $item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item['name']); ?></td>
                      <td><?php echo $item['quantity']; ?></td>
                      <td><?php echo htmlspecialchars($item['status']); ?></td>
                      <td><?php echo htmlspecialchars($item['date_issued']); ?></td>
                      <td><?php echo htmlspecialchars($item['expiry_date']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button onclick="window.print()" class="modal-submit">Print Report</button>
          </div>
          <script>
            setTimeout(function() {
              window.print();
            }, 500);
          </script>
        </div>
      </div>
  <?php
  endif;
  ?>
  
</body>
</html>
