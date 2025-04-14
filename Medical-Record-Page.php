<?php
session_start(); // Add this at the very top
require_once('connection.php');
require_once('function.php');

// Check if user is logged in
$user_data = check_login($con);

// Get user's medical records from database
$user_id = $user_data['user_id'];

// Get medical visits
$visits_query = "SELECT * FROM medical_visits WHERE user_id = ? ORDER BY visit_date DESC";
$visits_stmt = mysqli_prepare($con, $visits_query);
mysqli_stmt_bind_param($visits_stmt, "s", $user_id);
mysqli_stmt_execute($visits_stmt);
$visits_result = mysqli_stmt_get_result($visits_stmt);

// Get medications
$medications_query = "SELECT * FROM medications WHERE user_id = ? AND is_current = TRUE ORDER BY name";
$medications_stmt = mysqli_prepare($con, $medications_query);
mysqli_stmt_bind_param($medications_stmt, "s", $user_id);
mysqli_stmt_execute($medications_stmt);
$medications_result = mysqli_stmt_get_result($medications_stmt);

// Get medication history
$medications_history_query = "SELECT * FROM medications WHERE user_id = ? AND is_current = FALSE ORDER BY end_date DESC";
$medications_history_stmt = mysqli_prepare($con, $medications_history_query);
mysqli_stmt_bind_param($medications_history_stmt, "s", $user_id);
mysqli_stmt_execute($medications_history_stmt);
$medications_history_result = mysqli_stmt_get_result($medications_history_stmt);

// Get allergies
$allergies_query = "SELECT * FROM allergies WHERE user_id = ? ORDER BY reaction_severity DESC";
$allergies_stmt = mysqli_prepare($con, $allergies_query);
mysqli_stmt_bind_param($allergies_stmt, "s", $user_id);
mysqli_stmt_execute($allergies_stmt);
$allergies_result = mysqli_stmt_get_result($allergies_stmt);

