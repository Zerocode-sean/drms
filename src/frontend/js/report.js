
// Environment-aware base path detection
function getBasePath() {
    const hostname = window.location.hostname;
    const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1';
    return isLocalhost ? '/project' : '';
}

function getApiPath(endpoint) {
    return getBasePath() + '/src/backend/api/' + endpoint;
}

console.log("report js loaded");
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("reportForm");
  if (!form) return;
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    // Gather form data
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
      data[key] = value;
    });

    // Send AJAX request to the absolute backend API path
    fetch(getApiPath("submit_report.php")), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.success) {
          submitBtn.innerHTML = '<i class="fas fa-check"></i> Submitted!';
          form.reset();
          showReportMessage(
            "success",
            res.message || "Report submitted successfully."
          );
        } else {
          submitBtn.innerHTML = originalBtnHTML;
          showReportMessage("error", res.message || "Failed to submit report.");
        }
      })
      .catch(() => {
        submitBtn.innerHTML = originalBtnHTML;
        showReportMessage("error", "Network error. Please try again.");
      })
      .finally(() => {
        setTimeout(() => {
          submitBtn.innerHTML = originalBtnHTML;
          submitBtn.disabled = false;
        }, 1500);
      });
  });

  function showReportMessage(type, message) {
    let msg = document.getElementById("report-message");
    if (!msg) {
      msg = document.createElement("div");
      msg.id = "report-message";
      form.parentNode.insertBefore(msg, form);
    }
    msg.className = "report-message " + type;
    msg.innerHTML = message;
    msg.style.display = "block";
    setTimeout(() => {
      msg.style.display = "none";
    }, 5000);
  }
});
