// Ensure form inputs are valid before submission
document.addEventListener("DOMContentLoaded", function () {
    let forms = document.querySelectorAll("form");

    forms.forEach((form) => {
        form.addEventListener("submit", function (event) {
            let inputs = form.querySelectorAll("input[required]");
            let isValid = true;

            inputs.forEach((input) => {
                if (input.value.trim() === "") {
                    isValid = false;
                    alert("Please fill out all required fields.");
                    event.preventDefault();
                    return false;
                }
            });

            return isValid;
        });
    });
});

// Confirm before deleting
function confirmDelete() {
    return confirm("Are you sure you want to delete this record?");
}
