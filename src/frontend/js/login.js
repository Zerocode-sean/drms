document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("login-form");
  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("password");
  const submitBtn = loginForm.querySelector('button[type="submit"]');

  if (!loginForm || !usernameInput || !passwordInput || !submitBtn) {
    console.error("Some form elements not found in login.js!");
    return;
  }

  // Inline error message div
  let errorDiv = document.getElementById("login-error");
  if (!errorDiv) {
    errorDiv = document.createElement("div");
    errorDiv.id = "login-error";
    errorDiv.style.color = "red";
    errorDiv.style.marginTop = "10px";
    errorDiv.style.fontSize = "14px";
    loginForm.appendChild(errorDiv);
  }

  // Password visibility toggle
  const toggleBtn = document.createElement("button");
  toggleBtn.type = "button";
  toggleBtn.textContent = "Show";
  toggleBtn.style.marginLeft = "10px";
  toggleBtn.style.padding = "5px";
  toggleBtn.style.fontSize = "12px";
  toggleBtn.style.cursor = "pointer";
  passwordInput.parentNode.insertBefore(toggleBtn, passwordInput.nextSibling);

  toggleBtn.addEventListener("click", function () {
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      toggleBtn.textContent = "Hide";
    } else {
      passwordInput.type = "password";
      toggleBtn.textContent = "Show";
    }
  });

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
    return /^[a-zA-Z0-9_]+$/.test(str);
  }

  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();
    errorDiv.textContent = "";
    submitBtn.disabled = true;
    spinner.style.display = "inline-block";

    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();

    // Basic validation
    if (!username || !password) {
      errorDiv.textContent = "Please enter both username and password.";
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
    if (password.length < 6) {
      errorDiv.textContent = "Password must be at least 6 characters.";
      submitBtn.disabled = false;
      spinner.style.display = "none";
      return;
    }

    // AJAX call to backend
    fetch("/src/backend/api/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username, password }),
    })
      .then(async (response) => {
        console.log("Response status:", response.status);
        const data = await response.json();
        console.log("Response data:", data);
        if (!response.ok) {
          throw new Error(data.error || "Login failed.");
        }
        errorDiv.style.color = "green";
        errorDiv.textContent = "Login successful! Redirecting...";
        loginForm.reset();
        setTimeout(() => {
          if (data.user && data.user.role === "admin") {
            window.location.href = "/src/frontend/assets/admin.php";
          } else if (data.user && data.user.role === "driver") {
            window.location.href = "/src/frontend/assets/driver.php";
          } else {
            window.location.href = "/src/frontend/assets/home.php";
          }
        }, 1200);
      })
      .catch((err) => {
        console.error("Login error:", err);
        errorDiv.style.color = "red";
        errorDiv.textContent = err.message;
      })
      .finally(() => {
        submitBtn.disabled = false;
        spinner.style.display = "none";
      });
  });

  // Enter key submits form (default behavior, but ensure focus/UX)
  loginForm.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      submitBtn.focus();
    }
  });
});
