<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Settings | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Admin-Settings-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
    <body>
        <header>
            <nav class="navbar">
                <div class="nav-logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MedicalChecks</span>
                </div>
                
                <!-- Navigation Links -->
                <div class="nav-links" id="mainNavLinks">
                    <div class="dropdown">
                        <a href="#" class="dropbtn">Home</a>
                        <div class="dropdown-content">
                            <a href="Admin-Dashboard-Page.html"><i class="fas fa-home"></i> Dashboard</a>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <a href="#" class="dropbtn">Admin Portal</a>
                        <div class="dropdown-content">
                            <a href="Admin-Account-Management-Page.html"><i class="fas fa-users-cog"></i> Account Management</a>
                            <a href="Admin-Reports-Page.html"><i class="fas fa-chart-bar"></i> Reports</a>
                            <a href="Admin-Inventory-Management-Page.html"><i class="fas fa-boxes"></i> Inventory Management</a>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <a href="#" class="dropbtn">System</a>
                        <div class="dropdown-content">
                            <a href="Admin-Settings-Page.html" class="active"><i class="fas fa-cog"></i> Settings</a>
                            <a href="Admin-Audit-Logs-Page.html"><i class="fas fa-clipboard-list"></i> Audit Logs</a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="user-menu" id="userMenu">
                    <div class="dropdown">
                        <button class="dropbtn">
                            <i class="fas fa-user-circle"></i>
                            <span>Admin User</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="Admin-Profile-Page.html"><i class="fas fa-user"></i> Profile</a>
                            <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
                
                <!-- Hamburger Menu -->
                <button class="hamburger" id="hamburgerBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>

            <!-- Mobile Menu -->
            <div class="mobile-menu" id="mobileMenu">
                <div class="mobile-menu-content"></div>
            </div>
        </header>
        
        <main class="admin-settings-container">
            <div class="admin-header">
                <h1 class="admin-title">System Settings</h1>
                <div class="admin-actions">
                    <button class="save-settings-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
            
            <div class="settings-tabs">
                <button class="tab-btn active" data-tab="general">General Settings</button>
                <button class="tab-btn" data-tab="security">Security</button>
                <button class="tab-btn" data-tab="notifications">Notifications</button>
                <button class="tab-btn" data-tab="appearance">Appearance</button>
            </div>
            
            <div class="settings-content">
                <!-- General Settings Tab -->
                <div class="tab-pane active" id="general">
                    <form id="generalSettingsForm">
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> System Information</h3>
                            <div class="form-group">
                                <label for="systemName">System Name</label>
                                <input type="text" id="systemName" name="systemName" value="MedicalChecks" placeholder="Enter system name">
                            </div>
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select id="timezone" name="timezone">
                                    <option value="UTC" selected>UTC (Coordinated Universal Time)</option>
                                    <option value="EST">Eastern Standard Time (EST)</option>
                                    <option value="PST">Pacific Standard Time (PST)</option>
                                    <option value="CET">Central European Time (CET)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-calendar-alt"></i> Date & Time Format</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="dateFormat">Date Format</label>
                                    <select id="dateFormat" name="dateFormat">
                                        <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                        <option value="DD/MM/YYYY" selected>DD/MM/YYYY</option>
                                        <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="timeFormat">Time Format</label>
                                    <select id="timeFormat" name="timeFormat">
                                        <option value="12h" selected>12-hour format</option>
                                        <option value="24h">24-hour format</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-language"></i> Language & Region</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="language">Language</label>
                                    <select id="language" name="language">
                                        <option value="en" selected>English</option>
                                        <option value="es">Spanish</option>
                                        <option value="fr">French</option>
                                        <option value="de">German</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="region">Region</label>
                                    <select id="region" name="region">
                                        <option value="US" selected>United States</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="EU">European Union</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Security Tab -->
                <div class="tab-pane" id="security">
                    <form id="securitySettingsForm">
                        <div class="form-section">
                            <h3><i class="fas fa-shield-alt"></i> Authentication</h3>
                            <div class="form-group toggle-group">
                                <label for="twoFactorAuth">Two-Factor Authentication</label>
                                <label class="switch">
                                    <input type="checkbox" id="twoFactorAuth" name="twoFactorAuth">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group toggle-group">
                                <label for="passwordComplexity">Strong Password Requirements</label>
                                <label class="switch">
                                    <input type="checkbox" id="passwordComplexity" name="passwordComplexity" checked>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="sessionTimeout">Session Timeout (minutes)</label>
                                <input type="number" id="sessionTimeout" name="sessionTimeout" min="5" max="1440" value="30">
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-lock"></i> Password Policy</h3>
                            <div class="form-group">
                                <label for="minPasswordLength">Minimum Password Length</label>
                                <input type="number" id="minPasswordLength" name="minPasswordLength" min="6" max="32" value="8">
                            </div>
                            <div class="form-group">
                                <label for="passwordHistory">Password History (remember last)</label>
                                <input type="number" id="passwordHistory" name="passwordHistory" min="0" max="24" value="3">
                                <span class="hint">previous passwords</span>
                            </div>
                            <div class="form-group">
                                <label for="passwordExpiry">Password Expiry (days)</label>
                                <input type="number" id="passwordExpiry" name="passwordExpiry" min="0" max="365" value="90">
                                <span class="hint">0 means never expire</span>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Notifications Tab -->
                <div class="tab-pane" id="notifications">
                    <form id="notificationSettingsForm">
                        <div class="form-section">
                            <h3><i class="fas fa-bell"></i> Notification Preferences</h3>
                            <div class="form-group toggle-group">
                                <label for="emailNotifications">Email Notifications</label>
                                <label class="switch">
                                    <input type="checkbox" id="emailNotifications" name="emailNotifications" checked>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group toggle-group">
                                <label for="pushNotifications">Push Notifications</label>
                                <label class="switch">
                                    <input type="checkbox" id="pushNotifications" name="pushNotifications">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="notificationEmail">Notification Email Address</label>
                                <input type="email" id="notificationEmail" name="notificationEmail" value="admin@medicalchecks.example.com">
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-envelope"></i> Email Alerts</h3>
                            <div class="form-group toggle-group">
                                <label for="newUserAlerts">New User Registrations</label>
                                <label class="switch">
                                    <input type="checkbox" id="newUserAlerts" name="newUserAlerts" checked>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group toggle-group">
                                <label for="systemAlerts">System Alerts</label>
                                <label class="switch">
                                    <input type="checkbox" id="systemAlerts" name="systemAlerts" checked>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group toggle-group">
                                <label for="securityAlerts">Security Alerts</label>
                                <label class="switch">
                                    <input type="checkbox" id="securityAlerts" name="securityAlerts" checked>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Appearance Tab -->
                <div class="tab-pane" id="appearance">
                    <form id="appearanceSettingsForm">
                        <div class="form-section">
                            <h3><i class="fas fa-palette"></i> Theme</h3>
                            <div class="theme-options">
                                <div class="theme-option">
                                    <input type="radio" id="themeLight" name="theme" value="light" checked>
                                    <label for="themeLight" class="theme-card">
                                        <div class="theme-preview light-theme"></div>
                                        <span>Light Theme</span>
                                    </label>
                                </div>
                                <div class="theme-option">
                                    <input type="radio" id="themeDark" name="theme" value="dark">
                                    <label for="themeDark" class="theme-card">
                                        <div class="theme-preview dark-theme"></div>
                                        <span>Dark Theme</span>
                                    </label>
                                </div>
                                <div class="theme-option">
                                    <input type="radio" id="themeSystem" name="theme" value="system">
                                    <label for="themeSystem" class="theme-card">
                                        <div class="theme-preview system-theme"></div>
                                        <span>System Default</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-sliders-h"></i> Customization</h3>
                            <div class="form-group">
                                <label for="primaryColor">Primary Color</label>
                                <div class="color-picker">
                                    <input type="color" id="primaryColor" name="primaryColor" value="#00c698">
                                    <span>#00c698</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="secondaryColor">Secondary Color</label>
                                <div class="color-picker">
                                    <input type="color" id="secondaryColor" name="secondaryColor" value="#095461">
                                    <span>#095461</span>
                                </div>
                            </div>
                            <div class="form-group toggle-group">
                                <label for="compactMode">Compact Mode</label>
                                <label class="switch">
                                    <input type="checkbox" id="compactMode" name="compactMode">
                                    <span class="slider round"></span>
                                </label>
                                <span class="hint">Reduces padding for more content on screen</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <footer>
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MedicalChecks</span>
                </div>
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Services</h4>
                        <a href="#">Preventive Care</a>
                        <a href="#">Diagnostic Tests</a>
                        <a href="#">Wellness Programs</a>
                    </div>
                    <div class="footer-column">
                        <h4>Company</h4>
                        <a href="#">About Us</a>
                        <a href="#">Careers</a>
                        <a href="#">News</a>
                    </div>
                    <div class="footer-column">
                        <h4>Support</h4>
                        <a href="Contact-Us-Page.html">Contact</a>
                        <a href="#">FAQs</a>
                        <a href="#">Privacy Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 MedicalChecks. All rights reserved.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </footer>
        
        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Admin-Settings.js"></script>
    </body>
</html>