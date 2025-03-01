document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('signup-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');

    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Password validation function
    function validatePassword(password) {
        // At least 8 characters
        if (password.length < 8) {
            return "Password must be at least 8 characters long";
        }
        
        // At least one capital letter
        if (!/[A-Z]/.test(password)) {
            return "Password must contain at least one capital letter";
        }
        
        // At least one special character
        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            return "Password must contain at least one special character";
        }
        
        return ""; // Empty string means validation passed
    }

    // Live validation for email
    emailInput.addEventListener('input', function() {
        if (!validateEmail(emailInput.value) && emailInput.value !== "") {
            emailError.textContent = "Please enter a valid email address";
            emailInput.classList.add('invalid');
        } else {
            emailError.textContent = "";
            emailInput.classList.remove('invalid');
        }
    });

    // Live validation for password
    passwordInput.addEventListener('input', function() {
        const errorMessage = validatePassword(passwordInput.value);
        if (errorMessage && passwordInput.value !== "") {
            passwordError.textContent = errorMessage;
            passwordInput.classList.add('invalid');
        } else {
            passwordError.textContent = "";
            passwordInput.classList.remove('invalid');
        }
    });

    // Form submission
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Validate email
        if (!validateEmail(emailInput.value)) {
            emailError.textContent = "Please enter a valid email address";
            emailInput.classList.add('invalid');
            isValid = false;
        } else {
            emailError.textContent = "";
            emailInput.classList.remove('invalid');
        }
        
        // Validate password
        const passwordErrorMsg = validatePassword(passwordInput.value);
        if (passwordErrorMsg) {
            passwordError.textContent = passwordErrorMsg;
            passwordInput.classList.add('invalid');
            isValid = false;
        } else {
            passwordError.textContent = "";
            passwordInput.classList.remove('invalid');
        }
        
        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        } else {
            alert("Sign up successful!"); // This would normally be removed in production
        }
    });
});