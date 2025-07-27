
// Environment-aware base path detection
function getBasePath() {
    const hostname = window.location.hostname;
    const isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1';
    return isLocalhost ? '/project' : '';
}

function getApiPath(endpoint) {
    return getBasePath() + '/src/backend/api/' + endpoint;
}

document.addEventListener("DOMContentLoaded", function () {
  initializeMap();
  fetchTasks();
});

let map;
let markers = [];

function initializeMap() {
  // Initialize map centered on Nairobi, Kenya
  map = L.map("map").setView([-1.2921, 36.8219], 12);

  // Add OpenStreetMap tiles
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors",
  }).addTo(map);

  // Add a default marker for the driver's location (can be customized)
  L.marker([-1.2921, 36.8219])
    .addTo(map)
    .bindPopup("Driver Starting Location")
    .openPopup();
}

function fetchTasks() {
  fetch(getApiPath("get_driver_tasks.php"))
    .then((res) => res.json())
    .then((tasks) => renderTasks(tasks));
}

function renderTasks(tasks) {
  const container = document.getElementById("tasks-container");
  if (!tasks.length) {
    container.innerHTML = "<p>No assigned tasks.</p>";
    clearMapMarkers();
    return;
  }

  let html =
    "<table><tr><th>Address</th><th>Time</th><th>Status</th><th>User</th><th>Contact</th><th>Actions</th></tr>";
  tasks.forEach((task) => {
    html += `<tr>
            <td>${task.address}</td>
            <td>${task.scheduled_time}</td>
            <td>
                <select onchange="updateStatus(${task.request_id}, this.value)">
                    <option value="Assigned" ${
                      task.status === "Assigned" ? "selected" : ""
                    }>Assigned</option>
                    <option value="In Progress" ${
                      task.status === "In Progress" ? "selected" : ""
                    }>In Progress</option>
                    <option value="Completed" ${
                      task.status === "Completed" ? "selected" : ""
                    }>Completed</option>
                    <option value="Missed" ${
                      task.status === "Missed" ? "selected" : ""
                    }>Missed</option>
                </select>
            </td>
            <td>${task.user_name}</td>
            <td>${task.user_phone}<br>${task.user_email}</td>
            <td>
                <button class="map-btn" data-address="${
                  task.address
                }">📍 Map</button>
                <button class="notify-btn" data-request-id="${
                  task.request_id
                }" data-user-phone="${task.user_phone || ""}" data-user-name="${
      task.user_name || ""
    }" data-address="${task.address || ""}">📧 Notify</button>
            </td>
        </tr>`;
  });
  html += "</table>";
  container.innerHTML = html;

  // Add event listeners for the new buttons
  addTaskButtonListeners();

  // Plot all tasks on the map
  plotTasksOnMap(tasks);
}

function addTaskButtonListeners() {
  // Add listeners for map buttons
  document.querySelectorAll(".map-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const address = this.getAttribute("data-address");
      console.log("🗺️ Map button clicked for:", address);
      showOnMap(address);
    });
  });

  // Add listeners for notify buttons
  document.querySelectorAll(".notify-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const requestId = this.getAttribute("data-request-id");
      const userPhone = this.getAttribute("data-user-phone");
      const userName = this.getAttribute("data-user-name");
      const address = this.getAttribute("data-address");

      console.log("🔔 Notify button clicked for:", {
        requestId,
        userPhone,
        userName,
        address,
      });
      openNotificationModal(requestId, userPhone, userName, address);
    });
  });
}

window.updateStatus = function (requestId, status) {
  fetch(getApiPath("update_request_status.php"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ request_id: requestId, status }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        alert("Status updated successfully!");
        fetchTasks();
      } else {
        alert("Failed to update status: " + (data.error || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Update status error:", error);
      alert("Failed to update status. Please try again.");
    });
};

window.notifyMissed = function (requestId, userPhone) {
  // Call notification and SMS endpoints
  fetch(getApiPath("send_notification.php"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ request_id: requestId, type: "missed" }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        console.log("Notification sent successfully");
      } else {
        console.error("Failed to send notification:", data.error);
      }
    })
    .catch((error) => {
      console.error("Notification error:", error);
    });

  fetch(getApiPath("send_sms.php"), {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      phone: userPhone,
      message: "Your collection was missed.",
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        console.log("SMS sent successfully");
      } else {
        console.error("Failed to send SMS:", data.error);
      }
    })
    .catch((error) => {
      console.error("SMS error:", error);
    });

  alert("User notified of missed collection.");
};

