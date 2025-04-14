<?php
session_start();
require_once 'connection.php';
require_once 'function.php';

// Check if user is logged in
$user_data = check_login($con);

// Initialize variables
$error = '';
$success = '';
$test_type = 'Standard Test';
$price = 50.00;
$total = $price;

// Process payment if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form inputs
    $cardholder_name = sanitize_input($con, $_POST['name'] ?? '');
    $card_number = sanitize_input($con, str_replace(' ', '', $_POST['card-number'] ?? ''));
    $expiry_date = sanitize_input($con, $_POST['valid-until'] ?? '');
    $cvv = sanitize_input($con, $_POST['cvv'] ?? '');
    $billing_address = sanitize_input($con, $_POST['address'] ?? '');
    $contact_number = sanitize_input($con, $_POST['contact'] ?? '');
    $payment_method = sanitize_input($con, $_POST['payment-method'] ?? 'credit-card');
    $pin = sanitize_input($con, $_POST['pin'] ?? '');

    // Basic validation
    if (empty($cardholder_name)) {
        $error = 'Cardholder name is required';
    } elseif (!preg_match('/^\d{16}$/', $card_number)) {
        $error = 'Invalid card number. Must be 16 digits.';
    } elseif (!preg_match('/^\d{3}$/', $cvv)) {
        $error = 'Invalid CVV';
    } elseif (empty($billing_address)) {
        $error = 'Billing address is required';
    } elseif (empty($contact_number)) {
        $error = 'Contact number is required';
    } elseif (!isset($_POST['terms'])) {
        $error = 'You must agree to the Terms of Service and Privacy Policy';
    } elseif (!preg_match('/^\d{6}$/', $pin)) {
        $error = 'Invalid PIN. Must be a 6-digit number.';
    } else {
        // Verify PIN (replace with actual database check)
        $stored_pin = $user_data['pin'] ?? '123456'; // Placeholder: fetch from database
        if ($pin !== $stored_pin) {
            $error = 'Incorrect PIN. Please try again.';
        }
    }

    if (empty($error)) {
        try {
            // Start transaction
            mysqli_begin_transaction($con);

            // Create invoice
            $invoice_number = 'INV-' . date('Ymd') . '-' . random_num(4);
            $issue_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime('+7 days'));

            $invoice_query = "INSERT INTO invoices (
                invoice_id, user_id, invoice_number, issue_date, due_date, 
                status, subtotal, tax, discount, total
            ) VALUES (
                UUID(), ?, ?, ?, ?, 
                'paid', ?, 0, 0, ?
            )";

            $stmt = mysqli_prepare($con, $invoice_query);
            mysqli_stmt_bind_param($stmt, "ssssdd", 
                $user_data['user_id'], 
                $invoice_number, 
                $issue_date, 
                $due_date, 
                $total, 
                $total
            );
            mysqli_stmt_execute($stmt);
            $invoice_id = mysqli_insert_id($con);

            // Add invoice item
            $item_query = "INSERT INTO invoice_items (
                item_id, invoice_id, description, quantity, unit_price, total_price
            ) VALUES (
                UUID(), ?, ?, 1, ?, ?
            )";

            $stmt = mysqli_prepare($con, $item_query);
            mysqli_stmt_bind_param($stmt, "ssdd", 
                $invoice_id, 
                $test_type, 
                $price, 
                $total
            );
            mysqli_stmt_execute($stmt);

            // Record payment
            $payment_query = "INSERT INTO payments (
                payment_id, invoice_id, user_id, payment_date, amount, 
                payment_method, transaction_reference, status
            ) VALUES (
                UUID(), ?, ?, NOW(), ?, 
                ?, UUID(), 'completed'
            )";

            $stmt = mysqli_prepare($con, $payment_query);
            mysqli_stmt_bind_param($stmt, "ssds", 
                $invoice_id, 
                $user_data['user_id'], 
                $total, 
                $payment_method
            );
            mysqli_stmt_execute($stmt);

            // Save card details if credit card
            if ($payment_method === 'credit-card') {
                $card_type = 'other';
                if (preg_match('/^4/', $card_number)) $card_type = 'visa';
                elseif (preg_match('/^5[1-5]/', $card_number)) $card_type = 'mastercard';
                elseif (preg_match('/^3[47]/', $card_number)) $card_type = 'amex';

                $last_four = substr($card_number, -4);
                $expiry_parts = explode('-', $expiry_date);

                $card_query = "INSERT INTO payment_cards (
                    card_id, user_id, card_type, last_four, 
                    expiry_month, expiry_year, cardholder_name, is_default
                ) VALUES (
                    UUID(), ?, ?, ?, 
                    ?, ?, ?, 1
                )";

                $stmt = mysqli_prepare($con, $card_query);
                mysqli_stmt_bind_param($stmt, "ssssss", 
                    $user_data['user_id'], 
                    $card_type, 
                    $last_four, 
                    $expiry_parts[1], 
                    $expiry_parts[0], 
                    $cardholder_name
                );
                mysqli_stmt_execute($stmt);
            }

            // Commit transaction
            mysqli_commit($con);

            // Redirect to billing page with success parameter
            header("Location: Billing-Page.php?payment=success");
            exit();

        } catch (Exception $e) {
            mysqli_rollback($con);
            $error = 'Payment failed: ' . $e->getMessage();
            log_error("Payment Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Results Payment | MedicalChecks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Style/Payment-Method-Page.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            text-align: center;
        }
        .modal-content h3 {
            margin-bottom: 15px;
        }
        .modal-content input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .modal-content button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .modal-content button.cancel {
            background-color: #dc3545;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-logo">
                <i class="fas fa-heartbeat"></i>
                <span>MedicalChecks</span>
            </div>
            <div class="back-to-results">
                <a href="Billing-Page.php"><i class="fas fa-arrow-left"></i> Back to Billing</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="left-panel">
            <div class="payment-summary">
                <h3>Order Summary</h3>
                <div class="summary-item">
                    <span>Test Type:</span>
                    <span><?php echo htmlspecialchars($test_type); ?></span>
                </div>
                <div class="summary-item">
                    <span>Price:</span>
                    <span>Php <?php echo number_format($price, 2); ?></span>
                </div>
                <div class="summary-item total">
                    <span>Total:</span>
                    <span>Php <?php echo number_format($total, 2); ?></span>
                </div>
                <div class="secure-payment">
                    <i class="fas fa-lock"></i>
                    <span>Secure Payment</span>
                </div>
            </div>
        </div>
        <div class="right-panel">
            <div class="payment-header">
                <h2>Payment Method</h2>
                <p>Complete your payment to access your lab results</p>
                <?php if (!empty($error)): ?>
                    <div class="error-message" style="color: red; margin-top: 10px;"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="success-message" style="color: green; margin-top: 10px;"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
            </div>
            
            <form id="paymentForm" method="POST" action="">
                <input type="hidden" name="payment-method" id="paymentMethod" value="credit-card">
                <input type="hidden" name="pin" id="pinInput">
                
                <div class="payment-methods">
                    <button type="button" class="payment-method active" data-method="credit-card">
                        <i class="fas fa-credit-card"></i> 
                        <span>Credit/Debit Card</span>
                        <i class="fas fa-check-circle"></i>
                    </button>
                    <button type="button" class="payment-method" data-method="gcash">
                        <i class="fas fa-mobile-alt"></i> 
                        <span>GCash</span>
                        <i class="fas fa-check-circle"></i>
                    </button>
                </div>
                
                <div class="credit-card-info">
                    <div class="form-header">
                        <h3>Card Information</h3>
                        <div class="card-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-amex"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Cardholder Name</label>
                        <input type="text" id="name" name="name" placeholder="John Doe" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="card-number">Card Number</label>
                        <div class="input-with-icon">
                            <input type="text" id="card-number" name="card-number" placeholder="1234 5678 9012 3456" maxlength="19" value="<?php echo htmlspecialchars($_POST['card-number'] ?? ''); ?>" required>
                            <i class="far fa-credit-card"></i>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="valid-until">Expiry Date</label>
                            <input type="month" id="valid-until" name="valid-until" placeholder="MM/YY" value="<?php echo htmlspecialchars($_POST['valid-until'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <div class="input-with-icon">
                                <input type="text" id="cvv" name="cvv" placeholder="123" pattern="\d{3}" title="3-digit CVV" value="<?php echo htmlspecialchars($_POST['cvv'] ?? ''); ?>" required>
                                <i class="fas fa-question-circle" title="3-digit code on back of card"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="billing-info">
                    <h3>Billing Information</h3>
                    <div class="form-group">
                        <label for="address">Billing Address</label>
                        <textarea id="address" name="address" placeholder="Street, Province, City, Country" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="tel" id="contact" name="contact" placeholder="Enter contact number" value="<?php echo htmlspecialchars($_POST['contact'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="terms-agreement">
                    <input type="checkbox" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button class="pay-button" id="payButton" type="submit">
                    <span class="button-text">Pay Php <?php echo number_format($total, 2); ?></span>
                    <span class="spinner hidden"><i class="fas fa-spinner fa-spin"></i></span>
                </button>
            </form>
        </div>
    </div>

    <!-- PIN Verification Modal -->
    <div id="pinModal" class="modal">
        <div class="modal-content">
            <h3>Enter 6-Digit PIN</h3>
            <input type="text" id="pinEntry" maxlength="6" pattern="\d{6}" placeholder="Enter PIN" required>
            <button id="submitPin">Submit</button>
            <button id="cancelPin" class="cancel">Cancel</button>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="Contact-Us-Page.php">Contact Us</a>
            </div>
            <p>© 2025 MedicalChecks. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Card Number Formatting
        const cardNumberInput = document.getElementById('card-number');
        cardNumberInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 16) value = value.slice(0, 16); // Limit to 16 digits
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            e.target.value = formatted;
        });

        // PIN Modal Logic
        const paymentForm = document.getElementById('paymentForm');
        const pinModal = document.getElementById('pinModal');
        const pinEntry = document.getElementById('pinEntry');
        const submitPin = document.getElementById('submitPin');
        const cancelPin = document.getElementById('cancelPin');
        const pinInput = document.getElementById('pinInput');

        paymentForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Validate card number client-side
            const cardNumber = cardNumberInput.value.replace(/\D/g, '');
            if (!/^\d{16}$/.test(cardNumber)) {
                alert('Please enter a valid 16-digit card number.');
                return;
            }
            
            // Validate other required fields
            if (!document.getElementById('name').value || 
                !document.getElementById('valid-until').value || 
                !document.getElementById('cvv').value || 
                !document.getElementById('address').value || 
                !document.getElementById('contact').value || 
                !document.getElementById('terms').checked) {
                alert('Please fill in all required fields and agree to the terms.');
                return;
            }
            
            pinModal.style.display = 'flex';
            pinEntry.focus();
        });

        submitPin.addEventListener('click', () => {
            const pin = pinEntry.value;
            if (/^\d{6}$/.test(pin)) {
                pinInput.value = pin;
                pinModal.style.display = 'none';
                paymentForm.submit();
            } else {
                alert('Please enter a valid 6-digit PIN.');
            }
        });

        cancelPin.addEventListener('click', () => {
            pinModal.style.display = 'none';
            pinEntry.value = '';
        });

        // Submit PIN on Enter key
        pinEntry.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                submitPin.click();
            }
        });
    </script>
</body>
</html>