<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Orders </title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Order-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
    <body>
        <?php
        session_start();
        require_once('connection.php');
        require_once('function.php');
        
        // Check if user is logged in
        $user_data = check_login($con);
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


        <div class="form-container">
            <center>
            <h1> Orders </h1>
                <form method="GET" action="" class="search-bar">
                    <input type="text" name="search" placeholder="Search orders results here" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit"> Search </button>
                </form>
            </center>
            
            <h2> Latest Orders </h2>
            <div class="results-container">
                <?php
                // Build the query based on search
                $search = isset($_GET['search']) ? sanitize_input($con, $_GET['search']) : '';
                $query = "SELECT * FROM inventory_items";
                
                if (!empty($search)) {
                    $query .= " WHERE name LIKE '%$search%' OR description LIKE '%$search%' OR category LIKE '%$search%'";
                }
                
                $query .= " ORDER BY created_at DESC LIMIT 10";
                
                $result = mysqli_query($con, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="result-card">';
                        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                        echo '<p> Quantity: ' . htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['unit']) . '</p>';
                        
                        if (!empty($row['expiry_date'])) {
                            $expiry_date = date('M. d, Y', strtotime($row['expiry_date']));
                            echo '<p> Expiration Date: ' . htmlspecialchars($expiry_date) . '</p>';
                        }
                        
                        echo '<button class="details-btn" data-item-id="' . htmlspecialchars($row['item_id']) . '">Details</button>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No inventory items found.</p>';
                }
                ?>
            </div>
        </div>

        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Order.js"></script>
        
    </body>
</html>