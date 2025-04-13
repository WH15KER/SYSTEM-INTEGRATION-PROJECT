<?php
session_start(); // Add this line at the very beginning
require_once 'connection.php';
require_once 'function.php';

// Check if user is logged in
$user_data = check_login($con);

// Initialize variables
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $service_id = sanitize_input($con, $_POST['service_id'] ?? '');
    $appointment_date = sanitize_input($con, $_POST['appointment_date'] ?? '');
    $start_time = sanitize_input($con, $_POST['start_time'] ?? '');
    $notes = sanitize_input($con, $_POST['notes'] ?? '');
    
    // Validate inputs
    if (empty($service_id)) {
        $errors[] = "Please select a service";
    }
    
    if (empty($appointment_date)) {
        $errors[] = "Please select a date";
    }
    
    if (empty($start_time)) {
        $errors[] = "Please select a time";
    }
    
    // Calculate end time (assuming 30 minutes duration for all services)
    $end_time = date('H:i:s', strtotime($start_time) + 1800);
    
    // If no errors, save to database
    if (empty($errors)) {
        $appointment_id = uniqid('appt_', true);
        $status = 'scheduled';
        
        $query = "INSERT INTO appointments (
            appointment_id, user_id, service_id, appointment_date, 
            start_time, end_time, status, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param(
            $stmt, 
            "ssssssss", 
            $appointment_id, 
            $user_data['user_id'], 
            $service_id, 
            $appointment_date, 
            $start_time, 
            $end_time, 
            $status, 
            $notes
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            
            // Reset form values
            $_POST = [];
        } else {
            $errors[] = "Failed to book appointment. Please try again.";
        }
    }
}

