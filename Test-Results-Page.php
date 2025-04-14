<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Test-Results-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
    <body>
        <?php
        require_once('connection.php');
        require_once('function.php');
        
        // Start session and check login
        session_start();
        $user_data = check_login($con);
        $user_id = $user_data['user_id'];
        
        // Get test results from database
        $query = "SELECT tr.result_id, tt.name as test_name, tr.result_date, tr.result_value, 
                        tr.reference_range, tr.interpretation, tr.reviewed_by
                FROM test_results tr
                JOIN ordered_tests ot ON tr.ordered_test_id = ot.ordered_test_id
                JOIN test_orders tos ON ot.order_id = tos.order_id
                JOIN test_types tt ON ot.test_type_id = tt.test_type_id
                WHERE tos.user_id = ?
                ORDER BY tr.result_date DESC";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $test_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
        ?>
        
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

        <div class="form-container">
            <center>
                <h1>Laboratory Results</h1>
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search lab test results here">
                    <button type="button" id="searchBtn">Search</button>
                </div>
            </center>
            
            <h2>Latest Results</h2>
            <div class="results-container" id="resultsContainer">
                <?php if (empty($test_results)): ?>
                    <div class="no-results">
                        <p>No test results found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($test_results as $test): ?>
                        <div class="result-card" data-test-id="<?php echo htmlspecialchars($test['result_id']); ?>">
                            <h3><?php echo htmlspecialchars($test['test_name']); ?></h3>
                            <p><?php echo date('F j, Y', strtotime($test['result_date'])); ?></p>
                            <button class="details-btn" data-test-id="<?php echo htmlspecialchars($test['result_id']); ?>">Details</button>
                            
                            <!-- Hidden details section -->
                            <div class="result-details" id="details-<?php echo htmlspecialchars($test['result_id']); ?>" style="display: none;">
                                <div class="detail-row">
                                    <span class="detail-label">Test Name:</span>
                                    <span><?php echo htmlspecialchars($test['test_name']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Date:</span>
                                    <span><?php echo date('F j, Y', strtotime($test['result_date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Result:</span>
                                    <span><?php echo htmlspecialchars($test['result_value']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Reference Range:</span>
                                    <span><?php echo htmlspecialchars($test['reference_range']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Interpretation:</span>
                                    <span><?php echo htmlspecialchars($test['interpretation']); ?></span>
                                </div>
                                <?php if (!empty($test['reviewed_by'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Reviewed By:</span>
                                    <span><?php echo htmlspecialchars($test['reviewed_by']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Test-Results.js"></script>
    </body>
</html>