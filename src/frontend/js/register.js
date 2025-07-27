
// Environment-aware base path detection
function getBasePath() {
    const hostname = window.location.hostname;
    const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1';
    return isLocalhost ? '/project' : '';
}

function getApiPath(endpoint) {
    return getBasePath() + '/src/backend/api/' + endpoint;
}

console.log("registration js loaded ");

document.addEventListener("DOMContentLoaded", function () {
  const registerForm = document.getElementById("register-form");
  const usernameInput = document.getElementById("username");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm-password");
  const submitBtn = registerForm.querySelector('button[type="submit"]');
  
  if (!registerForm || !usernameInput || !emailInput || !passwordInput || !confirmPasswordInput || !submitBtn) {
    console.error("Some form elements not found!");
    return;
  }

  // Inline error message div
  let errorDiv = document.createElement("div");
  errorDiv.id = "register-error";
  errorDiv.style.color = "red";
  errorDiv.style.marginTop = "10px";
  errorDiv.style.fontSize = "14px";
  registerForm.appendChild(errorDiv);

  // Password visibility toggles
  function addToggle(input) {
    const toggleBtn = document.createElement("button");
    toggleBtn.type = "button";
    toggleBtn.textContent = "Show";
    toggleBtn.style.marginLeft = "10px";
    toggleBtn.style.padding = "5px";
    toggleBtn.style.fontSize = "12px";
    toggleBtn.style.cursor = "pointer";
    input.parentNode.insertBefore(toggleBtn, input.nextSibling);
    toggleBtn.addEventListener("click", function () {
      if (input.type === "password") {
        input.type = "text";
        toggleBtn.textContent = "Hide";
      } else {
        input.type = "password";
        toggleBtn.textContent = "Show";
      }
    });
  }
  addToggle(passwordInput);
  addToggle(confirmPasswordInput);

  // Spinner element
  const spinner = document.createElement("span");
  spinner.className = "spinner";
  spinner.style.display = "none";
  spinner.style.marginLeft = "10px";
  spinner.innerHTML =
    '<svg width="18" height="18" viewBox="0 0 50 50"><circle cx="25" cy="25" r="20" fill="none" stroke="#ff7e5f" stroke-width="5" stroke-linecap="round" stroke-dasharray="31.415, 31.415" transform="rotate(72.0001 25 25)"><animateTransform attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="1s" repeatCount="indefinite"/></circle></svg>';
  submitBtn.parentNode.insertBefore(spinner, submitBtn.nextSibling);

  // Validation helpers
  function isAlphanumeric(str) {
    return /^[a-zA-Z0-9]+$/.test(str);
  }
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  registerForm.addEventListener("submit", function (e) {
    e.preventDefault();
    errorDiv.textContent = "";
    submitBtn.disabled = true;
    spinner.style.display = "inline-block";

    const username = usernameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();
    const confirmPassword = confirmPasswordInput.value.trim();

    if (!username || !email || !password || !confirmPassword) {
      errorDiv.textContent = "Please fill in all fields.";
      submitBtn.disabled = false;
      spinner.style.display = "none";
      return;
    }
    if (!isAlphanumeric(username)) {
      errorDiv.textContent = "Username must be alphanumeric.";
      submitBtn.disabled = false;
      spinner.style.display = "none";
      return;
    }
    if (!isValidEmail(email)) {
      errorDiv.textContent = "Please enter a valid email address.";
      submitBtn.disabled = false;
      spinner.style.display = "none";
      return;
    }
    if (password.length < 6) {
      errorDiv.textContent = "Password must be at least 6 characters.";
      submitBtn.disabled = false;
      spinner.style.display = "none";
      return;
    }
    if (password !== confirmPassword) {
      errorDiv.textContent = "Passwords do not match.";
      submitBtn.disabled = false;
      spinner.style.display = "none";
      return;
    }

    // AJAX call to backend
    fetch(getApiPath("register.php"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username, email, password }),
    })
      .then(async (response) => {
        console.log("Response status:", response.status);
        const data = await response.json();
        console.log("Response data:", data);
        if (!response.ok) {
          throw new Error(data.error || "Registration failed.");
        }
        errorDiv.style.color = "green";
        errorDiv.textContent = "Registration successful! Redirecting to login...";
        registerForm.reset();
        setTimeout(() => {
          window.location.href = "login.php";
        }, 1200);
      })
      .catch((err) => {
        console.error("Registration error:", err);
        errorDiv.style.color = "red";
        errorDiv.textContent = err.message;
      })
      .finally(() => {
        submitBtn.disabled = false;
        spinner.style.display = "none";
      });
  });
});
