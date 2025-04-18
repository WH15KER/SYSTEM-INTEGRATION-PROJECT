<?php
session_start();
include("connection.php");
include("function.php");

$user_data = check_login($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | MedicalChecks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Style/Settings-Page.css">
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
                            <span><?= htmlspecialchars($user_data['first_name']) ?></span>
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
        
        <main class="settings-container">
            <div class="settings-header">
                <h1><i class="fas fa-cog"></i> Settings</h1>
                <p>Customize your MedicalChecks experience</p>
            </div>
            
            <!-- Notification Settings -->
            <div class="settings-section">
                <h2><i class="fas fa-bell"></i> Notifications</h2>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Email Notifications</h3>
                        <p>Receive important updates via email</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="emailNotifications" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>SMS Alerts</h3>
                        <p>Get urgent reminders via text message</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="smsAlerts">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>App Notifications</h3>
                        <p>Enable push notifications on your device</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="appNotifications" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            
            <!-- Privacy Settings -->
            <div class="settings-section">
                <h2><i class="fas fa-lock"></i> Privacy</h2>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Data Sharing</h3>
                        <p>Allow anonymized data for medical research</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="dataSharing">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Show in Directory</h3>
                        <p>Allow providers to find you in the patient directory</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="showInDirectory" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            
            <!-- Display Settings -->
            <div class="settings-section">
                <h2><i class="fas fa-palette"></i> Display</h2>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Dark Mode</h3>
                        <p>Switch between light and dark theme</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="darkMode">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Font Size</h3>
                        <p>Adjust the text size for better readability</p>
                    </div>
                    <select class="select-setting" id="fontSize">
                        <option value="small">Small</option>
                        <option value="medium" selected>Medium</option>
                        <option value="large">Large</option>
                    </select>
                </div>
            </div>
            
            <!-- Account Settings -->
            <div class="settings-section">
                <h2><i class="fas fa-user-shield"></i> Account</h2>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Two-Factor Authentication</h3>
                        <p>Add an extra layer of security to your account</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="twoFactorAuth">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>Activity Log</h3>
                        <p>Keep track of account access and changes</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="activityLog" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            
            <button class="save-settings" id="saveSettings">Save Settings</button>
        </main>
        
        <footer>
            <div class="footer-content">
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="Contact-Us-Page.html">Contact Us</a>
                </div>
                <p>&copy; 2025 MedicalChecks. All rights reserved.</p>
            </div>
        </footer>

        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Settings.js"></script>
        
    </body>
</html>