
// Environment-aware base path detection
function getBasePath() {
    const hostname = window.location.hostname;
    const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1';
    return isLocalhost ? '/project' : '';
}

function getApiPath(endpoint) {
    return getBasePath() + '/src/backend/api/' + endpoint;
}

// Mobile Navigation Toggle
console.log("notifications js resource loaded!");
const hamburger = document.querySelector(".hamburger");
const navMenu = document.querySelector(".nav-menu");

hamburger.addEventListener("click", () => {
  hamburger.classList.toggle("active");
  navMenu.classList.toggle("active");
});

// Close mobile menu when clicking on a link
document.querySelectorAll(".nav-link").forEach((n) =>
  n.addEventListener("click", () => {
    hamburger.classList.remove("active");
    navMenu.classList.remove("active");
  })
);

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// Add scroll effect to navbar
window.addEventListener("scroll", () => {
  const navbar = document.querySelector(".navbar");
  if (window.scrollY > 100) {
    navbar.style.background = "rgba(0, 0, 0, 0.8)";
    navbar.style.boxShadow = "0 2px 30px rgba(0, 0, 0, 0.2)";
  } else {
    navbar.style.background = "rgba(0, 0, 0, 0.5)";
    navbar.style.boxShadow = "0 2px 20px rgba(0, 0, 0, 0.2)";
  }
});

// Add animation on scroll for metric cards
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px",
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = "1";
      entry.target.style.transform = "translateY(0)";
    }
  });
}, observerOptions);

// Observe metric cards
document.querySelectorAll(".metric-card").forEach((card) => {
  card.style.opacity = "0";
  card.style.transform = "translateY(30px)";
  card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
  observer.observe(card);
});

// Add typing effect to hero title
function typeWriter(element, text, speed = 100) {
  let i = 0;
  element.innerHTML = "";

  function type() {
    if (i < text.length) {
      element.innerHTML += text.charAt(i);
      i++;
      setTimeout(type, speed);
    }
  }

  type();
}

// Initialize typing effect when page loads
window.addEventListener("load", () => {
  const heroTitle = document.querySelector(".hero-title");
  if (heroTitle) {
    const originalText = heroTitle.textContent;
    typeWriter(heroTitle, originalText, 50);
  }
});

// Add parallax effect to hero section
window.addEventListener("scroll", () => {
  const scrolled = window.pageYOffset;
  const hero = document.querySelector(".hero");
  const heroIllustration = document.querySelector(".hero-illustration");

  if (hero && heroIllustration) {
    const rate = scrolled * -0.5;
    heroIllustration.style.transform = `translateY(${rate}px)`;
  }
});

// Add hover effects to dashboard mockup
document.addEventListener("DOMContentLoaded", () => {
  const mockupCards = document.querySelectorAll(".mockup-card");

  mockupCards.forEach((card) => {
    card.addEventListener("mouseenter", () => {
      card.style.transform = "scale(1.05)";
      card.style.boxShadow = "0 8px 25px rgba(0, 0, 0, 0.15)";
    });

    card.addEventListener("mouseleave", () => {
      card.style.transform = "scale(1)";
      card.style.boxShadow = "none";
    });
  });
});

// Notification functionality
function toggleNotifications() {
  const notificationBox = document.getElementById("notificationBox");
  const body = document.body;
  if (notificationBox && body) {
    notificationBox.classList.toggle("active");
    body.classList.toggle("notification-open");
  }
}

// Add notification bell click handler
document.addEventListener("DOMContentLoaded", function () {
  const notificationBell = document.getElementById("notification-bell");
  const notificationBox = document.getElementById("notificationBox");
  const notificationContent = notificationBox.querySelector(
    ".notification-content"
  );

  function fetchNotifications() {
    fetch(getApiPath("get_notifications.php"))
      .then((res) => res.json())
      .then((data) => {
        if (Array.isArray(data) && data.length > 0) {
          notificationContent.innerHTML = data
            .map(
              (n) =>
                `<p>${n.message} <span style='font-size:0.8em;color:#888;'>(${
                  n.sent_at && !isNaN(new Date(n.sent_at))
                    ? new Date(n.sent_at).toLocaleString()
                    : "N/A"
                })</span></p>`
            )
            .join("");
        } else {
          notificationContent.innerHTML = "<p>No notifications.</p>";
        }
      })
      .catch(() => {
        notificationContent.innerHTML = "<p>Error loading notifications.</p>";
      });
  }

  // Show/hide notification box
  window.toggleNotifications = function () {
    notificationBox.classList.toggle("active");
    if (notificationBox.classList.contains("active")) {
      fetchNotifications();
    }
  };

  // Open notifications when bell is clicked
  if (notificationBell) {
    notificationBell.addEventListener("click", function (e) {
      e.preventDefault();
      toggleNotifications();
    });
  }
});

