<php?




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Account Management | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Admin-Account-Management-Page.css">
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
                            <a href="Admin-Account-Management-Page.html" class="active"><i class="fas fa-users-cog"></i> Account Management</a>
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
        
        <main class="admin-container">
            <div class="admin-header">
                <h1 class="admin-title">Account Management</h1>
                <div class="admin-actions">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search accounts...">
                    </div>
                    <button class="add-user-btn">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1001</td>
                            <td>John Smith</td>
                            <td>john.smith@example.com</td>
                            <td>Doctor</td>
                            <td><span class="status status-active">Active</span></td>
                            <td>Today, 09:45</td>
                            <td class="action-buttons">
                                <button class="edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1002</td>
                            <td>Sarah Johnson</td>
                            <td>sarah.j@example.com</td>
                            <td>Nurse</td>
                            <td><span class="status status-active">Active</span></td>
                            <td>Yesterday, 14:30</td>
                            <td class="action-buttons">
                                <button class="edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1003</td>
                            <td>Michael Brown</td>
                            <td>m.brown@example.com</td>
                            <td>Patient</td>
                            <td><span class="status status-inactive">Inactive</span></td>
                            <td>3 days ago</td>
                            <td class="action-buttons">
                                <button class="edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1004</td>
                            <td>Emily Davis</td>
                            <td>emily.d@example.com</td>
                            <td>Lab Technician</td>
                            <td><span class="status status-active">Active</span></td>
                            <td>Today, 11:20</td>
                            <td class="action-buttons">
                                <button class="edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>1005</td>
                            <td>Robert Wilson</td>
                            <td>robert.w@example.com</td>
                            <td>Admin</td>
                            <td><span class="status status-active">Active</span></td>
                            <td>Currently online</td>
                            <td class="action-buttons">
                                <button class="edit-btn"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn"><i class="fas fa-trash-alt"></i></button>
                            </td>
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
        <script src="Scripts/pages/Admin-Account-Management.js"></script>
    </body>
</html>