window.showOnMap = function (address) {
  // Geocode address using a free API (e.g., Nominatim) and show on Leaflet map
  fetch(
    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
      address
    )}`
  )
    .then((res) => res.json())
    .then((data) => {
      if (data && data[0]) {
        const lat = data[0].lat;
        const lon = data[0].lon;
        showMap(lat, lon, address);
      } else {
        alert("Address not found on map.");
      }
    });
};

function showMap(lat, lon, address) {
  if (!map) {
    console.log("Map already initialized, updating view");
  }
  map.setView([lat, lon], 15);

  // Clear existing markers except the first one (driver location)
  markers.forEach((marker) => {
    if (marker.options.isTaskMarker) {
      map.removeLayer(marker);
    }
  });

  // Add new marker for the specific task
  const taskMarker = L.marker([lat, lon], { isTaskMarker: true })
    .addTo(map)
    .bindPopup(address)
    .openPopup();
  markers.push(taskMarker);
}

function clearMapMarkers() {
  // Clear all task markers but keep the driver location marker
  markers.forEach((marker) => {
    if (marker.options.isTaskMarker) {
      map.removeLayer(marker);
    }
  });
  markers = markers.filter((marker) => !marker.options.isTaskMarker);
}

function plotTasksOnMap(tasks) {
  clearMapMarkers();

  if (!tasks.length) return;

  let validLocations = [];
  let processedTasks = 0;

  // Function to process each task address
  tasks.forEach((task, index) => {
    // Use a delay to avoid overwhelming the geocoding service
    setTimeout(() => {
      geocodeAddress(task.address).then((coords) => {
        processedTasks++;

        if (coords) {
          validLocations.push({
            ...coords,
            task: task,
          });

          // Create marker with status-based color
          const markerColor = getMarkerColor(task.status);
          const marker = L.marker([coords.lat, coords.lon], {
            isTaskMarker: true,
          }).addTo(map);

          // Create popup with task details
          const popupContent = `
                        <div>
                            <strong>${task.address}</strong><br>
                            <strong>User:</strong> ${task.user_name}<br>
                            <strong>Phone:</strong> ${task.user_phone}<br>
                            <strong>Status:</strong> <span style="color: ${markerColor}">${task.status}</span><br>
                            <strong>Scheduled:</strong> ${task.scheduled_time}
                        </div>
                    `;
          marker.bindPopup(popupContent);
          markers.push(marker);
        }

        // If all tasks processed, create route
        if (processedTasks === tasks.length && validLocations.length > 1) {
          createRoute(validLocations);
        }
      });
    }, index * 200); // 200ms delay between requests
  });
}

function geocodeAddress(address) {
  return fetch(
    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
      address + ", Nairobi, Kenya"
    )}`
  )
    .then((res) => res.json())
    .then((data) => {
      if (data && data[0]) {
        return {
          lat: parseFloat(data[0].lat),
          lon: parseFloat(data[0].lon),
        };
      }
      return null;
    })
    .catch((error) => {
      console.error("Geocoding error for address:", address, error);
      return null;
    });
}

function getMarkerColor(status) {
  switch (status) {
    case "Assigned":
      return "#007bff";
    case "In Progress":
      return "#ffc107";
    case "Completed":
      return "#28a745";
    case "Missed":
      return "#dc3545";
    default:
      return "#6c757d";
  }
}

function createRoute(locations) {
  if (locations.length < 2) return;

  // Create a simple polyline connecting all task locations
  const latLngs = locations.map((loc) => [loc.lat, loc.lon]);

  // Add driver starting location at the beginning
  latLngs.unshift([-1.2921, 36.8219]);

  const polyline = L.polyline(latLngs, {
    color: "#007bff",
    weight: 3,
    opacity: 0.7,
    dashArray: "10, 10",
  }).addTo(map);

  // Fit map bounds to show all locations
  const group = new L.featureGroup([polyline, ...markers]);
  map.fitBounds(group.getBounds().pad(0.1));

  // Add route info
  const routeInfo = `
        <div style="background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <strong>📍 Route Overview</strong><br>
            <strong>Total Stops:</strong> ${locations.length}<br>
            <strong>Route Type:</strong> Optimized Collection Route
        </div>
    `;

  // Add route info control to map
  const routeControl = L.control({ position: "topright" });
  routeControl.onAdd = function (map) {
    const div = L.DomUtil.create("div", "route-info");
    div.innerHTML = routeInfo;
    return div;
  };
  routeControl.addTo(map);
}

