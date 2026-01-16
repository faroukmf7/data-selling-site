// js/admin.js

document.addEventListener("DOMContentLoaded", function () {
  // Confirm before deleting
  const deleteButtons = document.querySelectorAll(".btn-danger");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (!confirm("Are you sure you want to delete this item?")) {
        e.preventDefault();
      }
    });
  });

  // Toggle network field
  const networkSelect = document.getElementById("network");
  if (networkSelect) {
    networkSelect.addEventListener("change", function () {
      const newNetworkField = document.getElementById("new_network");
      if (this.value === "NEW") {
        newNetworkField.style.display = "block";
        newNetworkField.required = true;
      } else {
        newNetworkField.style.display = "none";
        newNetworkField.required = false;
      }
    });
  }

  // Form validation
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      let valid = true;
      const requiredFields = this.querySelectorAll("[required]");

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          valid = false;
          field.style.borderColor = "red";
          field.focus();
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

  // Auto-hide messages
  const messages = document.querySelectorAll(
    ".flash-message, .error-message, .success-message"
  );
  messages.forEach((message) => {
    setTimeout(() => {
      message.style.opacity = "0";
      setTimeout(() => {
        message.style.display = "none";
      }, 500);
    }, 5000);
  });
});
