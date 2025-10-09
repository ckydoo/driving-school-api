<!-- Paynow Payment Modal -->
<!-- Location: resources/views/school/subscription/partials/paynow-payment-modal.blade.php -->

<div class="modal fade" id="paynowPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-credit-card me-2"></i>
                    Pay with Paynow
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="paynow-payment-options">
                    <!-- Payment Method Selection -->
                    <div class="mb-4">
                        <h6 class="mb-3">Select Payment Method</h6>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="paynow_method" id="paynow_web" value="web" checked>
                            <label class="form-check-label" for="paynow_web">
                                <strong>Web Payment</strong>
                                <small class="d-block text-muted">Pay using Visa, Mastercard, EcoCash, or OneMoney</small>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="paynow_method" id="paynow_ecocash" value="ecocash">
                            <label class="form-check-label" for="paynow_ecocash">
                                <strong>EcoCash Direct</strong>
                                <small class="d-block text-muted">Pay directly from your EcoCash wallet</small>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="paynow_method" id="paynow_onemoney" value="onemoney">
                            <label class="form-check-label" for="paynow_onemoney">
                                <strong>OneMoney Direct</strong>
                                <small class="d-block text-muted">Pay directly from your OneMoney wallet</small>
                            </label>
                        </div>
                    </div>

                    <!-- Mobile Payment Details -->
                    <div id="mobile-payment-details" style="display: none;">
                        <div class="mb-3">
                            <label for="mobile-phone-number" class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="mobile-phone-number" 
                                   placeholder="0771234567" pattern="07[0-9]{8}">
                            <small class="text-muted">Enter your mobile number in the format: 07XXXXXXXX</small>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="mb-2">Invoice Details</h6>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Invoice Number:</span>
                            <strong id="paynow-invoice-number"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Amount:</span>
                            <strong id="paynow-invoice-amount"></strong>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div id="paynow-message" class="alert" style="display: none;"></div>
                    <div id="paynow-instructions" class="alert alert-info" style="display: none;"></div>
                </div>

                <!-- Loading State -->
                <div id="paynow-loading" style="display: none;" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Processing...</span>
                    </div>
                    <p class="mt-3 mb-0">Processing your payment...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="paynow-submit-btn" onclick="submitPaynowPayment()">
                    <i class="fas fa-lock me-2"></i>
                    <span id="paynow-button-text">Proceed to Payment</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPaynowInvoiceId = null;
let statusCheckInterval = null;

// Show/hide mobile payment fields
document.querySelectorAll('input[name="paynow_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const mobileDetails = document.getElementById('mobile-payment-details');
        if (this.value === 'ecocash' || this.value === 'onemoney') {
            mobileDetails.style.display = 'block';
        } else {
            mobileDetails.style.display = 'none';
        }
    });
});

function openPaynowModal(invoiceId, invoiceNumber, invoiceAmount) {
    currentPaynowInvoiceId = invoiceId;
    
    document.getElementById('paynow-invoice-number').textContent = invoiceNumber;
    document.getElementById('paynow-invoice-amount').textContent = ' + parseFloat(invoiceAmount).toFixed(2);
    
    // Reset form
    document.querySelector('input[name="paynow_method"][value="web"]').checked = true;
    document.getElementById('mobile-payment-details').style.display = 'none';
    document.getElementById('mobile-phone-number').value = '';
    document.getElementById('paynow-message').style.display = 'none';
    document.getElementById('paynow-instructions').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('paynowPaymentModal'));
    modal.show();
}

