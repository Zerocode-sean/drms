
// Environment-aware base path detection
function getBasePath() {
    const hostname = window.location.hostname;
    const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1';
    return isLocalhost ? '/project' : '';
}

function getApiPath(endpoint) {
    return getBasePath() + '/src/backend/api/' + endpoint;
}

document.getElementById("paymentForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const amount = document.getElementById("amount").value;
  const phone = document.getElementById("phone").value;
  const service = document.getElementById("service").value;
  const status = document.getElementById("status");
  const payButton = document.querySelector(".pay-button");

  if (!amount || !phone || !service) {
    showStatus("Please fill in all fields.", "error");
    return;
  }

  if (phone.length !== 12 || !phone.startsWith("254")) {
    showStatus(
      "Please enter a valid M-Pesa number (e.g., 254712345678).",
      "error"
    );
    return;
  }

  // Start payment process
  initiatePayment(amount, phone, service);
});

function showStatus(message, type = "info", icon = "") {
  const status = document.getElementById("status");
  status.className = `status ${type}`;

  const iconHtml = icon ? `<i class="${icon}"></i>` : "";
  status.innerHTML = `${iconHtml}${message}`;
  status.style.display = "block";
}

function initiatePayment(amount, phone, service) {
  const payButton = document.querySelector(".pay-button");

  // Step 1: Processing initial request
  showStatus(
    "Initiating secure payment...",
    "processing",
    "fas fa-spinner fa-spin"
  );
  payButton.disabled = true;

  fetch(getApiPath("initiate_payment.php"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ amount, phone, service }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // Step 2: STK Push sent successfully
        showStkPushSent(data.checkout_request_id, amount, phone);
      } else {
        showStatus(
          data.error || "Payment initiation failed. Please try again.",
          "error",
          "fas fa-exclamation-triangle"
        );
        payButton.disabled = false;
      }
    })
    .catch((error) => {
      console.error("Payment error:", error);
      showStatus(
        "Network error. Please check your connection and try again.",
        "error",
        "fas fa-wifi"
      );
      payButton.disabled = false;
    });
}

function showStkPushSent(checkoutRequestId, amount, phone) {
  const maskedPhone = phone.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, "$1***$4");

  showStatus(
    `
        <div class="stk-push-status">
            <div class="stk-header">
                <i class="fas fa-mobile-alt"></i>
                <strong>M-Pesa Payment Request Sent</strong>
            </div>
            <div class="stk-details">
                <p>💰 Amount: KSh ${amount}</p>
                <p>📱 Phone: ${maskedPhone}</p>
                <p>⏰ <strong>Please stay on this page and complete the payment within 3 minutes</strong></p>
            </div>
            <div class="stk-instructions">
                <div class="instruction-step">
                    <span class="step-number">1</span>
                    <span>Check your phone for the M-Pesa PIN prompt</span>
                </div>
                <div class="instruction-step">
                    <span class="step-number">2</span>
                    <span>Enter your M-Pesa PIN to authorize payment</span>
                </div>
                <div class="instruction-step">
                    <span class="step-number">3</span>
                    <span>Wait for confirmation message</span>
                </div>
            </div>
            <div class="stk-timer">
                <div class="timer-circle">
                    <svg width="100" height="100">
                        <circle cx="50" cy="50" r="45" fill="none" stroke="#e0e0e0" stroke-width="4"/>
                        <circle cx="50" cy="50" r="45" fill="none" stroke="#4caf50" stroke-width="4" 
                                stroke-linecap="round" stroke-dasharray="283" stroke-dashoffset="0" 
                                class="timer-progress" id="timerProgress"/>
                    </svg>
                    <div class="timer-text" id="timerText">3:00</div>
                </div>
                <p>Time remaining to complete payment</p>
            </div>
        </div>
    `,
    "stk-push"
  );

  // Start timer and status checking
  startPaymentTimer(checkoutRequestId);
  startStatusPolling(checkoutRequestId);
}

