document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.signup-form');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordRequirements = document.querySelector('.password-requirements');

    function validatePassword(password) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        const requirements = [];
        
        if (password.length < minLength) {
            requirements.push('at least 8 characters');
        }
        if (!hasUpperCase) {
            requirements.push('an uppercase letter');
        }
        if (!hasLowerCase) {
            requirements.push('a lowercase letter');
        }
        if (!hasNumbers) {
            requirements.push('a number');
        }
        if (!hasSpecialChar) {
            requirements.push('a special character');
        }

        return {
            isValid: requirements.length === 0,
            requirements: requirements
        };
    }

    function updatePasswordFeedback() {
        const password = passwordInput.value;
        const validation = validatePassword(password);
        
        if (!validation.isValid) {
            passwordRequirements.style.color = 'var(--primary-color)';
            passwordRequirements.innerHTML = 'Password must include: ' + validation.requirements.join(', ');
            passwordInput.setCustomValidity('Password does not meet requirements');
        } else {
            passwordRequirements.style.color = '#4CAF50';
            passwordRequirements.innerHTML = '✓ Password meets all requirements';
            passwordInput.setCustomValidity('');
        }
    }

    function validatePasswordMatch() {
        if (confirmPasswordInput.value !== passwordInput.value) {
            confirmPasswordInput.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }

    passwordInput.addEventListener('input', updatePasswordFeedback);
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const validation = validatePassword(password);
        
        if (!validation.isValid) {
            e.preventDefault();
            updatePasswordFeedback();
        }
        
        if (confirmPasswordInput.value !== password) {
            e.preventDefault();
            validatePasswordMatch();
        }
    });
}); 