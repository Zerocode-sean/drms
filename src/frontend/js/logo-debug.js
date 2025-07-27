// Logo debugging and fallback script
document.addEventListener("DOMContentLoaded", function () {
  const logoImages = document.querySelectorAll('img[src*="logo.png"]');

  logoImages.forEach(function (img, index) {
    console.log(`Logo ${index + 1} found:`, img.src);

    // Add error handling
    img.onerror = function () {
      console.error(`Logo failed to load: ${this.src}`);

      // Try alternative paths
      const alternatives = [
        "/src/frontend/images/logo.png",
        "./src/frontend/images/logo.png",
        "../images/logo.png",
        "src/frontend/images/logo.png",
      ];

      let currentSrc = this.src;
      for (let alt of alternatives) {
        if (!currentSrc.includes(alt)) {
          console.log(`Trying alternative logo path: ${alt}`);
          this.src = alt;
          break;
        }
      }

      // If all else fails, show a placeholder
      if (this.src === currentSrc) {
        console.log("All logo paths failed, using placeholder");
        this.style.backgroundColor = "#007bff";
        this.style.color = "white";
        this.style.display = "flex";
        this.style.alignItems = "center";
        this.style.justifyContent = "center";
        this.style.fontSize = "14px";
        this.style.fontWeight = "bold";
        this.alt = "DRMS";

        // Create a text element as fallback
        const placeholder = document.createElement("div");
        placeholder.style.cssText = `
                    width: 80px; 
                    height: 80px; 
                    background: linear-gradient(135deg, #007bff, #0056b3); 
                    border-radius: 50%; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    color: white; 
                    font-weight: bold; 
                    font-size: 18px;
                    margin-bottom: 20px;
                `;
        placeholder.textContent = "DRMS";
        this.parentNode.replaceChild(placeholder, this);
      }
    };

    img.onload = function () {
      console.log(`Logo ${index + 1} loaded successfully:`, this.src);
    };

    // Test if image loads by checking naturalWidth
    if (img.complete && img.naturalWidth === 0) {
      img.onerror();
    }
  });

  // Also check favicon
  const favicon = document.querySelector('link[rel="icon"]');
  if (favicon) {
    console.log("Favicon found:", favicon.href);
  }
});