async function submitPaynowPayment() {
    const method = document.querySelector('input[name="paynow_method"]:checked').value;
    const messageDiv = document.getElementById('paynow-message');
    const instructionsDiv = document.getElementById('paynow-instructions');
    const submitBtn = document.getElementById('paynow-submit-btn');
    const buttonText = document.getElementById('paynow-button-text');
    const loadingDiv = document.getElementById('paynow-loading');
    const optionsDiv = document.getElementById('paynow-payment-options');
    
    messageDiv.style.display = 'none';
    instructionsDiv.style.display = 'none';
    
    // Validate mobile number for mobile payments
    if ((method === 'ecocash' || method === 'onemoney')) {
        const phoneNumber = document.getElementById('mobile-phone-number').value;
        if (!phoneNumber || !/^07[0-9]{8}$/.test(phoneNumber)) {
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = 'Please enter a valid mobile number (e.g., 0771234567)';
            messageDiv.style.display = 'block';
            return;
        }
    }
    
    submitBtn.disabled = true;
    buttonText.textContent = 'Processing...';
    
    try {
        let response;
        
        if (method === 'web') {
            // Web payment
            response = await fetch('{{ route("school.paynow.initiate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    invoice_id: currentPaynowInvoiceId
                })
            });
        } else {
            // Mobile payment
            const phoneNumber = document.getElementById('mobile-phone-number').value;
            response = await fetch('{{ route("school.paynow.initiate-mobile") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    invoice_id: currentPaynowInvoiceId,
                    phone_number: phoneNumber,
                    method: method
                })
            });
        }
        
        const result = await response.json();
        
        if (result.success) {
            if (method === 'web') {
                // Redirect to Paynow for web payment
                window.location.href = result.redirect_url;
            } else {
                // Show mobile payment instructions
                instructionsDiv.textContent = result.instructions;
                instructionsDiv.style.display = 'block';
                
                optionsDiv.style.display = 'none';
                loadingDiv.style.display = 'block';
                
                // Start checking payment status
                startStatusCheck();
            }
        } else {
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = result.error || 'Payment initialization failed';
            messageDiv.style.display = 'block';
            submitBtn.disabled = false;
            buttonText.textContent = 'Proceed to Payment';
        }
    } catch (error) {
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = 'An error occurred: ' + error.message;
        messageDiv.style.display = 'block';
        submitBtn.disabled = false;
        buttonText.textContent = 'Proceed to Payment';
    }
}

function startStatusCheck() {
    let attempts = 0;
    const maxAttempts = 60; // Check for 5 minutes (every 5 seconds)
    
    statusCheckInterval = setInterval(async () => {
        attempts++;
        
        if (attempts > maxAttempts) {
            clearInterval(statusCheckInterval);
            showStatusCheckTimeout();
            return;
        }
        
        try {
            const response = await fetch('{{ route("school.paynow.check-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    invoice_id: currentPaynowInvoiceId
                })
            });
            
            const result = await response.json();
            
            if (result.success && result.paid) {
                clearInterval(statusCheckInterval);
                showPaymentSuccess();
            }
        } catch (error) {
            console.error('Status check error:', error);
        }
    }, 5000); // Check every 5 seconds
}

function showPaymentSuccess() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('paynowPaymentModal'));
    modal.hide();
    
    alert('Payment successful! Your subscription has been updated.');
    window.location.reload();
}

function showStatusCheckTimeout() {
    const loadingDiv = document.getElementById('paynow-loading');
    const instructionsDiv = document.getElementById('paynow-instructions');
    const messageDiv = document.getElementById('paynow-message');
    
    loadingDiv.style.display = 'none';
    instructionsDiv.style.display = 'none';
    
    messageDiv.className = 'alert alert-warning';
    messageDiv.innerHTML = `
        <strong>Payment Status Unknown</strong><br>
        Your payment is being processed. Please refresh this page in a few moments to check your payment status.
        If the payment was successful, your subscription will be updated automatically.
    `;
    messageDiv.style.display = 'block';
    
    const submitBtn = document.getElementById('paynow-submit-btn');
    submitBtn.disabled = false;
    document.getElementById('paynow-button-text').textContent = 'Close';
}

// Clean up interval when modal is closed
document.getElementById('paynowPaymentModal').addEventListener('hidden.bs.modal', function () {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
    }
});
</script>