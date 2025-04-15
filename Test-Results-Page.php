<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Test-Results-Page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 350px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .modal-content h3 {
            margin-top: 0;
            color: #1a3c34;
            font-size: 24px;
        }
        .modal-content p {
            color: #666;
            margin-bottom: 20px;
        }
        .modal-content button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .modal-content .submit-btn {
            background-color: #1a3c34;
            color: white;
        }
        .modal-content .submit-btn:hover {
            background-color: #14524a;
        }
        .modal-content .cancel-btn {
            background-color: #ccc;
        }
        .modal-content .cancel-btn:hover {
            background-color: #b3b3b3;
        }
        .modal-content .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
            display: none;
        }

        /* OTP Input Styling */
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .otp-input {
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 20px;
            border: 2px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .otp-input:focus {
            outline: none;
            border-color: #1a3c34;
            box-shadow: 0 0 5px rgba(26, 60, 52, 0.3);
        }
        .otp-input.filled {
            border-color: #1a3c34;
        }
    </style>
</head>
<body>
    <?php
    require_once('connection.php');
    require_once('function.php');
    
    // Start session and check login
    session_start();
    $user_data = check_login($con);
    $user_id = $user_data['user_id'];
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
            <!-- Hard-coded example lab result with PIN protection -->
            <div class="result-card" data-test-id="example-1">
                <h3>Complete Blood Count (CBC)</h3>
                <p>April 10, 2025</p>
                <button class="details-btn" onclick="showPinModal('example-1')">Details</button>
                
                <!-- Hidden details section -->
                <div class="result-details" id="details-example-1" style="display: none;">
                    <div class="detail-row">
                        <span class="detail-label">Test Name:</span>
                        <span>Complete Blood Count (CBC)</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span>April 10, 2025</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Result:</span>
                        <span>WBC: 7.5 x10³/µL, RBC: 4.8 x10⁶/µL, Hemoglobin: 14.2 g/dL</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Reference Range:</span>
                        <span>WBC: 4.5-11.0 x10³/µL, RBC: 4.5-5.9 x10⁶/µL, Hemoglobin: 13.5-17.5 g/dL</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Interpretation:</span>
                        <span>Normal</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Reviewed By:</span>
                        <span>Dr. John Smith</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div class="modal" id="pinModal">
        <div class="modal-content">
            <h3>Enter PIN</h3>
            <p>Please enter the 6-digit PIN to view this result:</p>
            <div class="otp-container">
                <input type="text" class="otp-input" maxlength="1" oninput="handleOtpInput(this, 0)">
                <input type="text" class="otp-input" maxlength="1" oninput="handleOtpInput(this, 1)">
                <input type="text" class="otp-input" maxlength="1" oninput="handleOtpInput(this, 2)">
                <input type="text" class="otp-input" maxlength="1" oninput="handleOtpInput(this, 3)">
                <input type="text" class="otp-input" maxlength="1" oninput="handleOtpInput(this, 4)">
                <input type="text" class="otp-input" maxlength="1" oninput="handleOtpInput(this, 5)">
            </div>
            <div>
                <button class="submit-btn" onclick="verifyPin()">Submit</button>
                <button class="cancel-btn" onclick="closePinModal()">Cancel</button>
            </div>
            <p class="error-message" id="errorMessage">Incorrect PIN. Please try again.</p>
        </div>
    </div>

    <script src="Scripts/Main.js"></script>
    <script src="Scripts/pages/Test-Results.js"></script>
    <script>
        let currentTestId = null;

        function showPinModal(testId) {
            currentTestId = testId;
            document.getElementById('pinModal').style.display = 'flex';
            document.getElementById('errorMessage').style.display = 'none';
            // Reset OTP inputs
            const inputs = document.querySelectorAll('.otp-input');
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('filled');
            });
            inputs[0].focus();
        }

        function closePinModal() {
            document.getElementById('pinModal').style.display = 'none';
            currentTestId = null;
        }

        function handleOtpInput(currentInput, index) {
            // Allow only numbers
            currentInput.value = currentInput.value.replace(/[^0-9]/g, '');
            
            // Add filled class if input has a value
            if (currentInput.value) {
                currentInput.classList.add('filled');
            } else {
                currentInput.classList.remove('filled');
            }

            // Move to next input if a digit is entered
            if (currentInput.value && index < 5) {
                document.querySelectorAll('.otp-input')[index + 1].focus();
            }

            // Move to previous input on backspace if empty
            if (!currentInput.value && index > 0 && event.inputType === 'deleteContentBackward') {
                document.querySelectorAll('.otp-input')[index - 1].focus();
            }

            // Auto-submit if all digits are filled
            const inputs = document.querySelectorAll('.otp-input');
            const allFilled = Array.from(inputs).every(input => input.value);
            if (allFilled) {
                verifyPin();
            }
        }

        function verifyPin() {
            const inputs = document.querySelectorAll('.otp-input');
            const pin = Array.from(inputs).map(input => input.value).join('');
            if (pin === "123456") {
                document.getElementById(`details-${currentTestId}`).style.display = 'block';
                closePinModal();
            } else {
                document.getElementById('errorMessage').style.display = 'block';
                // Shake animation for error
                const modalContent = document.querySelector('.modal-content');
                modalContent.style.animation = 'shake 0.5s';
                modalContent.innerHTML += `
                    <style>
                        @keyframes shake {
                            0%, 100% { transform: translateX(0); }
                            25% { transform: translateX(-5px); }
                            50% { transform: translateX(5px); }
                            75% { transform: translateX(-5px); }
                        }
                    </style>
                `;
            }
        }

        // Close modal when clicking outside
        document.getElementById('pinModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePinModal();
            }
        });

        // Handle keyboard navigation
        document.querySelectorAll('.otp-input').forEach((input, index) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    verifyPin();
                } else if (e.key === 'Backspace' && !input.value && index > 0) {
                    document.querySelectorAll('.otp-input')[index - 1].focus();
                }
            });
        });
    </script>
</body>
</html>