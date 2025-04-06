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

// Get filter values from GET parameters (if any)
$patient_filter  = isset($_GET['patient']) ? trim($_GET['patient']) : "";
$contact_filter  = isset($_GET['contact']) ? trim($_GET['contact']) : "";
$date_filter     = isset($_GET['date']) ? trim($_GET['date']) : "";

// Build the SQL query
$sql = "SELECT 
          t.transaction_id,
          p.full_name AS patient_name,
          p.contact_no,
          (
            SELECT GROUP_CONCAT(CONCAT(m.medicine_name, ' (Qty: ', pm.quantity, ')') SEPARATOR ', ')
            FROM prescription_medicines pm 
            JOIN medicine m ON pm.medicine_id = m.medicine_id
            WHERE pm.prescription_id = t.prescription_id
          ) AS medicines_dispensed,
          t.transaction_date
        FROM transactions t
        JOIN prescriptions pr ON t.prescription_id = pr.prescription_id
        JOIN patients p ON pr.patient_id = p.patient_id
        WHERE 1=1";

// Append filters if provided
if (!empty($patient_filter)) {
    $patient_filter_esc = $conn->real_escape_string($patient_filter);
    $sql .= " AND p.full_name LIKE '%$patient_filter_esc%'";
}
if (!empty($contact_filter)) {
    $contact_filter_esc = $conn->real_escape_string($contact_filter);
    $sql .= " AND p.contact_no LIKE '%$contact_filter_esc%'";
}
if (!empty($date_filter)) {
    $date_filter_esc = $conn->real_escape_string($date_filter);
    $sql .= " AND DATE(t.transaction_date) = '$date_filter_esc'";
}

$sql .= " ORDER BY t.transaction_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transaction History</title>
  <link rel="stylesheet" href="style.css">
  <!-- Ensure that your style.css includes the Google-inspired design rules -->
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
    <aside class="global-sidebar">
    <div class="global-sidebar">
  <div class="profile">
    <div class="profile-logo">
      <img src="logo.png" alt="Logo" class="responsive-logo">
    </div>
    <div class="profile-name"><?php echo htmlspecialchars($profile_name); ?></div>
  </div>
  <div class="nav">
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
    </aside>
    
    <!-- Main Content -->
    <main class="page-content">
      <div class="page-header">
        <h1>Transaction History</h1>
      </div>
      
      <!-- Filter Form -->
      <div class="top-bar">
        <form method="GET" action="transaction_history.php">
          <input type="text" name="patient" placeholder="Patient Name" value="<?php echo htmlspecialchars($patient_filter); ?>">
          <input type="text" name="contact" placeholder="Contact Number" value="<?php echo htmlspecialchars($contact_filter); ?>">
          <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
          <button type="submit" class="search-button">Apply Filters</button>
          <a href="transaction_history.php" class="nav-button" style="margin-left:10px;">Reset</a>
        </form>
      </div>
      
      <?php if ($result && $result->num_rows > 0): ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>Patient Name</th>
              <th>Contact Number</th>
              <th>Medicines Dispensed</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                <td><?php echo htmlspecialchars($row['medicines_dispensed']); ?></td>
                <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="text-align:center;">No transactions found.</p>
      <?php endif; ?>
      
      <!-- Back to Dashboard Button -->
      <a href="index.php" class="nav-button" style="margin-top:20px; display:inline-block;">Back to Dashboard</a>
    </main>
  </div>
</body>
</html>
