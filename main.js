// Confirmation dialog for deletions
document.addEventListener("DOMContentLoaded", () => {
    const deleteBtns = document.querySelectorAll(".confirm-delete");
    deleteBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            if (!confirm("Are you sure you want to delete this record?")) {
                e.preventDefault();
            }
        });
    });
});
