<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: Admin-Login-Page.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Admin-Dashboard-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="#" class="dropbtn active">Home</a>
                    <div class="dropdown-content">
                        <a href="Admin-Dashboard-Page.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </div>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="dropbtn">Admin Portal</a>
                    <div class="dropdown-content">
                        <a href="Admin-Account-Management-Page.php"><i class="fas fa-users-cog"></i> Account Management</a>
                        <a href="Admin-Reports-Page.php"><i class="fas fa-chart-bar"></i> Reports</a>
                        <a href="Admin-Inventory-Management-Page.php"><i class="fas fa-boxes"></i> Inventory</a>
                    </div>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="dropbtn">System</a>
                    <div class="dropdown-content">
                        <a href="Admin-Settings-Page.php"><i class="fas fa-cog"></i> Settings</a>
                        <a href="Admin-Audit-Logs-Page.php"><i class="fas fa-clipboard-list"></i> Audit Logs</a>
                    </div>
                </div>
            </div>

            <div class="user-menu" id="userMenu">
                <div class="dropdown">
                    <button class="dropbtn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['admin_email']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="Admin-Profile-Page.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
        
        <main class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                <div class="dashboard-controls">
                    <div class="date-range">
                        <i class="fas fa-calendar-alt"></i>
                        <select id="dashboardRange">
                            <option value="today">Today</option>
                            <option value="week" selected>This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                        </select>
                    </div>
                    <button class="refresh-btn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="dashboard-summary">
                <div class="summary-card">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-content">
                        <h3>Active Users</h3>
                        <span class="card-value">1,248</span>
                        <span class="card-change positive">
                            <i class="fas fa-arrow-up"></i> 12% from last week
                        </span>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon bg-success">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <div class="card-content">
                        <h3>Tests Conducted</h3>
                        <span class="card-value">3,782</span>
                        <span class="card-change positive">
                            <i class="fas fa-arrow-up"></i> 8% from last week
                        </span>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="card-content">
                        <h3>Low Stock Items</h3>
                        <span class="card-value">24</span>
                        <span class="card-change negative">
                            <i class="fas fa-arrow-up"></i> 5 more than last week
                        </span>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-content">
                        <h3>System Alerts</h3>
                        <span class="card-value">8</span>
                        <span class="card-change positive">
                            <i class="fas fa-arrow-down"></i> 22% from last week
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-charts">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>User Activity Trend</h3>
                        <div class="chart-actions">
                            <button class="chart-action-btn" data-chart="userActivity" data-type="daily">Daily</button>
                            <button class="chart-action-btn active" data-chart="userActivity" data-type="weekly">Weekly</button>
                            <button class="chart-action-btn" data-chart="userActivity" data-type="monthly">Monthly</button>
                        </div>
                    </div>
                    <canvas id="userActivityChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>System Health Status</h3>
                        <div class="chart-actions">
                            <button class="chart-action-btn active" data-chart="systemHealth" data-type="status">Status</button>
                            <button class="chart-action-btn" data-chart="systemHealth" data-type="performance">Performance</button>
                        </div>
                    </div>
                    <canvas id="systemHealthChart"></canvas>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="recent-activity">
                    <div class="section-header">
                        <h3><i class="fas fa-history"></i> Recent Activity</h3>
                        <a href="Admin-Audit-Logs-Page.html" class="view-all">View All</a>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus text-success"></i>
                            </div>
                            <div class="activity-content">
                                <p>New user <strong>Dr. Sarah Johnson</strong> registered</p>
                                <span class="activity-time">10 minutes ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-box text-warning"></i>
                            </div>
                            <div class="activity-content">
                                <p>Inventory item <strong>Paracetamol 500mg</strong> is low in stock</p>
                                <span class="activity-time">25 minutes ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-chart-line text-primary"></i>
                            </div>
                            <div class="activity-content">
                                <p>Weekly system report generated</p>
                                <span class="activity-time">1 hour ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-cog text-info"></i>
                            </div>
                            <div class="activity-content">
                                <p>System settings updated by <strong>Admin User</strong></p>
                                <span class="activity-time">2 hours ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-exclamation-triangle text-danger"></i>
                            </div>
                            <div class="activity-content">
                                <p>Security alert: Multiple failed login attempts</p>
                                <span class="activity-time">3 hours ago</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="quick-stats">
                    <div class="section-header">
                        <h3><i class="fas fa-chart-pie"></i> User Distribution</h3>
                    </div>
                    <canvas id="userDistributionChart"></canvas>
                    <div class="stats-legend">
                        <div class="legend-item">
                            <span class="legend-color bg-primary"></span>
                            <span>Patients (62%)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color bg-success"></span>
                            <span>Doctors (18%)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color bg-warning"></span>
                            <span>Nurses (12%)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color bg-info"></span>
                            <span>Admins (5%)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color bg-secondary"></span>
                            <span>Others (3%)</span>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <div class="section-header">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="action-buttons">
                        <a href="Admin-Account-Management-Page.html" class="action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Add User</span>
                        </a>
                        <a href="Admin-Inventory-Management-Page.html" class="action-btn">
                            <i class="fas fa-box-open"></i>
                            <span>Manage Inventory</span>
                        </a>
                        <a href="Admin-Reports-Page.html" class="action-btn">
                            <i class="fas fa-file-export"></i>
                            <span>Generate Report</span>
                        </a>
                        <a href="Admin-Settings-Page.html" class="action-btn">
                            <i class="fas fa-cog"></i>
                            <span>System Settings</span>
                        </a>
                        <a href="Admin-Audit-Logs-Page.html" class="action-btn">
                            <i class="fas fa-clipboard-list"></i>
                            <span>View Audit Logs</span>
                        </a>
                        <a href="#" class="action-btn" id="systemBackupBtn">
                            <i class="fas fa-database"></i>
                            <span>Backup System</span>
                        </a>
                    </div>
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
        <script src="Scripts/pages/Admin-Dashboard.js"></script>
    </body>
</html>