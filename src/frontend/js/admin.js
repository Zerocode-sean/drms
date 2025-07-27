// Environment-aware base path detection
function getBasePath() {
  const hostname = window.location.hostname;
  const isLocalhost =
    hostname === "localhost" || hostname === "127.0.0.1" || hostname === "::1";
  return isLocalhost ? "/project" : "";
}

function getApiPath(endpoint) {
  return getBasePath() + "/src/backend/api/" + endpoint;
}

// Sidebar navigation highlight
const sidebarItems = document.querySelectorAll(".sidebar nav ul li");
sidebarItems.forEach((item) => {
  item.addEventListener("click", function () {
    sidebarItems.forEach((i) => i.classList.remove("active"));
    this.classList.add("active");
  });
});

document.addEventListener("DOMContentLoaded", () => {
  // Fetch dashboard metrics
  fetch(getApiPath("get_dashboard_metrics.php"), { credentials: "include" })
    .then((res) => res.json())
    .then((data) => {
      if (data.error) return;
      document.getElementById("total-requests").textContent =
        data.total_requests;
      document.getElementById("pending-approvals").textContent =
        data.pending_approvals;
      document.getElementById("active-drivers").textContent =
        data.active_drivers;
      document.getElementById("active-users").textContent = data.active_users;
    });

  // Fetch recent requests
  fetch(getApiPath("get_recent_requests.php"), { credentials: "include" })
    .then((res) => res.json())
    .then((data) => {
      console.log("Received requests data:", data); // Debug log

      // Handle both array and object responses
      const requests = Array.isArray(data) ? data : data.requests || [];

      const requestsTable = document.getElementById("requests-table-body");
      if (requests.length === 0) {
        requestsTable.innerHTML =
          '<tr><td colspan="5" style="text-align: center; color: #666;">No requests found</td></tr>';
      } else {
        requestsTable.innerHTML = requests
          .map(
            (r) => `
                    <tr>
                        <td>${r.id || "N/A"}</td>
                        <td>${r.username || "Unknown"}</td>
                        <td>${
                          r.document || r.waste_type || "Waste Collection"
                        }</td>
                        <td>${r.status || "Pending"}</td>
                        <td>
                            ${
                              r.status === "Pending" || !r.status
                                ? `<button class="approve-btn" data-id="${r.id}">Approve</button><button class="reject-btn" data-id="${r.id}">Reject</button>`
                                : `<button class="assign-btn" data-id="${r.id}">Assign to Driver</button>`
                            }
                        </td>
                    </tr>
                `
          )
          .join("");
      }

      // Also populate task management
      loadTaskManagement();
    })
    .catch((error) => {
      console.error("Error fetching requests:", error);
      const requestsTable = document.getElementById("requests-table-body");
      requestsTable.innerHTML =
        '<tr><td colspan="5" style="text-align: center; color: #e74c3c;">Error loading requests</td></tr>';
    });

  // Fetch driver assignments
  fetch(getApiPath("get_driver_assignments.php"), { credentials: "include" })
    .then((res) => res.json())
    .then((assignments) => {
      const assignmentsTable = document.getElementById(
        "assignments-table-body"
      );
      assignmentsTable.innerHTML = assignments
        .map(
          (a) => `
                <tr>
                    <td>${a.task_id}</td>
                    <td>${a.driver}</td>
                    <td>${a.request_id}</td>
                    <td>${a.status}</td>
                </tr>
            `
        )
        .join("");
    });

  // Helper to reload requests and assignments
  function reloadDashboardTables() {
    // Fetch recent requests
    fetch(getApiPath("get_recent_requests.php"), { credentials: "include" })
      .then((res) => res.json())
      .then((data) => {
        console.log("Reloaded requests data:", data); // Debug log

        // Handle both array and object responses
        const requests = Array.isArray(data) ? data : data.requests || [];

        const requestsTable = document.getElementById("requests-table-body");
        if (requests.length === 0) {
          requestsTable.innerHTML =
            '<tr><td colspan="5" style="text-align: center; color: #666;">No requests found</td></tr>';
        } else {
          requestsTable.innerHTML = requests
            .map(
              (r) => `
                        <tr>
                            <td>${r.id || "N/A"}</td>
                            <td>${r.username || "Unknown"}</td>
                            <td>${
                              r.document || r.waste_type || "Waste Collection"
                            }</td>
                            <td>${r.status || "Pending"}</td>
                            <td>
                                ${
                                  r.status === "Pending" || !r.status
                                    ? `<button class="approve-btn" data-id="${r.id}">Approve</button><button class="reject-btn" data-id="${r.id}">Reject</button>`
                                    : `<button class="assign-btn" data-id="${r.id}">Assign to Driver</button>`
                                }
                            </td>
                        </tr>
                    `
            )
            .join("");
        }

        // Also reload task management
        loadTaskManagement();
      })
      .catch((error) => {
        console.error("Error reloading requests:", error);
        const requestsTable = document.getElementById("requests-table-body");
        requestsTable.innerHTML =
          '<tr><td colspan="5" style="text-align: center; color: #e74c3c;">Error loading requests</td></tr>';
      });
    // Fetch driver assignments
    fetch(getApiPath("get_driver_assignments.php"), { credentials: "include" })
      .then((res) => res.json())
      .then((assignments) => {
        const assignmentsTable = document.getElementById(
          "assignments-table-body"
        );
        assignmentsTable.innerHTML = assignments
          .map(
            (a) => `
                    <tr>
                        <td>${a.task_id}</td>
                        <td>${a.driver}</td>
                        <td>${a.request_id}</td>
                        <td>${a.status}</td>
                    </tr>
                `
          )
          .join("");
      });
  }

  // Task Management Functions
  function loadTaskManagement() {
    // Fetch all requests for task management
    fetch(getApiPath("get_recent_requests.php"), { credentials: "include" })
      .then((res) => res.json())
      .then((data) => {
        console.log("Task management data:", data); // Debug log

        // Handle both array and object responses
        const requests = Array.isArray(data) ? data : data.requests || [];

        displayPendingTasks(requests.filter((r) => r.status === "Approved"));
        displayAssignedTasks(requests.filter((r) => r.status === "Assigned"));
      })
      .catch((error) => {
        console.error("Error loading task management:", error);
      });
  }

  function displayPendingTasks(pendingRequests) {
    const pendingContainer = document.getElementById("pending-tasks");

    if (!pendingRequests || pendingRequests.length === 0) {
      pendingContainer.innerHTML =
        '<p style="grid-column: 1/-1; text-align: center; color: #666; padding: 20px;">No pending tasks available.</p>';
      return;
    }

    const tasksHTML = pendingRequests
      .map(
        (request) => `
            <div class="task-card">
                <div class="task-header">
                    <span class="task-id">Task #${request.id}</span>
                    <span class="task-status pending">Pending</span>
                </div>
                <div class="task-details">
                    <div class="task-detail-row">
                        <span class="task-detail-label">Customer:</span>
                        <span class="task-detail-value">${
                          request.username || "Unknown"
                        }</span>
                    </div>
                    <div class="task-detail-row">
                        <span class="task-detail-label">Type:</span>
                        <span class="task-detail-value">${
                          request.document ||
                          request.waste_type ||
                          "Waste Collection"
                        }</span>
                    </div>
                    <div class="task-detail-row">
                        <span class="task-detail-label">Date:</span>
                        <span class="task-detail-value">${
                          request.created_at || "N/A"
                        }</span>
                    </div>
                </div>
                <div class="task-actions">
                    <button class="assign-task-btn" onclick="openDriverModal(${
                      request.id
                    })">
                        <i class="fas fa-user-plus"></i> Assign Driver
                    </button>
                    <button class="view-task-btn" onclick="viewTaskDetails(${
                      request.id
                    })">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                </div>
            </div>
        `
      )
      .join("");

    pendingContainer.innerHTML = tasksHTML;
  }

  function displayAssignedTasks(assignedRequests) {
    const assignedContainer = document.getElementById("assigned-tasks");

    if (!assignedRequests || assignedRequests.length === 0) {
      assignedContainer.innerHTML =
        '<p style="grid-column: 1/-1; text-align: center; color: #666; padding: 20px;">No assigned tasks available.</p>';
      return;
    }

    const tasksHTML = assignedRequests
      .map(
        (request) => `
            <div class="task-card">
                <div class="task-header">
                    <span class="task-id">Task #${request.id}</span>
                    <span class="task-status assigned">Assigned</span>
                </div>
                <div class="task-details">
                    <div class="task-detail-row">
                        <span class="task-detail-label">Customer:</span>
                        <span class="task-detail-value">${
                          request.username || "Unknown"
                        }</span>
                    </div>
                    <div class="task-detail-row">
                        <span class="task-detail-label">Type:</span>
                        <span class="task-detail-value">${
                          request.document ||
                          request.waste_type ||
                          "Waste Collection"
                        }</span>
                    </div>
                    <div class="task-detail-row">
                        <span class="task-detail-label">Date:</span>
                        <span class="task-detail-value">${
                          request.created_at || "N/A"
                        }</span>
                    </div>
                </div>
                <div class="task-actions">
                    <button class="view-task-btn" onclick="viewTaskDetails(${
                      request.id
                    })">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                </div>
            </div>
        `
      )
      .join("");

    assignedContainer.innerHTML = tasksHTML;
  }

  // Global variables for modal
  let currentTaskId = null;
  let availableDrivers = [];

  function openDriverModal(taskId) {
    currentTaskId = taskId;

    // Fetch available drivers
    fetch(getApiPath("get_available_drivers.php"), { credentials: "include" })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          availableDrivers = data.drivers;
          displayDriversInModal();
          document.getElementById("driver-modal").style.display = "block";
        } else {
          alert("Failed to load drivers: " + (data.error || "Unknown error"));
        }
      })
      .catch((error) => {
        console.error("Error loading drivers:", error);
        alert("Failed to load drivers. Please try again.");
      });
  }

  function displayDriversInModal() {
    const driverList = document.getElementById("driver-list");

    if (!availableDrivers || availableDrivers.length === 0) {
      driverList.innerHTML =
        '<p style="text-align: center; color: #666; padding: 20px;">No available drivers found.</p>';
      return;
    }

    const driversHTML = availableDrivers
      .map((driver) => {
        const capacityPercentage =
          (driver.current_load / driver.capacity) * 100;
        let capacityClass = "low";
        if (capacityPercentage > 70) capacityClass = "high";
        else if (capacityPercentage > 40) capacityClass = "medium";

        return `
                <div class="driver-item">
                    <div class="driver-info">
                        <div class="driver-name">${driver.name}</div>
                        <div class="driver-details">
                            ${driver.vehicle_type} • ${
          driver.phone || "No phone"
        }
                        </div>
                    </div>
                    <div class="driver-capacity">
                        <div>${Math.round(capacityPercentage)}% full</div>
                        <div class="capacity-bar">
                            <div class="capacity-fill ${capacityClass}" style="width: ${capacityPercentage}%"></div>
                        </div>
                    </div>
                    <button class="select-driver-btn" onclick="assignTaskToDriver(${
                      driver.id
                    })">
                        Select
                    </button>
                </div>
            `;
      })
      .join("");

    driverList.innerHTML = driversHTML;
  }

  function assignTaskToDriver(driverId) {
    if (!currentTaskId) return;

    fetch(getApiPath("assign_request.php"), {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({
        request_id: currentTaskId,
        driver_id: driverId,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert(data.message || "Task assigned successfully!");
          closeDriverModal();
          reloadDashboardTables();
        } else {
          alert("Failed to assign task: " + (data.error || "Unknown error"));
        }
      })
      .catch((error) => {
        console.error("Assignment error:", error);
        alert("Failed to assign task. Please try again.");
      });
  }

  function closeDriverModal() {
    document.getElementById("driver-modal").style.display = "none";
    currentTaskId = null;
    availableDrivers = [];
  }

  function viewTaskDetails(taskId) {
    // For now, just show an alert. You can expand this to show more details
    alert(
      `Viewing details for task #${taskId}. This feature can be expanded to show full task details.`
    );
  }

  // Add refresh tasks button functionality
  const refreshTasksBtn = document.getElementById("refresh-tasks");
  if (refreshTasksBtn) {
    refreshTasksBtn.addEventListener("click", function () {
      loadTaskManagement();
    });
  }

  // Action buttons (approve/reject/assign)
  const requestsTable = document.getElementById("requests-table-body");
  requestsTable.addEventListener("click", function (e) {
    const id = e.target.dataset.id;
    if (e.target.classList.contains("approve-btn")) {
      fetch(getApiPath("approve_request.php"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ id }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            alert("Request approved successfully!");
            reloadDashboardTables();
          } else {
            alert(data.error || "Failed to approve request.");
          }
        });
    } else if (e.target.classList.contains("reject-btn")) {
      fetch(getApiPath("reject_request.php"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ id }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            alert("Request rejected successfully!");
            reloadDashboardTables();
          } else {
            alert(data.error || "Failed to reject request.");
          }
        });
    } else if (e.target.classList.contains("assign-btn")) {
      const driver_id = prompt("Enter Driver ID to assign:");
      if (!driver_id) return;
      fetch(getApiPath("assign_request.php"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify({ request_id: id, driver_id }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) reloadDashboardTables();
          else alert(data.error || "Failed to assign request.");
        });
    }
  });

  // Smart Scheduling Functionality
  const generateScheduleBtn = document.getElementById("generate-schedule");
  const scheduleDateInput = document.getElementById("schedule-date");

  if (generateScheduleBtn) {
    generateScheduleBtn.addEventListener("click", function () {
      const date = scheduleDateInput.value;
      if (!date) {
        alert("Please select a date");
        return;
      }

      // Show loading state
      generateScheduleBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Generating...';
      generateScheduleBtn.disabled = true;

      // Generate schedule
      fetch(`${getApiPath("generate_schedule.php")}?date=${date}`, {
        credentials: "include",
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            displayScheduleResults(data);
          } else {
            alert(
              "Failed to generate schedule: " + (data.error || "Unknown error")
            );
          }
        })
        .catch((error) => {
          console.error("Schedule generation error:", error);
          alert("Failed to generate schedule. Please try again.");
        })
        .finally(() => {
          // Reset button state
          generateScheduleBtn.innerHTML =
            '<i class="fas fa-magic"></i> Generate Schedule';
          generateScheduleBtn.disabled = false;
        });
    });
  }

  function displayScheduleResults(data) {
    const schedule = data.schedule;
    const recommendations = data.recommendations;

    // Display summary
    document.getElementById("total-requests-count").textContent =
      schedule.total_requests;
    document.getElementById("clusters-formed").textContent = schedule.clusters;
    document.getElementById("fuel-savings").textContent =
      schedule.estimated_fuel_savings.savings_percentage + "%";

    // Calculate total estimated time
    let totalTime = 0;
    if (schedule.assignments) {
      schedule.assignments.forEach((assignment) => {
        if (assignment.estimated_duration) {
          totalTime += assignment.estimated_duration;
        }
      });
    }
    document.getElementById("estimated-time").textContent = totalTime + " min";

    // Show summary section
    document.getElementById("schedule-summary").style.display = "block";

    // Display routes
    displayRoutes(schedule.assignments);

    // Display recommendations
    displayRecommendations(recommendations.recommendations);
  }

  function displayRoutes(assignments) {
    const routesContainer = document.getElementById("routes-container");

    if (!assignments || assignments.length === 0) {
      routesContainer.innerHTML = "<p>No routes generated for this date.</p>";
      document.getElementById("schedule-details").style.display = "block";
      return;
    }

    const routesHTML = assignments
      .map((assignment, index) => {
        if (!assignment.route) return "";

        const stopsHTML = assignment.route
          .map(
            (stop, stopIndex) => `
                <div class="stop-item">
                    <div class="stop-number">${stopIndex + 1}</div>
                    <div class="stop-details">
                        <div class="stop-address">${
                          stop.address || "Address not available"
                        }</div>
                        <div class="stop-time">${
                          stop.preferred_time || "Time not specified"
                        }</div>
                    </div>
                </div>
            `
          )
          .join("");

        return `
                <div class="route-card">
                    <div class="route-header">
                        <div class="route-title">
                            ${
                              assignment.driver_name
                                ? `Route ${index + 1} - ${
                                    assignment.driver_name
                                  }`
                                : `Route ${index + 1} - Unassigned`
                            }
                        </div>
                        <div class="route-stats">
                            <span>${assignment.total_distance || 0} km</span>
                            <span>${
                              assignment.estimated_duration || 0
                            } min</span>
                            <span>${assignment.route.length} stops</span>
                        </div>
                    </div>
                    <div class="route-stops">
                        ${stopsHTML}
                    </div>
                </div>
            `;
      })
      .join("");

    routesContainer.innerHTML = routesHTML;
    document.getElementById("schedule-details").style.display = "block";
  }

  function displayRecommendations(recommendations) {
    const recommendationsList = document.getElementById("recommendations-list");

    if (!recommendations || recommendations.length === 0) {
      recommendationsList.innerHTML =
        "<li>No specific recommendations for this schedule.</li>";
    } else {
      const recommendationsHTML = recommendations
        .map((rec) => `<li>${rec}</li>`)
        .join("");
      recommendationsList.innerHTML = recommendationsHTML;
    }

    document.getElementById("schedule-recommendations").style.display = "block";
  }

  // Logout
  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
      fetch(getApiPath("logout.php"), {
        method: "POST",
        credentials: "include",
      })
        .then((res) => {
          if (res.ok) {
            window.location.href = "login.php";
          } else {
            alert("Logout failed.");
          }
        })
        .catch(() => alert("Logout failed."));
    });
  }
});
