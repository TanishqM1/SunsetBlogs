document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("form");

    if (form) {
        form.addEventListener("submit", function (event) {
            const firstName = document.getElementById("first-name").value.trim();
            const lastName = document.getElementById("last-name").value.trim();
            const question = document.getElementById("question").value.trim();

            if (!validateName(firstName)) {
                alert("First Name should only contain letters, numbers, and spaces.");
                event.preventDefault();
                return false;
            }

            if (!validateName(lastName)) {
                alert("Last Name should only contain letters, numbers, and spaces.");
                event.preventDefault();
                return false;
            }

            if (!validateQuestion(question)) {
                alert("Your question should only contain letters, numbers, spaces, and '@'.");
                event.preventDefault();
                return false;
            }

            return true;
        });
    }
});

/**
 * Validates name input (First Name & Last Name)
 * Only allows letters (A-Z, a-z), numbers (0-9), and spaces
 */
function validateName(input) {
    const regex = /^[a-zA-Z ]+$/;
    return regex.test(input);
}