// Get immunizations
$immunizations_query = "SELECT * FROM immunizations WHERE user_id = ? ORDER BY administration_date DESC";
$immunizations_stmt = mysqli_prepare($con, $immunizations_query);
mysqli_stmt_bind_param($immunizations_stmt, "s", $user_id);
mysqli_stmt_execute($immunizations_stmt);
$immunizations_result = mysqli_stmt_get_result($immunizations_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records | MedicalChecks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Style/Medical-Record-Page.css">
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
        
        <main class="medical-record-container">
            <div class="record-header">
                <h1><i class="fas fa-file-medical"></i> Medical Records</h1>
                <p>View and manage your complete medical history</p>
                
                <div class="record-actions">
                    <button class="btn btn-primary" id="downloadAllBtn">
                        <i class="fas fa-download"></i> Download All Records
                    </button>
                    <button class="btn btn-secondary" id="shareRecordsBtn">
                        <i class="fas fa-share-alt"></i> Share Records
                    </button>
                </div>
            </div>
            
            <div class="record-tabs">
                <button class="tab-btn active" data-tab="summary">Summary</button>
                <button class="tab-btn" data-tab="visits">Visits</button>
                <button class="tab-btn" data-tab="medications">Medications</button>
                <button class="tab-btn" data-tab="allergies">Allergies</button>
                <button class="tab-btn" data-tab="immunizations">Immunizations</button>
            </div>
            
            <div class="record-content">
                <!-- Summary Tab -->
                <div class="tab-content active" id="summary-tab">
                    <div class="summary-cards">
                        <div class="summary-card">
                            <div class="card-header">
                                <i class="fas fa-user"></i>
                                <h3>Personal Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <span>Name:</span>
                                    <span><?php echo htmlspecialchars($user_data['first_name'] . ' ' . htmlspecialchars($user_data['last_name'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span>Date of Birth:</span>
                                    <span><?php echo $user_data['date_of_birth'] ? date('F j, Y', strtotime($user_data['date_of_birth'])) : 'Not specified'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span>Gender:</span>
                                    <span><?php echo htmlspecialchars(ucfirst($user_data['gender'] ?? 'Not specified')); ?></span>
                                </div>
                                <div class="info-item">
                                    <span>Blood Type:</span>
                                    <span><?php echo htmlspecialchars($user_data['blood_type'] ?? 'Not specified'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="summary-card">
                            <div class="card-header">
                                <i class="fas fa-heartbeat"></i>
                                <h3>Health Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <span>Last Visit:</span>
                                    <span>
                                        <?php 
                                        if (mysqli_num_rows($visits_result) > 0) {
                                            $last_visit = mysqli_fetch_assoc($visits_result);
                                            echo date('F j, Y', strtotime($last_visit['visit_date']));
                                            mysqli_data_seek($visits_result, 0); // Reset pointer
                                        } else {
                                            echo 'No visits recorded';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span>Primary Physician:</span>
                                    <span><?php echo htmlspecialchars($user_data['primary_physician'] ?? 'Not specified'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span>Chronic Conditions:</span>
                                    <span>
                                        <?php 
                                        $chronic_conditions = [];
                                        while ($visit = mysqli_fetch_assoc($visits_result)) {
                                            if (strpos(strtolower($visit['diagnosis']), 'chronic') !== false) {
                                                $chronic_conditions[] = $visit['diagnosis'];
                                            }
                                        }
                                        mysqli_data_seek($visits_result, 0); // Reset pointer
                                        
                                        echo !empty($chronic_conditions) ? implode(', ', array_unique($chronic_conditions)) : 'None documented';
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span>Last Lab Results:</span>
                                    <span>
                                        <?php 
                                        $test_query = "SELECT t.result_value, tt.name 
                                                    FROM test_results t
                                                    JOIN ordered_tests ot ON t.ordered_test_id = ot.ordered_test_id
                                                    JOIN test_types tt ON ot.test_type_id = tt.test_type_id
                                                    JOIN test_orders o ON ot.order_id = o.order_id
                                                    WHERE o.user_id = ?
                                                    ORDER BY t.result_date DESC
                                                    LIMIT 1";
                                        $test_stmt = mysqli_prepare($con, $test_query);
                                        mysqli_stmt_bind_param($test_stmt, "s", $user_id);
                                        mysqli_stmt_execute($test_stmt);
                                        $test_result = mysqli_stmt_get_result($test_stmt);
                                        
                                        if (mysqli_num_rows($test_result) > 0) {
                                            $last_test = mysqli_fetch_assoc($test_result);
                                            echo htmlspecialchars($last_test['name']) . ': ' . htmlspecialchars($last_test['result_value']);
                                        } else {
                                            echo 'No test results';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="summary-card">
                            <div class="card-header">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h3>Alerts</h3>
                            </div>
                            <div class="card-body">
                                <?php 
                                // Check for severe allergies
                                $severe_allergies = false;
                                while ($allergy = mysqli_fetch_assoc($allergies_result)) {
                                    if ($allergy['reaction_severity'] == 'severe' || $allergy['reaction_severity'] == 'life-threatening') {
                                        $severe_allergies = true;
                                    }
                                }
                                mysqli_data_seek($allergies_result, 0); // Reset pointer
                                
                                if ($severe_allergies) {
                                    echo '<div class="alert-item warning">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>You have severe allergies documented in your record</span>
                                    </div>';
                                }
                                
                                // Check for upcoming immunizations
                                $upcoming_immunizations = false;
                                while ($immunization = mysqli_fetch_assoc($immunizations_result)) {
                                    if ($immunization['next_due_date'] && strtotime($immunization['next_due_date']) <= strtotime('+3 months')) {
                                        $upcoming_immunizations = true;
                                    }
                                }
                                mysqli_data_seek($immunizations_result, 0); // Reset pointer
                                
                                if ($upcoming_immunizations) {
                                    echo '<div class="alert-item info">
                                        <i class="fas fa-info-circle"></i>
                                        <span>You have immunizations due soon</span>
                                    </div>';
                                }
                                
                                // Check for overdue medications
                                $overdue_meds = false;
                                while ($med = mysqli_fetch_assoc($medications_result)) {
                                    if ($med['end_date'] && strtotime($med['end_date']) < time()) {
                                        $overdue_meds = true;
                                    }
                                }
                                mysqli_data_seek($medications_result, 0); // Reset pointer
                                
                                if ($overdue_meds) {
                                    echo '<div class="alert-item warning">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>Some medications may need renewal</span>
                                    </div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Visits Tab -->
                <div class="tab-content" id="visits-tab">
                    <div class="visits-filter">
                        <div class="filter-group">
                            <label for="visit-type">Visit Type:</label>
                            <select id="visit-type">
                                <option value="all">All Visits</option>
                                <option value="checkup">Regular Checkup</option>
                                <option value="emergency">Emergency</option>
                                <option value="specialist">Specialist Consultation</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="time-range">Time Range:</label>
                            <select id="time-range">
                                <option value="all">All Time</option>
                                <option value="year">Last Year</option>
                                <option value="6months">Last 6 Months</option>
                                <option value="month">Last Month</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="visits-list">
                        <?php if (mysqli_num_rows($visits_result) > 0): ?>
                            <?php while ($visit = mysqli_fetch_assoc($visits_result)): ?>
                                <div class="visit-card">
                                    <div class="visit-header">
                                        <h3><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $visit['visit_type']))); ?></h3>
                                        <span class="visit-date"><?php echo date('F j, Y', strtotime($visit['visit_date'])); ?></span>
                                        <span class="visit-type <?php echo htmlspecialchars($visit['visit_type']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($visit['visit_type'])); ?>
                                        </span>
                                    </div>
                                    <div class="visit-body">
                                        <div class="visit-details">
                                            <div class="detail-item">
                                                <span>Physician:</span>
                                                <span><?php echo htmlspecialchars($visit['physician_name']); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span>Diagnosis:</span>
                                                <span><?php echo htmlspecialchars($visit['diagnosis'] ? $visit['diagnosis'] : 'No diagnosis recorded'); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span>Notes:</span>
                                                <span><?php echo htmlspecialchars($visit['notes'] ? $visit['notes'] : 'No additional notes'); ?></span>
                                            </div>
                                        </div>
                                        <div class="visit-actions">
                                            <button class="btn btn-sm btn-outline">
                                                <i class="fas fa-file-pdf"></i> Download Report
                                            </button>
                                            <button class="btn btn-sm btn-outline">
                                                <i class="fas fa-share"></i> Share
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="fas fa-info-circle"></i>
                                <p>No medical visits recorded yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Medications Tab -->
                <div class="tab-content" id="medications-tab">
                    <div class="medications-header">
                        <h3>Current Medications</h3>
                        <button class="btn btn-sm btn-primary" id="addMedicationBtn">
                            <i class="fas fa-plus"></i> Add Medication
                        </button>
                    </div>
                    
                    <div class="medications-list">
                        <?php if (mysqli_num_rows($medications_result) > 0): ?>
                            <?php while ($medication = mysqli_fetch_assoc($medications_result)): ?>
                                <div class="medication-card">
                                    <div class="medication-info">
                                        <h4><?php echo htmlspecialchars($medication['name']); ?></h4>
                                        <div class="medication-details">
                                            <span><?php echo htmlspecialchars($medication['dosage']); ?></span>
                                            <span><?php echo htmlspecialchars($medication['frequency']); ?></span>
                                            <span>Prescribed by <?php echo htmlspecialchars($medication['prescribed_by']); ?></span>
                                        </div>
                                    </div>
                                    <div class="medication-actions">
                                        <button class="btn btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-icon" title="History">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="fas fa-info-circle"></i>
                                <p>No current medications recorded.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="medical-section">
                        <h3>Medications History</h3>
                        <?php if (mysqli_num_rows($medications_history_result) > 0): ?>
                            <div class="medications-history-list">
                                <?php while ($medication = mysqli_fetch_assoc($medications_history_result)): ?>
                                    <div class="medication-card">
                                        <div class="medication-info">
                                            <h4><?php echo htmlspecialchars($medication['name']); ?></h4>
                                            <div class="medication-details">
                                                <span><?php echo htmlspecialchars($medication['dosage']); ?></span>
                                                <span><?php echo htmlspecialchars($medication['frequency']); ?></span>
                                                <span>Prescribed by <?php echo htmlspecialchars($medication['prescribed_by']); ?></span>
                                                <span>From <?php echo date('M j, Y', strtotime($medication['start_date'])); ?> 
                                                    to <?php echo $medication['end_date'] ? date('M j, Y', strtotime($medication['end_date'])) : 'Present'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="fas fa-info-circle"></i>
                                <p>No medication history found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Allergies Tab -->
                <div class="tab-content" id="allergies-tab">
                    <div class="allergies-header">
                        <h3>Allergies & Reactions</h3>
                        <button class="btn btn-sm btn-primary" id="addAllergyBtn">
                            <i class="fas fa-plus"></i> Add Allergy
                        </button>
                    </div>
                    
                    <div class="allergies-list">
                        <?php if (mysqli_num_rows($allergies_result) > 0): ?>
                            <?php while ($allergy = mysqli_fetch_assoc($allergies_result)): ?>
                                <div class="allergy-card <?php echo htmlspecialchars($allergy['reaction_severity']); ?>">
                                    <div class="allergy-info">
                                        <h4><?php echo htmlspecialchars($allergy['allergen']); ?></h4>
                                        <div class="allergy-details">
                                            <span><?php echo htmlspecialchars(ucfirst($allergy['reaction_severity'])) . ' allergic reaction'; ?></span>
                                            <span>First observed: <?php echo $allergy['first_observed'] ? date('Y', strtotime($allergy['first_observed'])) : 'Unknown date'; ?></span>
                                        </div>
                                    </div>
                                    <div class="allergy-reaction">
                                        <strong>Reaction:</strong> <?php echo htmlspecialchars($allergy['reaction_description']); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="fas fa-info-circle"></i>
                                <p>No allergies recorded.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Immunizations Tab -->
                <div class="tab-content" id="immunizations-tab">
                    <div class="immunizations-header">
                        <h3>Vaccination Records</h3>
                        <button class="btn btn-sm btn-primary" id="addImmunizationBtn">
                            <i class="fas fa-plus"></i> Add Immunization
                        </button>
                    </div>
                    
                    <div class="immunizations-list">
                        <?php if (mysqli_num_rows($immunizations_result) > 0): ?>
                            <?php while ($immunization = mysqli_fetch_assoc($immunizations_result)): ?>
                                <div class="immunization-card">
                                    <div class="immunization-info">
                                        <h4><?php echo htmlspecialchars($immunization['vaccine_name']); ?></h4>
                                        <div class="immunization-details">
                                            <span>Administered on: <?php echo date('M j, Y', strtotime($immunization['administration_date'])); ?></span>
                                            <span>By: <?php echo htmlspecialchars($immunization['administered_by']); ?></span>
                                            <?php if ($immunization['next_due_date']): ?>
                                                <span>Next due: <?php echo date('M j, Y', strtotime($immunization['next_due_date'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-records">
                                <i class="fas fa-info-circle"></i>
                                <p>No immunization records found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
        <script src="Scripts/pages/Medical-Record.js"></script>
    </body>
</html>