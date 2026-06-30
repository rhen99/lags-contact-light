document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const messageBox = document.querySelector(".form-message");
  const submitButton = form.querySelector("button[type='submit']");
  const errorMessages = form.querySelectorAll(".error-message");
  const inputs = form.querySelectorAll("input, textarea");
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    errorMessages.forEach((errorMessage) => {
      errorMessage.textContent = "";
    });
    inputs.forEach((el) => {
      el.classList.remove("error");
    });
    inputs.forEach((input) => {
      input.addEventListener("input", () => {
        const errorEl = document.querySelector(`[data-field="${input.name}"]`);
        if (errorEl) errorEl.textContent = "";
        input.classList.remove("error");
      });
    });

    const formData = new FormData(form);
    formData.append("action", "lcf_submit_form");

    submitButton.disabled = true;
    submitButton.textContent = "Sending...";
    submitButton.classList.add("loading");
    fetch(lcf_ajax.ajax_url, {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          messageBox.textContent = data.data.message;
          messageBox.classList.add("success");
          form.reset();
        } else {
          if (data.data.errors) {
            for (const field in data.data.errors) {
              const errorEl = document.querySelector(`[data-field="${field}"]`);
              const inputEl = document.querySelector(`[name="${field}"]`);

              if (errorEl) errorEl.textContent = data.data.errors[field];
              if (inputEl) inputEl.classList.add("error");
            }
          }
          if (data.data.message) {
            messageBox.textContent = data.data.message;
            messageBox.classList.add("error");
          }
        }
        submitButton.classList.remove("loading");
      })
      .catch(() => {
        messageBox.textContent = "Network error. Please try again.";
        messageBox.classList.add("error");
        submitButton.classList.remove("loading");
      })
      .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = "Send Message";
      });
  });
});
