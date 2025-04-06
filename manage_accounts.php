<?php
session_start();
require_once 'db.php'; // Assumes $conn is defined in db.php

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
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
// Fetch available roles from SQL
// ---------------------------
$roles = [];
$sqlRoles = "SELECT role_id, role_name FROM roles ORDER BY role_name ASC";
$resultRoles = $conn->query($sqlRoles);
if ($resultRoles) {
    while ($row = $resultRoles->fetch_assoc()) {
        $roles[] = $row;
    }
    $resultRoles->free();
}

$message = "";

// ---------------------------
// Process GET/POST Actions
// ---------------------------

// DELETE Account
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_accounts.php");
    exit;
}

// CREATE Account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'create') {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $roleName = $_POST['role'];
    // No password is provided; store an empty string.
    $hashedPassword = "";
    // For simplicity, assign a default access level.
    $defaultAccessLevel = 1;

    // Insert into staff_details to get a staff_id.
    $stmt = $conn->prepare("INSERT INTO staff_details (full_name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $staff_id = $stmt->insert_id;
    $stmt->close();

    // Lookup role_id based on role name.
    $role_id = null;
    $stmt = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ? LIMIT 1");
    $stmt->bind_param("s", $roleName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $role_id = $row['role_id'];
    }
    $stmt->close();

    if (!$role_id) {
        $message = "Error: Invalid role selected.";
    } else {
        // Insert into users table using the empty password.
        $stmt = $conn->prepare("INSERT INTO users (staff_id, username, password, role_id, access_level_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issii", $staff_id, $username, $hashedPassword, $role_id, $defaultAccessLevel);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_accounts.php");
        exit;
    }
}

// EDIT Account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = $_GET['id'];
    $roleName = $_POST['role'];
    $defaultAccessLevel = 1; // For simplicity, reset to default

    // Lookup role_id based on role name.
    $role_id = null;
    $stmt = $conn->prepare("SELECT role_id FROM roles WHERE role_name = ? LIMIT 1");
    $stmt->bind_param("s", $roleName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $role_id = $row['role_id'];
    }
    $stmt->close();

    if (!$role_id) {
        $message = "Error: Invalid role selected.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role_id = ?, access_level_id = ? WHERE user_id = ?");
        $stmt->bind_param("iii", $role_id, $defaultAccessLevel, $edit_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_accounts.php");
        exit;
    }
}

// ---------------------------
// Build Filter Query for Accounts
// ---------------------------
$sql = "SELECT u.user_id as id, u.username, sd.full_name as name, r.role_name as role, al.level_name as access, u.created_at
        FROM users u
        JOIN staff_details sd ON u.staff_id = sd.staff_id
        JOIN roles r ON u.role_id = r.role_id
        JOIN access_levels al ON u.access_level_id = al.access_level_id
        WHERE 1=1";
