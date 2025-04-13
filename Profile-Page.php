<?php
session_start();
require_once('connection.php');
require_once('function.php');

// Check if user is logged in
$user_data = check_login($con);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    // Sanitize and validate input
    $fullName = isset($_POST['fullName']) ? sanitize_input($con, $_POST['fullName']) : '';
    $dob = isset($_POST['dob']) ? sanitize_input($con, $_POST['dob']) : '';
    $gender = isset($_POST['gender']) ? sanitize_input($con, $_POST['gender']) : '';
    $email = isset($_POST['email']) ? sanitize_input($con, $_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($con, $_POST['phone']) : '';
    $bloodType = isset($_POST['bloodType']) ? sanitize_input($con, $_POST['bloodType']) : '';
    $address = isset($_POST['address']) ? sanitize_input($con, $_POST['address']) : '';
    $physician = isset($_POST['physician']) ? sanitize_input($con, $_POST['physician']) : '';

    // Split full name into first and last name
    $nameParts = explode(' ', $fullName, 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

    // Update user data in database
    $query = "UPDATE users SET 
              first_name = ?, 
              last_name = ?, 
              date_of_birth = ?, 
              gender = ?, 
              email = ?, 
              phone = ?, 
              blood_type = ?, 
              address = ?, 
              primary_physician = ? 
              WHERE user_id = ?";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssss", 
        $firstName, 
        $lastName, 
        $dob, 
        $gender, 
        $email, 
        $phone, 
        $bloodType, 
        $address, 
        $physician, 
        $user_data['user_id']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = 'Profile updated successfully!';
        // Refresh user data
        $user_data = check_login($con);
        // Force immediate update by redirecting
        header("Location: Profile-Page.php");
        exit();
    } else {
        $_SESSION['error_message'] = 'Failed to update profile: ' . mysqli_error($con);
    }
}

// Format date of birth for display
$displayDob = '';
if (!empty($user_data['date_of_birth'])) {
    $date = new DateTime($user_data['date_of_birth']);
    $displayDob = $date->format('F j, Y');
}

// Get emergency contacts
$contacts = [];
$contact_query = "SELECT * FROM emergency_contacts WHERE user_id = ?";
$stmt = mysqli_prepare($con, $contact_query);
mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
mysqli_stmt_execute($stmt);
$contact_result = mysqli_stmt_get_result($stmt);
if ($contact_result) {
    $contacts = mysqli_fetch_all($contact_result, MYSQLI_ASSOC);
}