// Fetch available services from database
$services = [];
$query = "SELECT * FROM services WHERE is_active = TRUE";
$result = mysqli_query($con, $query);
if ($result) {
    $services = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fetch existing appointments to determine available time slots
$booked_slots = [];
$query = "SELECT appointment_date, start_time, end_time FROM appointments 
          WHERE status = 'scheduled' AND appointment_date >= CURDATE()";
$result = mysqli_query($con, $query);
if ($result) {
    $booked_slots = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | MedicalChecks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Style/Appointment-Page.css">
    <link rel="stylesheet" href="Style/Home-Page.css">
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
        
        <main class="appointment-container">
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <p>Appointment booked successfully! A confirmation has been sent to your email.</p>
                </div>
            <?php endif; ?>
            
            <div class="appointment-header">
                <h1><i class="fas fa-calendar-alt"></i> Book an Appointment</h1>
                <p>Schedule your health check with our certified specialists</p>
            </div>
            
            <div class="appointment-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Service</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Date & Time</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Details</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Confirmation</div>
                </div>
            </div>
            
            <form id="appointmentForm" method="POST" action="Appointment-Page.php">
                <!-- Step 1: Service Selection -->
                <div class="form-step active" data-step="1">
                    <h2>Select Service</h2>
                    <p>Choose the type of health check you need</p>
                    
                    <div class="service-options">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <input type="radio" name="service_id" id="service-<?php echo htmlspecialchars($service['service_id']); ?>" 
                                    value="<?php echo htmlspecialchars($service['service_id']); ?>" required>
                                <label for="service-<?php echo htmlspecialchars($service['service_id']); ?>">
                                    <i class="fas fa-<?php echo get_service_icon($service['name']); ?>"></i>
                                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                                    <div class="service-price">Php <?php echo number_format($service['price'], 2); ?></div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="service-options">
                        <div class="service-card">
                            <input type="radio" name="service" id="general-checkup" value="General Checkup" required>
                            <label for="general-checkup">
                                <i class="fas fa-user-md"></i>
                                <h3>General Checkup</h3>
                                <p>Comprehensive physical examination and basic tests</p>
                                <div class="service-price">Php 500</div>
                            </label>
                        </div>
                        
                        <div class="service-card">
                            <input type="radio" name="service" id="blood-test" value="Blood Test">
                            <label for="blood-test">
                                <i class="fas fa-tint"></i>
                                <h3>Blood Test</h3>
                                <p>Complete blood count and chemistry panel</p>
                                <div class="service-price">Php 300</div>
                            </label>
                        </div>
                        
                        <div class="service-card">
                            <input type="radio" name="service" id="vision-test" value="Vision Test">
                            <label for="vision-test">
                                <i class="fas fa-eye"></i>
                                <h3>Vision Test</h3>
                                <p>Comprehensive eye examination</p>
                                <div class="service-price">Php 400</div>
                            </label>
                        </div>
                        
                        <div class="service-card">
                            <input type="radio" name="service" id="cardiac-screen" value="Cardiac Screening">
                            <label for="cardiac-screen">
                                <i class="fas fa-heart"></i>
                                <h3>Cardiac Screening</h3>
                                <p>ECG and cardiovascular assessment</p>
                                <div class="service-price">Php 600</div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn-next" data-next="2">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 2: Date & Time Selection -->
                <div class="form-step" data-step="2">
                    <h2>Select Date & Time</h2>
                    <p>Choose a convenient time for your appointment</p>
                    
                    <div class="datetime-selection">
                        <div class="calendar-container">
                            <div class="calendar-header">
                                <button type="button" class="month-nav" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                                <h3 id="currentMonth"><?php echo date('F Y'); ?></h3>
                                <button type="button" class="month-nav" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                            </div>
                            <div class="calendar-grid" id="calendarGrid">
                                <!-- Calendar will be generated by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="time-selection">
                            <h3>Available Time Slots</h3>
                            <div class="time-slots" id="timeSlots">
                                <!-- Time slots will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn-prev" data-prev="1"><i class="fas fa-arrow-left"></i> Back</button>
                        <button type="button" class="btn-next" data-next="3">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 3: Personal Details -->
                <div class="form-step" data-step="3">
                    <h2>Your Information</h2>
                    <p>Please confirm your details for the appointment</p>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" name="fullName" 
                                value="<?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" 
                                value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob" 
                                value="<?php echo htmlspecialchars($user_data['date_of_birth'] ?? ''); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <input type="text" id="gender" name="gender" 
                                value="<?php echo ucfirst(htmlspecialchars($user_data['gender'] ?? '')); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3" readonly><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="notes">Additional Notes (Optional)</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Any special requirements or concerns"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-navigation">
                        <button type="button" class="btn-prev" data-prev="2"><i class="fas fa-arrow-left"></i> Back</button>
                        <button type="button" class="btn-next" data-next="4">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 4: Confirmation -->
                <div class="form-step" data-step="4">
                    <h2>Confirm Appointment</h2>
                    <p>Review your appointment details before submitting</p>
                    
                    <div class="confirmation-details">
                        <div class="detail-card">
                            <h3>Appointment Summary</h3>
                            <div class="detail-item">
                                <span>Service:</span>
                                <span id="confirm-service">Not selected</span>
                            </div>
                            <div class="detail-item">
                                <span>Date:</span>
                                <span id="confirm-date">Not selected</span>
                            </div>
                            <div class="detail-item">
                                <span>Time:</span>
                                <span id="confirm-time">Not selected</span>
                            </div>
                            <div class="detail-item">
                                <span>Duration:</span>
                                <span id="confirm-duration">Not selected</span>
                            </div>
                            <div class="detail-item total">
                                <span>Total:</span>
                                <span id="confirm-price">Not selected</span>
                            </div>
                        </div>
                        
                        <div class="detail-card">
                            <h3>Your Information</h3>
                            <div class="detail-item">
                                <span>Name:</span>
                                <span id="confirm-name"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span>Email:</span>
                                <span id="confirm-email"><?php echo htmlspecialchars($user_data['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span>Phone:</span>
                                <span id="confirm-phone"><?php echo htmlspecialchars($user_data['phone'] ?? 'Not provided'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="terms-agreement">
                        <input type="checkbox" id="appointment-terms" required>
                        <label for="appointment-terms">I agree to the <a href="#">Terms of Service</a> and confirm that the information provided is accurate.</label>
                    </div>
                    
                    <!-- Hidden fields for form submission -->
                    <input type="hidden" id="hidden-service-id" name="service_id">
                    <input type="hidden" id="hidden-appointment-date" name="appointment_date">
                    <input type="hidden" id="hidden-start-time" name="start_time">
                    
                    <div class="form-navigation">
                        <button type="button" class="btn-prev" data-prev="3"><i class="fas fa-arrow-left"></i> Back</button>
                        <button type="submit" class="btn-submit">
                            <span class="button-text">Confirm Appointment</span>
                            <span class="spinner hidden"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </div>
                </div>
            </form>
        </main>
        
        <footer>
            <div class="footer-content">
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="Contact-Us-Page.php">Contact Us</a>
                </div>
                <p>&copy; <?php echo date('Y'); ?> MedicalChecks. All rights reserved.</p>
            </div>
        </footer>

        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Appointment.js"></script>
        
        <script>
            // Pass PHP data to JavaScript
            const bookedSlots = <?php echo json_encode($booked_slots); ?>;
            const services = <?php echo json_encode($services); ?>;
        </script>
    </body>
</html>