function startPaymentTimer(checkoutRequestId) {
  let timeLeft = 180; // 3 minutes in seconds
  const timerText = document.getElementById("timerText");
  const timerProgress = document.getElementById("timerProgress");

  const timer = setInterval(() => {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    const displayTime = `${minutes}:${seconds.toString().padStart(2, "0")}`;

    if (timerText) {
      timerText.textContent = displayTime;
    }

    // Update progress circle
    if (timerProgress) {
      const percentage = (timeLeft / 180) * 100;
      const circumference = 2 * Math.PI * 45; // radius = 45
      const strokeDashoffset =
        circumference - (percentage / 100) * circumference;
      timerProgress.style.strokeDashoffset = strokeDashoffset;

      // Change color as time runs out
      if (timeLeft <= 30) {
        timerProgress.style.stroke = "#f44336"; // Red
      } else if (timeLeft <= 60) {
        timerProgress.style.stroke = "#ff9800"; // Orange
      }
    }

    timeLeft--;

    if (timeLeft < 0) {
      clearInterval(timer);
      showPaymentTimeout();
    }
  }, 1000);

  // Store timer ID for cleanup
  window.paymentTimer = timer;
}

function startStatusPolling(checkoutRequestId) {
  let pollCount = 0;
  const maxPolls = 36; // Poll for 3 minutes (every 5 seconds)

  const pollInterval = setInterval(() => {
    pollCount++;

    fetch(getApiPath("check_payment_status.php"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ checkout_request_id: checkoutRequestId }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "completed") {
          clearInterval(pollInterval);
          clearInterval(window.paymentTimer);
          showPaymentSuccess(data);
        } else if (data.status === "failed" || data.status === "cancelled") {
          clearInterval(pollInterval);
          clearInterval(window.paymentTimer);
          showPaymentFailed(data.message || "Payment was cancelled or failed");
        }
        // If status is 'pending', continue polling
      })
      .catch((error) => {
        console.error("Status polling error:", error);
      });

    if (pollCount >= maxPolls) {
      clearInterval(pollInterval);
    }
  }, 5000); // Poll every 5 seconds
}

function showPaymentSuccess(data) {
  showStatus(
    `
        <div class="payment-result success">
            <div class="result-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Payment Successful!</h3>
            <div class="result-details">
                <p><strong>Transaction ID:</strong> ${
                  data.transaction_id || "N/A"
                }</p>
                <p><strong>Amount:</strong> KSh ${data.amount || "N/A"}</p>
                <p><strong>Status:</strong> Completed</p>
            </div>
            <div class="result-actions">
                <button onclick="window.location.href='home.php'" class="btn-primary">
                    <i class="fas fa-home"></i> Return to Dashboard
                </button>
                <button onclick="window.print()" class="btn-secondary">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    `,
    "success"
  );

  // Re-enable form for new payments
  setTimeout(() => {
    document.querySelector(".pay-button").disabled = false;
    document.getElementById("paymentForm").reset();
  }, 3000);
}

function showPaymentFailed(message) {
  showStatus(
    `
        <div class="payment-result failed">
            <div class="result-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h3>Payment Failed</h3>
            <div class="result-details">
                <p>${message}</p>
                <p><strong>Common reasons:</strong></p>
                <ul>
                    <li>Insufficient M-Pesa balance</li>
                    <li>PIN entered incorrectly</li>
                    <li>Transaction cancelled by user</li>
                    <li>Network timeout</li>
                </ul>
            </div>
            <div class="result-actions">
                <button onclick="resetPaymentForm()" class="btn-primary">
                    <i class="fas fa-redo"></i> Try Again
                </button>
                <button onclick="window.location.href='home.php'" class="btn-secondary">
                    <i class="fas fa-home"></i> Return to Dashboard
                </button>
            </div>
        </div>
    `,
    "error"
  );
}

function showPaymentTimeout() {
  showStatus(
    `
        <div class="payment-result timeout">
            <div class="result-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h3>Payment Timeout</h3>
            <div class="result-details">
                <p>The payment request has expired after 3 minutes.</p>
                <p>If you completed the payment, it may still be processing. Check your M-Pesa messages.</p>
            </div>
            <div class="result-actions">
                <button onclick="resetPaymentForm()" class="btn-primary">
                    <i class="fas fa-redo"></i> Try Again
                </button>
                <button onclick="window.location.href='home.php'" class="btn-secondary">
                    <i class="fas fa-home"></i> Return to Dashboard
                </button>
            </div>
        </div>
    `,
    "warning"
  );
}

function resetPaymentForm() {
  // Clear any running timers
  if (window.paymentTimer) {
    clearInterval(window.paymentTimer);
  }

  // Reset form and UI
  document.getElementById("paymentForm").reset();
  document.querySelector(".pay-button").disabled = false;
  document.getElementById("status").style.display = "none";
}