// Notification Modal Functions
let currentTaskData = {};

window.openNotificationModal = function (
  requestId,
  userPhone,
  userName,
  address
) {
  console.log("🔔 Opening notification modal for:", {
    requestId,
    userPhone,
    userName,
    address,
  });

  // Add visual debugging
  try {
    currentTaskData = {
      requestId: requestId,
      userPhone: userPhone,
      userName: userName,
      address: address,
    };

    // Check if modal exists
    const modal = document.getElementById("notificationModal");
    if (!modal) {
      console.error("❌ Modal element not found");
      alert("❌ Error: Notification modal not found. Please refresh the page.");
      return;
    }
    console.log("✅ Modal element found");

    // Populate modal with user info with error checking
    const userNameEl = document.getElementById("modalUserName");
    const userPhoneEl = document.getElementById("modalUserPhone");
    const userAddressEl = document.getElementById("modalUserAddress");

    if (userNameEl) {
      userNameEl.textContent = userName || "Unknown User";
      console.log("✅ User name set:", userName);
    } else {
      console.error("❌ modalUserName element not found");
    }

    if (userPhoneEl) {
      userPhoneEl.textContent = userPhone || "No phone number";
      console.log("✅ User phone set:", userPhone);
    } else {
      console.error("❌ modalUserPhone element not found");
    }

    if (userAddressEl) {
      userAddressEl.textContent = address || "No address";
      console.log("✅ User address set:", address);
    } else {
      console.error("❌ modalUserAddress element not found");
    }

    // Clear previous message
    const messageTextarea = document.getElementById("notificationMessage");
    if (messageTextarea) {
      messageTextarea.value = "";
      updateCharacterCount();
      console.log("✅ Message textarea cleared");
    } else {
      console.error("❌ notificationMessage textarea not found");
    }

    // Clear template selection
    const templateButtons = document.querySelectorAll(".template-btn");
    console.log(`Found ${templateButtons.length} template buttons`);
    templateButtons.forEach((btn) => {
      btn.classList.remove("selected");
    });

    // Show modal
    modal.style.display = "block";
    document.body.style.overflow = "hidden";

    console.log("✅ Modal opened successfully");

    // Visual confirmation
    setTimeout(() => {
      if (modal.style.display === "block") {
        console.log("✅ Modal is visible after 100ms");
      } else {
        console.error("❌ Modal not visible after 100ms");
      }
    }, 100);
  } catch (error) {
    console.error("❌ Error in openNotificationModal:", error);
    alert("❌ Error opening notification modal: " + error.message);
  }
};

window.closeNotificationModal = function () {
  document.getElementById("notificationModal").style.display = "none";
  document.body.style.overflow = "auto";
  currentTaskData = {};
};

window.selectTemplate = function (templateType) {
  // Clear previous selection
  document.querySelectorAll(".template-btn").forEach((btn) => {
    btn.classList.remove("selected");
  });

  // Mark current template as selected
  event.target.classList.add("selected");

  const templates = {
    missed: `Dear ${currentTaskData.userName},\n\nWe apologize, but we missed your scheduled waste collection at ${currentTaskData.address} today. We will reschedule your collection for the next available slot.\n\nThank you for your patience.\n- DRMS Team`,

    delayed: `Dear ${currentTaskData.userName},\n\nYour waste collection at ${currentTaskData.address} has been delayed due to unforeseen circumstances. We expect to arrive within the next 2-3 hours.\n\nThank you for your patience.\n- DRMS Team`,

    rescheduled: `Dear ${currentTaskData.userName},\n\nYour waste collection at ${currentTaskData.address} has been rescheduled to a new date. You will receive confirmation of the new schedule shortly.\n\nThank you for understanding.\n- DRMS Team`,

    completed: `Dear ${currentTaskData.userName},\n\nYour waste collection at ${currentTaskData.address} has been completed successfully. Thank you for using DRMS services!\n\nHave a great day!\n- DRMS Team`,

    issue: `Dear ${currentTaskData.userName},\n\nWe encountered an issue during your scheduled collection at ${currentTaskData.address}. Our team will contact you shortly to resolve this matter.\n\nThank you for your patience.\n- DRMS Team`,

    custom: "",
  };

  const messageTextarea = document.getElementById("notificationMessage");
  messageTextarea.value = templates[templateType];
  updateCharacterCount();

  if (templateType === "custom") {
    messageTextarea.focus();
  }
};

