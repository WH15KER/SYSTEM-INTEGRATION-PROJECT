<?php
session_start();
include("connection.php");
include("function.php");

$user_data = check_login($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Checks - Your Health Companion</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
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

        <section class="hero">
            <div class="hero-content">
                <h1>Take Control of Your Health</h1>
                <p>Regular health checks can detect potential issues early and help prevent serious conditions. Start your wellness journey today.</p>
                <a href="Appointment-Page.php" class="check-now">
                    <i class="fas fa-calendar-alt"></i> Book an Appointment
                </a>
            </div>
            <div class="hero-image">
                <img src="Image/Doctor Smiling.jpg" alt="Friendly doctor with patient">
            </div>
        </section>

        <section class="checks-grid">
            <div class="section-header">
                <h2>Comprehensive Health Checks</h2>
                <p class="subtitle">Our specialized tests cover all aspects of your wellbeing</p>
            </div>
            <div class="grid-container">
                <!-- Checks repeated here... -->
                <div class="check-item">
                    <div class="icon"><i class="fas fa-eye"></i></div>
                    <h3>Vision Screening</h3>
                    <p class="description">Comprehensive eye exams to detect vision problems and eye diseases</p>
                    <a href="Appointment-Page.html" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="check-item">
                    <div class="icon"><i class="fas fa-ear-listen"></i></div>
                    <h3>Hearing Tests</h3>
                    <p class="description">Evaluate your hearing health and detect any auditory impairments</p>
                    <a href="Appointment-Page.html" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="check-item">
                    <div class="icon"><i class="fas fa-lungs"></i></div>
                    <h3>Respiratory Check</h3>
                    <p class="description">Assess lung function and detect respiratory conditions</p>
                    <a href="Appointment-Page.html" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="check-item">
                    <div class="icon"><i class="fas fa-tint"></i></div>
                    <h3>Blood Analysis</h3>
                    <p class="description">Complete blood work to monitor your overall health</p>
                    <a href="Appointment-Page.html" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="check-item">
                    <div class="icon"><i class="fas fa-bone"></i></div>
                    <h3>Bone Density</h3>
                    <p class="description">Screen for osteoporosis and assess bone health</p>
                    <a href="Appointment-Page.html" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="check-item">
                    <div class="icon"><i class="fas fa-heart"></i></div>
                    <h3>Cardiac Screening</h3>
                    <p class="description">Evaluate heart health and detect cardiovascular risks</p>
                    <a href="Appointment-Page.html" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <a href="Appointment-Page.html" class="see-more">View All Services <i class="fas fa-chevron-right"></i></a>
        </section>

        <section class="features">
            <div class="feature-card">
                <i class="fas fa-clock"></i>
                <h3>Quick Results</h3>
                <p>Receive most test results within 24-48 hours through our secure portal</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-user-md"></i>
                <h3>Expert Doctors</h3>
                <p>Board-certified physicians with specialized training in diagnostics</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Secure Data</h3>
                <p>HIPAA-compliant systems ensure your health information stays private</p>
            </div>
        </section>

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
    </body>
</html>
