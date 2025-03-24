document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contact-form");
    const responseMessage = document.getElementById("response-message");

    form.addEventListener("submit", async function (event) {
        event.preventDefault();

        const firstName = document.getElementById("first-name").value.trim();
        const lastName = document.getElementById("last-name").value.trim();
        const question = document.getElementById("question").value.trim();

        if (!validateName(firstName)) {
            responseMessage.textContent = "First Name should only contain letters and spaces.";
            responseMessage.style.color = "red";
            return;
        }

        if (!validateName(lastName)) {
            responseMessage.textContent = "Last Name should only contain letters and spaces.";
            responseMessage.style.color = "red";
            return;
        }

        const formData = new FormData();
        formData.append("first-name", firstName);
        formData.append("last-name", lastName);
        formData.append("question", question);

        try {
            const response = await fetch("../Pages/contact.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                responseMessage.textContent = "Your inquiry has been submitted successfully!";
                responseMessage.style.color = "green";
                form.reset();
            } else {
                responseMessage.textContent = "Error: " + result.message;
                responseMessage.style.color = "red";
            }
        } catch (error) {
            responseMessage.textContent = "An error occurred while submitting your inquiry.";
            responseMessage.style.color = "red";
        }
    });
});

// Validation Function
function validateName(input) {
    const regex = /^[a-zA-Z ]+$/;
    return regex.test(input);
}
