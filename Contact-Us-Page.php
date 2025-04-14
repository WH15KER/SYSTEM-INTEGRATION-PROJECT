<?php
session_start();
include("connection.php");
include("function.php");

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $name = isset($_POST['name']) ? sanitize_input($con, $_POST['name']) : '';
    $surname = isset($_POST['surname']) ? sanitize_input($con, $_POST['surname']) : '';
    $email = isset($_POST['email']) ? sanitize_input($con, $_POST['email']) : '';
    $message = isset($_POST['message']) ? sanitize_input($con, $_POST['message']) : '';
    
    // Basic validation
    if (empty($name)) {
        $error_message = "Name is required";
    } elseif (empty($surname)) {
        $error_message = "Surname is required";
    } elseif (empty($email) || !is_valid_email($email)) {
        $error_message = "Valid email is required";
    } elseif (empty($message)) {
        $error_message = "Message is required";
    } else {
        // Generate a unique ID for the submission
        $submission_id = 'CS-' . random_num(8);
        
        try {
            // Start transaction
            mysqli_begin_transaction($con);
            
            // Insert into database
            $query = "INSERT INTO contact_submissions (submission_id, name, surname, email, message) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssss", $submission_id, $name, $surname, $email, $message);
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_commit($con);
                    $success_message = "Thank you for your message! We'll get back to you soon.";
                    // Clear form fields
                    $name = $surname = $email = $message = '';
                } else {
                    mysqli_rollback($con);
                    error_log("Database error: " . mysqli_error($con));
                    $error_message = "Error submitting your message. Please try again later.";
                }
                mysqli_stmt_close($stmt);
            } else {
                mysqli_rollback($con);
                error_log("Prepare statement failed: " . mysqli_error($con));
                $error_message = "Database error. Please try again later.";
            }
        } catch (Exception $e) {
            mysqli_rollback($con);
            error_log("Exception: " . $e->getMessage());
            $error_message = "An unexpected error occurred. Please try again later.";
        }
    }
}

// Check if user is logged in to pre-fill some fields
$user_data = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT first_name, last_name, email FROM users WHERE user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Contact-Us-Page.css">
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
            <form method="POST" action="">
                <?php if (!empty($success_message)): ?>
                    <div class="success-message"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-input" 
                        placeholder="Enter your name" 
                        value="<?php echo htmlspecialchars($name ?? $user_data['first_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" id="surname" name="surname" class="form-input" 
                        placeholder="Enter your surname" 
                        value="<?php echo htmlspecialchars($surname ?? $user_data['last_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" 
                        placeholder="Enter your email" 
                        value="<?php echo htmlspecialchars($email ?? $user_data['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" class="form-input" 
                            placeholder="Enter your message" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Submit</button>
            </form>
        </div>

        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Contact-Us.js"></script>
    </body>
</html>