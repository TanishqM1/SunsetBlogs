function changeField(fieldId, message) {
    let newValue = prompt(message);
    if (newValue && newValue.trim() !== "") {
        document.getElementById(fieldId).textContent = newValue.trim();
    }
}

// Automatic Timezone Toggle
document.addEventListener("DOMContentLoaded", function() {
    const timezoneToggle = document.getElementById("timezone-toggle");

    timezoneToggle.addEventListener("change", function() {
        if (this.checked) {
            alert("Automatic timezone enabled.");
        } else {
            alert("Automatic timezone disabled.");
        }
    });
});