$params = [];
$types = "";
if (isset($_GET['filter_name']) && $_GET['filter_name'] !== "") {
    $sql .= " AND (sd.full_name LIKE ? OR u.username LIKE ?)";
    $filter = "%" . $_GET['filter_name'] . "%";
    $params[] = $filter;
    $params[] = $filter;
    $types .= "ss";
}
if (isset($_GET['filter_role']) && $_GET['filter_role'] !== "") {
    $sql .= " AND r.role_name = ?";
    $params[] = $_GET['filter_role'];
    $types .= "s";
}
$sql .= " ORDER BY u.user_id ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$accounts = [];
while ($row = $result->fetch_assoc()) {
    $accounts[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Accounts</title>
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
      <!-- Logout Link in the Sidebar -->
  <div class="logout-sidebar">
    <a href="logout.php">Logout</a>
  </div>
    </div>
    
    <!-- Page Content -->
    <div class="page-content">
      <!-- Top Bar for Manage Accounts Page -->
      <div class="top-bar">
        <div class="search-area">
          <form method="GET" action="manage_accounts.php">
            <input type="text" name="filter_name" placeholder="Search Accounts" value="<?php echo isset($_GET['filter_name']) ? htmlspecialchars($_GET['filter_name']) : ''; ?>">
            <button type="submit" class="search-button">Search</button>
          </form>
        </div>
        <div class="action-buttons">
          <a href="manage_accounts.php?action=create" class="add-item">Create Account</a>
        </div>
      </div>
      
      <!-- Main Container -->
      <div class="inventory-container">
        <!-- Left Sidebar: Account Filters -->
        <aside class="filter-sidebar">
          <h3>Account Filters</h3>
          <form method="GET" action="manage_accounts.php" class="filter-form">
            <div class="filter-block">
              <label for="filter_name">Name/Username:</label>
              <input type="text" name="filter_name" id="filter_name" placeholder="Enter name or username" value="<?php echo isset($_GET['filter_name']) ? htmlspecialchars($_GET['filter_name']) : ''; ?>">
            </div>
            <div class="filter-block">
              <label for="filter_role">Role:</label>
              <select name="filter_role" id="filter_role">
                <option value="">-- All Roles --</option>
                <?php foreach ($roles as $r): ?>
                  <option value="<?php echo $r['role_name']; ?>" <?php echo (isset($_GET['filter_role']) && $_GET['filter_role'] === $r['role_name']) ? "selected" : ""; ?>>
                    <?php echo $r['role_name']; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="apply-filter">Apply Filter</button>
          </form>
          
          <div class="inventory-stats">
            <h4>Account Stats</h4>
            <p><strong>Total Accounts:</strong> <?php echo count($accounts); ?></p>
          </div>
        </aside>
        
        <!-- Right Content Area: Account List -->
        <main class="inventory-list">
          <h2>Accounts</h2>
          <?php if (count($accounts) > 0): ?>
            <?php foreach ($accounts as $account): ?>
              <div class="inventory-item">
                <!-- Basic Info -->
                <div class="item-info">
                  <h3><?php echo htmlspecialchars($account['name']); ?></h3>
                  <p class="quantity">Username: <?php echo htmlspecialchars($account['username']); ?></p>
                  <p class="quantity">Role: <?php echo htmlspecialchars($account['role']); ?></p>
                  <p class="quantity">Access: <?php echo htmlspecialchars($account['access']); ?></p>
                </div>
                <!-- Action Buttons -->
                <div class="item-options">
                  <button class="overview-btn" onclick="window.location.href='manage_accounts.php?action=edit&id=<?php echo $account['id']; ?>'">Edit</button>
                  <button class="options-btn" onclick="window.location.href='manage_accounts.php?action=delete&id=<?php echo $account['id']; ?>'">Delete</button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No accounts found.</p>
          <?php endif; ?>
        </main>
      </div>
    </div>
  </div>
  
  <!-- Modal Popups -->
  <?php if (isset($_GET['action'])):
    $action = $_GET['action'];
    if ($action == "create"): ?>
      <!-- Create Account Modal -->
<div class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Create New Account</h2>
      <a href="manage_accounts.php" class="close">&times;</a>
    </div>
    <div class="modal-body">
      <?php
      // Determine the selected role.
      $selected_role = isset($_POST['role']) ? $_POST['role'] : ($roles[0]['role_name'] ?? 'Admin');
      ?>
      <form method="POST" action="manage_accounts.php?action=create">
        <div class="form-group">
          <label for="create-username">Username:</label>
          <input type="text" name="username" id="create-username" required>
        </div>
        <div class="form-group">
          <label for="create-name">Full Name:</label>
          <input type="text" name="name" id="create-name" required>
        </div>
        <div class="form-group">
          <label for="create-role">Role:</label>
          <select name="role" id="create-role" required>
            <?php foreach ($roles as $r): ?>
              <option value="<?php echo $r['role_name']; ?>" <?php echo ($selected_role === $r['role_name']) ? 'selected' : ''; ?>>
                <?php echo $r['role_name']; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Removed Access Rights section -->
        <button type="submit" class="modal-submit">Create Account</button>
      </form>
    </div>
  </div>
</div>

    <?php elseif ($action == "edit" && isset($_GET['id'])):
      // Retrieve the account from the database for editing.
      $edit_id = $_GET['id'];
      $stmt = $conn->prepare("SELECT u.user_id as id, u.username, sd.full_name as name, r.role_name as role, al.level_name as access 
                              FROM users u 
                              JOIN staff_details sd ON u.staff_id = sd.staff_id 
                              JOIN roles r ON u.role_id = r.role_id 
                              JOIN access_levels al ON u.access_level_id = al.access_level_id 
                              WHERE u.user_id = ? LIMIT 1");
      $stmt->bind_param("i", $edit_id);
      $stmt->execute();
      $edit_account = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      if ($edit_account):
    ?>
      <!-- Edit Account Modal -->
<div class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit Account: <?php echo htmlspecialchars($edit_account['username']); ?></h2>
      <a href="manage_accounts.php" class="close">&times;</a>
    </div>
    <div class="modal-body">
      <?php 
      // Use the account's stored role.
      $selected_role = $edit_account['role'];
      ?>
      <form method="POST" action="manage_accounts.php?action=edit&id=<?php echo $edit_account['id']; ?>">
        <div class="form-group">
          <label>Username:</label>
          <span><?php echo htmlspecialchars($edit_account['username']); ?></span>
        </div>
        <div class="form-group">
          <label>Full Name:</label>
          <span><?php echo htmlspecialchars($edit_account['name']); ?></span>
        </div>
        <div class="form-group">
          <label for="edit-role">Role:</label>
          <select name="role" id="edit-role" required>
            <?php foreach ($roles as $r): ?>
              <option value="<?php echo $r['role_name']; ?>" <?php echo ($selected_role === $r['role_name']) ? 'selected' : ''; ?>>
                <?php echo $r['role_name']; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Removed Access Rights section -->
        <button type="submit" class="modal-submit">Save Changes</button>
      </form>
    </div>
  </div>
</div>

    <?php 
      endif;
    endif;
  endif;
  ?>
  
</body>
</html>
