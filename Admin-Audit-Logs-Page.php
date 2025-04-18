<php?




?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Audit Logs | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Admin-Audit-Logs-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datepicker/1.0.10/datepicker.min.css">
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
                            <a href="Admin-Profile-Page.html"><i class="fas fa-user"></i> Profile</a>
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
        
        <main class="audit-container">
            <div class="audit-header">
                <h1 class="audit-title">
                    <i class="fas fa-clipboard-list"></i> Audit Logs
                    <span class="badge" id="totalLogsCount">125</span>
                </h1>
                <div class="audit-actions">
                    <div class="filter-controls">
                        <div class="filter-group">
                            <label for="dateRange"><i class="fas fa-calendar-alt"></i> Date Range</label>
                            <div class="date-range-picker">
                                <input type="text" id="startDate" class="date-input" placeholder="Start Date">
                                <span>to</span>
                                <input type="text" id="endDate" class="date-input" placeholder="End Date">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label for="actionType"><i class="fas fa-filter"></i> Action Type</label>
                            <select id="actionType" class="filter-select">
                                <option value="all">All Actions</option>
                                <option value="login">Login Attempts</option>
                                <option value="create">Record Creation</option>
                                <option value="update">Record Updates</option>
                                <option value="delete">Record Deletions</option>
                                <option value="system">System Changes</option>
                            </select>
                        </div>
                        
                        <button class="apply-filters-btn">
                            <i class="fas fa-check"></i> Apply
                        </button>
                        <button class="reset-filters-btn">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                    
                    <div class="search-export">
                        <div class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Search logs..." id="logSearch">
                        </div>
                        <button class="export-btn">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="audit-stats">
                <div class="stat-card">
                    <div class="stat-value" id="todayLogs">24</div>
                    <div class="stat-label">Today</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 12%
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="criticalLogs">8</div>
                    <div class="stat-label">Critical</div>
                    <div class="stat-trend down">
                        <i class="fas fa-arrow-down"></i> 5%
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="userActions">42</div>
                    <div class="stat-label">User Actions</div>
                    <div class="stat-trend up">
                        <i class="fas fa-arrow-up"></i> 23%
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="systemEvents">51</div>
                    <div class="stat-label">System Events</div>
                    <div class="stat-trend">
                        <i class="fas fa-equals"></i> 0%
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th data-sort="timestamp">Timestamp <i class="fas fa-sort"></i></th>
                            <th data-sort="user">User <i class="fas fa-sort"></i></th>
                            <th data-sort="action">Action <i class="fas fa-sort"></i></th>
                            <th data-sort="entity">Entity <i class="fas fa-sort"></i></th>
                            <th data-sort="details">Details <i class="fas fa-sort"></i></th>
                            <th data-sort="status">Status <i class="fas fa-sort"></i></th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody id="auditLogsBody">
                        <!-- Logs will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <span id="showingFrom">1</span>-<span id="showingTo">10</span> of <span id="totalLogs">125</span> logs
                </div>
                <div class="pagination">
                    <button class="pagination-btn" id="prevPage" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="page-numbers" id="pageNumbers">
                        <!-- Page numbers will be inserted here by JavaScript -->
                    </div>
                    <button class="pagination-btn" id="nextPage">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="page-size-selector">
                    <label for="pageSize">Logs per page:</label>
                    <select id="pageSize" class="page-size-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            
            <!-- Log Details Modal -->
            <div class="modal" id="logDetailsModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Audit Log Details</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body" id="logDetailsContent">
                        <!-- Details will be populated by JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary close-modal">Close</button>
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
                        <a href="../Contact-Us-Page.html">Contact</a>
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
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/datepicker/1.0.10/datepicker.min.js"></script>
        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Admin-Audit-Logs.js"></script>
    </body>
</html>