document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (event) {
            // Your validation code here
            const firstName = document.getElementById("first-name").value;
            const lastName = document.getElementById("last-name").value;
            const question = document.getElementById("question").value;

            if (!validateInput(firstName)) {
                alert("First Name should only contain letters, numbers, and the symbol '@'.");
                event.preventDefault();
                return false;
            }

            if (!validateInput(lastName)) {
                alert("Last Name should only contain letters, numbers, and the symbol '@'.");
                event.preventDefault();
                return false;
            }

            if (!validateInput(question)) {
                alert("Question should only contain letters, numbers, and the symbol '@'.");
                event.preventDefault();
                return false;
            }

            return true;
        });
    }
});