function updateCharacterCount() {
  const textarea = document.getElementById("notificationMessage");
  const charCount = document.getElementById("charCount");

  if (!textarea || !charCount) {
    console.warn("Character count elements not found");
    return;
  }

  const count = textarea.value.length;
  charCount.textContent = count;

  if (count > 200) {
    charCount.parentElement.classList.add("over-limit");
  } else {
    charCount.parentElement.classList.remove("over-limit");
  }
}

// Add event listener for character count
document.addEventListener("DOMContentLoaded", function () {
  const messageTextarea = document.getElementById("notificationMessage");
  if (messageTextarea) {
    messageTextarea.addEventListener("input", updateCharacterCount);
  }
});

window.sendCustomNotification = function () {
  const message = document.getElementById("notificationMessage").value.trim();
  const sendSMS = document.getElementById("sendSMS").checked;
  const sendInApp = document.getElementById("sendInApp").checked;

  if (!message) {
    alert("❌ Please enter a message or select a template.");
    return;
  }

  if (!sendSMS && !sendInApp) {
    alert("❌ Please select at least one notification method.");
    return;
  }

  if (message.length > 200 && sendSMS) {
    if (
      !confirm(
        "⚠️ Message is longer than 200 characters. SMS may be split into multiple messages. Continue?"
      )
    ) {
      return;
    }
  }

  console.log("Sending notification:", {
    message,
    sendSMS,
    sendInApp,
    currentTaskData,
  });

  // Show loading state
  const sendButton = document.querySelector(".modal-footer .btn-primary");
  if (!sendButton) {
    alert("❌ Error: Send button not found");
    return;
  }
  const originalText = sendButton.innerHTML;
  sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
  sendButton.disabled = true;

  const promises = [];
  let expectedCount = 0;

  // Send in-app notification
  if (sendInApp) {
    expectedCount++;
    promises.push(
      fetch(getApiPath("send_notification.php"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          request_id: currentTaskData.requestId,
          type: "custom",
          message: message,
        }),
      })
        .then((response) => {
          console.log("In-app notification response status:", response.status);
          return response.json();
        })
        .then((result) => {
          console.log("In-app notification result:", result);
          result.type = "in-app";
          return result;
        })
        .catch((error) => {
          console.error("In-app notification error:", error);
          return { success: false, error: error.message, type: "in-app" };
        })
    );
  }

  // Send SMS
  if (
    sendSMS &&
    currentTaskData.userPhone &&
    currentTaskData.userPhone !== "null"
  ) {
    expectedCount++;
    promises.push(
      fetch(getApiPath("send_sms.php"), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          phone: currentTaskData.userPhone,
          message: message,
        }),
      })
        .then((response) => {
          console.log("SMS response status:", response.status);
          return response.json();
        })
        .then((result) => {
          console.log("SMS result:", result);
          result.type = "sms";
          return result;
        })
        .catch((error) => {
          console.error("SMS error:", error);
          return { success: false, error: error.message, type: "sms" };
        })
    );
  } else if (sendSMS) {
    alert("⚠️ Cannot send SMS: No valid phone number available for this user.");
  }

  Promise.all(promises)
    .then((results) => {
      console.log("All results:", results);

      const successful = results.filter((r) => r.success);
      const failed = results.filter((r) => !r.success);

      let message = "";

      if (successful.length > 0) {
        const successTypes = successful
          .map((r) => (r.type === "sms" ? "SMS" : "In-App Notification"))
          .join(" & ");
        message += `✅ ${successTypes} sent successfully!\n`;
      }

      if (failed.length > 0) {
        const failedTypes = failed.map(
          (r) =>
            `${r.type === "sms" ? "SMS" : "In-App"}: ${
              r.error || "Unknown error"
            }`
        );
        message += `❌ Failed:\n${failedTypes.join("\n")}`;
      }

      if (successful.length === 0) {
        message = "❌ All notifications failed to send. Please try again.";
      }

      alert(message);

      if (successful.length > 0) {
        closeNotificationModal();
        fetchTasks(); // Refresh tasks
      }
    })
    .catch((error) => {
      console.error("Notification error:", error);
      alert("❌ Failed to send notification. Please try again.");
    })
    .finally(() => {
      // Restore button state
      sendButton.innerHTML = originalText;
      sendButton.disabled = false;
    });
};

// Close modal when clicking outside
window.addEventListener("click", function (event) {
  const modal = document.getElementById("notificationModal");
  if (event.target === modal) {
    closeNotificationModal();
  }
});

// Close modal with Escape key
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    closeNotificationModal();
  }
});
