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
});
document.addEventListener('click', (e) => {
    if (!e.target.classList.contains('toggle-read')) return;

    e.preventDefault();

    const el = e.target;

    const id = el.dataset.id;
    const action = el.dataset.action;
    const nonce = el.dataset.nonce;

    fetch(lags_admin_ajax.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'lags_toggle_read',
            id: id,
            toggle_action: action,
            nonce: nonce
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
          console.log(data);
            el.textContent = data.data.new_label;
            el.dataset.action = data.data.new_action;
            el.dataset.nonce = data.data.new_nonce;
            el.closest('tr').classList.toggle('lags-unread', !data.data.is_read);
            el.parentElement.previousElementSibling.innerHTML = data.data.is_read ? `<span style="color: green;">Read</span>` : `<strong style="color: #d63638;">Unread</strong>`;
            updateUnreadBubble(data.data.unread_count);
        } else {
            alert('Error updating');
        }
    });
});
function updateUnreadBubble(count) {
    const menuItem = document.querySelector('#toplevel_page_lags-messages');

    if (!menuItem) return;

    let bubble = menuItem.querySelector('.awaiting-mod');

    if (count > 0) {
        if (!bubble) {
            bubble = document.createElement('span');
            bubble.className = 'awaiting-mod';
            menuItem.querySelector('.wp-menu-name').appendChild(bubble);
        }

        bubble.textContent = count;
    } else {
        if (bubble) {
            bubble.remove();
        }
    }
}