<php?




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Inventory Management | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Admin-Inventory-Management-Page.css">
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
                            <a href="Admin-Inventory-Page.html" class="active"><i class="fas fa-boxes"></i> Inventory Management</a>
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
                <div class="admin-title-container">
                    <h1 class="admin-title">
                        <i class="fas fa-boxes"></i> Inventory Management
                    </h1>
                    <p class="admin-subtitle">Manage medical supplies and equipment</p>
                </div>
                <div class="admin-actions">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search inventory..." id="inventorySearch">
                    </div>
                    <button class="add-stock-btn" id="addStockBtn">
                        <i class="fas fa-plus"></i> Add Stock
                    </button>
                </div>
            </div>
            
            <!-- Inventory Summary Cards -->
            <div class="inventory-summary">
                <div class="summary-card">
                    <div class="summary-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Total Items</h3>
                        <span class="summary-value">1,248</span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon critical">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Low Stock</h3>
                        <span class="summary-value">24</span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Expiring Soon</h3>
                        <span class="summary-value">18</span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Categories</h3>
                        <span class="summary-value">12</span>
                    </div>
                </div>
            </div>
            
            <div class="inventory-controls">
                <div class="inventory-tabs">
                    <button class="tab-btn active" data-tab="current">Current Stock</button>
                    <button class="tab-btn" data-tab="new">New Arrivals</button>
                    <button class="tab-btn" data-tab="old">Expiring Soon</button>
                    <button class="tab-btn" data-tab="low">Low Stock</button>
                </div>
                
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="categoryFilter"><i class="fas fa-filter"></i> Category</label>
                        <select id="categoryFilter">
                            <option value="">All Categories</option>
                            <option>First Aid</option>
                            <option>Medication</option>
                            <option>Diagnostic</option>
                            <option>Equipment</option>
                            <option>Supplies</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="statusFilter"><i class="fas fa-info-circle"></i> Status</label>
                        <select id="statusFilter">
                            <option value="">All Statuses</option>
                            <option>Adequate</option>
                            <option>Low</option>
                            <option>Critical</option>
                        </select>
                    </div>
                    <button class="reset-filters-btn">
                        <i class="fas fa-sync-alt"></i> Reset
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th data-sort="id">ID <i class="fas fa-sort"></i></th>
                            <th data-sort="name">Item Name <i class="fas fa-sort"></i></th>
                            <th data-sort="category">Category <i class="fas fa-sort"></i></th>
                            <th data-sort="quantity">Quantity <i class="fas fa-sort"></i></th>
                            <th>Threshold</th>
                            <th data-sort="expiry">Expiry Date <i class="fas fa-sort"></i></th>
                            <th data-sort="status">Status <i class="fas fa-sort"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>INV1001</td>
                            <td>Bandages (Pack of 50)</td>
                            <td>First Aid</td>
                            <td><span class="quantity-value">120</span></td>
                            <td>30</td>
                            <td>2025-12-31</td>
                            <td><span class="status status-adequate"><i class="fas fa-check-circle"></i> Adequate</span></td>
                            <td class="action-buttons">
                                <button class="edit-btn" title="Edit item"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn" title="Delete item"><i class="fas fa-trash-alt"></i></button>
                                <button class="restock-btn" title="Request restock"><i class="fas fa-truck"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>INV1002</td>
                            <td>Paracetamol 500mg (Bottle of 100)</td>
                            <td>Medication</td>
                            <td><span class="quantity-value">15</span></td>
                            <td>20</td>
                            <td>2024-11-15</td>
                            <td><span class="status status-critical"><i class="fas fa-exclamation-circle"></i> Critical</span></td>
                            <td class="action-buttons">
                                <button class="edit-btn" title="Edit item"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn" title="Delete item"><i class="fas fa-trash-alt"></i></button>
                                <button class="restock-btn" title="Request restock"><i class="fas fa-truck"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>INV1003</td>
                            <td>Blood Pressure Monitor</td>
                            <td>Equipment</td>
                            <td><span class="quantity-value">8</span></td>
                            <td>5</td>
                            <td>N/A</td>
                            <td><span class="status status-adequate"><i class="fas fa-check-circle"></i> Adequate</span></td>
                            <td class="action-buttons">
                                <button class="edit-btn" title="Edit item"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn" title="Delete item"><i class="fas fa-trash-alt"></i></button>
                                <button class="restock-btn" title="Request restock"><i class="fas fa-truck"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>INV1004</td>
                            <td>Disposable Gloves (Box of 100)</td>
                            <td>Supplies</td>
                            <td><span class="quantity-value">25</span></td>
                            <td>30</td>
                            <td>2024-09-30</td>
                            <td><span class="status status-low"><i class="fas fa-exclamation-triangle"></i> Low</span></td>
                            <td class="action-buttons">
                                <button class="edit-btn" title="Edit item"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn" title="Delete item"><i class="fas fa-trash-alt"></i></button>
                                <button class="restock-btn" title="Request restock"><i class="fas fa-truck"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>INV1005</td>
                            <td>Alcohol Swabs (Pack of 200)</td>
                            <td>First Aid</td>
                            <td><span class="quantity-value">180</span></td>
                            <td>50</td>
                            <td>2026-03-15</td>
                            <td><span class="status status-adequate"><i class="fas fa-check-circle"></i> Adequate</span></td>
                            <td class="action-buttons">
                                <button class="edit-btn" title="Edit item"><i class="fas fa-edit"></i></button>
                                <button class="delete-btn" title="Delete item"><i class="fas fa-trash-alt"></i></button>
                                <button class="restock-btn" title="Request restock"><i class="fas fa-truck"></i></button>
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
                <span class="pagination-ellipsis">...</span>
                <button class="pagination-btn">8</button>
                <button class="pagination-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </main>

        <!-- Inventory Item Modal -->
        <div class="modal" id="inventoryModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalTitle">Add New Inventory Item</h3>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="inventoryForm">
                        <div class="form-group">
                            <label for="itemName">Item Name</label>
                            <input type="text" id="itemName" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="itemCategory">Category</label>
                                <select id="itemCategory" required>
                                    <option value="">Select Category</option>
                                    <option>First Aid</option>
                                    <option>Medication</option>
                                    <option>Diagnostic</option>
                                    <option>Equipment</option>
                                    <option>Supplies</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="itemId">Item ID</label>
                                <input type="text" id="itemId" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="itemQuantity">Quantity</label>
                                <input type="number" id="itemQuantity" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="itemThreshold">Threshold</label>
                                <input type="number" id="itemThreshold" min="1" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="itemExpiry">Expiry Date</label>
                            <input type="date" id="itemExpiry">
                        </div>
                        <div class="form-group">
                            <label for="itemNotes">Notes</label>
                            <textarea id="itemNotes" rows="3"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="cancel-btn">Cancel</button>
                            <button type="submit" class="save-btn">Save Item</button>
                        </div>
                    </form>
                </div>
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
        <script src="Scripts/pages/Admin-Inventory-Management.js"></script>
    </body>
</html>