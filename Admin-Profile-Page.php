<php?




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Admin-Profile-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
    <body>
        <header>
            <nav class="navbar">
                <div class="nav-logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>MedicalChecks</span>
                </div>
                
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
                            <a href="Admin-Settings-Page.html"><i class="fas fa-cog"></i> Settings</a>
                            <a href="Admin-Audit-Logs-Page.html"><i class="fas fa-clipboard-list"></i> Audit Logs</a>
                        </div>
                    </div>
                </div>

                <div class="user-menu" id="userMenu">
                    <div class="dropdown">
                        <button class="dropbtn">
                            <i class="fas fa-user-circle"></i>
                            <span>Admin User</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="Admin-Profile-Page.html" class="active"><i class="fas fa-user"></i> Profile</a>
                            <a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
                
                <button class="hamburger" id="hamburgerBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>

            <div class="mobile-menu" id="mobileMenu">
                <div class="mobile-menu-content"></div>
            </div>
        </header>
        
        <main class="profile-container">
            <div class="profile-header">
                <h1 class="profile-title">Admin Profile</h1>
                <div class="profile-status">
                    <span class="status-badge active">Active</span>
                    <span class="last-login">Last login: Today at 09:45 AM</span>
                </div>
            </div>
            
            <div class="profile-grid">
                <div class="profile-card profile-overview">
                    <div class="card-header">
                        <h2>Profile Overview</h2>
                        <button class="edit-btn" id="editProfileBtn"><i class="fas fa-edit"></i> Edit</button>
                    </div>
                    <div class="profile-avatar">
                        <div class="avatar-container">
                            <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Admin Avatar" class="avatar">
                            <button class="avatar-edit-btn"><i class="fas fa-camera"></i></button>
                        </div>
                        <div class="avatar-info">
                            <h3>Dr. Sarah Johnson</h3>
                            <p>Administrator</p>
                            <p class="member-since">Member since: June 2023</p>
                        </div>
                    </div>
                    <div class="profile-details">
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span>sarah.johnson@medicalchecks.com</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span>+1 (555) 123-4567</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Medical Center Dr, Boston, MA</span>
                        </div>
                    </div>
                </div>
                
                <div class="profile-card profile-security">
                    <div class="card-header">
                        <h2>Security Settings</h2>
                    </div>
                    <div class="security-item">
                        <div class="security-info">
                            <i class="fas fa-lock"></i>
                            <div>
                                <h3>Password</h3>
                                <p>Last changed 3 months ago</p>
                            </div>
                        </div>
                        <button class="change-btn" id="changePasswordBtn">Change</button>
                    </div>
                    <div class="security-item">
                        <div class="security-info">
                            <i class="fas fa-mobile-alt"></i>
                            <div>
                                <h3>Two-Factor Authentication</h3>
                                <p>Currently enabled</p>
                            </div>
                        </div>
                        <button class="toggle-btn active" id="toggle2faBtn">On</button>
                    </div>
                    <div class="security-item">
                        <div class="security-info">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h3>Login Alerts</h3>
                                <p>Notify me of new logins</p>
                            </div>
                        </div>
                        <button class="toggle-btn active" id="toggleAlertsBtn">On</button>
                    </div>
                </div>
                
                <div class="profile-card profile-activity">
                    <div class="card-header">
                        <h2>Recent Activity</h2>
                        <button class="view-all-btn">View All</button>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <i class="fas fa-user-edit activity-icon"></i>
                            <div class="activity-content">
                                <p>Updated user account #1002</p>
                                <span class="activity-time">Today, 10:30 AM</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-file-export activity-icon"></i>
                            <div class="activity-content">
                                <p>Generated monthly report</p>
                                <span class="activity-time">Yesterday, 4:15 PM</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-sign-in-alt activity-icon"></i>
                            <div class="activity-content">
                                <p>Logged in from new device</p>
                                <span class="activity-time">Yesterday, 9:00 AM</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-cog activity-icon"></i>
                            <div class="activity-content">
                                <p>Changed system settings</p>
                                <span class="activity-time">2 days ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="profile-card profile-notifications">
                    <div class="card-header">
                        <h2>Notification Preferences</h2>
                    </div>
                    <div class="notification-item">
                        <div class="notification-info">
                            <h3>Email Notifications</h3>
                            <p>Receive important updates via email</p>
                        </div>
                        <button class="toggle-btn active" id="toggleEmailBtn">On</button>
                    </div>
                    <div class="notification-item">
                        <div class="notification-info">
                            <h3>System Alerts</h3>
                            <p>Critical system notifications</p>
                        </div>
                        <button class="toggle-btn active" id="toggleSystemBtn">On</button>
                    </div>
                    <div class="notification-item">
                        <div class="notification-info">
                            <h3>Promotional Offers</h3>
                            <p>Updates about new features</p>
                        </div>
                        <button class="toggle-btn" id="togglePromoBtn">Off</button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Edit Profile Modal -->
        <div class="modal" id="editProfileModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Profile</h2>
                    <button class="close-btn" id="closeEditModal">&times;</button>
                </div>
                <form id="profileForm">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" value="Dr. Sarah Johnson">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" value="sarah.johnson@medicalchecks.com">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" value="+1 (555) 123-4567">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address">123 Medical Center Dr, Boston, MA</textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" id="cancelEdit">Cancel</button>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div class="modal" id="changePasswordModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Change Password</h2>
                    <button class="close-btn" id="closePasswordModal">&times;</button>
                </div>
                <form id="passwordForm">
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" id="currentPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" required>
                        <div class="password-strength">
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                            <span class="strength-bar"></span>
                            <span class="strength-text">Password strength</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" required>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" id="cancelPassword">Cancel</button>
                        <button type="submit" class="save-btn">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

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
        <script src="Scripts/pages/Admin-Profile.ts"></script>
    </body>
</html>