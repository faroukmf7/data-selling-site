// js/script.js

document.addEventListener("DOMContentLoaded", function () {
  // Mobile menu toggle
  const menuToggle = document.getElementById("menuToggle");
  const navMenu = document.getElementById("navMenu");
  const body = document.body;

  if (menuToggle) {
    menuToggle.addEventListener("click", function () {
      navMenu.classList.toggle("active");
      body.classList.toggle("menu-open");
    });

    // Close button functionality
    const closeButton = navMenu.querySelector(".bx-x");
    if (closeButton) {
      closeButton.parentElement.addEventListener("click", function (e) {
        e.stopPropagation();
        navMenu.classList.remove("active");
        body.classList.remove("menu-open");
      });
    }

    // Close menu when a link is clicked
    const navLinks = navMenu.querySelectorAll("a");
    navLinks.forEach((link) => {
      link.addEventListener("click", function () {
        navMenu.classList.remove("active");
        body.classList.remove("menu-open");
      });
    });

    // Close menu when clicking outside
    document.addEventListener("click", function (event) {
      if (!event.target.closest(".header")) {
        navMenu.classList.remove("active");
        body.classList.remove("menu-open");
      }
    });

    // Close menu when clicking on the blur background
    document.addEventListener("click", function (event) {
      if (event.target === document.body && navMenu.classList.contains("active")) {
        navMenu.classList.remove("active");
        body.classList.remove("menu-open");
      }
    });
  }

  // Add to cart with AJAX
  const addToCartForms = document.querySelectorAll(".add-to-cart-form");

  addToCartForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch("api/add_to_cart.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert(data.message);
            // Update cart count if you have a cart indicator
            const cartIndicator = document.querySelector(".cart-indicator");
            if (cartIndicator) {
              cartIndicator.textContent = data.cart_count;
            }
          } else {
            alert(data.message);
            if (data.message.includes("login")) {
              window.location.href = "login.php";
            }
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred. Please try again.");
        });
    });
  });

  // Auto-hide flash messages after 5 seconds
  const flashMessages = document.querySelectorAll(".flash-message");
  flashMessages.forEach((message) => {
    setTimeout(() => {
      message.style.opacity = "0";
      setTimeout(() => {
        message.style.display = "none";
      }, 500);
    }, 5000);
  });

  // Form validation
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const requiredFields = this.querySelectorAll("[required]");
      let valid = true;

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          valid = false;
          field.style.borderColor = "red";
        } else {
          field.style.borderColor = "";
        }
      });

      if (!valid) {
        e.preventDefault();
        alert("Please fill in all required fields.");
      }
    });
  });
});
