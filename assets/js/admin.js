document.addEventListener("DOMContentLoaded", () => {
  const selectAll = document.querySelector("#select-all");
  const bulkActionForm = document.querySelector("#lcf-messages-form");

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
