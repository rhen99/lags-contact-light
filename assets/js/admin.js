document.addEventListener("DOMContentLoaded", () => {
  const selectAll = document.querySelector("#select-all");
  const bulkActionForm = document.querySelector("#lags-messages-form");

  bulkActionForm?.addEventListener("submit", (event) => {
    const selectedAction = document.querySelector("[name='bulk_action']").value;
    if (selectedAction === "delete") {
      const confirmed = confirm(
        "Are you sure you want to delete the selected messages?",
      );
      if (!confirmed) {
        event.preventDefault();
      }
    }
  });
  selectAll.addEventListener("change", () => {
    const checkboxes = document.querySelectorAll("input[name='ids[]']");
    checkboxes.forEach((cb) => (cb.checked = selectAll.checked));
  });
  document.addEventListener("click", (e) => {
    if (!e.target.classList.contains("toggle-read")) return;

    e.preventDefault();

    const el = e.target;

    const id = el.dataset.id;
    const action = el.dataset.action;
    const nonce = el.dataset.nonce;

    fetch(lags_admin_ajax.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "lags_toggle_read",
        id: id,
        toggle_action: action,
        nonce: nonce,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          console.log(data);
          el.textContent = data.data.new_label;
          el.dataset.action = data.data.new_action;
          el.dataset.nonce = data.data.new_nonce;
          el.closest("tr").classList.toggle("lags-unread", !data.data.is_read);
          el.parentElement.previousElementSibling.innerHTML = data.data.is_read
            ? `<span style="color: green;">Read</span>`
            : `<strong style="color: #d63638;">Unread</strong>`;
          updateUnreadBubble(data.data.unread_count);
        } else {
          alert("Error updating");
        }
      });
  });
  document.addEventListener("click", function (e) {
    // OPEN MODAL
    if (e.target.classList.contains("view-message")) {
      e.preventDefault();

      const el = e.target;

      // Fill modal
      document.getElementById("modal-name").textContent = el.dataset.name;
      document.getElementById("modal-email").textContent = el.dataset.email;
      document.getElementById("modal-message").textContent = el.dataset.message;

      document.getElementById("message-modal").style.display = "block";

      // 🔥 Mark as read if unread
      if (el.dataset.isRead === "0") {
        markAsRead(el);
      }
    }

    // CLOSE MODAL
    if (e.target.id === "close-modal") {
      document.getElementById("message-modal").style.display = "none";
    }
  });
});

function updateUnreadBubble(count) {
  const menuItem = document.querySelector("#toplevel_page_lags-messages");

  if (!menuItem) return;

  let bubble = menuItem.querySelector(".awaiting-mod");

  if (count > 0) {
    if (!bubble) {
      bubble = document.createElement("span");
      bubble.className = "awaiting-mod";
      menuItem.querySelector(".wp-menu-name").appendChild(bubble);
    }

    bubble.textContent = count;
  } else {
    if (bubble) {
      bubble.remove();
    }
  }
}
function markAsRead(el) {
  fetch(lags_admin_ajax.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      action: "lags_toggle_read",
      id: el.dataset.id,
      toggle_action: "mark_read",
      nonce: el.dataset.nonce,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // Update toggle button in row
        const row = el.closest("tr");
        const toggleBtn = row.querySelector(".toggle-read");

        if (toggleBtn) {
          toggleBtn.textContent = data.data.new_label;
          toggleBtn.dataset.action = data.data.new_action;
          toggleBtn.dataset.nonce = data.data.new_nonce;
        }

        // Update unread bubble
        updateUnreadBubble(data.data.unread_count);

        // Mark row as read
        row.classList.add("is-read");

        // Update dataset so it doesn't fire again
        el.dataset.isRead = "1";
      }
    });
}
