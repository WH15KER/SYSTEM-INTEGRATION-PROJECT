<?php
session_start();
require_once 'connection.php';
require_once 'function.php';

// Check if user is logged in
$user_data = check_login($con);

// Get user's invoices from database
$invoices = [];
$payments = [];
$estimates = [];

// Fetch invoices
$invoice_query = "SELECT i.*, 
                 (SELECT SUM(total_price) FROM invoice_items WHERE invoice_id = i.invoice_id) as subtotal,
                 (SELECT COUNT(*) FROM payments WHERE invoice_id = i.invoice_id AND status = 'completed') as payment_count
                 FROM invoices i 
                 WHERE i.user_id = ? 
                 ORDER BY i.issue_date DESC";
$stmt = mysqli_prepare($con, $invoice_query);
mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
mysqli_stmt_execute($stmt);
$invoice_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($invoice_result)) {
    $invoices[] = $row;
}

// Fetch payments
$payment_query = "SELECT p.*, i.invoice_number 
                 FROM payments p
                 JOIN invoices i ON p.invoice_id = i.invoice_id
                 WHERE p.user_id = ?
                 ORDER BY p.payment_date DESC";
$stmt = mysqli_prepare($con, $payment_query);
mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
mysqli_stmt_execute($stmt);
$payment_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($payment_result)) {
    $payments[] = $row;
}

// Fetch estimates (using appointments as estimates in this example)
$estimate_query = "SELECT a.appointment_id as estimate_id, 
                  CONCAT('EST-', YEAR(a.appointment_date), '-', LPAD(a.appointment_id, 4, '0')) as estimate_number,
                  s.name as service_name, 
                  s.price as amount,
                  a.appointment_date as scheduled_date
                  FROM appointments a
                  JOIN services s ON a.service_id = s.service_id
                  WHERE a.user_id = ? AND a.status = 'scheduled'
                  ORDER BY a.appointment_date DESC";
$stmt = mysqli_prepare($con, $estimate_query);
mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
mysqli_stmt_execute($stmt);
$estimate_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($estimate_result)) {
    $estimates[] = $row;
}

// Calculate total balance (sum of all unpaid invoices)
$total_balance = 0;
$next_due_date = null;
foreach ($invoices as $invoice) {
    if ($invoice['status'] == 'pending' || $invoice['status'] == 'overdue') {
        $total_balance += $invoice['total'];
        if (!$next_due_date || strtotime($invoice['due_date']) < strtotime($next_due_date)) {
            $next_due_date = $invoice['due_date'];
        }
    }
}

// Get default payment method
$payment_method = "Not set";
$card_query = "SELECT * FROM payment_cards WHERE user_id = ? AND is_default = TRUE LIMIT 1";
$stmt = mysqli_prepare($con, $card_query);
mysqli_stmt_bind_param($stmt, "s", $user_data['user_id']);
mysqli_stmt_execute($stmt);
$card_result = mysqli_stmt_get_result($stmt);

