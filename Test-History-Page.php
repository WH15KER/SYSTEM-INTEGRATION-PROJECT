<?php
session_start();
require_once('connection.php');
require_once('function.php');

// Check if user is logged in
$user_data = check_login($con);

// Get user's test history from database
$test_history = [];
$summary_counts = [
    'total' => 0,
    'completed' => 0,
    'pending' => 0
];

// Query to get test history
$query = "SELECT 
            ot.ordered_test_id,
            tt.name AS test_name,
            tt.category,
            `to`.order_date,
            tr.result_date,
            `to`.physician_name,
            ot.status,
            tr.result_value,
            tr.reference_range,
            tr.interpretation
          FROM ordered_tests ot
          JOIN test_orders `to` ON ot.order_id = `to`.order_id
          JOIN test_types tt ON ot.test_type_id = tt.test_type_id
          LEFT JOIN test_results tr ON ot.ordered_test_id = tr.ordered_test_id
          WHERE `to`.user_id = ?
          ORDER BY `to`.order_date DESC";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $test_history[] = $row;
        
        // Update summary counts
        $summary_counts['total']++;
        if ($row['status'] === 'completed') {
            $summary_counts['completed']++;
        } else {
            $summary_counts['pending']++;
        }
    }
}

// Group tests by year and month for the timeline display
$grouped_tests = [];
foreach ($test_history as $test) {
    $year = date('Y', strtotime($test['order_date']));
    $month = date('F', strtotime($test['order_date']));
    
    if (!isset($grouped_tests[$year])) {
        $grouped_tests[$year] = [];
    }
    
    if (!isset($grouped_tests[$year][$month])) {
        $grouped_tests[$year][$month] = [];
    }
    
    $grouped_tests[$year][$month][] = $test;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test History | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Test-History-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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


        <main class="test-history-container">
            <section class="test-history-header">
                <h1><i class="fas fa-history"></i> Test History</h1>
                <p>View your complete laboratory test history and results</p>
            </section>

            <div class="test-history-content">
                <div class="filter-controls">
                    <div class="search-bar">
                        <input type="text" id="search-input" placeholder="Search tests...">
                        <button type="button" id="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="filter-options">
                        <select id="time-filter">
                            <option value="all">All Time</option>
                            <option value="year">Past Year</option>
                            <option value="month">Past Month</option>
                            <option value="week">Past Week</option>
                        </select>
                        <select id="test-type">
                            <option value="all">All Test Types</option>
                            <option value="blood">Blood Tests</option>
                            <option value="urine">Urine Tests</option>
                            <option value="imaging">Imaging</option>
                            <option value="physical">Physical Exams</option>
                        </select>
                    </div>
                </div>

                <div class="history-summary">
                    <div class="summary-card">
                        <i class="fas fa-vial"></i>
                        <div>
                            <h3>Total Tests</h3>
                            <p class="count"><?php echo $summary_counts['total']; ?></p>
                        </div>
                    </div>
                    <div class="summary-card">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h3>Completed</h3>
                            <p class="count"><?php echo $summary_counts['completed']; ?></p>
                        </div>
                    </div>
                    <div class="summary-card">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Pending Results</h3>
                            <p class="count"><?php echo $summary_counts['pending']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="timeline-container">
                    <?php if (empty($grouped_tests)): ?>
                        <div class="no-tests">
                            <p>No test history found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($grouped_tests as $year => $months): ?>
                            <div class="timeline-year">
                                <h2><?php echo $year; ?></h2>
                                <?php foreach ($months as $month => $tests): ?>
                                    <div class="timeline-month">
                                        <h3><?php echo $month; ?></h3>
                                        <?php foreach ($tests as $test): ?>
                                            <div class="test-item" data-category="<?php echo strtolower($test['category']); ?>">
                                                <div class="test-date">
                                                    <span class="day"><?php echo date('d', strtotime($test['order_date'])); ?></span>
                                                    <span class="month"><?php echo date('M', strtotime($test['order_date'])); ?></span>
                                                </div>
                                                <div class="test-details">
                                                    <h4><?php echo htmlspecialchars($test['test_name']); ?></h4>
                                                    <p class="test-status <?php echo $test['status'] === 'completed' ? 'completed' : 'pending'; ?>">
                                                        <i class="fas <?php echo $test['status'] === 'completed' ? 'fa-check-circle' : 'fa-clock'; ?>"></i> 
                                                        <?php echo $test['status'] === 'completed' ? 'Results Available' : 'Pending Results'; ?>
                                                    </p>
                                                    <div class="test-meta">
                                                        <span><i class="fas fa-flask"></i> <?php echo ucfirst($test['category']); ?> Test</span>
                                                        <?php if (!empty($test['physician_name'])): ?>
                                                            <span><i class="fas fa-user-md"></i> <?php echo htmlspecialchars($test['physician_name']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($test['status'] === 'completed'): ?>
                                                        <button class="view-results" data-test-id="<?php echo $test['ordered_test_id']; ?>">
                                                            <i class="fas fa-file-alt"></i> View Results
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
                        <a href="Contact-Us-Page.php">Contact</a>
                        <a href="#">FAQs</a>
                        <a href="#">Privacy Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> MedicalChecks. All rights reserved.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </footer>

        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Test-History.js"></script>
    </body>
</html>