// Get insurance information
$insurance = [];
$insurance_query = "SELECT * FROM insurance WHERE user_id = ? ORDER BY is_primary DESC";
$stmt = mysqli_prepare($con, $insurance_query);
mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
mysqli_stmt_execute($stmt);
$insurance_result = mysqli_stmt_get_result($stmt);
if ($insurance_result) {
    $insurance = mysqli_fetch_all($insurance_result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | MedicalChecks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Style/Profile-Page.css">
    <link rel="stylesheet" href="Style/Home-Page.css">
</head>
    <body>
    <header>
            <nav class="navbar">
                <div class="nav-logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MedicalChecks</span>
                </div>

                <!-- Navigation Links (visible only when logged in) -->
                <div class="nav-links" id="mainNavLinks" style="display: <?= isset($user_data) ? 'flex' : 'none' ?>;">
                    <div class="dropdown">
                        <a href="#" class="dropbtn">Home</a>
                        <div class="dropdown-content">
                            <a href="Home-Page.php"><i class="fas fa-home"></i> Dashboard</a>
                            <a href="Contact-Us-Page.php"><i class="fas fa-envelope"></i> Contact Us</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a href="#" class="dropbtn">Patient Portal</a>
                        <div class="dropdown-content">
                            <a href="Appointment-Page.php"><i class="fas fa-calendar-check"></i> Appointment</a>
                            <a href="Billing-Page.php"><i class="fas fa-file-invoice-dollar"></i> Billing</a>
                            <a href="Medical-Record-Page.php"><i class="fas fa-file-medical"></i> Medical Record</a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a href="#" class="dropbtn">Laboratory Tests</a>
                        <div class="dropdown-content">
                            <a href="Test-Results-Page.php"><i class="fas fa-flask"></i> Test Result</a>
                            <a href="Order-Page.php"><i class="fas fa-clipboard-list"></i> Request Tests</a>
                            <a href="Test-History-Page.php"><i class="fas fa-history"></i> Test History</a>
                        </div>
                    </div>
                </div>

                <!-- User Menu (visible only when logged in) -->
                <div class="user-menu" id="userMenu" style="display: <?= isset($user_data) ? 'block' : 'none' ?>;">
                    <div class="dropdown">
                        <button class="dropbtn">
                            <i class="fas fa-user-circle"></i>
                            <span><?= htmlspecialchars($user_data['user_name']) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="Profile-Page.php"><i class="fas fa-user"></i> Profile</a>
                            <a href="Settings-Page.php"><i class="fas fa-cog"></i> Settings</a>
                            <a href="logout.php" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>

                <!-- Auth Buttons (visible only when logged out) -->
                <div class="auth-buttons" id="authButtons" style="display: <?= isset($user_data) ? 'none' : 'flex' ?>;">
                    <button class="sign-in"><a href="Login-Page.php"><i class="fas fa-sign-in-alt"></i> Sign in</a></button>
                    <button class="register"><a href="Sign-Up-Page.html"><i class="fas fa-user-plus"></i> Register</a></button>
                </div>

                <!-- Hamburger Menu -->
                <button class="hamburger" id="hamburgerBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>

            <!-- Mobile Menu -->
            <div class="mobile-menu" id="mobileMenu">
                <div class="mobile-menu-content">
                    <!-- Populated by JS -->
                </div>
            </div>
        </header>

        
        <main class="profile-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-header">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Profile Picture" class="profile-avatar">
            <h1><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></h1>
            <p>Member since <?php echo date('F Y', strtotime($user_data['created_at'])); ?></p>
            
            <div class="profile-actions">
                <button class="btn btn-primary" id="editProfileBtn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <button class="btn btn-outline" id="changePasswordBtn">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </div>
        </div>
            
            <!-- Personal Information Section -->
            <section class="profile-section">
            <h2><i class="fas fa-user"></i> Personal Information</h2>
            
            <form id="profileForm" method="POST" action="Profile-Page.php">
                <div class="personal-info">
                    <div class="info-group">
                        <div class="info-item">
                            <label>Full Name</label>
                            <div class="info-value" id="fullName"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . htmlspecialchars($user_data['last_name'])); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Date of Birth</label>
                            <div class="info-value" id="dob"><?php echo htmlspecialchars($displayDob); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Gender</label>
                            <div class="info-value" id="gender"><?php echo ucfirst(htmlspecialchars($user_data['gender'] ?? 'Not specified')); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-item">
                            <label>Email</label>
                            <div class="info-value" id="email"><?php echo htmlspecialchars($user_data['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Phone Number</label>
                            <div class="info-value" id="phone"><?php echo htmlspecialchars($user_data['phone'] ?? 'Not specified'); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Blood Type</label>
                            <div class="info-value" id="bloodType"><?php echo htmlspecialchars($user_data['blood_type'] ?? 'Not specified'); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-item">
                            <label>Address</label>
                            <div class="info-value" id="address"><?php echo htmlspecialchars($user_data['address'] ?? 'Not specified'); ?></div>
                        </div>
                        <div class="info-item">
                            <label>Primary Physician</label>
                            <div class="info-value" id="physician"><?php echo htmlspecialchars($user_data['primary_physician'] ?? 'Not specified'); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden Save/Cancel Buttons (Shown via JavaScript) -->
                <div class="profile-actions" id="saveCancelButtons" style="display: none;">
                    <button type="submit" class="btn btn-primary" id="saveProfileBtn" name="save_profile">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn btn-outline" id="cancelEditBtn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </section>
            
            <!-- Emergency Contacts Section -->
            <section class="profile-section">
                <h2><i class="fas fa-address-book"></i> Emergency Contacts</h2>
                
                <div class="emergency-contacts">
                    <?php foreach ($contacts as $contact): ?>
                        <div class="contact-card">
                            <div class="contact-actions">
                                <button class="btn btn-icon edit-contact" title="Edit" data-contact-id="<?php echo $contact['contact_id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-icon delete-contact" title="Delete" data-contact-id="<?php echo $contact['contact_id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <h3><?php echo htmlspecialchars($contact['full_name']); ?></h3>
                            <span class="contact-relation"><?php echo htmlspecialchars($contact['relationship']); ?></span>
                            <div class="contact-details">
                                <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['phone']); ?></div>
                                <?php if (!empty($contact['email'])): ?>
                                    <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?></div>
                                <?php endif; ?>
                                <div><i class="fas fa-home"></i> <?php echo $contact['is_same_address'] ? 'Same as patient' : htmlspecialchars($contact['address']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <button class="btn btn-outline" id="addContactBtn">
                        <i class="fas fa-plus"></i> Add Emergency Contact
                    </button>
                </div>
            </section>
            
            <!-- Insurance Information Section -->
            <section class="profile-section">
                <h2><i class="fas fa-file-invoice-dollar"></i> Insurance Information</h2>
                
                <?php foreach ($insurance as $ins): ?>
                    <div class="insurance-card">
                        <div class="insurance-actions">
                            <button class="btn btn-icon edit-insurance" title="Edit" data-insurance-id="<?php echo $ins['insurance_id']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-icon delete-insurance" title="Delete" data-insurance-id="<?php echo $ins['insurance_id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <h3><?php echo $ins['is_primary'] ? 'Primary Insurance' : 'Secondary Insurance'; ?></h3>
                        <div class="insurance-details">
                            <div>
                                <label>Provider</label>
                                <div class="info-value"><?php echo htmlspecialchars($ins['provider_name']); ?></div>
                            </div>
                            <?php if (!empty($ins['plan_name'])): ?>
                            <div>
                                <label>Plan</label>
                                <div class="info-value"><?php echo htmlspecialchars($ins['plan_name']); ?></div>
                            </div>
                            <?php endif; ?>
                            <div>
                                <label>Member ID</label>
                                <div class="info-value"><?php echo htmlspecialchars($ins['member_id']); ?></div>
                            </div>
                            <?php if (!empty($ins['group_number'])): ?>
                            <div>
                                <label>Group Number</label>
                                <div class="info-value"><?php echo htmlspecialchars($ins['group_number']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($ins['expiration_date'])): ?>
                            <div>
                                <label>Expiration</label>
                                <div class="info-value"><?php echo date('F j, Y', strtotime($ins['expiration_date'])); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <button class="btn btn-outline" id="addInsuranceBtn">
                    <i class="fas fa-plus"></i> Add Insurance
                </button>
            </section>
        </main>
        
        <footer>
            <div class="footer-content">
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="Contact-Us-Page.php">Contact Us</a>
                </div>
                <p>&copy; 2025 MedicalChecks. All rights reserved.</p>
            </div>
        </footer>

        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Profile.js"></script>
        
    </body>
</html>