if ($card_row = mysqli_fetch_assoc($card_result)) {
    $payment_method = ucfirst($card_row['card_type']) . " •••• " . $card_row['last_four'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing | MedicalChecks</title>
    <link rel="stylesheet" href="Style/Home-Page.css">
    <link rel="stylesheet" href="Style/Billing-Page.css">
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


        <main class="billing-container">
            <section class="billing-header">
                <h1><i class="fas fa-file-invoice-dollar"></i> Billing & Payments</h1>
                <p>View and manage your medical bills and payments in one place</p>
            </section>

            <div class="billing-content">
                <section class="billing-summary">
                    <div class="summary-card">
                        <div class="summary-item">
                            <i class="fas fa-file-invoice"></i>
                            <div>
                                <h3>Total Balance</h3>
                                <p class="amount">$<?= number_format($total_balance, 2) ?></p>
                            </div>
                        </div>
                        <div class="summary-item">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <h3>Due Date</h3>
                                <p class="date"><?= $next_due_date ? date('M j, Y', strtotime($next_due_date)) : 'No pending invoices' ?></p>
                            </div>
                        </div>
                        <div class="summary-item">
                            <i class="fas fa-wallet"></i>
                            <div>
                                <h3>Payment Method</h3>
                                <p class="method"><?= htmlspecialchars($payment_method) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="payment-actions">
                        <a href="Payment-Method-Page.php" class="pay-now">
                            <i class="fas fa-credit-card"></i> Pay Now
                        </a>
                    </div>

                </section>

                <section class="billing-details">
                    <div class="tabs">
                        <button class="tab active" data-tab="invoices">Invoices</button>
                        <button class="tab" data-tab="payments">Payments</button>
                        <button class="tab" data-tab="estimates">Estimates</button>
                    </div>

                    <div class="tab-content active" id="invoices">
                        <?php if (empty($invoices)): ?>
                            <div class="no-results">
                                <i class="fas fa-file-invoice"></i>
                                <p>No invoices found</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                                <div class="invoice-card">
                                    <div class="invoice-header">
                                        <h3>Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?></h3>
                                        <span class="status <?= $invoice['status'] ?>">
                                            <?= ucfirst($invoice['status']) ?>
                                        </span>
                                    </div>
                                    <div class="invoice-details">
                                        <div>
                                            <p class="label">Issue Date</p>
                                            <p class="value"><?= date('M j, Y', strtotime($invoice['issue_date'])) ?></p>
                                        </div>
                                        <div>
                                            <p class="label">Amount</p>
                                            <p class="value">$<?= number_format($invoice['total'], 2) ?></p>
                                        </div>
                                        <div>
                                            <p class="label"><?= $invoice['status'] == 'paid' ? 'Paid On' : 'Due Date' ?></p>
                                            <p class="value">
                                                <?= $invoice['status'] == 'paid' ? 
                                                    date('M j, Y', strtotime($invoice['updated_at'])) : 
                                                    date('M j, Y', strtotime($invoice['due_date'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php if ($invoice['status'] == 'pending' || $invoice['status'] == 'overdue'): ?>
                                        <button class="pay-invoice" onclick="location.href='Payment-Method-Page.php?invoice_id=<?= $invoice['invoice_id'] ?>'">
                                            <i class="fas fa-credit-card"></i> Pay Now
                                        </button>
                                    <?php else: ?>
                                        <button class="view-invoice" onclick="location.href='Invoice-Details-Page.php?id=<?= $invoice['invoice_id'] ?>'">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="tab-content" id="payments">
                        <?php if (empty($payments)): ?>
                            <div class="no-results">
                                <i class="fas fa-receipt"></i>
                                <p>No payments found</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                                <div class="payment-card">
                                    <div class="payment-header">
                                        <h3>Payment #PMT-<?= date('Y', strtotime($payment['payment_date'])) ?>-<?= str_pad($payment['payment_id'], 4, '0', STR_PAD_LEFT) ?></h3>
                                        <span class="amount">$<?= number_format($payment['amount'], 2) ?></span>
                                    </div>
                                    <div class="payment-details">
                                        <div>
                                            <p class="label">Date</p>
                                            <p class="value"><?= date('M j, Y', strtotime($payment['payment_date'])) ?></p>
                                        </div>
                                        <div>
                                            <p class="label">Method</p>
                                            <p class="value"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></p>
                                        </div>
                                        <div>
                                            <p class="label">Invoice</p>
                                            <p class="value">#<?= htmlspecialchars($payment['invoice_number']) ?></p>
                                        </div>
                                    </div>
                                    <button class="view-receipt" onclick="location.href='Payment-Receipt-Page.php?id=<?= $payment['payment_id'] ?>'">
                                        <i class="fas fa-receipt"></i> View Receipt
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="tab-content" id="estimates">
                        <?php if (empty($estimates)): ?>
                            <div class="no-results">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <p>No estimates found</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($estimates as $estimate): ?>
                                <div class="estimate-card">
                                    <div class="estimate-header">
                                        <h3>Estimate #<?= htmlspecialchars($estimate['estimate_number']) ?></h3>
                                        <span class="amount">$<?= number_format($estimate['amount'], 2) ?></span>
                                    </div>
                                    <div class="estimate-details">
                                        <div>
                                            <p class="label">Service</p>
                                            <p class="value"><?= htmlspecialchars($estimate['service_name']) ?></p>
                                        </div>
                                        <div>
                                            <p class="label">Date</p>
                                            <p class="value">Scheduled for <?= date('M j, Y', strtotime($estimate['scheduled_date'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="estimate-actions">
                                        <button class="view-details" onclick="location.href='Appointment-Details-Page.php?id=<?= $estimate['estimate_id'] ?>'">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <button class="schedule" onclick="location.href='Appointment-Page.php'">
                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
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
                <p>&copy; 2025 MedicalChecks. All rights reserved.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </footer>

        <script src="Scripts/Main.js"></script>
        <script src="Scripts/pages/Billing.js"></script>
        
    </body>
</html>