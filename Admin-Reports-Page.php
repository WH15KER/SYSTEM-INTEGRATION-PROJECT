<php?




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reports | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Admin-Reports-Page.css">
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
                            <a href="Admin-Reports-Page.html" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
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
        
        <main class="admin-reports-container">
            <div class="admin-header">
                <h1 class="admin-title">System Reports</h1>
                <div class="report-controls">
                    <div class="date-range-picker">
                        <i class="fas fa-calendar-alt"></i>
                        <select id="reportPeriod">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="custom">Custom Range</option>
                        </select>
                        <div class="custom-range" id="customRange" style="display: none;">
                            <input type="date" id="startDate">
                            <span>to</span>
                            <input type="date" id="endDate">
                            <button id="applyDateRange">Apply</button>
                        </div>
                    </div>
                    <button class="export-btn">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                </div>
            </div>
            
            <div class="report-filters">
                <div class="filter-group">
                    <label for="reportType"><i class="fas fa-chart-pie"></i> Report Type</label>
                    <select id="reportType">
                        <option value="summary">Summary Overview</option>
                        <option value="userActivity">User Activity</option>
                        <option value="systemUsage">System Usage</option>
                        <option value="inventory">Inventory Reports</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="userRole"><i class="fas fa-user-tag"></i> User Role</label>
                    <select id="userRole">
                        <option value="all">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="doctor">Doctor</option>
                        <option value="nurse">Nurse</option>
                        <option value="patient">Patient</option>
                    </select>
                </div>
                
                <button class="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            
            <div class="report-summary-cards">
                <div class="summary-card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-content">
                        <h3>Active Users</h3>
                        <span class="card-value" id="activeUsers">1,245</span>
                        <span class="card-change positive"><i class="fas fa-arrow-up"></i> 12% from last month</span>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <div class="card-content">
                        <h3>Tests Conducted</h3>
                        <span class="card-value" id="testsConducted">3,782</span>
                        <span class="card-change positive"><i class="fas fa-arrow-up"></i> 8% from last month</span>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-content">
                        <h3>Avg. Session</h3>
                        <span class="card-value" id="avgSession">24.5 min</span>
                        <span class="card-change negative"><i class="fas fa-arrow-down"></i> 5% from last month</span>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="card-content">
                        <h3>System Alerts</h3>
                        <span class="card-value" id="systemAlerts">18</span>
                        <span class="card-change positive"><i class="fas fa-arrow-down"></i> 22% from last month</span>
                    </div>
                </div>
            </div>
            
            <div class="report-charts">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>User Activity Trend</h3>
                        <div class="chart-actions">
                            <button class="chart-action-btn" data-chart="userActivity" data-type="daily"><i class="fas fa-calendar-day"></i> Daily</button>
                            <button class="chart-action-btn active" data-chart="userActivity" data-type="weekly"><i class="fas fa-calendar-week"></i> Weekly</button>
                            <button class="chart-action-btn" data-chart="userActivity" data-type="monthly"><i class="fas fa-calendar-alt"></i> Monthly</button>
                        </div>
                    </div>
                    <canvas id="userActivityChart"></canvas>
                </div>
                
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>User Role Distribution</h3>
                        <div class="chart-actions">
                            <button class="chart-action-btn active" data-chart="roleDistribution" data-type="count"><i class="fas fa-users"></i> Count</button>
                            <button class="chart-action-btn" data-chart="roleDistribution" data-type="activity"><i class="fas fa-chart-line"></i> Activity</button>
                        </div>
                    </div>
                    <canvas id="roleDistributionChart"></canvas>
                </div>
                
                <div class="chart-container full-width">
                    <div class="chart-header">
                        <h3>System Usage Metrics</h3>
                        <div class="chart-actions">
                            <button class="chart-action-btn active" data-chart="systemUsage" data-type="logins"><i class="fas fa-sign-in-alt"></i> Logins</button>
                            <button class="chart-action-btn" data-chart="systemUsage" data-type="features"><i class="fas fa-cogs"></i> Features</button>
                            <button class="chart-action-btn" data-chart="systemUsage" data-type="errors"><i class="fas fa-bug"></i> Errors</button>
                        </div>
                    </div>
                    <canvas id="systemUsageChart"></canvas>
                </div>
            </div>
            
            <div class="report-data-table">
                <div class="table-header">
                    <h3>Detailed Activity Log</h3>
                    <div class="table-actions">
                        <div class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Search logs...">
                        </div>
                        <button class="filter-btn">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="activity-log-table">
                        <thead>
                            <tr>
                                <th>Timestamp <i class="fas fa-sort"></i></th>
                                <th>User <i class="fas fa-sort"></i></th>
                                <th>Role <i class="fas fa-sort"></i></th>
                                <th>Activity <i class="fas fa-sort"></i></th>
                                <th>Details <i class="fas fa-sort"></i></th>
                                <th>Status <i class="fas fa-sort"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2025-04-09 09:45:23</td>
                                <td>John Smith</td>
                                <td>Doctor</td>
                                <td>Patient Record Update</td>
                                <td>Updated patient #10045 vitals</td>
                                <td><span class="status status-success">Success</span></td>
                            </tr>
                            <tr>
                                <td>2025-04-09 09:32:10</td>
                                <td>Sarah Johnson</td>
                                <td>Nurse</td>
                                <td>Test Results Upload</td>
                                <td>Uploaded blood test results</td>
                                <td><span class="status status-success">Success</span></td>
                            </tr>
                            <tr>
                                <td>2025-04-09 09:15:47</td>
                                <td>Michael Brown</td>
                                <td>Patient</td>
                                <td>Appointment Booking</td>
                                <td>Booked annual checkup</td>
                                <td><span class="status status-success">Success</span></td>
                            </tr>
                            <tr>
                                <td>2025-04-09 08:59:12</td>
                                <td>Emily Davis</td>
                                <td>Lab Technician</td>
                                <td>Test Order</td>
                                <td>Ordered CBC test</td>
                                <td><span class="status status-success">Success</span></td>
                            </tr>
                            <tr>
                                <td>2025-04-09 08:45:30</td>
                                <td>Robert Wilson</td>
                                <td>Admin</td>
                                <td>System Configuration</td>
                                <td>Updated user permissions</td>
                                <td><span class="status status-success">Success</span></td>
                            </tr>
                            <tr>
                                <td>2025-04-08 16:20:15</td>
                                <td>System</td>
                                <td>System</td>
                                <td>Backup Process</td>
                                <td>Nightly database backup</td>
                                <td><span class="status status-warning">Warning</span></td>
                            </tr>
                            <tr>
                                <td>2025-04-08 14:05:33</td>
                                <td>Lisa Wong</td>
                                <td>Doctor</td>
                                <td>Prescription</td>
                                <td>Prescribed medication</td>
                                <td><span class="status status-success">Success</span></td>
                            </tr>
                            <tr>
                                <td>2025-04-08 11:45:22</td>
                                <td>System</td>
                                <td>System</td>
                                <td>Security Scan</td>
                                <td>Completed vulnerability scan</td>
                                <td><span class="status status-error">Error</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="pagination">
                    <button class="pagination-btn" disabled><i class="fas fa-chevron-left"></i></button>
                    <button class="pagination-btn active">1</button>
                    <button class="pagination-btn">2</button>
                    <button class="pagination-btn">3</button>
                    <button class="pagination-btn"><i class="fas fa-chevron-right"></i></button>
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
        <script src="Scripts/pages/Admin-Reports.js"></script>
    </body>
</html>