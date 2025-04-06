<?php
session_start();
require_once 'db.php'; 
// Fetch available roles from SQL
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

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About - Pharmacy Management System</title>
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
    <!-- Fixed Global Sidebar -->
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
<div class="logout-sidebar">
    <a href="logout.php">Logout</a>
  </div>


    </div>
    
    <!-- Page Content -->
    <div class="page-content">
      <div class="page-header">
        <h1>About the Pharmacy Management System</h1>
      </div>
      <div class="about-content">
        <p>
          The Pharmacy Management System is designed to streamline and automate the daily operations of the Macarthur Leyte Rural Health Unit’s pharmacy. This system integrates multiple modules—including inventory management, dispensing, transaction tracking, and account management—to improve efficiency and ensure high-quality healthcare services.
        </p>
        <h2>System Functions</h2>
        <ul>
          <li><strong>Inventory Management:</strong> Real-time tracking of medicine stock levels, ensuring optimal inventory and reducing waste.</li>
          <li><strong>Dispensing Process:</strong> Automated prescription generation and dispensing, with automatic inventory deductions.</li>
          <li><strong>Transaction & Record-Keeping:</strong> Accurate, easily retrievable records of medicine distribution and stock adjustments.</li>
          <li><strong>User Account Management:</strong> Role-based access control, allowing administrators to assign and restrict access as needed.</li>
          <li><strong>Reporting:</strong> Comprehensive reports that offer insights into inventory, dispensing history, and overall system performance.</li>
        </ul>
        <h2>Design & User Experience</h2>
        <p>
          The system features a fixed sidebar for intuitive navigation and a responsive layout that adapts seamlessly across devices. Clean dashboards, consistent design elements, and user-friendly forms ensure that healthcare providers can quickly access necessary information and perform their tasks with minimal training.
        </p>
        <h2>Support</h2>
        <div class="contact-info">
          <p>If you encounter any issues or have questions regarding the system, please contact <strong>ITS-Mapua Group 2</strong> for support.</p>
        </div>
      </div>
    </div>
  </div>
  
  <footer class="bg-light py-3 mt-5">
    <div class="container text-center">
      <p class="mb-0">&copy; 2025 Macarthur Leyte RHU. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