// Quick request form handling
document.addEventListener("DOMContentLoaded", () => {
  const quickRequestForm = document.getElementById("quick-request-form");
  if (quickRequestForm) {
    quickRequestForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const requestType = document.getElementById("request-type").value;
      const requestDate = document.getElementById("request-date").value;

      if (!requestType || !requestDate) {
        alert("Please fill in all fields");
        return;
      }

      // Get current user's phone number (you might want to store this in session)
      const phone = prompt(
        "Please enter your phone number (e.g., 0712345678):"
      );
      if (!phone || !phone.match(/^0\d{9}$/)) {
        alert("Please enter a valid phone number");
        return;
      }

      // Prepare data for submission
      const requestData = {
        phone: phone,
        location: "Quick request location", // Default location
        waste_type:
          requestType === "waste-pickup"
            ? "General"
            : requestType === "recycling"
            ? "Recyclable"
            : "Special",
        preferred_date: requestDate + "T10:00", // Default time 10 AM
        notes: `Quick request: ${requestType}`,
        urgency: "Normal",
        resolved_address: "Quick request address",
        address_details: "",
      };

      // Submit to backend with timeout handling
      const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error("Request timeout")), 8000); // 8 second timeout
      });

      const fetchPromise = fetch(getApiPath("place_request.php"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(requestData),
      });

      Promise.race([fetchPromise, timeoutPromise])
        .then((res) => {
          if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
          }
          return res.json();
        })
        .then((data) => {
          if (data.success) {
            alert(
              `Request Submitted Successfully!\nType: ${requestType}\nDate: ${requestDate}\nRequest ID: ${data.request_id}`
            );
            quickRequestForm.reset();
          } else {
            alert(
              "Error submitting request: " +
                (data.error || data.message || "Unknown error")
            );
          }
        })
        .catch((error) => {
          console.error("Quick request error:", error);
          if (error.message === "Request timeout") {
            alert(
              "Request timed out. Please check your connection and try again."
            );
          } else {
            alert("Error submitting request. Please try again.");
          }
        });
    });
  }
});

// Add loading animation
window.addEventListener("load", () => {
  document.body.style.opacity = "0";
  document.body.style.transition = "opacity 0.5s ease";

  setTimeout(() => {
    document.body.style.opacity = "1";
  }, 100);
});

// Add counter animation for metrics
function animateCounter(element, target, duration = 2000) {
  let start = 0;
  const increment = target / (duration / 16);

  function updateCounter() {
    start += increment;
    if (start < target) {
      element.textContent = Math.floor(start);
      requestAnimationFrame(updateCounter);
    } else {
      element.textContent = target;
    }
  }

  updateCounter();
}

// Animate metrics when they come into view
const metricsObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const metricValue = entry.target.querySelector(".metric-value");
        if (metricValue) {
          const text = metricValue.textContent;
          const number = parseInt(text.replace(/\D/g, ""));
          if (number) {
            animateCounter(metricValue, number);
          }
        }
        metricsObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.5 }
);

document.querySelectorAll(".metric-card").forEach((card) => {
  metricsObserver.observe(card);
});

// Add smooth hover effects to buttons
document.querySelectorAll(".btn").forEach((btn) => {
  btn.addEventListener("mouseenter", () => {
    btn.style.transform = "translateY(-2px)";
  });

  btn.addEventListener("mouseleave", () => {
    btn.style.transform = "translateY(0)";
  });
});

// Add ripple effect to buttons
function createRipple(event) {
  const button = event.currentTarget;
  const ripple = document.createElement("span");
  const rect = button.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const x = event.clientX - rect.left - size / 2;
  const y = event.clientY - rect.top - size / 2;

  ripple.style.width = ripple.style.height = size + "px";
  ripple.style.left = x + "px";
  ripple.style.top = y + "px";
  ripple.classList.add("ripple");

  button.appendChild(ripple);

  setTimeout(() => {
    ripple.remove();
  }, 600);
}

document.querySelectorAll(".btn").forEach((btn) => {
  btn.addEventListener("click", createRipple);
});
